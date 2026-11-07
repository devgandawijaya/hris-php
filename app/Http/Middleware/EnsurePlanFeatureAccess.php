<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Subscription\PlanGatingService;
use App\Models\Tenant;

class EnsurePlanFeatureAccess
{
    public function __construct(
        protected PlanGatingService $planGatingService
    ) {}

    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $tenantId = session('active_tenant_id') ?? $request->user()?->tenant_id;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if ($tenant && !$this->planGatingService->hasFeature($tenant, $featureKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Akses ditolak: Fitur {$featureKey} tidak termasuk dalam paket langganan aktif Anda.",
                    'upgrade_required' => true,
                ], 403);
            }

            return redirect()->route('company.billing.index')->with(
                'error',
                "Fitur ini memerlukan paket langganan yang lebih tinggi. Silakan lakukan upgrade paket untuk membuka akses."
            );
        }

        return $next($request);
    }
}
