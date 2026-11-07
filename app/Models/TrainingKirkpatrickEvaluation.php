<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingKirkpatrickEvaluation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'training_enrollment_id',
        'pre_test_score',
        'post_test_score',
        'learning_gain_percentage',
        'level1_reaction_score',
        'level1_feedback',
        'level3_behavior_score',
        'level3_notes',
        'level3_evaluated_by',
        'level3_evaluated_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'pre_test_score' => 'decimal:2',
            'post_test_score' => 'decimal:2',
            'learning_gain_percentage' => 'decimal:2',
            'level1_reaction_score' => 'decimal:2',
            'level3_behavior_score' => 'decimal:2',
            'level3_evaluated_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnrollment::class, 'training_enrollment_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'level3_evaluated_by');
    }

    public function getReactionScoreAttribute(): ?float
    {
        return $this->level1_reaction_score !== null ? (float) $this->level1_reaction_score : null;
    }

    public function getBehaviorScoreAttribute(): ?float
    {
        return $this->level3_behavior_score !== null ? (float) $this->level3_behavior_score : null;
    }

    public function getSupervisorObservationAttribute(): ?string
    {
        return $this->level3_notes;
    }
}
