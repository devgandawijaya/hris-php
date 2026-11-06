<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;

class Shift extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'start_time',
        'end_time',
        'is_night_shift',
    ];

    protected function casts(): array
    {
        return [
            'is_night_shift' => 'boolean',
        ];
    }
}
