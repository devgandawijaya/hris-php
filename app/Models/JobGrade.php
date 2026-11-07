<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobGrade extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'grade_code',
        'name',
        'level_order',
        'min_salary',
        'mid_salary',
        'max_salary',
        'spread_percentage',
        'midpoint_progression',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level_order' => 'integer',
            'min_salary' => 'decimal:2',
            'mid_salary' => 'decimal:2',
            'max_salary' => 'decimal:2',
            'spread_percentage' => 'decimal:2',
            'midpoint_progression' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
