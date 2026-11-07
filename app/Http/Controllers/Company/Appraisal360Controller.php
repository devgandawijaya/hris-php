<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PerformanceCycle;
use App\Models\PerformanceFeedback360;
use App\Models\PerformanceImprovementPlan;
use App\Services\Talent\Appraisal360Service;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Appraisal360Controller extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected Appraisal360Service $appraisalService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();

        $employees = Employee::with(['department', 'position', 'reportsTo'])
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $cycles = PerformanceCycle::where('tenant_id', $tenant->id)->latest()->get();

        $feedbacks = PerformanceFeedback360::with(['subjectEmployee.department', 'reviewerEmployee'])
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(15);

        $pips = PerformanceImprovementPlan::with(['employee.department', 'supervisor'])
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->get();

        $activePipCount = $pips->where('status', 'in_progress')->count();
        $completedFeedbacksCount = PerformanceFeedback360::where('tenant_id', $tenant->id)->where('status', 'completed')->count();
        $pendingFeedbacksCount = PerformanceFeedback360::where('tenant_id', $tenant->id)->where('status', 'pending')->count();

        return view('company.appraisals360.index', compact(
            'employees',
            'cycles',
            'feedbacks',
            'pips',
            'activePipCount',
            'completedFeedbacksCount',
            'pendingFeedbacksCount'
        ));
    }

    public function storeFeedbackRequest(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'subject_employee_id' => 'required|exists:employees,id',
            'reviewer_employee_id' => 'required|exists:employees,id|different:subject_employee_id',
            'rater_relationship' => 'required|in:supervisor,peer,subordinate,self',
            'review_cycle_id' => 'nullable|exists:performance_cycles,id',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $this->appraisalService->createFeedbackRequest(
            $tenant->id,
            (int) $validated['subject_employee_id'],
            (int) $validated['reviewer_employee_id'],
            $validated['rater_relationship'],
            $validated['review_cycle_id'] ?? null,
            (bool) ($request->has('is_anonymous'))
        );

        return back()->with('success', 'Permintaan umpan balik 360 derajat berhasil dikirimkan ke penilai.');
    }

    public function storePip(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'supervisor_employee_id' => 'nullable|exists:employees,id',
            'title' => 'required|string|max:150',
            'reason' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'target_objectives' => 'required|string',
            'action_plan' => 'required|string',
        ]);

        $this->appraisalService->createPip($tenant->id, (int) $validated['employee_id'], $validated);

        return back()->with('success', 'Program Peningkatan Kinerja (PIP) resmi diterbitkan untuk karyawan terpilih.');
    }

    public function updatePipNotes(Request $request, PerformanceImprovementPlan $pip): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'required|string',
        ]);

        $this->appraisalService->updatePipProgress($pip, $validated);

        return back()->with('success', 'Catatan progres evaluasi PIP berhasil ditambahkan.');
    }

    public function resolvePip(Request $request, PerformanceImprovementPlan $pip): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:passed,failed,extended',
            'final_evaluation' => 'nullable|string',
        ]);

        $this->appraisalService->completePip($pip, $validated['status'], $validated['final_evaluation'] ?? null);

        $statusText = match ($validated['status']) {
            'passed' => 'LULUS (Passed)',
            'failed' => 'TIDAK LULUS (Failed) — Siap ditindaklanjuti ke terminasi / demosi',
            'extended' => 'DIPERPANJANG (Extended)',
        };

        return back()->with('success', "Status evaluasi PIP berhasil diperbarui menjadi: {$statusText}.");
    }

    public function showSummary(Employee $employee): View
    {
        $summary = $this->appraisalService->getEmployee360Summary($employee->id);

        return view('company.appraisals360.summary', compact('employee', 'summary'));
    }
}
