<?php

namespace App\Services\Talent;

use App\Models\Employee;
use App\Models\PerformanceReview;
use App\Models\Position;
use App\Models\SuccessionCandidate;
use App\Models\SuccessionPlan;
use Illuminate\Database\Eloquent\Collection;

class SuccessionService
{
    public function __construct(
        protected SkillMatrixService $skillMatrixService
    ) {}

    /**
     * Get overall succession pipeline statistics and key roles
     */
    public function getSuccessionPipeline(int $tenantId): array
    {
        $plans = SuccessionPlan::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with(['position.department', 'currentHolder', 'candidates.candidate.position', 'candidates.candidate.department'])
            ->get();

        $totalPlans = $plans->count();
        $coveredCount = $plans->filter(fn($p) => $p->ready_now_count > 0)->count();
        $uncoveredCount = $totalPlans - $coveredCount;
        $totalCandidates = $plans->sum(fn($p) => $p->candidates->where('status', 'active')->count());

        $benchStrengthPct = $totalPlans > 0 
            ? round(($coveredCount / $totalPlans) * 100, 1) 
            : 0;

        return [
            'total_plans' => $totalPlans,
            'covered_count' => $coveredCount,
            'uncovered_count' => $uncoveredCount,
            'total_candidates' => $totalCandidates,
            'bench_strength_percentage' => $benchStrengthPct,
            'plans' => $plans,
        ];
    }

    /**
     * Nominate an employee to a succession plan bench
     */
    public function nominateSuccessor(
        int $tenantId,
        int $successionPlanId,
        int $candidateEmployeeId,
        string $readinessLevel = 'ready_1_year',
        ?string $strengths = null,
        ?string $developmentNeeds = null
    ): SuccessionCandidate {
        return SuccessionCandidate::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'succession_plan_id' => $successionPlanId,
                'candidate_employee_id' => $candidateEmployeeId,
            ],
            [
                'readiness_level' => $readinessLevel,
                'strengths' => $strengths,
                'development_needs' => $developmentNeeds,
                'status' => 'active',
            ]
        );
    }

    /**
     * Algorithmic talent suggestion for a position based on Performance Reviews and Skill Match
     */
    public function suggestSuccessorsForPosition(int $tenantId, Position $position): array
    {
        $plan = SuccessionPlan::where('position_id', $position->id)->first();
        $currentHolderId = $plan?->current_holder_employee_id;

        // Query active employees in the tenant excluding the current holder
        $query = Employee::where('tenant_id', $tenantId)
            ->where('status', 'active');

        if ($currentHolderId) {
            $query->where('id', '!=', $currentHolderId);
        }

        $candidates = $query->with(['position', 'department'])->get();
        $suggestions = [];

        foreach ($candidates as $candidate) {
            // 1. Get latest performance score from Phase 4 reviews (default 75 if unreviewed)
            $latestReview = PerformanceReview::where('employee_id', $candidate->id)
                ->where('status', 'finalized')
                ->latest()
                ->first();

            $perfScore = $latestReview ? (float)$latestReview->final_score : 75.0;

            // 2. Temporarily evaluate candidate's skills against target position
            $originalPositionId = $candidate->position_id;
            $candidate->position_id = $position->id;
            $skillRes = $this->skillMatrixService->calculateEmployeeSkillGap($candidate);
            $candidate->position_id = $originalPositionId; // restore

            $skillScore = (float)$skillRes['overall_match_percentage'];

            // 3. Combined Readiness Index: 60% Performance + 40% Skill Match
            $combinedScore = round((0.6 * $perfScore) + (0.4 * $skillScore), 1);

            // Determine suggested readiness tier
            $suggestedReadiness = match (true) {
                $combinedScore >= 85 => 'ready_now',
                $combinedScore >= 70 => 'ready_1_year',
                default => 'ready_2_plus_years',
            };

            $suggestions[] = [
                'employee_id' => $candidate->id,
                'employee_name' => $candidate->full_name,
                'employee_code' => $candidate->employee_code,
                'current_position' => $candidate->position?->title ?? 'Staf',
                'current_department' => $candidate->department?->name ?? 'Umum',
                'performance_score' => $perfScore,
                'skill_match_score' => $skillScore,
                'combined_score' => $combinedScore,
                'suggested_readiness' => $suggestedReadiness,
            ];
        }

        // Sort descending by combined score
        usort($suggestions, fn($a, $b) => $b['combined_score'] <=> $a['combined_score']);

        return array_slice($suggestions, 0, 5); // top 5 talent recommendations
    }
}

