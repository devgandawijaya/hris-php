<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CooperativeTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_cooperative_id',
        'transaction_type', // mandatory_saving, voluntary_saving, loan_disbursement, loan_repayment, welfare_aid_disbursement, shu_dividend
        'amount',
        'transaction_date',
        'is_payroll_deducted',
        'reference_notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'is_payroll_deducted' => 'boolean',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(EmployeeCooperative::class, 'employee_cooperative_id');
    }
}
