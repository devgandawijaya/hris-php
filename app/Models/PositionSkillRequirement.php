<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionSkillRequirement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'position_id',
        'skill_id',
        'required_level',
        'is_mandatory',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'required_level' => 'integer',
            'is_mandatory' => 'boolean',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function getProficiencyLabelAttribute(): string
    {
        return match ($this->required_level) {
            1 => 'Level 1 - Dasar (Novice)',
            2 => 'Level 2 - Menengah (Intermediate)',
            3 => 'Level 3 - Mahir (Competent)',
            4 => 'Level 4 - Ahli (Advanced)',
            5 => 'Level 5 - Pakar (Master/Expert)',
            default => "Level {$this->required_level}",
        };
    }
}
