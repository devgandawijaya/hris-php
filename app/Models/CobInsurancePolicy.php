<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CobInsurancePolicy extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'insurance_company_name',
        'policy_number',
        'plan_type',
        'bpjs_health_card_number',
        'cob_claim_mechanism',
        'annual_limit_amount',
        'current_usage_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'annual_limit_amount' => 'decimal:2',
            'current_usage_amount' => 'decimal:2',
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
