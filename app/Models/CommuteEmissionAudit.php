<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommuteEmissionAudit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'audit_year',
        'participating_employees_count',
        'total_annual_commute_distance_km',
        'gross_emissions_ton_co2e',
        'avoided_emissions_wfh_ton_co2e',
        'net_commute_emissions_ton_co2e',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'audit_year' => 'integer',
            'participating_employees_count' => 'integer',
            'total_annual_commute_distance_km' => 'decimal:2',
            'gross_emissions_ton_co2e' => 'decimal:2',
            'avoided_emissions_wfh_ton_co2e' => 'decimal:2',
            'net_commute_emissions_ton_co2e' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
