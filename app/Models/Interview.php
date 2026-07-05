<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'stage',
        'interviewer',
        'interview_date',
        'score',
        'feedback',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'interview_date' => 'datetime',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}
