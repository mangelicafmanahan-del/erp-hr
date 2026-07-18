@extends('layouts.app')

@section('title', 'Payroll Run Detail')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('payroll.dashboard') }}" class="hover:text-gray-600">Payroll</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Payroll Run Detail</span>
@endsection

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $run->period_start->format('M d') }} &ndash; {{ $run->period_end->format('M d, Y') }}
            </h1>
            <p class="text-gray-500">{{ $run->payslips->count() }} employee(s) paid in this run.</p>
        </div>
        <a href="{{ route('payroll.dashboard') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Back to Payroll</a>
    </div>

    <div class="bg-white border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Employee</th>
                    <th class="text-left px-4 py-3">Department</th>
                    <th class="text-left px-4 py-3">Gross Pay</th>
                    <th class="text-left px-4 py-3">Deductions</th>
                    <th class="text-left px-4 py-3">Net Pay</th>
                    <th class="text-right px-4 py-3">Payslip</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($run->payslips as $payslip)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $payslip->employee->full_name }}</td>
                        <td class="px-4 py-3">{{ $payslip->employee->department?->name ?? '—' }}</td>
                        <td class="px-4 py-3">&#8369;{{ number_format($payslip->gross_pay, 2) }}</td>
                        <td class="px-4 py-3">&#8369;{{ number_format($payslip->total_deductions, 2) }}</td>
                        <td class="px-4 py-3 font-medium">&#8369;{{ number_format($payslip->net_pay, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('payroll.payslips.show', $payslip) }}" class="text-blue-600 text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No payslips in this run.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
