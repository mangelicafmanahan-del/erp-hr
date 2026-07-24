@extends('layouts.app')

@section('title', 'Attendance Log')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Attendance and Leave</span>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Attendance Log</span>
@endsection

@section('content')
    @php
        $isHr = in_array(auth()->user()->role, ['admin', 'hr_manager']);
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Attendance Log</h1>
        <p class="text-gray-500">
            @if ($isHr)
                Manage team attendance while also recording your own daily attendance.
            @else
                Track your daily attendance, time in/out, and work hours.
            @endif
        </p>
    </div>

    @if ($myEmployee)
        <div class="bg-white border rounded-lg p-5 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">My Attendance Today</p>
                    <h2 class="text-lg font-semibold text-gray-900 mt-1">{{ $myEmployee->full_name }}</h2>
                    <p class="text-sm text-gray-500">
                        Clock in: {{ $myAttendance?->time_in ? \Carbon\Carbon::parse($myAttendance->time_in)->format('g:i A') : '—' }}
                        &middot;
                        Clock out: {{ $myAttendance?->time_out ? \Carbon\Carbon::parse($myAttendance->time_out)->format('g:i A') : '—' }}
                    </p>
                </div>
                <div>
                    @if (! $myAttendance || ! $myAttendance->time_in)
                        <form action="{{ route('attendance.clockin', $myEmployee) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-md">Clock In</button>
                        </form>
                    @elseif (! $myAttendance->time_out)
                        <form action="{{ route('attendance.clockout', $myEmployee) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-5 py-2 rounded-md">Clock Out</button>
                        </form>
                    @else
                        <span class="inline-flex bg-green-100 text-green-700 text-sm font-medium px-4 py-2 rounded-md">Completed for today</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg border p-4"><div class="text-xs text-gray-500">Present (this month)</div><div class="text-2xl font-bold text-green-600 mt-1">{{ $summary['present'] }}</div></div>
        <div class="bg-white rounded-lg border p-4"><div class="text-xs text-gray-500">Late (this month)</div><div class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['late'] }}</div></div>
        <div class="bg-white rounded-lg border p-4"><div class="text-xs text-gray-500">Absent (this month)</div><div class="text-2xl font-bold text-red-600 mt-1">{{ $summary['absent'] }}</div></div>
        <div class="bg-white rounded-lg border p-4"><div class="text-xs text-gray-500">On Leave (this month)</div><div class="text-2xl font-bold text-blue-600 mt-1">{{ $summary['on_leave'] }}</div></div>
        <div class="bg-white rounded-lg border p-4"><div class="text-xs text-gray-500">Total Overtime (hrs)</div><div class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($summary['total_overtime'], 1) }}</div></div>
    </div>

    <form method="GET" action="{{ route('attendance.log') }}" class="bg-white border rounded-lg p-4 mb-4 flex flex-wrap gap-3">
        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="border rounded-md px-3 py-2 text-sm">
        @if ($isHr)
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by employee name..." class="flex-1 min-w-[200px] border rounded-md px-3 py-2 text-sm">
            <select name="department_id" onchange="this.form.submit()" class="border rounded-md px-3 py-2 text-sm">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        @endif
        <a href="{{ route('attendance.log') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Reset</a>
    </form>

    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b"><h2 class="font-semibold text-gray-900">{{ $isHr ? 'Team Attendance' : 'My Attendance History' }}</h2></div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Employee</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Clock In</th>
                    <th class="text-left px-4 py-3">Clock Out</th>
                    <th class="text-left px-4 py-3">Hours Worked</th>
                    <th class="text-left px-4 py-3">Overtime</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($employees as $employee)
                    @php
                        $record = $employee->attendanceRecords->first();
                        $statusColors = ['present' => 'bg-green-100 text-green-700', 'late' => 'bg-amber-100 text-amber-700', 'absent' => 'bg-red-100 text-red-700', 'on_leave' => 'bg-blue-100 text-blue-700'];
                        $status = $record->status ?? 'absent';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><div class="font-medium text-gray-800">{{ $employee->full_name }}</div><div class="text-xs text-gray-400">{{ $employee->employee_number }}</div></td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full {{ $statusColors[$status] ?? 'bg-gray-100' }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span></td>
                        <td class="px-4 py-3">{{ $record?->time_in ? \Carbon\Carbon::parse($record->time_in)->format('g:i A') : '—' }}</td>
                        <td class="px-4 py-3">{{ $record?->time_out ? \Carbon\Carbon::parse($record->time_out)->format('g:i A') : '—' }}</td>
                        <td class="px-4 py-3">{{ $record ? number_format($record->hours_worked, 2) : '0.00' }}</td>
                        <td class="px-4 py-3">{{ $record ? number_format($record->overtime_hours, 2) : '0.00' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No active employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $employees->links() }}</div>
@endsection
