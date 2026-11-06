<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuccessionPlan extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'position_id',
        'current_holder_employee_id',
        'criticality_level',
        'emergency_replacement_plan',
        'notes',
        'status',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function currentHolder(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_holder_employee_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(SuccessionCandidate::class)->orderByRaw("
            CASE readiness_level 
                WHEN 'ready_now' THEN 1 
                WHEN 'ready_1_year' THEN 2 
                WHEN 'ready_2_plus_years' THEN 3 
                ELSE 4 
            END
        ");
    }

    public function getReadyNowCountAttribute(): int
    {
        return $this->candidates->where('readiness_level', 'ready_now')->where('status', 'active')->count();
    }

    public function getRiskBadgeAttribute(): array
    {
        if ($this->ready_now_count > 0) {
            return ['color' => 'emerald', 'label' => 'Terlindungi (Ada Suksesor Siap)'];
        }

        return ['color' => 'amber', 'label' => 'Beresiko (Belum Ada Suksesor Siap Pakai)'];
    }
}

