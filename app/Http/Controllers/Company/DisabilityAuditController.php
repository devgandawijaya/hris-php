<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\DisabilityEmploymentAudit;
use App\Models\EmployeeDisabilityProfile;
use App\Services\Compliance\DisabilityComplianceAuditService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DisabilityAuditController extends Controller
{
    public function __construct(
        protected DisabilityComplianceAuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $audits = DisabilityEmploymentAudit::with('company')
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('audit_year')
            ->paginate(10);

        $latestAudit = $audits->first();

        $profiles = EmployeeDisabilityProfile::with(['employee.department'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        return view('company.disability_audit.index', compact('company', 'audits', 'latestAudit', 'profiles', 'employees'));
    }

    public function runAudit(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'entity_type' => 'required|in:private_sector,state_owned_bumn',
        ]);

        $audit = $this->auditService->performAudit(
            $company,
            $validated['entity_type'],
            auth()->id()
        );

        $statusLabel = match ($audit->compliance_status) {
            'compliant' => 'MEMENUHI KUOTA SAH (100%)',
            'near_target' => 'MENDEKATI KUOTA (Kurang 1 Pegawai)',
            default => 'BELUM MEMENUHI KUOTA',
        };

        return back()->with('success', "Audit Ketenagakerjaan Disabilitas ({$audit->audit_code}) berhasil dijalankan. Hasil: {$statusLabel}.");
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'disability_category' => 'required|in:physical,sensory_vision,sensory_hearing,intellectual,mental_psychosocial,multiple',
            'medical_certificate_number' => 'nullable|string|max:100',
            'accommodations_provided' => 'required|string',
            'accessibility_needs' => 'nullable|string',
            'emergency_evacuation_assistance_plan' => 'nullable|string',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        $profile = $this->auditService->registerDisabilityProfile($employee, $validated);

        return back()->with('success', "Profil Karyawan Disabilitas & Akomodasi yang Layak untuk {$employee->full_name} berhasil dicatat.");
    }

    public function printReport(DisabilityEmploymentAudit $audit): View
    {
        $audit->load('company');
        $reportData = $this->auditService->generateDisnakerComplianceReport($audit);
        return view('company.disability_audit.print_report', compact('audit', 'reportData'));
    }
}
