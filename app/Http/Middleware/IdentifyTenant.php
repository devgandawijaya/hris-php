<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Tenancy\TenantManager;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class IdentifyTenant
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = null;

        // 1. Resolve by header if present
        if ($request->hasHeader('X-Tenant-ID')) {
            $tenantId = $request->header('X-Tenant-ID');
        }
        // 2. Resolve by session
        elseif ($request->session()->has('active_tenant_id')) {
            $tenantId = $request->session()->get('active_tenant_id');
        }
        // 3. Resolve by authenticated user
        elseif (Auth::check()) {
            $user = Auth::user();
            if ($user->active_tenant_id) {
                $tenantId = $user->active_tenant_id;
            } else {
                $tenantUser = $user->tenants()->first();
                $tenantId = $tenantUser?->id;
            }
        }

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                $this->tenantManager->setCurrentTenant($tenant);

                // Share globally with Blade views
                View::share('currentTenant', $tenant);
                View::share('currentCompany', $this->tenantManager->getCompany());
            }
        }

        return $next($request);
    }
}

