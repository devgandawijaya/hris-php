<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'start_date',
        'end_date',
        'payment_date',
        'status', // draft, processed, approved, paid
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'payment_date' => 'date',
        ];
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function bankDisbursalBatches(): HasMany
    {
        return $this->hasMany(BankDisbursalBatch::class);
    }

    public function bpjsReports(): HasMany
    {
        return $this->hasMany(BpjsComplianceReport::class);
    }

    public function getTotalGrossAttribute(): float
    {
        return (float) $this->payrolls()->sum('gross_salary');
    }

    public function getTotalTakeHomePayAttribute(): float
    {
        return (float) $this->payrolls()->sum('take_home_pay');
    }

    public function getTotalTaxAttribute(): float
    {
        return (float) $this->payrolls()->sum('pph21_tax');
    }

    public function getTotalBpjsEmployeeAttribute(): float
    {
        return (float) ($this->payrolls()->sum('bpjs_tk_employee') + $this->payrolls()->sum('bpjs_kes_employee'));
    }

    public function getTotalBpjsEmployerAttribute(): float
    {
        return (float) ($this->payrolls()->sum('bpjs_tk_employer') + $this->payrolls()->sum('bpjs_kes_employer'));
    }
}
