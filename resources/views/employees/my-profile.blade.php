@extends('layouts.app')

@section('title', 'My Profile')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">My Profile</span>

    <div class="bg-white border rounded-lg p-6 mt-6">
        <h2 class="font-semibold text-gray-900 mb-1">Payment Preferences</h2>
        <p class="text-sm text-gray-500 mb-4">Choose how you would like to receive future payroll payouts. Check is the default method.</p>

        <form action="{{ route('my.profile.payment-preferences') }}" method="POST" class="space-y-4 max-w-2xl">
            @csrf
            <div>
                <label class="text-xs text-gray-500 block mb-1">Preferred Payment Method</label>
                <select name="payment_method" id="payment_method" required class="border rounded-md px-3 py-2 text-sm w-full sm:w-80" onchange="toggleBankFields()">
                    <option value="Check" @selected(($employee->payment_method ?? 'Check') === 'Check')>Check</option>
                    <option value="Bank Transfer" @selected(($employee->payment_method ?? 'Check') === 'Bank Transfer')>Bank Transfer</option>
                    <option value="Cash" @selected(($employee->payment_method ?? 'Check') === 'Cash')>Cash</option>
                </select>
            </div>

            <div id="bank-fields" class="grid grid-cols-1 sm:grid-cols-3 gap-3 {{ ($employee->payment_method ?? 'Check') === 'Bank Transfer' ? '' : 'hidden' }}">
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" class="border rounded-md px-3 py-2 text-sm w-full">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Account Name</label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $employee->bank_account_name) }}" class="border rounded-md px-3 py-2 text-sm w-full">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Account Number</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number) }}" class="border rounded-md px-3 py-2 text-sm w-full">
                </div>
            </div>

            @error('payment_method') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('bank_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('bank_account_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('bank_account_number') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                Save Payment Preferences
            </button>
        </form>
    </div>

    <script>
        function toggleBankFields() {
            const method = document.getElementById('payment_method').value;
            document.getElementById('bank-fields').classList.toggle('hidden', method !== 'Bank Transfer');
        }
    </script>

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

    <div class="flex items-center gap-4 mb-6">
        @if ($employee->profile_photo_path)
            <img src="{{ asset('storage/' . $employee->profile_photo_path) }}" alt="{{ $employee->full_name }}"
                 class="h-16 w-16 rounded-full object-cover border">
        @else
            <div class="h-16 w-16 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold text-xl">
                {{ strtoupper(substr($employee->first_name, 0, 1)) }}
            </div>
        @endif
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                {{ $employee->full_name }}
                <span class="text-xs px-2 py-1 rounded-full {{ $statusColors[$employee->employment_status] ?? 'bg-gray-100' }}">
                    {{ ucfirst(str_replace('_', ' ', $employee->employment_status)) }}
                </span>
            </h1>
            <p class="text-gray-500">{{ $employee->job_title ?? 'No job title set' }} &middot; {{ $employee->department?->name ?? 'No department' }}</p>
            <p class="text-xs text-gray-400 mt-1">This is a read-only view. Contact HR to update any of this information.</p>

            <form action="{{ route('my.profile.photo') }}" method="POST" enctype="multipart/form-data"
                  class="flex items-center gap-2 mt-2">
                @csrf
                <input type="file" name="photo" accept="image/*" required
                       class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-blue-50 file:text-blue-700">
                <button type="submit" class="text-xs font-medium text-blue-600 hover:text-blue-700">
                    {{ $employee->profile_photo_path ? 'Change photo' : 'Upload photo' }}
                </button>
            </form>
            @error('photo')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
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
        <div class="space-y-2 mb-6">
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

        <form action="{{ route('my.documents.store') }}" method="POST" enctype="multipart/form-data"
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

    <div class="bg-white border rounded-lg p-6 mt-6">
        <h2 class="font-semibold text-gray-900 mb-1">Payment Preferences</h2>
        <p class="text-sm text-gray-500 mb-4">Choose how you would like to receive future payroll payouts. Check is the default method.</p>

        <form action="{{ route('my.profile.payment-preferences') }}" method="POST" class="space-y-4 max-w-2xl">
            @csrf
            <div>
                <label class="text-xs text-gray-500 block mb-1">Preferred Payment Method</label>
                <select name="payment_method" id="payment_method" required class="border rounded-md px-3 py-2 text-sm w-full sm:w-80" onchange="toggleBankFields()">
                    <option value="Check" @selected(($employee->payment_method ?? 'Check') === 'Check')>Check</option>
                    <option value="Bank Transfer" @selected(($employee->payment_method ?? 'Check') === 'Bank Transfer')>Bank Transfer</option>
                    <option value="Cash" @selected(($employee->payment_method ?? 'Check') === 'Cash')>Cash</option>
                </select>
            </div>

            <div id="bank-fields" class="grid grid-cols-1 sm:grid-cols-3 gap-3 {{ ($employee->payment_method ?? 'Check') === 'Bank Transfer' ? '' : 'hidden' }}">
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" class="border rounded-md px-3 py-2 text-sm w-full">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Account Name</label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $employee->bank_account_name) }}" class="border rounded-md px-3 py-2 text-sm w-full">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Account Number</label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number) }}" class="border rounded-md px-3 py-2 text-sm w-full">
                </div>
            </div>

            @error('payment_method') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('bank_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('bank_account_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('bank_account_number') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                Save Payment Preferences
            </button>
        </form>
    </div>

    <script>
        function toggleBankFields() {
            const method = document.getElementById('payment_method').value;
            document.getElementById('bank-fields').classList.toggle('hidden', method !== 'Bank Transfer');
        }
    </script>

@endsection
