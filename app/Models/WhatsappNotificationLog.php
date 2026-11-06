<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;

class WhatsappNotificationLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'phone_number',
        'template_name',
        'message_content',
        'status', // queued, sent, failed
        'error_message',
    ];
}

