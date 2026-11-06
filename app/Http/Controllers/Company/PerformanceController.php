<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\KpiGoal;
use App\Models\PerformanceCycle;
use App\Models\PerformanceReview;
use App\Services\Talent\PerformanceService;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected PerformanceService $performanceService
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $cycleId = $request->query('cycle_id');

        $cycles = PerformanceCycle::where('tenant_id', $tenant->id)->latest()->get();
        $activeCycle = $cycleId ? PerformanceCycle::where('tenant_id', $tenant->id)->find($cycleId) : $cycles->first();

        $goalsQuery = KpiGoal::with(['employee.department', 'performanceCycle'])
            ->where('tenant_id', $tenant->id);

        if ($activeCycle) {
            $goalsQuery->where('performance_cycle_id', $activeCycle->id);
        }

        $goals = $goalsQuery->latest()->paginate(15);

        $reviews = $activeCycle ? PerformanceReview::with(['employee.department', 'reviewer'])
            ->where('tenant_id', $tenant->id)
            ->where('performance_cycle_id', $activeCycle->id)
            ->get() : collect();

        $employees = Employee::where('tenant_id', $tenant->id)->where('status', 'active')->get();

        $stats = [
            'total_goals' => KpiGoal::where('tenant_id', $tenant->id)->count(),
            'achieved_goals' => KpiGoal::where('tenant_id', $tenant->id)->where('status', 'achieved')->count(),
            'finalized_reviews' => PerformanceReview::where('tenant_id', $tenant->id)->where('status', 'finalized')->count(),
            'average_score' => round(PerformanceReview::where('tenant_id', $tenant->id)->where('status', 'finalized')->avg('final_score') ?? 0, 1),
        ];

        return view('company.performance.index', compact('cycles', 'activeCycle', 'goals', 'reviews', 'employees', 'stats'));
    }

    public function storeCycle(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $cycle = $this->performanceService->createCycle($tenant->id, $validated);

        return back()->with('success', "Periode evaluasi '{$cycle->name}' berhasil dibuat.");
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'performance_cycle_id' => 'nullable|exists:performance_cycles,id',
            'type' => 'required|in:kpi,okr',
            'title' => 'required|string|max:255',
            'target_value' => 'required|numeric|min:0.1',
            'current_value' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:30',
            'weight' => 'nullable|numeric|min:1|max:100',
            'deadline' => 'nullable|date',
        ]);

        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);
        $this->performanceService->addGoal($employee, $validated);

        return back()->with('success', "Sasaran kerja {$validated['type']} berhasil ditetapkan untuk {$employee->full_name}.");
    }

    public function updateProgress(Request $request, KpiGoal $goal): RedirectResponse
    {
        $validated = $request->validate([
            'current_value' => 'required|numeric|min:0',
        ]);

        $this->performanceService->updateGoalProgress($goal, (float) $validated['current_value']);

        return back()->with('success', 'Pencapaian sasaran kerja berhasil diperbarui.');
    }

    public function submitManagerAppraisal(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();

        $validated = $request->validate([
            'performance_cycle_id' => 'required|exists:performance_cycles,id',
            'employee_id' => 'required|exists:employees,id',
            'manager_rating' => 'required|numeric|min:0|max:100',
            'manager_feedback' => 'required|string',
        ]);

        $cycle = PerformanceCycle::where('tenant_id', $tenant->id)->findOrFail($validated['performance_cycle_id']);
        $employee = Employee::where('tenant_id', $tenant->id)->findOrFail($validated['employee_id']);
        $manager = Auth::user();

        $review = PerformanceReview::firstOrNew([
            'tenant_id' => $tenant->id,
            'performance_cycle_id' => $cycle->id,
            'employee_id' => $employee->id,
        ]);

        $this->performanceService->submitManagerReview(
            $review,
            $manager,
            (float) $validated['manager_rating'],
            $validated['manager_feedback']
        );

        return back()->with('success', "Penilaian kinerja untuk {$employee->full_name} berhasil difinalisasi dengan Skor: {$review->final_score}.");
    }
}

