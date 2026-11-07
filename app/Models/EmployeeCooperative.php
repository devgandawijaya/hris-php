<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeCooperative extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'member_number',
        'join_date',
        'status', // active, suspended, withdrawn
        'mandatory_savings',
        'voluntary_savings',
        'principal_savings_paid',
        'total_accumulated_savings',
        'active_loan_balance',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'mandatory_savings' => 'decimal:2',
            'voluntary_savings' => 'decimal:2',
            'principal_savings_paid' => 'decimal:2',
            'total_accumulated_savings' => 'decimal:2',
            'active_loan_balance' => 'decimal:2',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(CooperativeTransaction::class);
    }
}
