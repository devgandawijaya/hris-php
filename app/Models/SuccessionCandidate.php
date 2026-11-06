<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuccessionCandidate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'succession_plan_id',
        'candidate_employee_id',
        'readiness_level',
        'strengths',
        'development_needs',
        'status',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SuccessionPlan::class, 'succession_plan_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'candidate_employee_id');
    }

    public function getReadinessBadgeAttribute(): array
    {
        return match ($this->readiness_level) {
            'ready_now' => ['color' => 'emerald', 'label' => 'Ready Now (Siap Sekarang)'],
            'ready_1_year' => ['color' => 'blue', 'label' => 'Ready 1 Year (Siap 1 Tahun)'],
            'ready_2_plus_years' => ['color' => 'amber', 'label' => 'Ready 2+ Years (Jangka Panjang)'],
            default => ['color' => 'slate', 'label' => ucfirst($this->readiness_level)],
        };
    }
}

