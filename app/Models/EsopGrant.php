<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsopGrant extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'grant_number',
        'grant_date',
        'total_shares_granted',
        'vesting_schedule_years',
        'cliff_months',
        'strike_price_per_share',
        'vested_shares_count',
        'exercised_shares_count',
        'fair_market_value',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'grant_date' => 'date',
            'total_shares_granted' => 'integer',
            'vesting_schedule_years' => 'integer',
            'cliff_months' => 'integer',
            'strike_price_per_share' => 'decimal:2',
            'vested_shares_count' => 'integer',
            'exercised_shares_count' => 'integer',
            'fair_market_value' => 'decimal:2',
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
}
