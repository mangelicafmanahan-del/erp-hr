<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_vacancy_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'resume_file_name',
        'resume_path',
        'status',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class)->orderBy('interview_date');
    }

    public function offer(): HasOne
    {
        return $this->hasOne(JobOffer::class);
    }

    public function onboardingTasks(): HasMany
    {
        return $this->hasMany(OnboardingTask::class);
    }
}
