<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCommuteProfile extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'home_to_office_distance_km',
        'primary_commute_mode',
        'weekly_office_days',
        'weekly_remote_days',
        'weekly_emissions_kg_co2',
        'weekly_avoided_emissions_kg_co2',
    ];

    protected function casts(): array
    {
        return [
            'home_to_office_distance_km' => 'decimal:2',
            'weekly_office_days' => 'integer',
            'weekly_remote_days' => 'integer',
            'weekly_emissions_kg_co2' => 'decimal:2',
            'weekly_avoided_emissions_kg_co2' => 'decimal:2',
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
