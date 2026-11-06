<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryComponent extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'type', // allowance, deduction
        'is_fixed',
        'is_taxable',
    ];

    protected function casts(): array
    {
        return [
            'is_fixed' => 'boolean',
            'is_taxable' => 'boolean',
        ];
    }

    public function employeeComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }
}

