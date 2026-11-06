<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'work_days_per_week',
        'regular_work_hours_per_day',
        'weekly_work_hour_limit',
        'late_tolerance_minutes',
        'bpjs_kes_max_cap',
        'bpjs_tk_jp_max_cap',
        'jkk_risk_grade',
        'payroll_cutoff_day',
        'payroll_payment_day',
    ];

    protected function casts(): array
    {
        return [
            'work_days_per_week' => 'integer',
            'regular_work_hours_per_day' => 'decimal:2',
            'weekly_work_hour_limit' => 'decimal:2',
            'late_tolerance_minutes' => 'integer',
            'bpjs_kes_max_cap' => 'decimal:2',
            'bpjs_tk_jp_max_cap' => 'decimal:2',
            'payroll_cutoff_day' => 'integer',
            'payroll_payment_day' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

