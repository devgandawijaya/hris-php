<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorshipCheckin extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'mentorship_pair_id',
        'milestone', // day_30, day_60, day_90
        'scheduled_date',
        'completed_date',
        'status', // pending, completed, missed
        'agenda_topics',
        'mentee_satisfaction_score', // 1 to 5
        'mentee_feedback',
        'mentor_adaptation_score', // 1 to 5
        'mentor_notes',
        'action_plan',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_date' => 'date',
            'mentee_satisfaction_score' => 'integer',
            'mentor_adaptation_score' => 'integer',
        ];
    }

    public function mentorshipPair(): BelongsTo
    {
        return $this->belongsTo(MentorshipPair::class);
    }
}
