<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisasterRecoveryDrillLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'drill_date',
        'scenario_type',
        'actual_failover_duration_seconds',
        'data_loss_records_count',
        'rpo_target_achieved',
        'rto_target_achieved',
        'audit_summary',
        'lead_auditor_name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'drill_date' => 'date',
            'actual_failover_duration_seconds' => 'integer',
            'data_loss_records_count' => 'integer',
            'rpo_target_achieved' => 'boolean',
            'rto_target_achieved' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
