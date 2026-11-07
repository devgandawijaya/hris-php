<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Traits\BelongsToTenant;

class NotificationDispatchLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'template_id',
        'channel',
        'recipient_name',
        'recipient_target',
        'subject',
        'message_preview',
        'status',
        'sent_at',
        'error_message',
        'dispatched_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by_user_id');
    }
}
