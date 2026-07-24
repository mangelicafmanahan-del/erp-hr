<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\JobVacancy;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isHr = in_array($user->role, ['admin', 'hr_manager']);

        if (! $isHr) {
            $employee = $user->employee;
            $myAttendance = $employee
                ? AttendanceRecord::where('employee_id', $employee->id)
                    ->whereDate('work_date', now()->toDateString())
                    ->first()
                : null;

            $myLeaveRequests = $employee
                ? LeaveRequest::where('employee_id', $employee->id)
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get()
                : collect();

            $openVacancies = JobVacancy::with('department')
                ->where('status', 'open')
                ->where(function ($query) {
                    $query->whereNull('closing_date')
                        ->orWhereDate('closing_date', '>=', today());
                })
                ->orderByDesc('posted_date')
                ->take(5)
                ->get();

            $myApplications = $employee
                ? Applicant::where('employee_id', $employee->id)
                    ->with('jobVacancy')
                    ->orderByDesc('applied_at')
                    ->take(5)
                    ->get()
                : collect();

            return view('dashboard', compact('employee', 'myAttendance', 'myLeaveRequests', 'openVacancies', 'myApplications'));
        }

        $today = today();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $totalEmployees = Employee::where('employment_status', 'active')->count();
        $totalPayroll = Payslip::whereHas('payrollRun', function ($query) use ($monthStart, $monthEnd) {
            $query->whereBetween('period_end', [$monthStart->toDateString(), $monthEnd->toDateString()]);
        })->sum('net_pay');

        $presentToday = AttendanceRecord::whereDate('work_date', $today)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $onLeaveToday = AttendanceRecord::whereDate('work_date', $today)
            ->where('status', 'on_leave')
            ->count();

        $openPositions = JobVacancy::where('status', 'open')
            ->where(function ($query) {
                $query->whereNull('closing_date')
                    ->orWhereDate('closing_date', '>=', today());
            })->count();

        $departmentDistribution = Employee::where('employment_status', 'active')
            ->with('department')
            ->get()
            ->groupBy(fn ($employee) => $employee->department?->name ?? 'Unassigned')
            ->map(fn ($employees) => $employees->count())
            ->sortDesc();

        $payrollOverview = PayrollRun::withSum('payslips', 'net_pay')
            ->orderByDesc('period_end')
            ->take(6)
            ->get()
            ->reverse()
            ->values();

        $attendanceOverview = [
            'present' => AttendanceRecord::whereDate('work_date', $today)->where('status', 'present')->count(),
            'late' => AttendanceRecord::whereDate('work_date', $today)->where('status', 'late')->count(),
            'on_leave' => $onLeaveToday,
        ];

        $leaveRequests = LeaveRequest::with(['employee', 'leaveType'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $recruitmentPipeline = [
            'Applications' => Applicant::where('status', 'applied')->count(),
            'Screening' => Applicant::where('status', 'screening')->count(),
            'Interview' => Applicant::where('status', 'interview')->count(),
            'Offered' => Applicant::where('status', 'offered')->count(),
            'Hired' => Applicant::where('status', 'hired')->count(),
        ];

        $recentActivities = collect([
            ['label' => 'New employee records', 'count' => Employee::whereDate('created_at', $today)->count(), 'route' => 'employees.index'],
            ['label' => 'Leave requests submitted', 'count' => LeaveRequest::whereDate('created_at', $today)->count(), 'route' => 'attendance.leave'],
            ['label' => 'New job applications', 'count' => Applicant::whereDate('created_at', $today)->count(), 'route' => 'recruitment.applicants'],
            ['label' => 'Open job positions', 'count' => $openPositions, 'route' => 'recruitment.vacancies'],
        ]);

        return view('dashboard', compact(
            'totalEmployees', 'totalPayroll', 'presentToday', 'onLeaveToday', 'openPositions',
            'departmentDistribution', 'payrollOverview', 'attendanceOverview',
            'leaveRequests', 'recruitmentPipeline', 'recentActivities'
        ));
    }
}
