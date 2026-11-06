<?php

namespace App\Services\Talent;

use App\Models\Employee;
use App\Models\EmployeeSkill;
use App\Models\Position;
use App\Models\PositionSkillRequirement;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\TrainingCourse;
use Illuminate\Support\Collection;

class SkillMatrixService
{
    /**
     * Calculate competency gaps and matching score for an employee against their position requirements.
     */
    public function calculateEmployeeSkillGap(Employee $employee): array
    {
        $position = $employee->position;
        if (!$position) {
            return [
                'has_requirements' => false,
                'overall_match_percentage' => 0,
                'total_requirements' => 0,
                'met_requirements' => 0,
                'skills' => [],
                'recommended_trainings' => [],
            ];
        }

        // Load position requirements
        $requirements = PositionSkillRequirement::where('position_id', $position->id)
            ->with(['skill.category'])
            ->get();

        if ($requirements->isEmpty()) {
            return [
                'has_requirements' => false,
                'overall_match_percentage' => 100,
                'total_requirements' => 0,
                'met_requirements' => 0,
                'skills' => [],
                'recommended_trainings' => [],
            ];
        }

        // Load employee's current skills
        $employeeSkills = EmployeeSkill::where('employee_id', $employee->id)
            ->with('skill')
            ->get()
            ->keyBy('skill_id');

        $skillDetails = [];
        $totalRequiredPoints = 0;
        $totalEarnedPoints = 0;
        $metCount = 0;
        $gapCategories = [];

        foreach ($requirements as $req) {
            $empSkill = $employeeSkills->get($req->skill_id);
            $currentLevel = $empSkill ? $empSkill->current_level : 0;
            $requiredLevel = $req->required_level;

            $gap = max(0, $requiredLevel - $currentLevel);
            $isMet = $currentLevel >= $requiredLevel;
            if ($isMet) {
                $metCount++;
            } else {
                // Collect category to recommend training
                if ($req->skill && $req->skill->category) {
                    $gapCategories[] = strtolower($req->skill->category->name);
                }
            }

            $totalRequiredPoints += $requiredLevel;
            $totalEarnedPoints += min($currentLevel, $requiredLevel);

            $matchPct = $requiredLevel > 0 
                ? min(100, round(($currentLevel / $requiredLevel) * 100)) 
                : 100;

            $skillDetails[] = [
                'skill_id' => $req->skill_id,
                'skill_name' => $req->skill->name ?? 'Skill',
                'category_name' => $req->skill->category->name ?? 'General',
                'required_level' => $requiredLevel,
                'current_level' => $currentLevel,
                'gap' => $gap,
                'is_met' => $isMet,
                'is_mandatory' => $req->is_mandatory,
                'match_percentage' => $matchPct,
                'certification_name' => $empSkill?->certification_name,
            ];
        }

        $overallMatch = $totalRequiredPoints > 0 
            ? round(($totalEarnedPoints / $totalRequiredPoints) * 100, 1) 
            : 100.0;

        // Find recommended training courses from Phase 4
        $recommendedTrainings = $this->findRecommendedTrainings($employee->tenant_id, $gapCategories);

        return [
            'has_requirements' => true,
            'overall_match_percentage' => $overallMatch,
            'total_requirements' => $requirements->count(),
            'met_requirements' => $metCount,
            'skills' => $skillDetails,
            'recommended_trainings' => $recommendedTrainings,
        ];
    }

    /**
     * Find relevant training courses based on identified skill gaps
     */
    protected function findRecommendedTrainings(int $tenantId, array $gapCategories): Collection
    {
        if (empty($gapCategories)) {
            return collect();
        }

        $query = TrainingCourse::where('tenant_id', $tenantId)
            ->whereIn('status', ['scheduled', 'ongoing']);

        return $query->limit(4)->get();
    }

    /**
     * Assess or endorse an employee's skill proficiency level
     */
    public function assessEmployeeSkill(
        int $tenantId,
        int $employeeId,
        int $skillId,
        int $currentLevel,
        ?string $certificationName = null,
        ?string $certificationDate = null,
        ?string $validUntil = null,
        ?int $verifiedByUserId = null,
        ?string $assessmentNotes = null
    ): EmployeeSkill {
        return EmployeeSkill::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'skill_id' => $skillId,
            ],
            [
                'current_level' => max(1, min(5, $currentLevel)),
                'certification_name' => $certificationName,
                'certification_date' => $certificationDate,
                'valid_until' => $validUntil,
                'verified_by_user_id' => $verifiedByUserId,
                'assessment_notes' => $assessmentNotes,
            ]
        );
    }

    /**
     * Get department-wide competency health overview
     */
    public function getDepartmentCompetencyOverview(int $tenantId, ?int $departmentId = null): array
    {
        $query = Employee::where('tenant_id', $tenantId)->where('status', 'active');
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->with(['position', 'department'])->get();
        if ($employees->isEmpty()) {
            return [
                'total_employees' => 0,
                'average_match_score' => 0,
                'high_readiness_count' => 0,
                'gap_count' => 0,
            ];
        }

        $totalScore = 0;
        $highReadiness = 0;
        $gapCount = 0;

        foreach ($employees as $emp) {
            $res = $this->calculateEmployeeSkillGap($emp);
            $totalScore += $res['overall_match_percentage'];
            if ($res['overall_match_percentage'] >= 80) {
                $highReadiness++;
            }
            if ($res['has_requirements'] && $res['met_requirements'] < $res['total_requirements']) {
                $gapCount++;
            }
        }

        return [
            'total_employees' => $employees->count(),
            'average_match_score' => round($totalScore / $employees->count(), 1),
            'high_readiness_count' => $highReadiness,
            'gap_count' => $gapCount,
        ];
    }
}

