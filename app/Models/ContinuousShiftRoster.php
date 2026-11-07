<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContinuousShiftRoster extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'roster_code',
        'name',
        'department_id',
        'cycle_days',
        'work_days_per_cycle',
        'off_days_per_cycle',
        'night_shift_allowance_rate',
        'standby_allowance_rate',
        'rotation_pattern_json',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cycle_days' => 'integer',
            'work_days_per_cycle' => 'integer',
            'off_days_per_cycle' => 'integer',
            'night_shift_allowance_rate' => 'decimal:2',
            'standby_allowance_rate' => 'decimal:2',
            'rotation_pattern_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftRosterAssignment::class);
    }
}
