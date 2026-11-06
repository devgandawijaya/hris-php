<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWarning extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'warning_level', // sp1, sp2, sp3
        'letter_number',
        'issued_date',
        'valid_until',
        'reason',
        'action_plan',
        'authorized_by_user_id',
        'status', // active, expired, revoked
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_user_id');
    }

    public function isExpired(): bool
    {
        return $this->valid_until->isPast();
    }
}
