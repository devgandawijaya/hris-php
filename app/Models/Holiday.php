<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;

class Holiday extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'date',
        'name',
        'is_joint_leave',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_joint_leave' => 'boolean',
        ];
    }
}
