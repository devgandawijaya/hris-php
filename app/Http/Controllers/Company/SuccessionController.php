<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Position;
use App\Models\SuccessionCandidate;
use App\Models\SuccessionPlan;
use App\Services\Talent\SuccessionService;
use App\Tenancy\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuccessionController extends Controller
{
    public function __construct(
        protected SuccessionService $successionService
    ) {}

    public function index(): View
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();

        $pipeline = $this->successionService->getSuccessionPipeline($tenantId);

        $positions = Position::where('tenant_id', $tenantId)
            ->with('department')
            ->orderBy('title')
            ->get();

        $employees = Employee::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with(['position', 'department'])
            ->orderBy('first_name')
            ->get();

        return view('company.succession.index', compact('pipeline', 'positions', 'employees'));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();

        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'current_holder_employee_id' => 'nullable|exists:employees,id',
            'criticality_level' => 'required|in:low,medium,high,critical',
            'emergency_replacement_plan' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500',
        ]);

        SuccessionPlan::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'position_id' => $validated['position_id'],
            ],
            [
                'current_holder_employee_id' => $validated['current_holder_employee_id'] ?? null,
                'criticality_level' => $validated['criticality_level'],
                'emergency_replacement_plan' => $validated['emergency_replacement_plan'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'active',
            ]
        );

        return redirect()->route('company.succession.index')
            ->with('success', 'Rencana suksesi posisi kunci berhasil disimpan.');
    }

    public function storeCandidate(Request $request): RedirectResponse
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();

        $validated = $request->validate([
            'succession_plan_id' => 'required|exists:succession_plans,id',
            'candidate_employee_id' => 'required|exists:employees,id',
            'readiness_level' => 'required|in:ready_now,ready_1_year,ready_2_plus_years',
            'strengths' => 'nullable|string|max:1000',
            'development_needs' => 'nullable|string|max:1000',
        ]);

        $this->successionService->nominateSuccessor(
            $tenantId,
            (int)$validated['succession_plan_id'],
            (int)$validated['candidate_employee_id'],
            $validated['readiness_level'],
            $validated['strengths'] ?? null,
            $validated['development_needs'] ?? null
        );

        return redirect()->route('company.succession.index')
            ->with('success', 'Kandidat suksesor berhasil dinominasikan ke dalam talent pool.');
    }

    public function suggestCandidates(Position $position): JsonResponse
    {
        $tenantId = app(TenantManager::class)->getCurrentTenantId();
        if ($position->tenant_id !== $tenantId) {
            abort(403);
        }

        $suggestions = $this->successionService->suggestSuccessorsForPosition($tenantId, $position);

        return response()->json([
            'position' => [
                'id' => $position->id,
                'title' => $position->title,
                'department' => $position->department?->name ?? 'Umum',
            ],
            'suggestions' => $suggestions,
        ]);
    }
}
