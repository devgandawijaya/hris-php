<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BusinessTripOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'sppd_number',
        'purpose',
        'origin_city',
        'destination_city',
        'departure_date',
        'return_date',
        'duration_days',
        'transport_type',
        'lodging_needed',
        'per_diem_daily_rate',
        'estimated_budget',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'duration_days' => 'integer',
            'lodging_needed' => 'boolean',
            'per_diem_daily_rate' => 'decimal:2',
            'estimated_budget' => 'decimal:2',
            'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function advances(): HasMany
    {
        return $this->hasMany(BusinessTripAdvance::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(BusinessTripExpense::class);
    }

    public function settlement(): HasOne
    {
        return $this->hasOne(BusinessTripSettlement::class);
    }
}
