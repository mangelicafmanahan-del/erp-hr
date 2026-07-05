<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'date_of_birth',
        'gender',
        'civil_status',
        'nationality',
        'email',
        'phone_number',
        'alternate_number',
        'current_address',
        'permanent_address',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'tin_number',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_number',
        'job_title',
        'contract_type',
        'employment_status',
        'hire_date',
        'profile_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
        ];
    }

    // ----- Relationships -----

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employmentHistory(): HasMany
    {
        return $this->hasMany(EmploymentHistory::class)->orderByDesc('start_date');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function userAccount(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(SalaryStructure::class)->orderByDesc('effective_date');
    }

    public function currentSalary(): HasOne
    {
        return $this->hasOne(SalaryStructure::class)->latestOfMany('effective_date');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class)->orderByDesc('created_at');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class)->orderByDesc('work_date');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class)->orderByDesc('start_date');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // ----- Convenience accessors -----

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name, $this->suffix]);
        return implode(' ', $parts);
    }
}
