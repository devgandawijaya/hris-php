<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipLogbook extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'internship_program_id',
        'log_date',
        'activity_summary',
        'competency_mastered',
        'mentor_feedback',
        'mentor_rating',
        'is_verified_by_mentor',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'mentor_rating' => 'decimal:2',
            'is_verified_by_mentor' => 'boolean',
        ];
    }

    public function internshipProgram(): BelongsTo
    {
        return $this->belongsTo(InternshipProgram::class);
    }
}
