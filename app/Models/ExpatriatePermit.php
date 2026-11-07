<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpatriatePermit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'rptka_number',
        'rptka_approval_date',
        'rptka_expiry_date',
        'job_position_title',
        'passport_number',
        'passport_expiry_date',
        'itas_vitas_number',
        'itas_expiry_date',
        'dkptka_monthly_amount_usd',
        'dkptka_payment_proof',
        'companion_employee_id',
        'technology_transfer_plan',
        'status', // active, expiring_soon, expired, repatriated
    ];

    protected function casts(): array
    {
        return [
            'rptka_approval_date' => 'date',
            'rptka_expiry_date' => 'date',
            'passport_expiry_date' => 'date',
            'itas_expiry_date' => 'date',
            'dkptka_monthly_amount_usd' => 'decimal:2',
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

    public function companionEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'companion_employee_id');
    }

    public function isExpiringSoon(int $daysThreshold = 60): bool
    {
        if (!$this->rptka_expiry_date) {
            return false;
        }

        return now()->diffInDays($this->rptka_expiry_date, false) <= $daysThreshold;
    }
}
