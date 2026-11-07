<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\BelongsToTenant;

class OvertimeOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'order_number',
        'employee_id',
        'overtime_date',
        'start_time',
        'end_time',
        'planned_duration_hours',
        'actual_duration_hours',
        'reason_task_description',
        'is_day_off',
        'manager_approved_by',
        'manager_approved_at',
        'employee_consented_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'overtime_date' => 'date',
            'planned_duration_hours' => 'decimal:2',
            'actual_duration_hours' => 'decimal:2',
            'is_day_off' => 'boolean',
            'manager_approved_at' => 'datetime',
            'employee_consented_at' => 'datetime',
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

    public function managerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }
}
