<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Services\Security\ZeroTrustSecurityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZeroTrustSecurityController extends Controller
{
    public function __construct(
        protected ZeroTrustSecurityService $securityService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $auditReport = $this->securityService->auditDataProtectionSecurity($company);

        $recentAccessLogs = class_exists(AuditLog::class)
            ? AuditLog::where('tenant_id', $company->tenant_id)->latest()->take(10)->get()
            : collect();

        return view('company.dr.security_audit', compact('company', 'auditReport', 'recentAccessLogs'));
    }
}
