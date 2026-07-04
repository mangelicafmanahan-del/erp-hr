@extends('layouts.app')

@section('title', 'Run Payroll')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('payroll.dashboard') }}" class="hover:text-gray-600">Payroll</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Run Payroll</span>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Run Payroll</h1>
        <p class="text-gray-500">Select the pay period. A payslip will be generated for every active employee.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payroll.runs.store') }}" method="POST" class="bg-white border rounded-lg p-6 max-w-lg space-y-4">
        @csrf
        <div>
            <label class="text-sm text-gray-600">Period Start *</label>
            <input type="date" name="period_start" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
        </div>
        <div>
            <label class="text-sm text-gray-600">Period End *</label>
            <input type="date" name="period_end" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
        </div>
        <div>
            <label class="text-sm text-gray-600">Payout Method *</label>
            <select name="payment_method" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Cash">Cash</option>
                <option value="Check">Check</option>
            </select>
        </div>

        <p class="text-xs text-gray-400">
            Note: only employees with an <strong>active</strong> status and a salary set (from their profile page)
            will be included. Employees without a salary structure will be skipped with &#8369;0 pay.
        </p>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('payroll.dashboard') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-md">
                Process Payroll
            </button>
        </div>
    </form>
@endsection
