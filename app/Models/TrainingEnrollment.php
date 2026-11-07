<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingEnrollment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'training_course_id',
        'employee_id',
        'status', // enrolled, attended, completed, dropped
        'score',
        'certificate_number',
        'feedback',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function trainingCourse(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function kirkpatrickEvaluation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TrainingKirkpatrickEvaluation::class);
    }

    public function digitalCertificate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TrainingCertificate::class);
    }

    public function getTrainingAttribute()
    {
        return $this->trainingCourse;
    }
}

