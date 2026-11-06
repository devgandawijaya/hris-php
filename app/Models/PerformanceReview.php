<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'performance_cycle_id',
        'employee_id',
        'reviewer_id',
        'self_rating',
        'self_feedback',
        'manager_rating',
        'manager_feedback',
        'final_score',
        'status', // draft, submitted, finalized
    ];

    protected function casts(): array
    {
        return [
            'self_rating' => 'decimal:2',
            'manager_rating' => 'decimal:2',
            'final_score' => 'decimal:2',
        ];
    }

    public function performanceCycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycle::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
