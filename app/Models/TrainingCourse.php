<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingCourse extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'title',
        'trainer_name',
        'category', // technical, leadership, compliance, soft_skills
        'location',
        'start_date',
        'end_date',
        'duration_hours',
        'capacity',
        'description',
        'status', // scheduled, ongoing, completed, cancelled
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'duration_hours' => 'decimal:2',
            'capacity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TrainingCourse $course) {
            if (empty($course->start_date)) {
                $course->start_date = now();
            }
            if (empty($course->end_date)) {
                $course->end_date = now()->addDays(7);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class);
    }
}
