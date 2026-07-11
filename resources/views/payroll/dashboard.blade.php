@extends('layouts.app')

@section('title', 'Payroll Dashboard')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Payroll</span>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Dashboard</span>
@endsection

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Payroll Dashboard</h1>
            <p class="text-gray-500">Overview of payroll operations and summaries.</p>
        </div>
        <a href="{{ route('payroll.runs.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md">
            &#9654; Run Payroll
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border p-5">
            <div class="text-sm text-gray-500">Active Employees</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $totals['employees'] }}</div>
        </div>
        <div class="bg-white rounded-lg border p-5">
            <div class="text-sm text-gray-500">Total Payroll Cost</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">&#8369;{{ number_format($totals['total_payroll_cost'], 2) }}</div>
        </div>
        <div class="bg-white rounded-lg border p-5">
            <div class="text-sm text-gray-500">Net Pay</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">&#8369;{{ number_format($totals['net_pay'], 2) }}</div>
        </div>
        <div class="bg-white rounded-lg border p-5">
            <div class="text-sm text-gray-500">Deductions</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">&#8369;{{ number_format($totals['deductions'], 2) }}</div>
        </div>
    </div>

    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b font-semibold text-gray-900">Recent Payroll Runs</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Payroll Period</th>
                    <th class="text-left px-4 py-3">Employees Processed</th>
                    <th class="text-left px-4 py-3">Total Payroll Cost</th>
                    <th class="text-left px-4 py-3">Net Pay</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-right px-4 py-3">Payslips</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($runs as $run)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $run->period_start->format('M d') }} &ndash; {{ $run->period_end->format('M d, Y') }}</td>
                        <td class="px-4 py-3">{{ $run->payslips_count }}</td>
                        <td class="px-4 py-3">&#8369;{{ number_format($run->payslips_sum_gross_pay ?? 0, 2) }}</td>
                        <td class="px-4 py-3">&#8369;{{ number_format($run->payslips_sum_net_pay ?? 0, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">{{ ucfirst($run->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('payroll.runs.show', $run) }}" class="text-blue-600 text-xs">
                                {{ $run->payslips_count }} {{ $run->payslips_count === 1 ? 'employee' : 'employees' }} &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            No payroll runs yet. <a href="{{ route('payroll.runs.create') }}" class="text-blue-600">Run your first payroll</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $runs->links() }}
    </div>
@endsection
