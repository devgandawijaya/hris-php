<?php

namespace App\Support\Traits;

use App\Models\Tenant;
use App\Support\Scopes\TenantScope;
use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait to automatically attach TenantScope and populate tenant_id.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            /** @var TenantManager $tenantManager */
            $tenantManager = app(TenantManager::class);

            if ($tenantManager->hasActiveTenant() && empty($model->tenant_id)) {
                $model->tenant_id = $tenantManager->getActiveTenantId();
            }
        });
    }

    /**
     * Tenant relationship.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}

