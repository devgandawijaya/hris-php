<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkExperienceLetter extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'offboarding_clearance_id',
        'letter_number',
        'signer_name',
        'signer_position',
        'start_date',
        'end_date',
        'last_position',
        'last_department',
        'conduct_appraisal',
        'achievement_summary',
        'issued_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'issued_date' => 'date',
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

    public function offboardingClearance(): BelongsTo
    {
        return $this->belongsTo(OffboardingClearance::class);
    }
}
