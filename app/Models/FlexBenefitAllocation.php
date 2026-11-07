<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlexBenefitAllocation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'job_grade_id',
        'employee_id',
        'period_year',
        'total_points_allocated',
        'points_used',
        'points_remaining',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'total_points_allocated' => 'decimal:2',
            'points_used' => 'decimal:2',
            'points_remaining' => 'decimal:2',
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

    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(FlexBenefitClaim::class);
    }
}
