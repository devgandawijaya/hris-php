<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Services\SuperAdmin\ImpersonationService;
use Illuminate\Http\RedirectResponse;

class ImpersonationController extends Controller
{
    public function __construct(
        protected ImpersonationService $impersonationService
    ) {}

    public function start(Request $request, int $tenantId): RedirectResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        $superAdmin = $request->user();

        try {
            $impersonatedUser = $this->impersonationService->impersonateTenant($tenant, $superAdmin);
            return redirect()->route('company.dashboard')->with(
                'warning',
                "Mode Impersonasi Aktif: Anda masuk sebagai {$impersonatedUser->name} pada perusahaan {$tenant->name}."
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function leave(): RedirectResponse
    {
        $tenantId = session('impersonated_tenant_id');
        $superAdmin = $this->impersonationService->leaveImpersonation();

        if ($superAdmin) {
            $redirect = $tenantId
                ? route('super.admin.companies.show', $tenantId)
                : route('super.admin.dashboard');

            return redirect($redirect)->with('success', 'Sesi impersonasi selesai. Anda telah kembali ke Portal Super Admin.');
        }

        return redirect()->route('super.admin.dashboard');
    }
}
