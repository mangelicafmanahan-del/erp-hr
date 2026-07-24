<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private const WORKDAY_START = '09:00:00'; // clocking in after this counts as Late
    private const WORKDAY_END = '17:00:00'; // clocking out after this counts toward Overtime

    /**
     * Attendance Log (4a) - shows every active employee's status for a chosen date
     */
    public function log(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $user = auth()->user();

        $query = Employee::where('employment_status', 'active')
            ->with(['attendanceRecords' => fn ($q) => $q->where('work_date', $date)]);

        // Self-service scoping: an employee only ever sees their own row here
        if ($user->role === 'employee') {
            $query->where('id', $user->employee_id);
        }

        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        if ($search = $request->input('search')) {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like);
            });
        }

        $employees = $query->orderBy('last_name')->paginate(10)->withQueryString();
        $departments = Department::orderBy('name')->get();

        // HR/Admin accounts are also employees. Give every linked account a
        // personal attendance card while preserving team-wide visibility for HR.
        $myEmployee = $user->employee;
        $myAttendance = $myEmployee
            ? AttendanceRecord::where('employee_id', $myEmployee->id)
                ->where('work_date', $date)
                ->first()
            : null;

        // Monthly summary for the selected date's month (4d)
        $monthStart = Carbon::parse($date)->startOfMonth();
        $monthEnd = Carbon::parse($date)->endOfMonth();
        $monthQuery = AttendanceRecord::whereBetween('work_date', [$monthStart, $monthEnd]);
        if ($user->role === 'employee') {
            $monthQuery->where('employee_id', $user->employee_id);
        }
        $monthRecords = $monthQuery->get();

        $summary = [
            'present' => $monthRecords->where('status', 'present')->count(),
            'late' => $monthRecords->where('status', 'late')->count(),
            'absent' => $monthRecords->where('status', 'absent')->count(),
            'on_leave' => $monthRecords->where('status', 'on_leave')->count(),
            'total_overtime' => $monthRecords->sum('overtime_hours'),
        ];

        return view('attendance.log', compact('employees', 'departments', 'date', 'summary', 'myEmployee', 'myAttendance'));
    }

    /**
     * Clock in (4a) - digital substitute for biometric hardware
     */
    public function clockIn(Employee $employee)
    {
        $user = auth()->user();
        if ($user->role === 'employee' && $employee->id !== $user->employee_id) {
            abort(403, 'You can only clock yourself in.');
        }

        $today = now()->format('Y-m-d');
        $now = now();
        $isLate = $now->format('H:i:s') > self::WORKDAY_START;

        AttendanceRecord::updateOrCreate(
            ['employee_id' => $employee->id, 'work_date' => $today],
            [
                'time_in' => $now->format('H:i:s'),
                'status' => $isLate ? 'late' : 'present',
                'late_minutes' => $isLate ? (int) round($now->diffInMinutes(Carbon::parse($today . ' ' . self::WORKDAY_START), true)) : 0,
            ]
        );

        return back()->with('success', "{$employee->full_name} clocked in at " . $now->format('g:i A') . '.');
    }

    /**
     * Clock out (4a) - computes hours worked and overtime
     */
    public function clockOut(Employee $employee)
    {
        $user = auth()->user();
        if ($user->role === 'employee' && $employee->id !== $user->employee_id) {
            abort(403, 'You can only clock yourself out.');
        }

        $today = now()->format('Y-m-d');
        $record = AttendanceRecord::where('employee_id', $employee->id)->where('work_date', $today)->first();

        if (! $record || ! $record->time_in) {
            return back()->with('success', "{$employee->full_name} hasn't clocked in yet today.");
        }

        $now = now();
        $timeIn = Carbon::parse($today . ' ' . $record->time_in);
        $hoursWorked = round($timeIn->diffInMinutes($now, true) / 60, 2);

        // Overtime = time actually worked past the 5PM shift end, not just
        // "total hours over 8" - someone who clocks in late (say 11AM) and
        // leaves at 7PM has worked 8 hours but 2 of them were past the shift,
        // and that's what should be paid as overtime.
        $workdayEnd = Carbon::parse($today . ' ' . self::WORKDAY_END);
        $overtime = $now->greaterThan($workdayEnd)
            ? round($workdayEnd->diffInMinutes($now, true) / 60, 2)
            : 0;

        $record->update([
            'time_out' => $now->format('H:i:s'),
            'hours_worked' => $hoursWorked,
            'overtime_hours' => $overtime,
        ]);

        return back()->with('success', "{$employee->full_name} clocked out at " . $now->format('g:i A') . '.');
    }

    /**
     * Leave Request & Approval (4b, 4c)
     */
    public function leaveIndex(Request $request)
    {
        $user = auth()->user();
        $employees = Employee::where('employment_status', 'active')->orderBy('last_name')->get();
        $leaveTypes = LeaveType::orderBy('name')->get();

        $query = LeaveRequest::with(['employee', 'leaveType']);
        if ($user->role === 'employee') {
            $query->where('employee_id', $user->employee_id);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        $requests = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $currentYear = now()->year;
        $balanceQuery = LeaveBalance::with(['employee', 'leaveType'])->where('year', $currentYear);
        if ($user->role === 'employee') {
            $balanceQuery->where('employee_id', $user->employee_id);
        }
        $balances = $balanceQuery->get()->sortBy(fn ($b) => $b->employee->full_name ?? '');

        $activeLeave = null;
        if ($user->role === 'employee' && $user->employee_id) {
            $activeLeave = LeaveRequest::with('leaveType')
                ->where('employee_id', $user->employee_id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->whereNull('returned_at')
                ->orderBy('end_date')
                ->first();
        }

        return view('attendance.leave', compact('employees', 'leaveTypes', 'requests', 'balances', 'activeLeave'));
    }

    public function storeLeaveRequest(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ];
        // HR can file on behalf of anyone; an employee can only file for themselves
        $rules['employee_id'] = $user->role === 'employee' ? 'nullable' : 'required|exists:employees,id';

        $validated = $request->validate($rules);

        // Force-override employee_id for self-service, ignoring anything submitted
        $validated['employee_id'] = $user->role === 'employee' ? $user->employee_id : $validated['employee_id'];

        $days = Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) + 1;
        $validated['days_requested'] = $days;
        $validated['status'] = 'pending';

        LeaveRequest::create($validated);

        return back()->with('success', 'Leave request filed.');
    }

    /**
     * Approve a leave request - deducts the balance and marks the days On Leave
     * in the attendance log (4c, and feeds 4a's status field for those dates).
     */
    public function approveLeaveRequest(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $year = $leaveRequest->start_date->year;
        $balance = $this->getOrCreateBalance($leaveRequest->employee_id, $leaveRequest->leave_type_id, $year);
        $balance->increment('used_days', $leaveRequest->days_requested);

        // Mark each day in the range as On Leave in the attendance log
        $period = Carbon::parse($leaveRequest->start_date)->daysUntil($leaveRequest->end_date);
        foreach ($period as $day) {
            AttendanceRecord::updateOrCreate(
                ['employee_id' => $leaveRequest->employee_id, 'work_date' => $day->format('Y-m-d')],
                ['status' => 'on_leave']
            );
        }

        return back()->with('success', 'Leave request approved.');
    }

    /**
     * Employee self-service: return early from an active approved leave.
     * The actual return is represented by today's attendance record.
     */
    public function returnFromLeave(LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        if ($user->role !== 'employee' || $leaveRequest->employee_id !== $user->employee_id) {
            abort(403, 'You can only return yourself from leave.');
        }

        $today = today();

        if ($leaveRequest->status !== 'approved'
            || $leaveRequest->returned_at !== null
            || $leaveRequest->start_date->isAfter($today)
            || $leaveRequest->end_date->isBefore($today)) {
            return back()->with('success', 'This leave request is no longer an active leave that can be returned from.');
        }

        DB::transaction(function () use ($leaveRequest, $today) {
            // Restore unused future leave days exactly once.
            $unusedDays = $today->diffInDays($leaveRequest->end_date);

            if ($unusedDays > 0) {
                $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', $leaveRequest->start_date->year)
                    ->lockForUpdate()
                    ->first();

                if ($balance) {
                    $balance->decrement('used_days', min($unusedDays, $balance->used_days));
                }

                AttendanceRecord::where('employee_id', $leaveRequest->employee_id)
                    ->whereDate('work_date', '>', $today)
                    ->whereDate('work_date', '<=', $leaveRequest->end_date)
                    ->where('status', 'on_leave')
                    ->delete();
            }

            // Permanently record the actual return so the same leave cannot
            // be returned from a second time.
            $leaveRequest->update([
                'returned_at' => now(),
            ]);

            // Today's record becomes the employee's return-to-work record.
            $now = now();
            $isLate = $now->format('H:i:s') > self::WORKDAY_START;

            AttendanceRecord::updateOrCreate(
                ['employee_id' => $leaveRequest->employee_id, 'work_date' => $today->format('Y-m-d')],
                [
                    'time_in' => $now->format('H:i:s'),
                    'status' => $isLate ? 'late' : 'present',
                    'late_minutes' => $isLate
                        ? (int) round($now->diffInMinutes(Carbon::parse($today->format('Y-m-d') . ' ' . self::WORKDAY_START), true))
                        : 0,
                ]
            );
        });

        return back()->with('success', 'You have been marked as back at work today.');
    }

    public function rejectLeaveRequest(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update(['status' => 'rejected']);

        return back()->with('success', 'Leave request rejected.');
    }

    /**
     * Lazily create a leave balance row the first time it's needed,
     * using the leave type's default allocation.
     */
    private function getOrCreateBalance(int $employeeId, int $leaveTypeId, int $year): LeaveBalance
    {
        $leaveType = LeaveType::findOrFail($leaveTypeId);

        return LeaveBalance::firstOrCreate(
            ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId, 'year' => $year],
            ['allocated_days' => $leaveType->default_days_per_year]
        );
    }
}
