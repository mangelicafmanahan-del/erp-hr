@extends('layouts.app')

@section('title', 'Payslip Detail')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-gray-600">Home</a>
    <span class="mx-1">/</span>
    <a href="{{ route('payroll.dashboard') }}" class="hover:text-gray-600">Payroll</a>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Payslips</span>
    <span class="mx-1">/</span>
    <span class="text-gray-600">Payslip Detail</span>
@endsection

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Payslip Detail</h1>
            <p class="text-gray-500">View detailed payslip information for the selected payroll period.</p>
        </div>
        <a href="{{ route('payroll.dashboard') }}" class="border rounded-md px-4 py-2 text-sm text-gray-600">Back to Payroll</a>
    </div>

    <div class="bg-white border rounded-lg p-6 mb-6 flex flex-wrap justify-between gap-6">
        <div>
            <div class="font-semibold text-gray-900 flex items-center gap-2">
                {{ $payslip->employee->full_name }}
                @php
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-700',
                        'on_leave' => 'bg-amber-100 text-amber-700',
                        'inactive' => 'bg-gray-100 text-gray-600',
                        'terminated' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <span class="text-xs px-2 py-1 rounded-full {{ $statusColors[$payslip->employee->employment_status] ?? 'bg-gray-100' }}">
                    {{ ucfirst(str_replace('_', ' ', $payslip->employee->employment_status)) }}
                </span>
            </div>
            <div class="text-sm text-gray-500">{{ $payslip->employee->employee_number }} &middot; {{ $payslip->employee->job_title }}</div>
            <div class="text-sm text-gray-500">{{ $payslip->employee->department?->name }}</div>
        </div>
        <div class="text-sm">
            <div class="text-gray-500">Pay Period</div>
            <div class="font-medium">{{ $payslip->payrollRun->period_start->format('M d') }} &ndash; {{ $payslip->payrollRun->period_end->format('M d, Y') }}</div>
        </div>
        <div class="text-sm">
            <div class="text-gray-500">Payout Method</div>
            <div class="font-medium">{{ $payslip->payment_method }}</div>
        </div>
        <div class="text-sm">
            <div class="text-gray-500">SSS Number</div>
            <div class="font-medium">{{ $payslip->employee->sss_number ?? '\u2014' }}</div>
        </div>
        <div class="text-sm">
            <div class="text-gray-500">PhilHealth Number</div>
            <div class="font-medium">{{ $payslip->employee->philhealth_number ?? '\u2014' }}</div>
        </div>
        <div class="text-sm">
            <div class="text-gray-500">Pag-IBIG Number</div>
            <div class="font-medium">{{ $payslip->employee->pagibig_number ?? '\u2014' }}</div>
        </div>
        <div class="text-sm">
            <div class="text-gray-500">TIN Number</div>
            <div class="font-medium">{{ $payslip->employee->tin_number ?? '\u2014' }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="bg-white border rounded-lg p-5 lg:col-span-1 border-t-4 border-t-green-400">
            <div class="text-xs font-semibold text-green-700 mb-3">EARNINGS</div>
            @foreach ($payslip->earnings as $item)
                <div class="flex justify-between text-sm py-1">
                    <span class="text-gray-600">{{ $item->description }}</span>
                    <span>{{ number_format($item->amount, 2) }}</span>
                </div>
            @endforeach
            <div class="flex justify-between text-sm font-semibold border-t mt-2 pt-2">
                <span>Total Earnings</span>
                <span>{{ number_format($payslip->gross_pay, 2) }}</span>
            </div>
        </div>

        <div class="bg-white border rounded-lg p-5 lg:col-span-1 border-t-4 border-t-red-400">
            <div class="text-xs font-semibold text-red-700 mb-3">DEDUCTIONS</div>
            @foreach ($payslip->deductions as $item)
                <div class="flex justify-between text-sm py-1">
                    <span class="text-gray-600">{{ $item->description }}</span>
                    <span>{{ number_format($item->amount, 2) }}</span>
                </div>
            @endforeach
            <div class="flex justify-between text-sm font-semibold border-t mt-2 pt-2">
                <span>Total Deductions</span>
                <span>{{ number_format($payslip->total_deductions, 2) }}</span>
            </div>
        </div>

        <div class="bg-white border rounded-lg p-5 lg:col-span-1 border-t-4 border-t-blue-400">
            <div class="text-xs font-semibold text-blue-700 mb-3">ADDITIONAL INFORMATION</div>
            <div class="flex justify-between text-sm py-1"><span class="text-gray-600">Basic Pay</span><span>{{ number_format($payslip->basic_pay, 2) }}</span></div>
            <div class="flex justify-between text-sm py-1"><span class="text-gray-600">Overtime Pay</span><span>{{ number_format($payslip->overtime_pay, 2) }}</span></div>
            <div class="flex justify-between text-sm py-1"><span class="text-gray-600">Bonus Pay</span><span>{{ number_format($payslip->bonus_pay, 2) }}</span></div>
            <p class="text-[11px] text-gray-400 mt-3">
                Overtime/bonus will populate automatically once the Attendance &amp; Leave module is integrated.
            </p>
        </div>

        <div class="bg-slate-900 text-white rounded-lg p-5 lg:col-span-1">
            <div class="text-xs text-slate-400 mb-1">NET PAY</div>
            <div class="text-2xl font-bold mb-4">&#8369;{{ number_format($payslip->net_pay, 2) }}</div>
            <div class="flex justify-between text-sm text-slate-300 py-1"><span>Gross Pay</span><span>{{ number_format($payslip->gross_pay, 2) }}</span></div>
            <div class="flex justify-between text-sm text-red-300 py-1"><span>Total Deductions</span><span>-{{ number_format($payslip->total_deductions, 2) }}</span></div>
        </div>
    </div>

    {{-- Payslip Summary - this employee's full payroll history and running totals --}}
    <div class="bg-white border rounded-lg p-6 mt-6">
        <h2 class="font-semibold text-gray-900 mb-1">Payslip Summary</h2>
        <p class="text-xs text-gray-400 mb-4">All payslips generated for {{ $payslip->employee->full_name }} to date.</p>

        @php
            $allPayslips = $payslip->employee->payslips;
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
            <div class="border rounded-md p-3">
                <div class="text-xs text-gray-500">Payslips Issued</div>
                <div class="text-lg font-bold text-gray-900">{{ $allPayslips->count() }}</div>
            </div>
            <div class="border rounded-md p-3">
                <div class="text-xs text-gray-500">Total Gross Earnings</div>
                <div class="text-lg font-bold text-gray-900">&#8369;{{ number_format($allPayslips->sum('gross_pay'), 2) }}</div>
            </div>
            <div class="border rounded-md p-3">
                <div class="text-xs text-gray-500">Total Deductions</div>
                <div class="text-lg font-bold text-gray-900">&#8369;{{ number_format($allPayslips->sum('total_deductions'), 2) }}</div>
            </div>
            <div class="border rounded-md p-3">
                <div class="text-xs text-gray-500">Total Net Pay</div>
                <div class="text-lg font-bold text-gray-900">&#8369;{{ number_format($allPayslips->sum('net_pay'), 2) }}</div>
            </div>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-3 py-2">Pay Period</th>
                    <th class="text-left px-3 py-2">Gross Pay</th>
                    <th class="text-left px-3 py-2">Deductions</th>
                    <th class="text-left px-3 py-2">Net Pay</th>
                    <th class="text-right px-3 py-2">Payslip</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($allPayslips as $item)
                    <tr class="{{ $item->id === $payslip->id ? 'bg-blue-50' : '' }}">
                        <td class="px-3 py-2">{{ $item->payrollRun->period_start->format('M d') }} &ndash; {{ $item->payrollRun->period_end->format('M d, Y') }}</td>
                        <td class="px-3 py-2">{{ number_format($item->gross_pay, 2) }}</td>
                        <td class="px-3 py-2">{{ number_format($item->total_deductions, 2) }}</td>
                        <td class="px-3 py-2">{{ number_format($item->net_pay, 2) }}</td>
                        <td class="px-3 py-2 text-right">
                            @if ($item->id === $payslip->id)
                                <span class="text-gray-400 text-xs">Currently viewing</span>
                            @else
                                <a href="{{ route('payroll.payslips.show', $item) }}" class="text-blue-600 text-xs">View</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
