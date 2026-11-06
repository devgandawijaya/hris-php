<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSurveyResponse extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_survey_id',
        'employee_id',
        'enps_score',
        'satisfaction_rating',
        'feedback',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'enps_score' => 'integer',
            'satisfaction_rating' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(EmployeeSurvey::class, 'employee_survey_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
