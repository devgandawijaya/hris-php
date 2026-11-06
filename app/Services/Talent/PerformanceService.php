<?php

namespace App\Services\Talent;

use App\Models\Employee;
use App\Models\KpiGoal;
use App\Models\PerformanceCycle;
use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PerformanceService
{
    /**
     * Create a performance appraisal cycle.
     */
    public function createCycle(int $tenantId, array $data): PerformanceCycle
    {
        return PerformanceCycle::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'active',
        ]);
    }

    /**
     * Add a KPI/OKR goal for an employee.
     */
    public function addGoal(Employee $employee, array $data): KpiGoal
    {
        return KpiGoal::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'performance_cycle_id' => $data['performance_cycle_id'] ?? null,
            'type' => $data['type'] ?? 'kpi',
            'title' => $data['title'],
            'target_value' => $data['target_value'],
            'current_value' => $data['current_value'] ?? 0.00,
            'unit' => $data['unit'] ?? '%',
            'weight' => $data['weight'] ?? 100.00,
            'deadline' => $data['deadline'] ?? null,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Update current progress of a KPI/OKR goal.
     */
    public function updateGoalProgress(KpiGoal $goal, float $currentValue): KpiGoal
    {
        $status = 'in_progress';
        if ($currentValue >= (float) $goal->target_value) {
            $status = 'achieved';
        }

        $goal->update([
            'current_value' => $currentValue,
            'status' => $status,
        ]);

        return $goal;
    }

    /**
     * Submit employee self-review for a cycle.
     */
    public function submitSelfReview(
        PerformanceCycle $cycle,
        Employee $employee,
        float $rating,
        string $feedback
    ): PerformanceReview {
        return PerformanceReview::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'performance_cycle_id' => $cycle->id,
                'employee_id' => $employee->id,
            ],
            [
                'self_rating' => $rating,
                'self_feedback' => $feedback,
                'status' => 'submitted',
            ]
        );
    }

    /**
     * Submit manager review & calculate final score.
     */
    public function submitManagerReview(
        PerformanceReview $review,
        User $manager,
        float $rating,
        string $feedback
    ): PerformanceReview {
        // Final score formula: 30% Self + 70% Manager (or 100% Manager if self rating is empty)
        $selfRating = (float) ($review->self_rating ?? $rating);
        $finalScore = round(($selfRating * 0.30) + ($rating * 0.70), 2);

        $review->update([
            'reviewer_id' => $manager->id,
            'manager_rating' => $rating,
            'manager_feedback' => $feedback,
            'final_score' => $finalScore,
            'status' => 'finalized',
        ]);

        return $review;
    }
}

