<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Payroll Dashboard (Image 5) - 2b
     */
    public function dashboard()
    {
        $runs = PayrollRun::withCount('payslips')
            ->withSum('payslips', 'net_pay')
            ->withSum('payslips', 'gross_pay')
            ->orderByDesc('id')
            ->paginate(5);

        $totals = [
            'employees' => Employee::where('employment_status', 'active')->count(),
            'total_payroll_cost' => Payslip::sum('gross_pay'),
            'net_pay' => Payslip::sum('net_pay'),
            'deductions' => Payslip::sum('total_deductions'),
        ];

        return view('payroll.dashboard', compact('runs', 'totals'));
    }

    /**
     * All payslips generated in a single payroll run - replaces the old
     * inline name list on the dashboard (which was also capped at 3 names
     * regardless of how many employees were actually paid).
     */
    public function showRun(PayrollRun $run)
    {
        $run->load(['payslips.employee.department']);

        return view('payroll.run-show', compact('run'));
    }

    /**
     * Form to start a new payroll run
     */
    public function createRun()
    {
        return view('payroll.create-run');
    }

    /**
     * Process a payroll run - generates a payslip for every active employee (2a, 2b)
     *
     * NOTE ON CONTRIBUTION FORMULAS:
     * The SSS / PhilHealth / Pag-IBIG / withholding tax percentages below are
     * SIMPLIFIED placeholder rates for demonstration purposes only. For an
     * accurate, compliant system these should be replaced with the actual
     * published SSS/PhilHealth/Pag-IBIG contribution tables and the BIR
     * withholding tax table. Flagged here so this isn't mistaken for a
     * production-accurate implementation.
     */
    public function storeRun(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'payment_method' => 'required|in:Bank Transfer,Cash,Check',
        ]);

        $run = PayrollRun::create([
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        $employees = Employee::where('employment_status', 'active')
            ->with('currentSalary')
            ->get();

        foreach ($employees as $employee) {
            $basicSalary = (float) ($employee->currentSalary->basic_salary ?? 0);
            $allowance = (float) ($employee->currentSalary->allowance ?? 0);

            // 2d/4d integration: pull overtime hours logged in Attendance for this exact
            // pay period, and pay them at a standard 1.25x hourly rate. The hourly rate
            // itself is a simplified estimate (basic salary / 22 working days / 8 hours) -
            // same "simplified for demonstration" caveat as the contribution rates below.
            $overtimeHours = (float) AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$validated['period_start'], $validated['period_end']])
                ->sum('overtime_hours');
            $hourlyRate = $basicSalary > 0 ? $basicSalary / 22 / 8 : 0;
            $overtimePay = round($hourlyRate * 1.25 * $overtimeHours, 2);

            $grossPay = $basicSalary + $allowance + $overtimePay;

            // Simplified placeholder contribution rates - see note above.
            $sss = round($basicSalary * 0.045, 2);
            $philhealth = round($basicSalary * 0.025, 2);
            $pagibig = 200.00;
            $taxableBase = max($grossPay - $sss - $philhealth - $pagibig, 0);
            $withholdingTax = round($taxableBase * 0.10, 2);

            $totalDeductions = $sss + $philhealth + $pagibig + $withholdingTax;
            $netPay = $grossPay - $totalDeductions;

            $payslip = Payslip::create([
                'payroll_run_id' => $run->id,
                'employee_id' => $employee->id,
                'basic_pay' => $basicSalary,
                'overtime_pay' => $overtimePay,
                'bonus_pay' => 0,
                'gross_pay' => $grossPay,
                'sss_contribution' => $sss,
                'philhealth_contribution' => $philhealth,
                'pagibig_contribution' => $pagibig,
                'withholding_tax' => $withholdingTax,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,
                'payment_method' => $validated['payment_method'],
            ]);

            // Itemized breakdown (2a) - what actually renders on the Payslip Detail page
            if ($basicSalary > 0) {
                PayslipItem::create(['payslip_id' => $payslip->id, 'type' => 'earning', 'description' => 'Basic Salary', 'amount' => $basicSalary]);
            }
            if ($allowance > 0) {
                PayslipItem::create(['payslip_id' => $payslip->id, 'type' => 'earning', 'description' => 'Allowance', 'amount' => $allowance]);
            }
            if ($overtimePay > 0) {
                PayslipItem::create(['payslip_id' => $payslip->id, 'type' => 'earning', 'description' => "Overtime ({$overtimeHours}h)", 'amount' => $overtimePay]);
            }
            PayslipItem::create(['payslip_id' => $payslip->id, 'type' => 'deduction', 'description' => 'SSS Contribution', 'amount' => $sss]);
            PayslipItem::create(['payslip_id' => $payslip->id, 'type' => 'deduction', 'description' => 'PhilHealth Contribution', 'amount' => $philhealth]);
            PayslipItem::create(['payslip_id' => $payslip->id, 'type' => 'deduction', 'description' => 'Pag-IBIG Contribution', 'amount' => $pagibig]);
            if ($withholdingTax > 0) {
                PayslipItem::create(['payslip_id' => $payslip->id, 'type' => 'deduction', 'description' => 'Withholding Tax', 'amount' => $withholdingTax]);
            }
        }

        return redirect()->route('payroll.dashboard')
            ->with('success', "Payroll run processed for {$employees->count()} employee(s).");
    }

    /**
     * Payslip Detail (Image 6) - 2a, 2c
     */
    public function showPayslip(Payslip $payslip)
    {
        $payslip->load(['employee.department', 'payrollRun', 'earnings', 'deductions', 'employee.payslips.payrollRun']);

        return view('payroll.payslip', compact('payslip'));
    }

    /**
     * Set/update an employee's salary structure - called from the employee profile page
     */
    public function storeSalary(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
        ]);

        SalaryStructure::create([
            'employee_id' => $employee->id,
            'basic_salary' => $validated['basic_salary'],
            'allowance' => $validated['allowance'] ?? 0,
            'effective_date' => $validated['effective_date'],
        ]);

        return back()->with('success', 'Salary updated.');
    }
}
