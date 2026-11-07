<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceImprovementPlan extends Model
{
    use BelongsToTenant;

    protected $table = 'performance_improvement_plans';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'supervisor_employee_id',
        'title',
        'reason',
        'start_date',
        'end_date',
        'target_objectives',
        'action_plan',
        'progress_notes',
        'status',
        'final_evaluation',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_employee_id');
    }
}
