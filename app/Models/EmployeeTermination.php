<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTermination extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'letter_number',
        'termination_type',
        'termination_date',
        'years_of_service',
        'months_of_service',
        'last_salary',
        'severance_multiplier',
        'severance_amount',
        'upmk_amount',
        'uph_amount',
        'separation_pay_amount',
        'total_severance',
        'status',
        'notes',
        'approved_by_user_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'termination_date' => 'date',
            'paid_at' => 'datetime',
            'years_of_service' => 'integer',
            'months_of_service' => 'integer',
            'last_salary' => 'decimal:2',
            'severance_multiplier' => 'decimal:2',
            'severance_amount' => 'decimal:2',
            'upmk_amount' => 'decimal:2',
            'uph_amount' => 'decimal:2',
            'separation_pay_amount' => 'decimal:2',
            'total_severance' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}

