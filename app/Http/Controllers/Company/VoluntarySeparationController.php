<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\VoluntarySeparationProgram;
use App\Models\VssApplication;
use App\Models\DplkParticipation;
use App\Services\Payroll\VoluntarySeparationService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class VoluntarySeparationController extends Controller
{
    public function __construct(
        protected VoluntarySeparationService $vssService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $programs = VoluntarySeparationProgram::with(['applications.employee'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest('open_date')
            ->paginate(10);

        $dplkList = DplkParticipation::with('employee')
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest()
            ->take(10)
            ->get();

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        return view('company.vss.index', compact('company', 'programs', 'dplkList', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'open_date' => 'required|date',
            'close_date' => 'required|date|after:open_date',
            'severance_multiplier' => 'required|numeric|min:1.0|max:5.0',
            'additional_golden_handshake_bonus' => 'nullable|numeric|min:0',
            'max_quota' => 'required|integer|min:1',
        ]);

        $this->vssService->createProgram($company, $validated);

        return redirect()->route('vss.index')->with('success', 'Program Pensiun Dini Sukarela (VSS) berhasil diluncurkan.');
    }

    public function apply(Request $request, string $programId): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $program = VoluntarySeparationProgram::where('tenant_id', $tenantId)->findOrFail($programId);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        try {
            $this->vssService->applyForProgram($program, $employee);

            return back()->with('success', 'Pengajuan pensiun dini sukarela berhasil didaftarkan dan nilai paket dihitung otomatis.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, string $applicationId): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $application = VssApplication::where('tenant_id', $tenantId)->findOrFail($applicationId);

        $effectiveDate = $request->input('settlement_effective_date', now()->addDays(14)->toDateString());
        $this->vssService->approveApplication($application, auth()->id() ?? 1, $effectiveDate);

        return back()->with('success', 'Pengajuan VSS disetujui dan tanggal efektif kompensasi ditetapkan.');
    }

    public function storeDplk(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'dplk_provider_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'employer_contribution_percentage' => 'required|numeric|min:0|max:20',
            'employee_contribution_percentage' => 'required|numeric|min:0|max:20',
            'initial_balance' => 'nullable|numeric|min:0',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);
        $this->vssService->registerDplk($company, $employee, $validated);

        return back()->with('success', 'Kepesertaan program DPLK (UU No. 4/2023 P2SK) berhasil didaftarkan.');
    }
}
