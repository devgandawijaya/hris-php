<?php

namespace App\Services\Compensation;

use App\Models\Company;
use App\Models\Employee;
use App\Models\JobGrade;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service to manage Struktur dan Skala Upah per Permenaker No. 1 Tahun 2017.
 */
class WageScaleService
{
    /**
     * Create or update a Job Grade with automated spread and midpoint calculation.
     */
    public function upsertJobGrade(Company $company, array $data, ?JobGrade $existingGrade = null): JobGrade
    {
        $min = (float) $data['min_salary'];
        $max = (float) $data['max_salary'];
        $mid = isset($data['mid_salary']) && $data['mid_salary'] > 0
            ? (float) $data['mid_salary']
            : round(($min + $max) / 2, 2);

        $spread = $min > 0 ? round((($max - $min) / $min) * 100, 2) : 0.00;

        $grade = $existingGrade ?? new JobGrade();
        $grade->tenant_id = $company->tenant_id;
        $grade->company_id = $company->id;
        $grade->grade_code = strtoupper(trim($data['grade_code']));
        $grade->name = trim($data['name']);
        $grade->level_order = (int) ($data['level_order'] ?? 1);
        $grade->min_salary = $min;
        $grade->mid_salary = $mid;
        $grade->max_salary = $max;
        $grade->spread_percentage = $spread;
        $grade->description = $data['description'] ?? null;
        $grade->is_active = $data['is_active'] ?? true;
        $grade->save();

        $this->recalculateMidpointProgressions($company);

        return $grade;
    }

    /**
     * Recalculate midpoint progression % across all active grades sorted by level_order.
     */
    public function recalculateMidpointProgressions(Company $company): void
    {
        $grades = JobGrade::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('level_order', 'asc')
            ->get();

        $prevMid = null;
        foreach ($grades as $grade) {
            if ($prevMid !== null && $prevMid > 0) {
                $progression = round((($grade->mid_salary - $prevMid) / $prevMid) * 100, 2);
                $grade->midpoint_progression = $progression;
            } else {
                $grade->midpoint_progression = 0.00;
            }
            $grade->saveQuietly();
            $prevMid = (float) $grade->mid_salary;
        }
    }

    /**
     * Perform compa-ratio audit for all employees in a company.
     * Returns statistics, compa-ratio breakdown, and compliance alerts.
     */
    public function performWageScaleAudit(Company $company): array
    {
        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->with(['jobGrade', 'department', 'position'])
            ->get();

        $totalEmployees = $employees->count();
        $assignedCount = 0;
        $unassignedCount = 0;
        $underpaidCount = 0;
        $penetrationCount = 0;
        $parityCount = 0;
        $premiumCount = 0;
        $redCircleCount = 0;

        $auditedEmployees = [];

        foreach ($employees as $emp) {
            $basic = (float) $emp->basic_salary;
            $grade = $emp->jobGrade;

            if (!$grade) {
                $unassignedCount++;
                $auditedEmployees[] = [
                    'employee' => $emp,
                    'grade' => null,
                    'compa_ratio' => null,
                    'band_status' => 'unassigned',
                    'band_label' => 'Belum Ada Golongan',
                    'badge_class' => 'bg-gray-100 text-gray-700',
                ];
                continue;
            }

            $assignedCount++;
            $mid = (float) $grade->mid_salary;
            $min = (float) $grade->min_salary;
            $max = (float) $grade->max_salary;

            $compaRatio = $mid > 0 ? round(($basic / $mid) * 100, 1) : 100.0;

            if ($basic < $min) {
                $underpaidCount++;
                $status = 'underpaid';
                $label = 'Di Bawah Minimum (< Min)';
                $badge = 'bg-rose-100 text-rose-700';
            } elseif ($compaRatio < 100.0) {
                $penetrationCount++;
                $status = 'penetration';
                $label = 'Penetrasi (80% - 99%)';
                $badge = 'bg-amber-100 text-amber-700';
            } elseif (abs($compaRatio - 100.0) < 0.1) {
                $parityCount++;
                $status = 'parity';
                $label = 'Market Parity (100%)';
                $badge = 'bg-emerald-100 text-emerald-700';
            } elseif ($basic <= $max) {
                $premiumCount++;
                $status = 'premium';
                $label = 'Premium (101% - 120%)';
                $badge = 'bg-blue-100 text-blue-700';
            } else {
                $redCircleCount++;
                $status = 'red_circle';
                $label = 'Red Circle (> Max Band)';
                $badge = 'bg-purple-100 text-purple-700';
            }

            $auditedEmployees[] = [
                'employee' => $emp,
                'grade' => $grade,
                'compa_ratio' => $compaRatio,
                'band_status' => $status,
                'band_label' => $label,
                'badge_class' => $badge,
            ];
        }

        return [
            'total_employees' => $totalEmployees,
            'assigned_count' => $assignedCount,
            'unassigned_count' => $unassignedCount,
            'underpaid_count' => $underpaidCount,
            'penetration_count' => $penetrationCount,
            'parity_count' => $parityCount,
            'premium_count' => $premiumCount,
            'red_circle_count' => $redCircleCount,
            'compliance_rate' => $totalEmployees > 0 ? round((($totalEmployees - $underpaidCount) / $totalEmployees) * 100, 1) : 100.0,
            'audited_employees' => $auditedEmployees,
        ];
    }
}
