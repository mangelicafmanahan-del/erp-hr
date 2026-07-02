@extends('layouts.app')

@section('title', 'Add Employee')
@section('breadcrumb', 'Home / Employee Records / Add Employee')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Employee</h1>
            <p class="text-gray-500">Fill in the details below to add a new employee to the organization.</p>
        </div>
        <div class="text-sm text-gray-400">Employee ID (auto): <span class="font-medium text-gray-600">{{ $nextEmployeeNumber }}</span></div>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
            <div class="font-medium mb-1">Please fix the following:</div>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('employees.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Personal Information --}}
        <section class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Personal Information</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm text-gray-600">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Suffix</label>
                    <input type="text" name="suffix" value="{{ old('suffix') }}" placeholder="Jr., Sr., III"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Gender</label>
                    <select name="gender" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select</option>
                        <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Civil Status</label>
                    <select name="civil_status" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select</option>
                        @foreach (['Single', 'Married', 'Widowed', 'Separated'] as $status)
                            <option value="{{ $status }}" @selected(old('civil_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Alternate Number</label>
                    <input type="text" name="alternate_number" value="{{ old('alternate_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm text-gray-600">Current Address</label>
                    <input type="text" name="current_address" value="{{ old('current_address') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm text-gray-600">Permanent Address</label>
                    <input type="text" name="permanent_address" value="{{ old('permanent_address') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div>
                    <label class="text-sm text-gray-600">SSS Number</label>
                    <input type="text" name="sss_number" value="{{ old('sss_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">PhilHealth Number</label>
                    <input type="text" name="philhealth_number" value="{{ old('philhealth_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Pag-IBIG Number</label>
                    <input type="text" name="pagibig_number" value="{{ old('pagibig_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">TIN Number</label>
                    <input type="text" name="tin_number" value="{{ old('tin_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Relationship</label>
                    <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Emergency Contact Number</label>
                    <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
            </div>
        </section>

        {{-- Employment Details --}}
        <section class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Employment Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm text-gray-600">Department</label>
                    <select name="department_id" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Job Title</label>
                    <input type="text" name="job_title" value="{{ old('job_title') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Contract Type</label>
                    <select name="contract_type" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select</option>
                        @foreach (['Regular', 'Probationary', 'Contractual'] as $type)
                            <option value="{{ $type }}" @selected(old('contract_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Employment Status *</label>
                    <select name="employment_status" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="active" @selected(old('employment_status', 'active') === 'active')>Active</option>
                        <option value="on_leave" @selected(old('employment_status') === 'on_leave')>On Leave</option>
                        <option value="inactive" @selected(old('employment_status') === 'inactive')>Inactive</option>
                        <option value="terminated" @selected(old('employment_status') === 'terminated')>Terminated</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Hire Date</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3">
                Document upload and employment history are managed from the employee's profile page after saving.
            </p>
        </section>

        {{-- Account & Roles --}}
        <section class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-1">Account &amp; Roles</h2>
            <p class="text-sm text-gray-500 mb-4">Optionally create a system login for this employee (Function 1d - access control).</p>

            <label class="flex items-center gap-2 text-sm mb-4">
                <input type="checkbox" name="create_account" value="1" {{ old('create_account') ? 'checked' : '' }}
                       onchange="document.getElementById('account-fields').classList.toggle('hidden')">
                Create a login account for this employee
            </label>

            <div id="account-fields" class="grid grid-cols-1 sm:grid-cols-3 gap-4 {{ old('create_account') ? '' : 'hidden' }}">
                <div>
                    <label class="text-sm text-gray-600">Login Email</label>
                    <input type="email" name="account_email" value="{{ old('account_email') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Temporary Password</label>
                    <input type="password" name="account_password"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Role</label>
                    <select name="role" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="employee" @selected(old('role') === 'employee')>Employee</option>
                        <option value="hr_manager" @selected(old('role') === 'hr_manager')>HR Manager</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('employees.index') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-md">
                Save Employee
            </button>
        </div>
    </form>
@endsection
