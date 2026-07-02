@extends('layouts.app')

@section('title', 'Employee Directory')
@section('breadcrumb', 'Home / Employee Records / Employee Directory')

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Employee Directory</h1>
            <p class="text-gray-500">Search, filter and manage your organization's employees.</p>
        </div>
        <a href="{{ route('employees.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
            + Add Employee
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('employees.index') }}" class="bg-white border rounded-lg p-4 mb-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name, employee ID or email..."
               class="flex-1 min-w-[220px] border rounded-md px-3 py-2 text-sm">

        <select name="department_id" onchange="this.form.submit()" class="border rounded-md px-3 py-2 text-sm">
            <option value="">All Departments</option>
            @foreach ($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>

        <select name="employment_status" onchange="this.form.submit()" class="border rounded-md px-3 py-2 text-sm">
            <option value="">All Employment Status</option>
            <option value="active" @selected(request('employment_status') === 'active')>Active</option>
            <option value="on_leave" @selected(request('employment_status') === 'on_leave')>On Leave</option>
            <option value="inactive" @selected(request('employment_status') === 'inactive')>Inactive</option>
            <option value="terminated" @selected(request('employment_status') === 'terminated')>Terminated</option>
        </select>

        <button type="submit" class="border rounded-md px-4 py-2 text-sm bg-gray-50 hover:bg-gray-100">Filter</button>
        <a href="{{ route('employees.index') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Reset</a>
    </form>

    {{-- Table --}}
    <div class="bg-white border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Employee ID</th>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">Department</th>
                    <th class="text-left px-4 py-3">Job Title</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Email</th>
                    <th class="text-left px-4 py-3">Phone</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($employees as $employee)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $employee->employee_number }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('employees.show', $employee) }}" class="text-blue-600 font-medium">
                                {{ $employee->full_name }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $employee->department?->name ?? '\u2014' }}</td>
                        <td class="px-4 py-3">{{ $employee->job_title ?? '\u2014' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-700',
                                    'on_leave' => 'bg-amber-100 text-amber-700',
                                    'inactive' => 'bg-gray-100 text-gray-600',
                                    'terminated' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="text-xs px-2 py-1 rounded-full {{ $statusColors[$employee->employment_status] ?? 'bg-gray-100' }}">
                                {{ ucfirst(str_replace('_', ' ', $employee->employment_status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $employee->email }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $employee->phone_number ?? '\u2014' }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('employees.edit', $employee) }}" class="text-gray-500 hover:text-blue-600">Edit</a>
                            <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Remove this employee record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-500 hover:text-red-600">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                            No employees found. <a href="{{ route('employees.create') }}" class="text-blue-600">Add your first employee</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $employees->links() }}
    </div>
@endsection
