<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\OffboardingClearance;
use App\Models\WorkExperienceLetter;
use App\Services\Offboarding\ClearanceService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OffboardingClearanceController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected ClearanceService $clearanceService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $company = Company::where('tenant_id', $tenant->id)->firstOrFail();

        $query = OffboardingClearance::where('company_id', $company->id)
            ->with(['employee.department', 'employee.position', 'paklaringLetter'])
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->where('overall_status', $request->input('status'));
        }

        $clearances = $query->paginate(15);

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $stats = [
            'total' => OffboardingClearance::where('company_id', $company->id)->count(),
            'in_progress' => OffboardingClearance::where('company_id', $company->id)->where('overall_status', 'in_progress')->count(),
            'completed' => OffboardingClearance::where('company_id', $company->id)->where('overall_status', 'completed')->count(),
        ];

        return view('company.clearance.index', compact('clearances', 'employees', 'stats'));
    }

    public function show(OffboardingClearance $clearance): View
    {
        $clearance->load([
            'employee.department',
            'employee.position',
            'hrOfficer',
            'itOfficer',
            'financeOfficer',
            'supervisorUser',
            'paklaringLetter',
        ]);

        return view('company.clearance.show', compact('clearance'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'resignation_date' => ['required', 'date'],
            'last_working_day' => ['required', 'date', 'after_or_equal:resignation_date'],
            'separation_reason' => ['required', 'string', 'in:resigned,contract_end,retired,terminated,deceased'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $clearance = $this->clearanceService->initiateClearance($employee, $validated);

        return redirect()->route('company.clearances.show', $clearance)
            ->with('success', "Proses offboarding clearance untuk {$employee->full_name} berhasil diinisiasi.");
    }

    public function signOff(Request $request, OffboardingClearance $clearance, string $department): RedirectResponse
    {
        if (!in_array($department, ['hr', 'it', 'finance', 'supervisor'])) {
            return redirect()->back()->with('error', 'Departemen clearance tidak valid.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:cleared,pending'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->clearanceService->signOffDepartment(
            $clearance,
            $department,
            $request->user(),
            $validated
        );

        return redirect()->back()->with('success', "Status clearance departemen " . strtoupper($department) . " berhasil diperbarui.");
    }

    public function issuePaklaring(Request $request, OffboardingClearance $clearance): RedirectResponse
    {
        $validated = $request->validate([
            'signer_position' => ['required', 'string', 'max:100'],
            'conduct_appraisal' => ['required', 'in:exemplary,very_good,good,satisfactory'],
            'achievement_summary' => ['nullable', 'string', 'max:1000'],
        ]);

        $letter = $this->clearanceService->issuePaklaring($clearance, $request->user(), $validated);

        return redirect()->route('company.clearances.show', $clearance)
            ->with('success', "Surat Pengalaman Kerja (Paklaring) {$letter->letter_number} berhasil diterbitkan.");
    }

    public function printPaklaring(WorkExperienceLetter $letter): View
    {
        $letter->load(['company', 'employee.department', 'employee.position']);
        return view('company.clearance.paklaring', compact('letter'));
    }
}
