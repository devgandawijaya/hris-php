<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessTripSettlement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'business_trip_order_id',
        'total_advance',
        'total_expenses',
        'balance_amount',
        'status',
        'audited_by',
        'audited_at',
        'settled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_advance' => 'decimal:2',
            'total_expenses' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'audited_at' => 'datetime',
            'settled_at' => 'datetime',
        ];
    }

    public function businessTripOrder(): BelongsTo
    {
        return $this->belongsTo(BusinessTripOrder::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }
}
