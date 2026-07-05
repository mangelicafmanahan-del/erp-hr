@extends('layouts.app')

@section('title', 'Leave Requests')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Attendance and Leave</span>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Leave Requests</span>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Leave Request &amp; Approval</h1>
        <p class="text-gray-500">File leave requests and track approval status.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- File a new request --}}
        <div class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">New Leave Request</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('attendance.leave.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs text-gray-500">Employee *</label>
                    <select name="employee_id" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select Employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Leave Type *</label>
                    <select name="leave_type_id" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                        <option value="">Select Leave Type</option>
                        @foreach ($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->default_days_per_year }} days/yr)</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-500">Start Date *</label>
                        <input type="date" name="start_date" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">End Date *</label>
                        <input type="date" name="end_date" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Reason</label>
                    <textarea name="reason" rows="2" class="w-full border rounded-md px-3 py-2 text-sm mt-1"></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                    Submit Request
                </button>
            </form>
        </div>

        {{-- Requests table --}}
        <div class="lg:col-span-2 bg-white border rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Leave Requests</h2>
                <form method="GET" action="{{ route('attendance.leave') }}">
                    <select name="status" onchange="this.form.submit()" class="border rounded-md px-3 py-1.5 text-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>
                </form>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Employee</th>
                        <th class="text-left px-4 py-3">Leave Type</th>
                        <th class="text-left px-4 py-3">Dates</th>
                        <th class="text-left px-4 py-3">Days</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($requests as $request)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $request->employee->full_name }}</td>
                            <td class="px-4 py-3">{{ $request->leaveType->name }}</td>
                            <td class="px-4 py-3">{{ $request->start_date->format('M d') }} &ndash; {{ $request->end_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3">{{ $request->days_requested }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $reqColors = ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700'];
                                @endphp
                                <span class="text-xs px-2 py-1 rounded-full {{ $reqColors[$request->status] ?? 'bg-gray-100' }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($request->status === 'pending')
                                    <form action="{{ route('attendance.leave.approve', $request) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 text-xs mr-2">Approve</button>
                                    </form>
                                    <form action="{{ route('attendance.leave.reject', $request) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-red-600 text-xs">Reject</button>
                                    </form>
                                @else
                                    <span class="text-gray-300 text-xs">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No leave requests filed yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $requests->links() }}</div>
        </div>
    </div>

    {{-- Leave Balances --}}
    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b">
            <h2 class="font-semibold text-gray-900">Leave Balances ({{ now()->year }})</h2>
            <p class="text-xs text-gray-400">A balance appears here automatically the first time a leave request for that employee/type is approved.</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Employee</th>
                    <th class="text-left px-4 py-3">Leave Type</th>
                    <th class="text-left px-4 py-3">Allocated</th>
                    <th class="text-left px-4 py-3">Used</th>
                    <th class="text-left px-4 py-3">Remaining</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($balances as $balance)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $balance->employee->full_name }}</td>
                        <td class="px-4 py-3">{{ $balance->leaveType->name }}</td>
                        <td class="px-4 py-3">{{ $balance->allocated_days }}</td>
                        <td class="px-4 py-3">{{ $balance->used_days }}</td>
                        <td class="px-4 py-3 font-medium">{{ $balance->remaining_days }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No leave balances tracked yet - approve a request to start one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
