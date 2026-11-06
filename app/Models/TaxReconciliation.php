<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxReconciliation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'tax_year',
        'gross_income_annual',
        'biaya_jabatan',
        'bpjs_deductions_annual',
        'net_income_annual',
        'ptkp_annual',
        'pkp',
        'pph21_annual_due',
        'pph21_ter_paid_total',
        'pph21_december_tax',
        'status', // draft, finalized, submitted
        'reconciliation_data',
    ];

    protected function casts(): array
    {
        return [
            'tax_year' => 'integer',
            'gross_income_annual' => 'decimal:2',
            'biaya_jabatan' => 'decimal:2',
            'bpjs_deductions_annual' => 'decimal:2',
            'net_income_annual' => 'decimal:2',
            'ptkp_annual' => 'decimal:2',
            'pkp' => 'decimal:2',
            'pph21_annual_due' => 'decimal:2',
            'pph21_ter_paid_total' => 'decimal:2',
            'pph21_december_tax' => 'decimal:2',
            'reconciliation_data' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
