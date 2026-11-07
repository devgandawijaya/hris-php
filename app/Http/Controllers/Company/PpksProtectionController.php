<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\PpksComplaintCase;
use App\Models\FemaleWorkerFacilityCheck;
use App\Services\Compliance\PpksProtectionService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class PpksProtectionController extends Controller
{
    public function __construct(
        protected PpksProtectionService $ppksService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $cases = PpksComplaintCase::where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('incident_date')
            ->paginate(10);

        $facilityChecks = FemaleWorkerFacilityCheck::with('auditedByUser')
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('inspection_date')
            ->take(5)
            ->get();

        $complianceReport = $this->ppksService->generateDisnakerGenderComplianceReport($company);

        return view('company.ppks.index', compact('company', 'cases', 'facilityChecks', 'complianceReport'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'incident_date' => 'required|date',
            'allegation_type' => 'required|string|in:verbal,physical,non_physical,digital_online',
            'is_anonymous' => 'nullable|boolean',
            'complainant_pseudonym' => 'nullable|string|max:100',
            'investigator_satgas_lead' => 'required|string|max:100',
            'victim_support_measures' => 'nullable|string',
        ]);

        $this->ppksService->fileComplaint($company, $validated);

        return redirect()->route('ppks.index')->with('success', 'Laporan pengaduan Satgas PPKS berhasil dicatat secara rahasia dengan perlindungan identitas.');
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $case = PpksComplaintCase::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:received,investigating,resolved,referred_to_authorities',
            'sanction_recommendation' => 'nullable|string|max:50',
            'victim_support_measures' => 'nullable|string',
        ]);

        $this->ppksService->updateInvestigation(
            $case,
            $validated['status'],
            $validated['sanction_recommendation'] ?? null,
            $validated['victim_support_measures'] ?? null
        );

        return back()->with('success', 'Status investigasi kasus PPKS berhasil diperbarui.');
    }

    public function auditFacility(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'lactation_room_standard_met' => 'nullable|boolean',
            'night_shift_transport_provided' => 'nullable|boolean',
            'night_shift_nutritious_food_provided' => 'nullable|boolean',
            'daycare_subsidy_available' => 'nullable|boolean',
            'compliance_notes' => 'nullable|string',
        ]);

        $this->ppksService->auditFemaleWorkerFacilities($company, $validated, auth()->id());

        return back()->with('success', 'Audit fasilitas ramah perempuan (ruang laktasi & transportasi shift malam) berhasil dicatat.');
    }
}
