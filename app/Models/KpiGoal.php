<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiGoal extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'performance_cycle_id',
        'type', // kpi, okr
        'title',
        'target_value',
        'current_value',
        'unit',
        'weight',
        'deadline',
        'status', // in_progress, achieved, missed
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'weight' => 'decimal:2',
            'deadline' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function performanceCycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycle::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_value <= 0) {
            return 0.0;
        }
        return min(100.0, round(($this->current_value / $this->target_value) * 100, 1));
    }
}

