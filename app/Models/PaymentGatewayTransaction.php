<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewayTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'subscription_invoice_id',
        'gateway_provider',
        'transaction_id',
        'order_id',
        'gross_amount',
        'payment_type',
        'transaction_status',
        'signature_hash',
        'snap_token',
        'payment_url',
        'raw_payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'raw_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'subscription_invoice_id');
    }
}
