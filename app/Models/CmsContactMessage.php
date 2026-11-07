<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'message',
        'is_read',
        'status',
        'notes',
        'contacted_by_user_id',
        'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'contacted_at' => 'datetime',
        ];
    }

    public function contactedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'contacted_by_user_id');
    }
}

