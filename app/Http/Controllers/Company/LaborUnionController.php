<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LaborUnion;
use App\Models\PkbNegotiation;
use App\Services\Relations\PkbNegotiationService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class LaborUnionController extends Controller
{
    public function __construct(
        protected PkbNegotiationService $pkbService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $unions = LaborUnion::with(['pkbNegotiations', 'duesDeductions.employee'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(10);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();
        $totalWorkforce = $employees->count();

        return view('company.unions.index', compact('company', 'unions', 'employees', 'totalWorkforce'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'union_name' => 'required|string|max:150',
            'registration_number' => 'required|string|max:100',
            'chairperson_name' => 'required|string|max:100',
            'total_verified_members' => 'required|integer|min:0',
        ]);

        $totalWorkforce = Employee::where('company_id', $company->id)->where('status', 'active')->count();

        try {
            $this->pkbService->registerUnion($company, $validated, $totalWorkforce);

            return redirect()->route('unions.index')->with('success', 'Serikat Pekerja berhasil didaftarkan dan persentase keterwakilan dihitung.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function storePkb(Request $request, string $unionId): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();
        $union = LaborUnion::where('tenant_id', $tenantId)->findOrFail($unionId);

        $validated = $request->validate([
            'pkb_period_years' => 'required|string|max:50',
            'negotiation_start_date' => 'required|date',
            'negotiation_target_end_date' => 'required|date|after:negotiation_start_date',
            'employer_delegation_lead' => 'required|string|max:100',
            'union_delegation_lead' => 'required|string|max:100',
            'summary_clauses' => 'nullable|string',
        ]);

        try {
            $this->pkbService->initiatePkbNegotiation($company, $union, $validated);

            return back()->with('success', 'Perundingan PKB resmi dimulai dengan batas waktu 30 hari kerja sesuai Permenaker No. 28/2014.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function storeCheckoff(Request $request, string $unionId): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $union = LaborUnion::where('tenant_id', $tenantId)->findOrFail($unionId);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'monthly_dues_amount' => 'required|numeric|min:1000',
            'consent_letter_number' => 'required|string|max:100',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        try {
            $this->pkbService->registerDuesCheckoff($union, $employee, (float) $validated['monthly_dues_amount'], $validated['consent_letter_number']);

            return back()->with('success', 'Pemotongan iuran serikat pekerja (Check-off System) berhasil didaftarkan.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
