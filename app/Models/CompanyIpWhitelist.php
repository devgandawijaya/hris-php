<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\BelongsToTenant;

class CompanyIpWhitelist extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'ip_address',
        'label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
