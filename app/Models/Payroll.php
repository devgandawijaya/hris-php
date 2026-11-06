<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'payroll_period_id',
        'employee_id',
        'basic_salary',
        'allowances_total',
        'overtime_pay',
        'reimbursement_pay',
        'gross_salary',
        'deductions_total',
        'bpjs_tk_employee',
        'bpjs_tk_employer',
        'bpjs_kes_employee',
        'bpjs_kes_employer',
        'pph21_tax',
        'take_home_pay',
        'breakdown',
        'payment_method',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'allowances_total' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'reimbursement_pay' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'deductions_total' => 'decimal:2',
            'bpjs_tk_employee' => 'decimal:2',
            'bpjs_tk_employer' => 'decimal:2',
            'bpjs_kes_employee' => 'decimal:2',
            'bpjs_kes_employer' => 'decimal:2',
            'pph21_tax' => 'decimal:2',
            'take_home_pay' => 'decimal:2',
            'breakdown' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reimbursements(): HasMany
    {
        return $this->hasMany(Reimbursement::class);
    }
}
