<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsTestimonial extends Model
{
    protected $fillable = [
        'author_name',
        'author_title',
        'company_name',
        'company_logo_url',
        'quote',
        'rating',
        'order_index',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'order_index' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

