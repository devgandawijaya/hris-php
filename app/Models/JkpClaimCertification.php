<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JkpClaimCertification extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'certificate_number',
        'employee_id',
        'termination_date',
        'termination_reason_code', // efisiensi_merger, pailit, force_majeure, perusahaan_tutup
        'bpjs_tk_card_number',
        'consecutive_paid_months',
        'total_paid_months_24m',
        'eligible_for_jkp',
        'reference_salary_cap',
        'month_1_to_3_benefit_monthly',
        'month_4_to_6_benefit_monthly',
        'total_potential_cash_benefit',
        'disnaker_reporting_status', // reported, verified_kemnaker, claimed
        'siapkerja_tracking_code',
        'company_recommendation_letter',
    ];

    protected function casts(): array
    {
        return [
            'termination_date' => 'date',
            'eligible_for_jkp' => 'boolean',
            'consecutive_paid_months' => 'integer',
            'total_paid_months_24m' => 'integer',
            'reference_salary_cap' => 'decimal:2',
            'month_1_to_3_benefit_monthly' => 'decimal:2',
            'month_4_to_6_benefit_monthly' => 'decimal:2',
            'total_potential_cash_benefit' => 'decimal:2',
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
