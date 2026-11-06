<?php

namespace App\Support\Scopes;

use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the tenant scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        /** @var TenantManager $tenantManager */
        $tenantManager = app(TenantManager::class);

        if ($tenantManager->hasActiveTenant()) {
            $builder->where($model->getTable() . '.tenant_id', '=', $tenantManager->getActiveTenantId());
        }
    }
}

