<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Support\Traits\BelongsToTenant;

class WorkplaceIncident extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'incident_number',
        'incident_date',
        'incident_time',
        'location',
        'employee_id',
        'severity',
        'injury_type',
        'lost_workdays',
        'description',
        'root_cause_analysis',
        'corrective_actions',
        'status',
        'reported_by_user_id',
        'investigated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'lost_workdays' => 'integer',
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

    public function bpjsJkkClaim(): HasOne
    {
        return $this->hasOne(BpjsJkkClaim::class, 'workplace_incident_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public function investigatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigated_by_user_id');
    }
}
