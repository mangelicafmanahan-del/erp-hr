<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmploymentHistory;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Employee Directory - list/search/filter (Image 3)
     */
    public function index(Request $request)
    {
        $query = Employee::with('department');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $like = "%{$search}%";
                $q->where('first_name', 'like', $like)
                  ->orWhere('last_name', 'like', $like)
                  ->orWhere('employee_number', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhereRaw("(first_name || ' ' || last_name) LIKE ?", [$like]);
            });
        }

        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        if ($status = $request->input('employment_status')) {
            if ($status === 'on_leave') {
                $query->where('employment_status', 'active')
                    ->whereHas('leaveRequests', function ($q) {
                        $q->where('status', 'approved')
                            ->whereDate('start_date', '<=', today())
                            ->whereDate('end_date', '>=', today());
                    });
            } elseif ($status === 'active') {
                $query->where('employment_status', 'active')
                    ->whereDoesntHave('leaveRequests', function ($q) {
                        $q->where('status', 'approved')
                            ->whereDate('start_date', '<=', today())
                            ->whereDate('end_date', '>=', today());
                    });
            } else {
                $query->where('employment_status', $status);
            }
        }

        // FIX: was orderBy('last_name') - regressed back to alphabetical
        // sorting during the auth/RBAC rebuild. Restoring the successive
        // employee_id ordering you asked for earlier.
        $employees = $query->orderBy('id')->paginate(10)->withQueryString();
        $employees->getCollection()->each(function ($employee) {
            $employee->setAttribute('current_work_status', $employee->current_work_status);
        });
        $departments = Department::orderBy('name')->get();

        return view('employees.index', compact('employees', 'departments'));
    }

    /**
     * Add Employee form (Image 4)
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $nextEmployeeNumber = $this->generateEmployeeNumber();

        return view('employees.create', compact('departments', 'nextEmployeeNumber'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEmployee($request);
        $validated['employee_number'] = $this->generateEmployeeNumber();

        // NEW: profile photo upload
        if ($request->hasFile('photo')) {
            $request->validate(['photo' => 'image|max:2048']); // 2MB max
            $validated['profile_photo_path'] = $request->file('photo')->store('employee-photos', 'public');
        }

        $employee = Employee::create($validated);

        // 1b - initial employment history entry
        EmploymentHistory::create([
            'employee_id' => $employee->id,
            'company_name' => null, // null = internal record at this company
            'position' => $employee->job_title,
            'department_id' => $employee->department_id,
            'start_date' => $employee->hire_date ?? now(),
            'change_reason' => 'New Hire',
        ]);

        // 1d - optional login account creation (Account & Roles section)
        if ($request->boolean('create_account')) {
            $request->validate([
                'account_email' => 'required|email|unique:users,email',
                'account_password' => 'required|min:8',
                'role' => 'required|in:admin,hr_manager,employee',
            ]);

            User::create([
                'employee_id' => $employee->id,
                'name' => $employee->full_name,
                'email' => $request->input('account_email'),
                'password' => Hash::make($request->input('account_password')),
                'role' => $request->input('role'),
            ]);
        }

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee added successfully.');
    }

    /**
     * Employee Profile view (replaces the inspo's slide-out panel)
     */
    public function show(Employee $employee)
    {
        $employee->load(['department', 'employmentHistory.department', 'documents', 'userAccount', 'payslips.payrollRun']);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $this->validateEmployee($request, $employee->id);

        // NEW: profile photo upload - replace the old file if a new one is given
        if ($request->hasFile('photo')) {
            $request->validate(['photo' => 'image|max:2048']); // 2MB max
            if ($employee->profile_photo_path) {
                Storage::disk('public')->delete($employee->profile_photo_path);
            }
            $validated['profile_photo_path'] = $request->file('photo')->store('employee-photos', 'public');
        }

        $employee->update($validated);

        // NEW: create a login account from the Edit page too - previously this
        // was only possible on the Add Employee form, which meant an employee
        // converted from Recruitment (who lands here automatically) had no way
        // to get an account without a separate, undocumented step.
        if (! $employee->userAccount && $request->boolean('create_account')) {
            $request->validate([
                'account_email' => 'required|email|unique:users,email',
                'account_password' => 'required|min:8',
                'role' => 'required|in:admin,hr_manager,employee',
            ]);

            User::create([
                'employee_id' => $employee->id,
                'name' => $employee->full_name,
                'email' => $request->input('account_email'),
                'password' => Hash::make($request->input('account_password')),
                'role' => $request->input('role'),
            ]);
        }

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete(); // soft delete - record is preserved, not erased

        return redirect()->route('employees.index')
            ->with('success', 'Employee removed.');
    }

    /**
     * Upload a document/certification/ID for an employee (1c)
     */
    public function storeDocument(Request $request, Employee $employee)
    {
        $request->validate([
            'document_type' => 'required|string|max:255',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png', // 5MB max
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);

        $path = $request->file('file')->store('employee-documents', 'public');

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => $request->input('document_type'),
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'issue_date' => $request->input('issue_date'),
            'expiry_date' => $request->input('expiry_date'),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function destroyDocument(EmployeeDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $employee = $document->employee;
        $document->delete();

        return redirect()->route('employees.show', $employee)->with('success', 'Document removed.');
    }

    /**
     * Self-service: an employee replaces their own profile photo.
     * Same validation/storage/old-file-cleanup as the HR-side store()/update(),
     * but the employee is resolved from the logged-in account, not a route
     * parameter — so there's nothing in the request that could target
     * someone else's record.
     */
    public function updateMyPhoto(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            abort(404, 'No employee record is linked to your account yet. Contact HR.');
        }

        $request->validate(['photo' => 'required|image|max:2048']); // 2MB max

        if ($employee->profile_photo_path) {
            Storage::disk('public')->delete($employee->profile_photo_path);
        }

        $employee->update([
            'profile_photo_path' => $request->file('photo')->store('employee-photos', 'public'),
        ]);

        return back()->with('success', 'Profile photo updated.');
    }
    /**
     * Same validation storage as the HR-side storeDocument(), but the
     * employee is resolved from the logged-in account rather than a route
     * parameter, so a user can never upload to someone else's file no
     * matter what's submitted in the request.
     */
    public function storeMyDocument(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            abort(404, 'No employee record is linked to your account yet. Contact HR.');
        }

        $request->validate([
            'document_type' => 'required|string|max:255',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png', // 5MB max
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);

        $path = $request->file('file')->store('employee-documents', 'public');

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => $request->input('document_type'),
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'issue_date' => $request->input('issue_date'),
            'expiry_date' => $request->input('expiry_date'),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    /**
     * Manually add an employment history entry (1b) - promotions, transfers,
     * or prior employment at other companies.
     */
    public function storeEmploymentHistory(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'position' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'change_reason' => 'nullable|string|max:255',
        ]);

        EmploymentHistory::create($validated + ['employee_id' => $employee->id]);

        return redirect()->route('employees.show', $employee)->with('success', 'Employment history entry added.');
    }

    public function destroyEmploymentHistory(EmploymentHistory $history)
    {
        $employee = $history->employee;
        $history->delete();

        return redirect()->route('employees.show', $employee)->with('success', 'Employment history entry removed.');
    }

    /**
     * Self-service: an employee chooses how future payroll payouts should be made.
     * Check is the default when no preference has been saved.
     */
    public function updatePaymentPreferences(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            abort(404, 'No employee record is linked to your account yet. Contact HR.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:Check,Bank Transfer,Cash',
            'bank_name' => 'nullable|required_if:payment_method,Bank Transfer|string|max:255',
            'bank_account_name' => 'nullable|required_if:payment_method,Bank Transfer|string|max:255',
            'bank_account_number' => 'nullable|required_if:payment_method,Bank Transfer|string|max:100',
        ]);

        if ($validated['payment_method'] !== 'Bank Transfer') {
            $validated['bank_name'] = null;
            $validated['bank_account_name'] = null;
            $validated['bank_account_number'] = null;
        }

        $employee->update($validated);

        return back()->with('success', 'Payment preferences updated successfully.');
    }

    /**
     * Self-service: an employee viewing their OWN profile (read-only).
     */
    public function myProfile()
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            abort(404, 'No employee record is linked to your account yet. Contact HR.');
        }

        $employee->load(['department', 'employmentHistory.department', 'documents', 'userAccount']);

        return view('employees.my-profile', compact('employee'));
    }

    /**
     * Lightweight JSON summary used by the Employee Directory slide-over panel.
     */
    public function summary(Employee $employee)
    {
        $employee->load([
            'department',
            'employmentHistory.department',
            'documents',
        ]);

        return response()->json([
            'id' => $employee->id,
            'employee_number' => $employee->employee_number,
            'full_name' => $employee->full_name,
            'job_title' => $employee->job_title,
            'department' => $employee->department?->name,
            'employment_status' => $employee->employment_status,
            'current_work_status' => $employee->current_work_status,
            'email' => $employee->email,
            'phone_number' => $employee->phone_number,
            'gender' => $employee->gender,
            'date_of_birth' => optional($employee->date_of_birth)->format('M d, Y'),
            'current_address' => $employee->current_address,
            // NEW: photo, so the slide-over panel can show it instead of just an initial
            'photo_url' => $employee->profile_photo_path ? Storage::url($employee->profile_photo_path) : null,

            'employment_history' => $employee->employmentHistory->map(function ($history) {
                return [
                    'position' => $history->position,
                    'company_name' => $history->company_name ?? 'This Company',
                    'start' => optional($history->start_date)->format('M Y'),
                    'end' => optional($history->end_date)->format('M Y') ?? 'Present',
                ];
            })->values(),

            'documents' => $employee->documents->map(function ($document) {
                return [
                    'document_type' => $document->document_type,
                    'file_name' => $document->file_name,
                    'url' => Storage::url($document->file_path),
                ];
            })->values(),

            'profile_url' => route('employees.show', $employee),
        ]);
    }

    // ----- Helpers -----

    private function validateEmployee(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'civil_status' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'email' => ['required', 'email', Rule::unique('employees', 'email')->ignore($ignoreId)],
            'phone_number' => 'nullable|string|max:30',
            'alternate_number' => 'nullable|string|max:30',
            'current_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'sss_number' => 'nullable|string|max:50',
            'philhealth_number' => 'nullable|string|max:50',
            'pagibig_number' => 'nullable|string|max:50',
            'tin_number' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_number' => 'nullable|string|max:30',
            'job_title' => 'nullable|string|max:255',
            'contract_type' => 'nullable|in:Regular,Probationary,Contractual',
            'employment_status' => 'required|in:active,on_leave,inactive,terminated',
            'hire_date' => 'nullable|date',
        ]);
    }

    private function generateEmployeeNumber(): string
    {
        $last = Employee::orderByDesc('id')->first();
        $nextId = $last ? $last->id + 1 : 1;

        return 'EMP-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}
