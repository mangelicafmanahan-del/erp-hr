@extends('layouts.app')

@section('title', $employee->full_name)
@section('breadcrumb', 'Home / Employee Records / ' . $employee->full_name)

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $employee->full_name }}</h1>
            <p class="text-gray-500">{{ $employee->job_title ?? 'No job title set' }} &middot; {{ $employee->department?->name ?? 'No department' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('employees.edit', $employee) }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Edit</a>
            <a href="{{ route('employees.index') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Back to Directory</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Personal Summary --}}
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
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd>{{ ucfirst(str_replace('_', ' ', $employee->employment_status)) }}</dd></div>
            </dl>

            @if ($employee->userAccount)
                <div class="mt-4 pt-4 border-t text-sm">
                    <div class="text-gray-500 mb-1">System Account</div>
                    <div>{{ $employee->userAccount->email }} &middot; <span class="text-gray-500">{{ $employee->userAccount->role }}</span></div>
                </div>
            @endif
        </div>

        {{-- Employment History (1b) --}}
        <div class="bg-white border rounded-lg p-6 lg:col-span-2">
            <h2 class="font-semibold text-gray-900 mb-4">Employment History</h2>
            @forelse ($employee->employmentHistory as $history)
                <div class="flex justify-between items-start py-3 {{ !$loop->last ? 'border-b' : '' }}">
                    <div>
                        <div class="font-medium text-gray-800">{{ $history->position }}</div>
                        <div class="text-sm text-gray-500">{{ $history->company_name ?? 'PeopleCore (this company)' }}</div>
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

    {{-- Documents (1c) --}}
    <div class="bg-white border rounded-lg p-6 mt-6">
        <h2 class="font-semibold text-gray-900 mb-4">Documents</h2>

        <div class="space-y-2 mb-6">
            @forelse ($employee->documents as $doc)
                <div class="flex items-center justify-between text-sm border rounded-md px-4 py-2">
                    <div>
                        <span class="font-medium">{{ $doc->document_type }}</span>
                        <span class="text-gray-400"> &middot; {{ $doc->file_name }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-blue-600">Download</a>
                        <form action="{{ route('employees.documents.destroy', $doc) }}" method="POST"
                              onsubmit="return confirm('Remove this document?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">No documents uploaded yet.</p>
            @endforelse
        </div>

        <form action="{{ route('employees.documents.store', $employee) }}" method="POST" enctype="multipart/form-data"
              class="flex flex-wrap items-end gap-3 border-t pt-4">
            @csrf
            <div>
                <label class="text-xs text-gray-500 block mb-1">Document Type</label>
                <select name="document_type" required class="border rounded-md px-3 py-2 text-sm">
                    <option value="Resume">Resume</option>
                    <option value="Government ID">Government ID</option>
                    <option value="Certification">Certification</option>
                    <option value="License">License</option>
                    <option value="Contract">Contract</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Expiry Date (optional)</label>
                <input type="date" name="expiry_date" class="border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">File (PDF/JPG/PNG, max 5MB)</label>
                <input type="file" name="file" required class="text-sm">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                Upload
            </button>
        </form>
    </div>
@endsection
