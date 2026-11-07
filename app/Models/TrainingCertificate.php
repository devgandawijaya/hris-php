<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingCertificate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'training_enrollment_id',
        'certificate_number',
        'recipient_name',
        'course_title',
        'completion_date',
        'score_achieved',
        'grade_label',
        'verification_hash',
        'signer_name',
        'signer_title',
    ];

    protected function casts(): array
    {
        return [
            'completion_date' => 'date',
            'score_achieved' => 'decimal:2',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnrollment::class, 'training_enrollment_id');
    }

    public function getTrainingTitleAttribute(): string
    {
        return $this->course_title ?? 'Pelatihan Kompetensi Profesional';
    }

    public function getIssuedAtAttribute()
    {
        return $this->completion_date ?? $this->created_at;
    }
}
