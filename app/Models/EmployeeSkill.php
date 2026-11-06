<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSkill extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'skill_id',
        'current_level',
        'certification_name',
        'certification_date',
        'valid_until',
        'verified_by_user_id',
        'assessment_notes',
    ];

    protected function casts(): array
    {
        return [
            'current_level' => 'integer',
            'certification_date' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function getProficiencyLabelAttribute(): string
    {
        return match ($this->current_level) {
            1 => 'Level 1 - Dasar (Novice)',
            2 => 'Level 2 - Menengah (Intermediate)',
            3 => 'Level 3 - Mahir (Competent)',
            4 => 'Level 4 - Ahli (Advanced)',
            5 => 'Level 5 - Pakar (Master/Expert)',
            default => "Level {$this->current_level}",
        };
    }
}

