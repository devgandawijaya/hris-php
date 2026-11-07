<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WltkReport extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'report_number',
        'reporting_year',
        'report_period', // annual, establishment, relocation, closure
        'status', // draft, submitted_kemnaker, verified_disnaker
        'submission_date',
        'valid_until',
        'company_registration_number',
        'kbli_code',
        'total_workers',
        'total_wni_male',
        'total_wni_female',
        'total_wna_male',
        'total_wna_female',
        'total_pkwtt',
        'total_pkwt',
        'total_disability_workers',
        'lowest_wage',
        'highest_wage',
        'bpjs_tk_registered',
        'bpjs_kes_registered',
        'has_pp_or_pkb',
        'has_lks_bipartit',
        'raw_payload_json',
        'disnaker_receipt_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'submission_date' => 'date',
            'valid_until' => 'date',
            'lowest_wage' => 'decimal:2',
            'highest_wage' => 'decimal:2',
            'bpjs_tk_registered' => 'boolean',
            'bpjs_kes_registered' => 'boolean',
            'has_pp_or_pkb' => 'boolean',
            'has_lks_bipartit' => 'boolean',
            'raw_payload_json' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isRenewalDue(int $daysThreshold = 30): bool
    {
        if (!$this->valid_until) {
            return false;
        }

        return now()->diffInDays($this->valid_until, false) <= $daysThreshold;
    }
}
