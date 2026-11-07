<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\IjpVacancy;
use App\Models\IjpApplication;
use App\Services\Talent\IjpMarketplaceService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class IjpMarketplaceController extends Controller
{
    public function __construct(
        protected IjpMarketplaceService $ijpService
    ) {}

    public function index(Request $request): View
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $vacancies = IjpVacancy::with(['department', 'position', 'applications'])
            ->where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(10);

        $applications = IjpApplication::with(['vacancy.position', 'employee.department', 'currentDepartment', 'currentPosition'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->paginate(15);

        $departments = Department::where('company_id', $company->id)->orderBy('name')->get();
        $positions = Position::where('company_id', $company->id)->orderBy('title')->get();
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('first_name')->get();

        return view('company.ijp.index', compact('company', 'vacancies', 'applications', 'departments', 'positions', 'employees'));
    }

    public function storeVacancy(Request $request): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $company = Company::where('tenant_id', $tenantId)->first() ?? Company::first();

        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'title' => 'required|string|max:150',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'min_tenure_months' => 'required|integer|min:0|max:120',
            'min_performance_score' => 'required|numeric|min:1.0|max:5.0',
            'open_slots' => 'required|integer|min:1|max:50',
            'application_deadline' => 'required|date|after_or_equal:today',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['company_id'] = $company->id;

        $vacancy = $this->ijpService->createVacancy($validated, auth()->id() ?? 1);

        return back()->with('success', "Lowongan internal {$vacancy->title} ({$vacancy->vacancy_code}) berhasil dipublikasikan.");
    }

    public function apply(Request $request, IjpVacancy $vacancy): RedirectResponse
    {
        $tenantId = session('active_tenant_id');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'motivation_letter' => 'required|string|min:20',
            'portfolio_url' => 'nullable|url|max:255',
        ]);

        $employee = Employee::where('tenant_id', $tenantId)->findOrFail($validated['employee_id']);

        try {
            $application = $this->ijpService->applyVacancy($employee, $vacancy, $validated);

            $msg = $application->is_eligible
                ? "Lamaran internal berhasil diajukan untuk ditinjau oleh Manager Divisi asal."
                : "Lamaran internal tercatat dengan catatan ketidaksesuaian kualifikasi awal: {$application->ineligibility_reason}";

            return back()->with('success', $msg);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approveManager(Request $request, IjpApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $isApproved = ($validated['decision'] === 'approved');
        $this->ijpService->reviewManagerApproval($application, $isApproved, $validated['notes']);

        $statusText = $isApproved ? 'disetujui' : 'ditolak';
        return back()->with('success', "Persetujuan pelepasan manajer asal berhasil dicatat ({$statusText}).");
    }

    public function decideHr(Request $request, IjpApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => 'required|in:accepted,offered,rejected',
            'transfer_effective_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $this->ijpService->reviewHrDecision(
            $application,
            $validated['decision'],
            $validated['transfer_effective_date'] ?? null,
            $validated['notes'] ?? null
        );

        return back()->with('success', "Keputusan akhir HR untuk pelamar {$application->employee->full_name} berhasil disimpan.");
    }
}
