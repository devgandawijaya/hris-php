<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdpDsarRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'request_number',
        'request_type',
        'reason_or_scope',
        'request_date',
        'completion_deadline',
        'fulfilled_at',
        'handled_by_user_id',
        'status',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'completion_deadline' => 'date',
            'fulfilled_at' => 'datetime',
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

    public function handledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }
}
