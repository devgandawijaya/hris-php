<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\BelongsToTenant;

class BipartiteMeeting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'meeting_number',
        'meeting_date',
        'meeting_title',
        'agenda_topics',
        'employer_representatives',
        'worker_representatives',
        'meeting_minutes',
        'action_items',
        'disnaker_reported_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
            'employer_representatives' => 'array',
            'worker_representatives' => 'array',
            'disnaker_reported_at' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
