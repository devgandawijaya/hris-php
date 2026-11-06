<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Auth;

class EnsureTenantAccess
{
    public function __construct(protected TenantManager $tenantManager) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->tenantManager->hasActiveTenant()) {
            return redirect()->route('login')->with('error', 'Silakan pilih atau login ke perusahaan Anda.');
        }

        $tenant = $this->tenantManager->getTenant();
        $user = Auth::user();

        // Super Admin bypasses tenant membership check
        if ($user && $user->is_super_admin) {
            return $next($request);
        }

        // Validate user belongs to this tenant
        if ($user && !$user->tenants()->where('tenants.id', $tenant->id)->exists()) {
            abort(403, 'Anda tidak memiliki hak akses ke entitas perusahaan ini.');
        }

        // Validate tenant status
        if ($tenant->status === 'suspended') {
            abort(403, 'Akses perusahaan ini sedang ditangguhkan. Silakan hubungi Administrator.');
        }

        if ($tenant->status === 'expired') {
            return redirect()->route('subscription.expired')->with('warning', 'Masa aktif langganan perusahaan telah berakhir. Silakan lakukan perpanjangan.');
        }

        return $next($request);
    }
}

