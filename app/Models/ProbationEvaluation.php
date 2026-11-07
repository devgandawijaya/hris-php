<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProbationEvaluation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'evaluator_user_id',
        'evaluation_date',
        'technical_competence_score',
        'attendance_discipline_score',
        'culture_integrity_score',
        'final_score',
        'decision',
        'sk_number',
        'evaluator_notes',
        'development_recommendations',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_date' => 'date',
            'technical_competence_score' => 'decimal:2',
            'attendance_discipline_score' => 'decimal:2',
            'culture_integrity_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }

    public function getStatusAttribute(): string
    {
        if ($this->decision === 'passed_pkwtt') {
            return 'PASSED';
        }
        if ($this->decision === 'failed_terminated') {
            return 'FAILED';
        }
        return 'EXTENDED';
    }

    public function getTechnicalScoreAttribute(): float
    {
        return (float) ($this->technical_competence_score ?? 0);
    }

    public function getAttendanceScoreAttribute(): float
    {
        return (float) ($this->attendance_discipline_score ?? 0);
    }

    public function getCultureScoreAttribute(): float
    {
        return (float) ($this->culture_integrity_score ?? 0);
    }

    public function getEvaluatorNameAttribute(): string
    {
        return $this->evaluator?->name ?? 'HR Reviewer';
    }

    public function getProbationEndDateAttribute(): ?string
    {
        return $this->evaluation_date ? $this->evaluation_date->toDateString() : null;
    }

    public function getNewJobTitleAttribute(): ?string
    {
        return $this->employee?->position?->title ?? 'Karyawan Tetap';
    }

    public function getNewBasicSalaryAttribute(): ?float
    {
        return $this->employee?->basic_salary ? (float) $this->employee->basic_salary : null;
    }
}
