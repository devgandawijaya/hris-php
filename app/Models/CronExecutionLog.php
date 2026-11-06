<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronExecutionLog extends Model
{
    protected $fillable = [
        'command_name',
        'status',
        'items_processed',
        'summary',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
            'items_processed' => 'integer',
        ];
    }
}

