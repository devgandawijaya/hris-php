<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DplkParticipation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'dplk_provider_name',
        'account_number',
        'employer_contribution_percentage',
        'employee_contribution_percentage',
        'current_accumulated_fund',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'employer_contribution_percentage' => 'decimal:2',
            'employee_contribution_percentage' => 'decimal:2',
            'current_accumulated_fund' => 'decimal:2',
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
