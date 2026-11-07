<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalStressSurvey extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'employee_id',
        'survey_date',
        'avg_daily_screen_hours',
        'meeting_fatigue_score',
        'after_hours_communication_score',
        'eyestrain_headache_frequency',
        'digital_burnout_risk_index',
        'risk_classification',
        'coping_recommendation',
    ];

    protected function casts(): array
    {
        return [
            'survey_date' => 'date',
            'avg_daily_screen_hours' => 'decimal:1',
            'meeting_fatigue_score' => 'integer',
            'after_hours_communication_score' => 'integer',
            'eyestrain_headache_frequency' => 'integer',
            'digital_burnout_risk_index' => 'decimal:2',
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
}
