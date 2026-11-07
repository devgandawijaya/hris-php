<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlexBenefitClaim extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'flex_benefit_allocation_id',
        'flex_benefit_category_id',
        'employee_id',
        'claim_number',
        'claim_date',
        'claim_amount',
        'points_deducted',
        'receipt_file_url',
        'notes',
        'status',
        'approved_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'claim_date' => 'date',
            'claim_amount' => 'decimal:2',
            'points_deducted' => 'decimal:2',
        ];
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(FlexBenefitAllocation::class, 'flex_benefit_allocation_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FlexBenefitCategory::class, 'flex_benefit_category_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
