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
                            <button type="button" onclick="openEmployeePanel({{ $employee->id }})"
                                    class="text-blue-600 font-medium hover:underline text-left">
                                {{ $employee->full_name }}
                            </button>
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

    {{-- Slide-over panel: employee quick summary --}}
    <div id="employee-panel-overlay" onclick="closeEmployeePanel()"
         class="fixed inset-0 bg-black/30 hidden z-40"></div>

    <aside id="employee-panel"
           class="fixed top-0 right-0 h-full w-full sm:w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-200 ease-out z-50 overflow-y-auto">
        <div class="p-5 border-b flex justify-between items-center sticky top-0 bg-white">
            <h3 class="font-semibold text-gray-900">Employee Summary</h3>
            <button type="button" onclick="closeEmployeePanel()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div id="employee-panel-body" class="p-5 text-sm">
            <div class="text-gray-400">Loading...</div>
        </div>
    </aside>

    <script>
        function openEmployeePanel(id) {
            const overlay = document.getElementById('employee-panel-overlay');
            const panel = document.getElementById('employee-panel');
            const body = document.getElementById('employee-panel-body');

            overlay.classList.remove('hidden');
            panel.classList.remove('translate-x-full');
            body.innerHTML = '<div class="text-gray-400">Loading...</div>';

            fetch(`/employees/${id}/summary`)
                .then(res => {
                    if (!res.ok) throw new Error('Request failed');
                    return res.json();
                })
                .then(data => {
                    const historyHtml = data.employment_history.map(h => `
                        <div class="py-2 border-b last:border-0">
                            <div class="font-medium text-gray-800">${h.position ?? ''}</div>
                            <div class="text-xs text-gray-500">${h.company_name ?? ''}</div>
                            <div class="text-xs text-gray-400">${h.start ?? ''} &ndash; ${h.end ?? ''}</div>
                        </div>
                    `).join('') || '<p class="text-gray-400 text-xs">No history recorded.</p>';

                    const docsHtml = data.documents.map(d => `
                        <a href="${d.url}" target="_blank" class="block py-2 border-b last:border-0 text-blue-600 text-xs hover:underline">
                            ${d.document_type ?? ''} &middot; ${d.file_name ?? ''}
                        </a>
                    `).join('') || '<p class="text-gray-400 text-xs">No documents uploaded.</p>';

                    const initial = (data.full_name || '?').charAt(0);

                    body.innerHTML = `
                        <div class="flex items-center gap-3 mb-5">
                            <div class="h-12 w-12 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold text-lg">
                                ${initial}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">${data.full_name ?? ''}</div>
                                <div class="text-xs text-gray-500">${data.job_title ?? '\u2014'} &middot; ${data.department ?? '\u2014'}</div>
                                <div class="text-xs text-gray-400">${data.employee_number ?? ''}</div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <div class="text-xs font-semibold text-gray-500 mb-2 tracking-wide">PERSONAL SUMMARY</div>
                            <div class="space-y-1 text-xs text-gray-700">
                                <div>Email: ${data.email ?? '\u2014'}</div>
                                <div>Phone: ${data.phone_number ?? '\u2014'}</div>
                                <div>Gender: ${data.gender ?? '\u2014'}</div>
                                <div>Date of Birth: ${data.date_of_birth ?? '\u2014'}</div>
                                <div>Address: ${data.current_address ?? '\u2014'}</div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <div class="text-xs font-semibold text-gray-500 mb-2 tracking-wide">EMPLOYMENT HISTORY</div>
                            ${historyHtml}
                        </div>

                        <div class="mb-5">
                            <div class="text-xs font-semibold text-gray-500 mb-2 tracking-wide">DOCUMENTS</div>
                            ${docsHtml}
                        </div>

                        <a href="${data.profile_url}" class="block text-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                            View Full Profile
                        </a>
                    `;
                })
                .catch(() => {
                    body.innerHTML = '<p class="text-red-500 text-xs">Could not load this employee. Try again.</p>';
                });
        }

        function closeEmployeePanel() {
            document.getElementById('employee-panel-overlay').classList.add('hidden');
            document.getElementById('employee-panel').classList.add('translate-x-full');
        }
    </script>
@endsection
