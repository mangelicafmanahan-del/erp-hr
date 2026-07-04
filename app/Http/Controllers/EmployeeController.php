<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmploymentHistory;
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
            $query->where('employment_status', $status);
        }

        $employees = $query->orderBy('id')->paginate(10)->withQueryString();
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
        $employee->load(['department', 'employmentHistory.department', 'documents', 'userAccount']);

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

        $employee->update($validated);

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
        'email' => $employee->email,
        'phone_number' => $employee->phone_number,
        'gender' => $employee->gender,
        'date_of_birth' => optional($employee->date_of_birth)->format('M d, Y'),
        'current_address' => $employee->current_address,

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
