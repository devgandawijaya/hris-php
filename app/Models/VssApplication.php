<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VssApplication extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'voluntary_separation_program_id',
        'employee_id',
        'application_date',
        'tenure_years',
        'last_basic_salary',
        'calculated_severance_amount',
        'total_package_received',
        'status',
        'approved_by_user_id',
        'settlement_effective_date',
    ];

    protected function casts(): array
    {
        return [
            'application_date' => 'date',
            'tenure_years' => 'integer',
            'last_basic_salary' => 'decimal:2',
            'calculated_severance_amount' => 'decimal:2',
            'total_package_received' => 'decimal:2',
            'settlement_effective_date' => 'date',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(VoluntarySeparationProgram::class, 'voluntary_separation_program_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
