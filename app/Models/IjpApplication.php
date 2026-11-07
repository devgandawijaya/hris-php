<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IjpApplication extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'ijp_vacancy_id',
        'employee_id',
        'current_department_id',
        'current_position_id',
        'application_date',
        'motivation_letter',
        'portfolio_url',
        'current_tenure_months',
        'latest_performance_score',
        'has_active_warning',
        'is_eligible',
        'ineligibility_reason',
        'manager_approval_status', // pending, approved, rejected
        'manager_approval_notes',
        'manager_approved_at',
        'hr_interview_status', // pending, passed, failed
        'hr_interview_notes',
        'final_decision', // in_review, offered, accepted, rejected, withdrawn
        'transfer_effective_date',
    ];

    protected function casts(): array
    {
        return [
            'application_date' => 'date',
            'manager_approved_at' => 'datetime',
            'transfer_effective_date' => 'date',
            'latest_performance_score' => 'decimal:2',
            'has_active_warning' => 'boolean',
            'is_eligible' => 'boolean',
            'current_tenure_months' => 'integer',
        ];
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(IjpVacancy::class, 'ijp_vacancy_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function currentDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    public function currentPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'current_position_id');
    }
}
