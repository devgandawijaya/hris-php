<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutsourcingContract extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'outsourcing_vendor_id',
        'contract_number',
        'service_type',
        'start_date',
        'end_date',
        'tupe_protection_guarantee',
        'headcount_quota',
        'monthly_billing_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'tupe_protection_guarantee' => 'boolean',
            'headcount_quota' => 'integer',
            'monthly_billing_amount' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(OutsourcingVendor::class, 'outsourcing_vendor_id');
    }

    public function workerAssignments(): HasMany
    {
        return $this->hasMany(VendorWorkerAssignment::class);
    }
}
