<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'offered_position',
        'employment_type',
        'salary_offered',
        'offer_date',
        'start_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'offer_date' => 'date',
            'start_date' => 'date',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}
