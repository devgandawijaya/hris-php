<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentContract extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'contract_number',
        'type', // pkwt, pkwtt, internship, probation
        'start_date',
        'end_date',
        'probation_end_date',
        'monthly_wage',
        'status', // active, expiring_soon, expired, renewed, terminated
        'compensation_calculated',
        'compensation_paid',
        'renewal_count',
        'document_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'probation_end_date' => 'date',
            'monthly_wage' => 'decimal:2',
            'compensation_calculated' => 'decimal:2',
            'compensation_paid' => 'boolean',
            'renewal_count' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
