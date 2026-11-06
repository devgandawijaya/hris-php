<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Position extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'department_id',
        'title',
        'job_level',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // --- PHASE 9: COMPETENCY & SUCCESSION ---

    public function skillRequirements(): HasMany
    {
        return $this->hasMany(PositionSkillRequirement::class);
    }

    public function successionPlan(): HasOne
    {
        return $this->hasOne(SuccessionPlan::class);
    }
}

