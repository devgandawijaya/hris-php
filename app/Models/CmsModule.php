<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsModule extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'order_index',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'order_index' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

