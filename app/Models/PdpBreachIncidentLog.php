<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdpBreachIncidentLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'incident_number',
        'breach_type',
        'detected_at',
        'affected_records_count',
        'authority_notified_at',
        'subjects_notified_at',
        'incident_description',
        'remediation_measures',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'affected_records_count' => 'integer',
            'authority_notified_at' => 'datetime',
            'subjects_notified_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
