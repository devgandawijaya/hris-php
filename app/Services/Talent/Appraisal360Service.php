<?php

namespace App\Services\Talent;

use App\Models\Employee;
use App\Models\PerformanceFeedback360;
use App\Models\PerformanceImprovementPlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Appraisal360Service
{
    /**
     * Request 360 multi-rater feedback.
     */
    public function createFeedbackRequest(
        int $tenantId,
        int $subjectEmployeeId,
        ?int $reviewerEmployeeId,
        string $relationship,
        ?int $cycleId = null,
        bool $isAnonymous = true
    ): PerformanceFeedback360 {
        return PerformanceFeedback360::create([
            'tenant_id' => $tenantId,
            'review_cycle_id' => $cycleId,
            'subject_employee_id' => $subjectEmployeeId,
            'reviewer_employee_id' => $reviewerEmployeeId,
            'rater_relationship' => $relationship,
            'is_anonymous' => $isAnonymous,
            'status' => 'pending',
        ]);
    }

    /**
     * Submit completed 360 feedback evaluation.
     */
    public function submitFeedback(PerformanceFeedback360 $feedback, array $data): PerformanceFeedback360
    {
        $core = (int) ($data['core_values_score'] ?? 4);
        $leadership = (int) ($data['leadership_score'] ?? 4);
        $collab = (int) ($data['collaboration_score'] ?? 4);
        $exec = (int) ($data['execution_score'] ?? 4);

        $overall = round(($core + $leadership + $collab + $exec) / 4, 2);

        $feedback->update([
            'core_values_score' => $core,
            'leadership_score' => $leadership,
            'collaboration_score' => $collab,
            'execution_score' => $exec,
            'overall_score' => $overall,
            'strengths' => $data['strengths'] ?? null,
            'areas_for_improvement' => $data['areas_for_improvement'] ?? null,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $feedback->fresh();
    }

    /**
     * Compile 360 review summary metrics for an employee.
     */
    public function getEmployee360Summary(int $employeeId): array
    {
        $feedbacks = PerformanceFeedback360::where('subject_employee_id', $employeeId)
            ->where('status', 'completed')
            ->get();

        if ($feedbacks->isEmpty()) {
            return [
                'total_responses' => 0,
                'overall_average' => 0.0,
                'core_values_avg' => 0.0,
                'leadership_avg' => 0.0,
                'collaboration_avg' => 0.0,
                'execution_avg' => 0.0,
                'by_relationship' => [],
                'feedbacks' => [],
            ];
        }

        $overallAvg = round($feedbacks->avg('overall_score'), 2);
        $coreAvg = round($feedbacks->avg('core_values_score'), 2);
        $leadAvg = round($feedbacks->avg('leadership_score'), 2);
        $collabAvg = round($feedbacks->avg('collaboration_score'), 2);
        $execAvg = round($feedbacks->avg('execution_score'), 2);

        $byRelationship = [];
        foreach (['supervisor', 'peer', 'subordinate', 'self'] as $rel) {
            $subset = $feedbacks->where('rater_relationship', $rel);
            if ($subset->isNotEmpty()) {
                $byRelationship[$rel] = [
                    'count' => $subset->count(),
                    'average' => round($subset->avg('overall_score'), 2),
                ];
            }
        }

        return [
            'total_responses' => $feedbacks->count(),
            'overall_average' => $overallAvg,
            'core_values_avg' => $coreAvg,
            'leadership_avg' => $leadAvg,
            'collaboration_avg' => $collabAvg,
            'execution_avg' => $execAvg,
            'by_relationship' => $byRelationship,
            'feedbacks' => $feedbacks,
        ];
    }

    /**
     * Create formal Performance Improvement Plan (PIP).
     */
    public function createPip(int $tenantId, int $employeeId, array $data): PerformanceImprovementPlan
    {
        return PerformanceImprovementPlan::create([
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'supervisor_employee_id' => $data['supervisor_employee_id'] ?? null,
            'title' => $data['title'],
            'reason' => $data['reason'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'target_objectives' => $data['target_objectives'],
            'action_plan' => $data['action_plan'],
            'status' => 'in_progress',
        ]);
    }

    /**
     * Update progress notes for an ongoing PIP.
     */
    public function updatePipProgress(PerformanceImprovementPlan $pip, array $data): PerformanceImprovementPlan
    {
        $existingNotes = $pip->progress_notes ? $pip->progress_notes . "\n---\n" : '';
        $newEntry = "[" . now()->format('Y-m-d H:i') . "] " . ($data['notes'] ?? '');

        $pip->update([
            'progress_notes' => trim($existingNotes . $newEntry),
        ]);

        return $pip->fresh();
    }

    /**
     * Complete and evaluate PIP (passed, failed, extended).
     */
    public function completePip(PerformanceImprovementPlan $pip, string $status, ?string $finalEvaluation): PerformanceImprovementPlan
    {
        $pip->update([
            'status' => $status,
            'final_evaluation' => $finalEvaluation,
            'completed_at' => now(),
        ]);

        return $pip->fresh();
    }

    /**
     * Retrieve all pending 360 feedback requests for a reviewer.
     */
    public function getPendingRequestsForReviewer(int $reviewerEmployeeId): Collection
    {
        return PerformanceFeedback360::with('subjectEmployee.position')
            ->where('reviewer_employee_id', $reviewerEmployeeId)
            ->where('status', 'pending')
            ->get();
    }
}
