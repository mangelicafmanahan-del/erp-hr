<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'basic_pay',
        'overtime_pay',
        'bonus_pay',
        'gross_pay',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'withholding_tax',
        'total_deductions',
        'net_pay',
        'payment_method',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayslipItem::class);
    }

    public function earnings(): HasMany
    {
        return $this->items()->where('type', 'earning');
    }

    public function deductions(): HasMany
    {
        return $this->items()->where('type', 'deduction');
    }
}
