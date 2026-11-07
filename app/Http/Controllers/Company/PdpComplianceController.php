<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\PdpBreachIncidentLog;
use App\Models\PdpConsentLog;
use App\Models\PdpDsarRequest;
use App\Services\Compliance\PdpComplianceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PdpComplianceController extends Controller
{
    public function __construct(
        protected PdpComplianceService $pdpService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $dsarRequests = PdpDsarRequest::with(['employee.department', 'handledByUser'])
            ->where('company_id', $company->id)
            ->latest('request_date')
            ->paginate(10);

        $consentLogs = PdpConsentLog::with('employee')
            ->where('company_id', $company->id)
            ->latest('consented_at')
            ->paginate(10);

        $breachLogs = PdpBreachIncidentLog::where('company_id', $company->id)
            ->latest('detected_at')
            ->get();

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        $stats = [
            'total_consents' => PdpConsentLog::where('company_id', $company->id)->where('status', 'granted')->count(),
            'pending_dsar' => PdpDsarRequest::where('company_id', $company->id)->where('status', 'pending')->count(),
            'fulfilled_dsar' => PdpDsarRequest::where('company_id', $company->id)->where('status', 'fulfilled')->count(),
            'total_breaches' => $breachLogs->count(),
        ];

        return view('company.pdp.index', compact(
            'company',
            'dsarRequests',
            'consentLogs',
            'breachLogs',
            'employees',
            'stats'
        ));
    }

    public function storeConsent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'consent_type' => 'required|string|max:100',
            'purpose_description' => 'required|string|max:500',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $this->pdpService->recordConsent(
            $employee,
            $validated['consent_type'],
            $validated['purpose_description'],
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->route('pdp.index')->with('success', "Persetujuan pemrosesan data pribadi untuk {$employee->full_name} berhasil dicatat.");
    }

    public function revokeConsent(PdpConsentLog $consent): RedirectResponse
    {
        $this->pdpService->revokeConsent($consent);
        return back()->with('success', "Persetujuan pemrosesan data {$consent->consent_type} berhasil dicabut (Pasal 9 UU PDP).");
    }

    public function storeDsar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'request_type' => 'required|string|in:data_portability,data_access,data_rectification,data_erasure',
            'reason_or_scope' => 'nullable|string|max:500',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        try {
            $dsar = $this->pdpService->submitDsarRequest(
                $employee,
                $validated['request_type'],
                $validated['reason_or_scope'] ?? null
            );

            return redirect()->route('pdp.index')->with('success', "Permohonan hak subjek data {$dsar->request_number} berhasil diregistrasi (Batas waktu: {$dsar->completion_deadline}).");
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function fulfillExport(PdpDsarRequest $request): View
    {
        $userId = auth()->id() ?? 1;
        $exportData = $this->pdpService->exportPortabilityPackage($request, $userId);

        return view('company.pdp.export_dsar', compact('request', 'exportData'));
    }

    public function storeBreach(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'breach_type' => 'required|string|max:100',
            'affected_records_count' => 'required|integer|min:1',
            'incident_description' => 'required|string|max:1000',
            'remediation_measures' => 'required|string|max:1000',
        ]);

        $breach = $this->pdpService->recordDataBreach(
            $company,
            $validated['breach_type'],
            (int) $validated['affected_records_count'],
            $validated['incident_description'],
            $validated['remediation_measures']
        );

        return redirect()->route('pdp.index')->with('success', "Insiden {$breach->incident_number} berhasil dicatat & notifikasi 72 jam terkirim ke otoritas PDP.");
    }
}
