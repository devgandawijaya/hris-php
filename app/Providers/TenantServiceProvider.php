<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Tenancy\TenantManager;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantManager::class, function ($app) {
            return new TenantManager();
        });

        $this->app->alias(TenantManager::class, 'tenant.manager');
    }

    public function boot(): void
    {
        //
    }
}

