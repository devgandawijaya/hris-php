<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price_monthly',
        'price_yearly',
        'max_employees',
        'max_admins',
        'storage_limit_mb',
        'has_api_access',
        'has_whatsapp',
        'has_custom_workflows',
        'has_bank_disbursal',
        'has_ebupot_export',
        'has_digital_contracts',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'max_employees' => 'integer',
            'max_admins' => 'integer',
            'storage_limit_mb' => 'integer',
            'has_api_access' => 'boolean',
            'has_whatsapp' => 'boolean',
            'has_custom_workflows' => 'boolean',
            'has_bank_disbursal' => 'boolean',
            'has_ebupot_export' => 'boolean',
            'has_digital_contracts' => 'boolean',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}

