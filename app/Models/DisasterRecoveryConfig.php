<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisasterRecoveryConfig extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'primary_region',
        'standby_region',
        'replication_mode',
        'rpo_target_minutes',
        'rto_target_minutes',
        'current_replication_lag_ms',
        'health_status',
        'last_heartbeat_at',
    ];

    protected function casts(): array
    {
        return [
            'rpo_target_minutes' => 'integer',
            'rto_target_minutes' => 'integer',
            'current_replication_lag_ms' => 'integer',
            'last_heartbeat_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
