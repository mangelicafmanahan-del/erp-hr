@extends('layouts.app')

@section('title', 'My Profile')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">My Profile</span>
@endsection

@section('content')
    @php
        $statusColors = [
            'active' => 'bg-green-100 text-green-700',
            'on_leave' => 'bg-amber-100 text-amber-700',
            'inactive' => 'bg-gray-100 text-gray-600',
            'terminated' => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            {{ $employee->full_name }}
            <span class="text-xs px-2 py-1 rounded-full {{ $statusColors[$employee->employment_status] ?? 'bg-gray-100' }}">
                {{ ucfirst(str_replace('_', ' ', $employee->employment_status)) }}
            </span>
        </h1>
        <p class="text-gray-500">{{ $employee->job_title ?? 'No job title set' }} &middot; {{ $employee->department?->name ?? 'No department' }}</p>
        <p class="text-xs text-gray-400 mt-1">This is a read-only view. Contact HR to update any of this information.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white border rounded-lg p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Personal Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Employee ID</dt><dd>{{ $employee->employee_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $employee->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd>{{ $employee->phone_number ?? '\u2014' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Gender</dt><dd>{{ $employee->gender ?? '\u2014' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Civil Status</dt><dd>{{ $employee->civil_status ?? '\u2014' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Date of Birth</dt><dd>{{ optional($employee->date_of_birth)->format('M d, Y') ?? '\u2014' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Contract Type</dt><dd>{{ $employee->contract_type ?? '\u2014' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Hire Date</dt><dd>{{ optional($employee->hire_date)->format('M d, Y') ?? '\u2014' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white border rounded-lg p-6 lg:col-span-2">
            <h2 class="font-semibold text-gray-900 mb-4">Employment History</h2>
            @forelse ($employee->employmentHistory as $history)
                <div class="flex justify-between items-start py-3 {{ !$loop->last ? 'border-b' : '' }}">
                    <div>
                        <div class="font-medium text-gray-800">{{ $history->position }}</div>
                        <div class="text-sm text-gray-500">{{ $history->company_name ?? 'This company' }}</div>
                        @if ($history->change_reason)
                            <div class="text-xs text-gray-400 mt-1">{{ $history->change_reason }}</div>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500 text-right">
                        {{ $history->start_date->format('M Y') }} &ndash; {{ $history->end_date?->format('M Y') ?? 'Present' }}
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">No employment history recorded yet.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white border rounded-lg p-6 mt-6">
        <h2 class="font-semibold text-gray-900 mb-4">My Documents</h2>
        <div class="space-y-2">
            @forelse ($employee->documents as $doc)
                <div class="flex items-center justify-between text-sm border rounded-md px-4 py-2">
                    <div>
                        <span class="font-medium">{{ $doc->document_type }}</span>
                        <span class="text-gray-400"> &middot; {{ $doc->file_name }}</span>
                    </div>
                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-blue-600">Download</a>
                </div>
            @empty
                <p class="text-sm text-gray-400">No documents on file.</p>
            @endforelse
        </div>
    </div>
@endsection
