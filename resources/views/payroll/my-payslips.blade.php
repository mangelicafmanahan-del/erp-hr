@extends('layouts.app')

@section('title', 'My Payslips')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">My Payslips</span>
@endsection

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Payslips</h1>
        <p class="text-gray-500">{{ $employee->full_name }} &middot; {{ $employee->employee_number }}</p>
    </div>

    <div class="bg-white border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Pay Period</th>
                    <th class="text-left px-4 py-3">Gross Pay</th>
                    <th class="text-left px-4 py-3">Deductions</th>
                    <th class="text-left px-4 py-3">Net Pay</th>
                    <th class="text-right px-4 py-3">Payslip</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($payslips as $payslip)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $payslip->payrollRun->period_start->format('M d') }} &ndash; {{ $payslip->payrollRun->period_end->format('M d, Y') }}</td>
                        <td class="px-4 py-3">&#8369;{{ number_format($payslip->gross_pay, 2) }}</td>
                        <td class="px-4 py-3">&#8369;{{ number_format($payslip->total_deductions, 2) }}</td>
                        <td class="px-4 py-3 font-medium">&#8369;{{ number_format($payslip->net_pay, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('payroll.payslips.show', $payslip) }}" class="text-blue-600 text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No payslips yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payslips->links() }}</div>
@endsection
