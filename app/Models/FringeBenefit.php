<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\BelongsToTenant;

class FringeBenefit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'tax_year',
        'period_month',
        'benefit_type',
        'description',
        'gross_market_value',
        'non_taxable_threshold',
        'taxable_natura_value',
        'is_taxable_object',
        'fiscal_notes',
    ];

    protected function casts(): array
    {
        return [
            'tax_year' => 'integer',
            'period_month' => 'integer',
            'gross_market_value' => 'decimal:2',
            'non_taxable_threshold' => 'decimal:2',
            'taxable_natura_value' => 'decimal:2',
            'is_taxable_object' => 'boolean',
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
