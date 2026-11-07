<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OffboardingClearance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'resignation_date',
        'last_working_day',
        'separation_reason',
        'hr_status',
        'hr_notes',
        'hr_cleared_by',
        'hr_cleared_at',
        'it_status',
        'it_notes',
        'it_cleared_by',
        'it_cleared_at',
        'finance_status',
        'finance_notes',
        'finance_cleared_by',
        'finance_cleared_at',
        'supervisor_status',
        'supervisor_notes',
        'supervisor_cleared_by',
        'supervisor_cleared_at',
        'overall_status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'resignation_date' => 'date',
            'last_working_day' => 'date',
            'hr_cleared_at' => 'datetime',
            'it_cleared_at' => 'datetime',
            'finance_cleared_at' => 'datetime',
            'supervisor_cleared_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function hrOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_cleared_by');
    }

    public function itOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'it_cleared_by');
    }

    public function financeOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_cleared_by');
    }

    public function supervisorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_cleared_by');
    }

    public function paklaringLetter(): HasOne
    {
        return $this->hasOne(WorkExperienceLetter::class, 'offboarding_clearance_id');
    }
}
