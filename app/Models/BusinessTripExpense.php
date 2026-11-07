<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessTripExpense extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'business_trip_order_id',
        'expense_category',
        'amount',
        'expense_date',
        'description',
        'receipt_path',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'is_verified' => 'boolean',
        ];
    }

    public function businessTripOrder(): BelongsTo
    {
        return $this->belongsTo(BusinessTripOrder::class);
    }
}
