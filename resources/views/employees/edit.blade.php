@extends('layouts.app')

@section('title', 'Edit Employee')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('employees.index') }}" class="hover:text-gray-600">Employee Records</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Edit Employee</span>
@endsection

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Employee</h1>
            <p class="text-gray-500">{{ $employee->employee_number }} &middot; {{ $employee->full_name }}</p>
        </div>
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

    <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Personal Information</h2>

            <div class="flex items-center gap-4 mb-4 pb-4 border-b">
                @if ($employee->profile_photo_path)
                    <img src="{{ asset('storage/' . $employee->profile_photo_path) }}" alt="{{ $employee->full_name }}"
                         class="h-16 w-16 rounded-full object-cover border">
                @else
                    <div class="h-16 w-16 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold text-xl">
                        {{ strtoupper(substr($employee->first_name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <label class="text-sm text-gray-600">Profile Photo (PNG/JPG, max 2MB)</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-sm mt-1">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                @php
                    $f = fn($field) => old($field, $employee->$field);
                @endphp
                <div>
                    <label class="text-sm text-gray-600">First Name *</label>
                    <input type="text" name="first_name" value="{{ $f('first_name') }}" required
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ $f('middle_name') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Last Name *</label>
                    <input type="text" name="last_name" value="{{ $f('last_name') }}" required
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Suffix</label>
                    <input type="text" name="suffix" value="{{ $f('suffix') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ optional($employee->date_of_birth)->format('Y-m-d') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Gender</label>
                    <select name="gender" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select</option>
                        <option value="Male" @selected($f('gender') === 'Male')>Male</option>
                        <option value="Female" @selected($f('gender') === 'Female')>Female</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Civil Status</label>
                    <select name="civil_status" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select</option>
                        @foreach (['Single', 'Married', 'Widowed', 'Separated'] as $status)
                            <option value="{{ $status }}" @selected($f('civil_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Nationality</label>
                    <input type="text" name="nationality" value="{{ $f('nationality') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Email Address *</label>
                    <input type="email" name="email" value="{{ $f('email') }}" required
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ $f('phone_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Alternate Number</label>
                    <input type="text" name="alternate_number" value="{{ $f('alternate_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm text-gray-600">Current Address</label>
                    <input type="text" name="current_address" value="{{ $f('current_address') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm text-gray-600">Permanent Address</label>
                    <input type="text" name="permanent_address" value="{{ $f('permanent_address') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div>
                    <label class="text-sm text-gray-600">SSS Number</label>
                    <input type="text" name="sss_number" value="{{ $f('sss_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">PhilHealth Number</label>
                    <input type="text" name="philhealth_number" value="{{ $f('philhealth_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Pag-IBIG Number</label>
                    <input type="text" name="pagibig_number" value="{{ $f('pagibig_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">TIN Number</label>
                    <input type="text" name="tin_number" value="{{ $f('tin_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" value="{{ $f('emergency_contact_name') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Relationship</label>
                    <input type="text" name="emergency_contact_relationship" value="{{ $f('emergency_contact_relationship') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Emergency Contact Number</label>
                    <input type="text" name="emergency_contact_number" value="{{ $f('emergency_contact_number') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
            </div>
        </section>

        <section class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Employment Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm text-gray-600">Department</label>
                    <select name="department_id" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected($f('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Job Title</label>
                    <input type="text" name="job_title" value="{{ $f('job_title') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
                <div>
                    <label class="text-sm text-gray-600">Contract Type</label>
                    <select name="contract_type" class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select</option>
                        @foreach (['Regular', 'Probationary', 'Contractual'] as $type)
                            <option value="{{ $type }}" @selected($f('contract_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Employment Status *</label>
                    <select name="employment_status" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="active" @selected($f('employment_status') === 'active')>Active</option>
                        <option value="on_leave" @selected($f('employment_status') === 'on_leave')>On Leave</option>
                        <option value="inactive" @selected($f('employment_status') === 'inactive')>Inactive</option>
                        <option value="terminated" @selected($f('employment_status') === 'terminated')>Terminated</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Hire Date</label>
                    <input type="date" name="hire_date" value="{{ optional($employee->hire_date)->format('Y-m-d') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                </div>
            </div>
        </section>

        {{-- Account & Roles - NEW on the Edit form. Previously this option only
             existed on Add Employee, which meant an employee converted from
             Recruitment (who lands here automatically) had no way to get a
             login account without a separate, undocumented step. --}}
        <section class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-1">Account &amp; Roles</h2>

            @if ($employee->userAccount)
                <p class="text-sm text-gray-500 mb-2">This employee already has a login account:</p>
                <div class="text-sm bg-gray-50 border rounded-md px-4 py-3">
                    {{ $employee->userAccount->email }} &middot; <span class="text-gray-500">{{ $employee->userAccount->role }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-2">To change their password or role, that's a separate step not yet built - contact your system administrator.</p>
            @else
                <p class="text-sm text-gray-500 mb-4">This employee doesn't have a login account yet.</p>

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
            @endif
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('employees.show', $employee) }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-md">
                Save Changes
            </button>
        </div>
    </form>
@endsection
