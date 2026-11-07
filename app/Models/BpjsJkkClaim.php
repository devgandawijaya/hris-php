<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\BelongsToTenant;

class BpjsJkkClaim extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'workplace_incident_id',
        'claim_number',
        'stage',
        'medical_facility_name',
        'estimated_medical_expense',
        'approved_bpjs_coverage',
        'temporary_disability_allowance',
        'claim_status',
        'submitted_at',
        'settled_at',
        'bpjs_notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_medical_expense' => 'decimal:2',
            'approved_bpjs_coverage' => 'decimal:2',
            'temporary_disability_allowance' => 'decimal:2',
            'submitted_at' => 'date',
            'settled_at' => 'date',
        ];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(WorkplaceIncident::class, 'workplace_incident_id');
    }
}
