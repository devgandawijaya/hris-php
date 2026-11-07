<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisabilityEmploymentAudit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'audit_code',
        'audit_year',
        'audit_date',
        'entity_type', // private_sector (1%), state_owned_bumn (2%)
        'total_active_workforce',
        'disability_workforce_count',
        'statutory_quota_percentage',
        'actual_quota_percentage',
        'statutory_headcount_required',
        'headcount_shortfall',
        'compliance_status', // compliant, near_target, non_compliant
        'physical_accessibility_score', // 0-100%
        'digital_accessibility_score', // 0-100%
        'reasonable_accommodations_summary',
        'recommendations',
        'audited_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'audit_date' => 'date',
            'statutory_quota_percentage' => 'decimal:2',
            'actual_quota_percentage' => 'decimal:2',
            'statutory_headcount_required' => 'integer',
            'headcount_shortfall' => 'integer',
            'total_active_workforce' => 'integer',
            'disability_workforce_count' => 'integer',
            'physical_accessibility_score' => 'integer',
            'digital_accessibility_score' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function auditedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by_user_id');
    }
}
