<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThrDisbursement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'tax_year',
        'religious_holiday_type',
        'tenure_months',
        'prorata_multiplier',
        'regular_wage_basis',
        'calculated_thr_amount',
        'ter_tax_deduction',
        'net_thr_paid',
        'statutory_payment_deadline',
        'actual_payment_date',
        'late_penalty_interest',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tax_year' => 'integer',
            'tenure_months' => 'integer',
            'prorata_multiplier' => 'decimal:4',
            'regular_wage_basis' => 'decimal:2',
            'calculated_thr_amount' => 'decimal:2',
            'ter_tax_deduction' => 'decimal:2',
            'net_thr_paid' => 'decimal:2',
            'statutory_payment_deadline' => 'date',
            'actual_payment_date' => 'date',
            'late_penalty_interest' => 'decimal:2',
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
