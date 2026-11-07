<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpjsComplianceReport extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'payroll_period_id',
        'report_type',
        'report_month',
        'total_employees',
        'total_wages',
        'total_company_share',
        'total_employee_share',
        'total_contribution',
    ];

    protected function casts(): array
    {
        return [
            'total_employees' => 'integer',
            'total_wages' => 'decimal:2',
            'total_company_share' => 'decimal:2',
            'total_employee_share' => 'decimal:2',
            'total_contribution' => 'decimal:2',
        ];
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }
}
