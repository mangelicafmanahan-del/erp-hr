<?php

namespace App\Http\Controllers;

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
            ->orderByDesc('period_start')
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

            $grossPay = $basicSalary + $allowance;

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
                'overtime_pay' => 0,
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
