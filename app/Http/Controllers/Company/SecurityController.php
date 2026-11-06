<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CompanyIpWhitelist;
use App\Services\Security\AuditService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $actionFilter = $request->query('action');

        $query = AuditLog::with('user')
            ->where('tenant_id', $tenant->id)
            ->latest();

        if (!empty($actionFilter)) {
            $query->where('action', $actionFilter);
        }

        $auditLogs = $query->paginate(20);
        $ipWhitelists = CompanyIpWhitelist::where('tenant_id', $tenant->id)->latest()->get();

        return view('company.security.index', compact('auditLogs', 'ipWhitelists'));
    }

    public function storeIpWhitelist(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'label' => 'nullable|string|max:100',
        ]);

        $this->auditService->addIpWhitelist($tenant->id, $validated['ip_address'], $validated['label']);

        return back()->with('success', "Alamat IP {$validated['ip_address']} berhasil ditambahkan ke whitelist keamanan perusahaan.");
    }

    public function destroyIpWhitelist(CompanyIpWhitelist $ipWhitelist): RedirectResponse
    {
        $this->auditService->removeIpWhitelist($ipWhitelist);

        return back()->with('success', 'Alamat IP berhasil dihapus dari whitelist.');
    }
}

