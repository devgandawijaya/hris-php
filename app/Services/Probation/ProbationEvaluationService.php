<?php

namespace App\Services\Probation;

use App\Models\Company;
use App\Models\Employee;
use App\Models\ProbationEvaluation;
use App\Models\User;
use Carbon\Carbon;

class ProbationEvaluationService
{
    /**
     * Conduct formal probation evaluation pursuant to Pasal 60 UU No. 13/2003.
     */
    public function evaluateProbation(Employee $employee, User $evaluator, array $data): ProbationEvaluation
    {
        $tech = (float) ($data['technical_competence_score'] ?? 0.0);
        $attend = (float) ($data['attendance_discipline_score'] ?? 0.0);
        $culture = (float) ($data['culture_integrity_score'] ?? 0.0);

        // Weighted composite score (40% Technical, 30% Attendance, 30% Culture)
        $finalScore = round(($tech * 0.40) + ($attend * 0.30) + ($culture * 0.30), 2);

        $decision = $data['decision'] ?? ($finalScore >= 75.0 ? 'passed_pkwtt' : 'failed_terminated');

        $skNumber = null;
        if ($decision === 'passed_pkwtt') {
            $year = date('Y');
            $seq = str_pad((string) (ProbationEvaluation::where('company_id', $employee->company_id)->where('decision', 'passed_pkwtt')->count() + 1), 3, '0', STR_PAD_LEFT);
            $skNumber = "{$seq}/SK-DIR/PKWTT/{$year}";

            // Auto-promote employee to permanent (PKWTT)
            $employee->employment_status = 'pkwtt';
            $employee->saveQuietly();
        } elseif ($decision === 'failed_terminated') {
            $employee->status = 'inactive';
            $employee->end_date = now()->toDateString();
            $employee->saveQuietly();
        }

        $evaluation = new ProbationEvaluation();
        $evaluation->tenant_id = $employee->tenant_id;
        $evaluation->company_id = $employee->company_id;
        $evaluation->employee_id = $employee->id;
        $evaluation->evaluator_user_id = $evaluator->id;
        $evaluation->evaluation_date = $data['evaluation_date'] ?? date('Y-m-d');
        $evaluation->technical_competence_score = $tech;
        $evaluation->attendance_discipline_score = $attend;
        $evaluation->culture_integrity_score = $culture;
        $evaluation->final_score = $finalScore;
        $evaluation->decision = $decision;
        $evaluation->sk_number = $skNumber;
        $evaluation->evaluator_notes = $data['evaluator_notes'] ?? null;
        $evaluation->development_recommendations = $data['development_recommendations'] ?? null;
        $evaluation->decided_at = now();
        $evaluation->save();

        return $evaluation;
    }
}
