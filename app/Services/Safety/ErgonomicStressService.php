<?php

namespace App\Services\Safety;

use App\Models\Company;
use App\Models\Employee;
use App\Models\ErgonomicAssessment;
use App\Models\DigitalStressSurvey;
use Carbon\Carbon;

class ErgonomicStressService
{
    /**
     * Conduct workstation ergonomic assessment per Permenaker No. 5 Tahun 2018.
     */
    public function recordErgonomicAssessment(
        Company $company,
        ?int $employeeId,
        ?int $departmentId,
        int $lightingLuxLevel = 350,
        bool $lumbarSupportCompliant = true,
        bool $monitorEyeLevelCompliant = true,
        bool $wristRestCompliant = true,
        string $workstationType = 'Office Desk & Monitor Workstation',
        ?string $correctiveActions = null
    ): ErgonomicAssessment {
        // Compute compliance score (0 - 100)
        $score = 0;

        // 1. Lighting (Permenaker 5/2018: standard min 300 Lux)
        if ($lightingLuxLevel >= 300) {
            $score += 30;
        } elseif ($lightingLuxLevel >= 200) {
            $score += 15;
        } else {
            $score += 5;
        }

        // 2. Physical accessories
        if ($lumbarSupportCompliant) $score += 25;
        if ($monitorEyeLevelCompliant) $score += 25;
        if ($wristRestCompliant) $score += 20;

        // Posture Risk Level
        if ($score >= 80) {
            $riskLevel = 'low';
            $status = 'completed';
        } elseif ($score >= 60) {
            $riskLevel = 'moderate';
            $status = 'completed';
        } else {
            $riskLevel = 'high';
            $status = 'action_required';
        }

        return ErgonomicAssessment::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'employee_id' => $employeeId,
            'department_id' => $departmentId,
            'assessment_date' => Carbon::today()->toDateString(),
            'workstation_type' => $workstationType,
            'lighting_lux_level' => $lightingLuxLevel,
            'chair_lumbar_support_compliant' => $lumbarSupportCompliant,
            'monitor_eye_level_compliant' => $monitorEyeLevelCompliant,
            'keyboard_wrist_rest_compliant' => $wristRestCompliant,
            'posture_risk_level' => $riskLevel,
            'ergonomic_compliance_score' => (float) $score,
            'corrective_actions' => $correctiveActions,
            'status' => $status,
        ]);
    }

    /**
     * Evaluate and store a digital workload & screen fatigue stress survey.
     */
    public function recordStressSurvey(
        Employee $employee,
        float $avgDailyScreenHours,
        int $meetingFatigueScore,
        int $afterHoursCommunicationScore,
        int $eyestrainHeadacheFrequency
    ): DigitalStressSurvey {
        $screenPenalty = min(30.0, max(0.0, ($avgDailyScreenHours - 6.0) * 7.5));
        $meetingPenalty = ($meetingFatigueScore / 5.0) * 25.0;
        $afterHoursPenalty = ($afterHoursCommunicationScore / 5.0) * 25.0;
        $eyestrainPenalty = ($eyestrainHeadacheFrequency / 5.0) * 20.0;

        $burnoutIndex = round($screenPenalty + $meetingPenalty + $afterHoursPenalty + $eyestrainPenalty, 2);

        if ($burnoutIndex < 35.0) {
            $classification = 'low';
            $recommendation = 'Beban kerja digital terpantau seimbang dan produktif.';
        } elseif ($burnoutIndex < 60.0) {
            $classification = 'moderate';
            $recommendation = 'Disarankan menerapkan aturan 20-20-20 (tiap 20 menit tatap objek 20 kaki selama 20 detik) dan membatasi meeting virtual.';
        } elseif ($burnoutIndex < 80.0) {
            $classification = 'high';
            $recommendation = 'Tingkat kelelahan kognitif tinggi. Perlu penataan batas jam komunikasi di luar jam kerja (Right to Disconnect) dan rotasi tugas.';
        } else {
            $classification = 'severe';
            $recommendation = 'Risiko burnout digital akut. Rekomendasikan konseling psikologis kerja dan cuti penyegaran segera.';
        }

        return DigitalStressSurvey::create([
            'tenant_id' => $employee->tenant_id,
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'survey_date' => Carbon::today()->toDateString(),
            'avg_daily_screen_hours' => $avgDailyScreenHours,
            'meeting_fatigue_score' => $meetingFatigueScore,
            'after_hours_communication_score' => $afterHoursCommunicationScore,
            'eyestrain_headache_frequency' => $eyestrainHeadacheFrequency,
            'digital_burnout_risk_index' => $burnoutIndex,
            'risk_classification' => $classification,
            'coping_recommendation' => $recommendation,
        ]);
    }
}
