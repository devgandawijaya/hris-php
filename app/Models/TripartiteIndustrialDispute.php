<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripartiteIndustrialDispute extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'case_number',
        'dispute_type', // perselisihan_hak, perselisihan_kepentingan, perselisihan_phk, perselisihan_antar_serikat
        'employee_id',
        'employee_name_external',
        'bipartite_meeting_id',
        'disnaker_mediator_name',
        'disnaker_office',
        'mediation_date',
        'dispute_chronology',
        'mediator_written_recommendation',
        'company_response', // pending, accepted, rejected
        'worker_response', // pending, accepted, rejected
        'settlement_status', // bipartite_failed, mediation_stage, peaceful_agreement_pb, escalated_to_phi_court, settled
        'joint_agreement_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'mediation_date' => 'date',
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

    public function bipartiteMeeting(): BelongsTo
    {
        return $this->belongsTo(BipartiteMeeting::class);
    }
}
