<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceFeedback360 extends Model
{
    use BelongsToTenant;

    protected $table = 'performance_feedbacks_360';

    protected $fillable = [
        'tenant_id',
        'review_cycle_id',
        'subject_employee_id',
        'reviewer_employee_id',
        'rater_relationship',
        'is_anonymous',
        'core_values_score',
        'leadership_score',
        'collaboration_score',
        'execution_score',
        'overall_score',
        'strengths',
        'areas_for_improvement',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'core_values_score' => 'integer',
            'leadership_score' => 'integer',
            'collaboration_score' => 'integer',
            'execution_score' => 'integer',
            'overall_score' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function subjectEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'subject_employee_id');
    }

    public function reviewerEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewer_employee_id');
    }

    public function reviewCycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycle::class, 'review_cycle_id');
    }
}
