<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'skill_category_id',
        'name',
        'code',
        'description',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }

    public function positionRequirements(): HasMany
    {
        return $this->hasMany(PositionSkillRequirement::class);
    }

    public function employeeSkills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }
}
