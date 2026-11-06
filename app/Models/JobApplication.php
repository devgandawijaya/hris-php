<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'job_posting_id',
        'job_candidate_id',
        'stage', // applied, screening, interview, offered, hired, rejected
        'interview_scheduled_at',
        'interview_notes',
        'interview_score',
        'offered_salary',
        'rejection_reason',
        'converted_employee_id',
    ];

    protected function casts(): array
    {
        return [
            'interview_scheduled_at' => 'datetime',
            'interview_score' => 'decimal:2',
            'offered_salary' => 'decimal:2',
        ];
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(JobCandidate::class, 'job_candidate_id');
    }

    public function convertedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'converted_employee_id');
    }
}
