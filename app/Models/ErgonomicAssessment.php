<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErgonomicAssessment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'department_id',
        'employee_id',
        'assessment_date',
        'workstation_type',
        'lighting_lux_level',
        'chair_lumbar_support_compliant',
        'monitor_eye_level_compliant',
        'keyboard_wrist_rest_compliant',
        'posture_risk_level',
        'ergonomic_compliance_score',
        'corrective_actions',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
            'lighting_lux_level' => 'integer',
            'chair_lumbar_support_compliant' => 'boolean',
            'monitor_eye_level_compliant' => 'boolean',
            'keyboard_wrist_rest_compliant' => 'boolean',
            'ergonomic_compliance_score' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
