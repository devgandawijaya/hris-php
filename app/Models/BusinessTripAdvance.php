<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessTripAdvance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'business_trip_order_id',
        'amount_requested',
        'amount_approved',
        'status',
        'transfer_reference',
        'disbursed_at',
        'disbursed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount_requested' => 'decimal:2',
            'amount_approved' => 'decimal:2',
            'disbursed_at' => 'datetime',
        ];
    }

    public function businessTripOrder(): BelongsTo
    {
        return $this->belongsTo(BusinessTripOrder::class);
    }

    public function disburser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}
