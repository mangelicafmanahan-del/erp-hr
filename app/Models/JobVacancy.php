<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobVacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'title',
        'description',
        'employment_type',
        'location',
        'status',
        'posted_date',
        'closing_date',
    ];

    protected function casts(): array
    {
        return [
            'posted_date' => 'date',
            'closing_date' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
