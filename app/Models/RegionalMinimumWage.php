<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionalMinimumWage extends Model
{
    protected $fillable = [
        'tenant_id',
        'province',
        'city_or_regency',
        'year',
        'minimum_wage_amount',
        'legal_reference',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'minimum_wage_amount' => 'decimal:2',
        ];
    }
}
