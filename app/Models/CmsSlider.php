<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsSlider extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image_url',
        'cta_text',
        'cta_url',
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

