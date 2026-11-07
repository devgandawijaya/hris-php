<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FwaSchedule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'date',
        'work_mode',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getWorkDateAttribute()
    {
        return $this->date;
    }

    public function getArrangementTypeAttribute(): string
    {
        return strtoupper($this->work_mode ?? 'WFH');
    }

    public function getApprovalStatusAttribute(): string
    {
        return strtoupper($this->status ?? 'PENDING');
    }

    public function getApprovedByNameAttribute(): string
    {
        return $this->approver?->name ?? 'Manager';
    }
}
