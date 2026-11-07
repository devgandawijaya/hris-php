<?php

namespace App\Services\Offboarding;

use App\Models\Company;
use App\Models\Employee;
use App\Models\OffboardingClearance;
use App\Models\User;
use App\Models\WorkExperienceLetter;
use Carbon\Carbon;

class ClearanceService
{
    /**
     * Initiate offboarding clearance process for an employee.
     */
    public function initiateClearance(Employee $employee, array $data): OffboardingClearance
    {
        $clearance = new OffboardingClearance();
        $clearance->tenant_id = $employee->tenant_id;
        $clearance->company_id = $employee->company_id;
        $clearance->employee_id = $employee->id;
        $clearance->resignation_date = $data['resignation_date'] ?? date('Y-m-d');
        $clearance->last_working_day = $data['last_working_day'] ?? Carbon::parse($clearance->resignation_date)->addMonth()->toDateString();
        $clearance->separation_reason = $data['separation_reason'] ?? 'resigned';
        $clearance->overall_status = 'in_progress';
        $clearance->save();

        return $clearance;
    }

    /**
     * Sign off a specific department (hr, it, finance, supervisor).
     */
    public function signOffDepartment(OffboardingClearance $clearance, string $department, User $officer, array $data): OffboardingClearance
    {
        $statusField = "{$department}_status";
        $notesField = "{$department}_notes";
        $byField = "{$department}_cleared_by";
        $atField = "{$department}_cleared_at";

        $clearance->$statusField = $data['status'] ?? 'cleared';
        $clearance->$notesField = $data['notes'] ?? null;
        $clearance->$byField = $officer->id;
        $clearance->$atField = now();

        // Check if all 4 departments are cleared
        if (
            $clearance->hr_status === 'cleared' &&
            $clearance->it_status === 'cleared' &&
            $clearance->finance_status === 'cleared' &&
            $clearance->supervisor_status === 'cleared'
        ) {
            $clearance->overall_status = 'completed';
            $clearance->completed_at = now();

            // Set employee status to inactive/terminated on their last working day
            $employee = $clearance->employee;
            if ($employee) {
                $employee->end_date = $clearance->last_working_day;
                $employee->status = 'inactive';
                $employee->saveQuietly();
            }
        }

        $clearance->save();
        return $clearance;
    }

    /**
     * Generate official Indonesian Surat Pengalaman Kerja (Paklaring) pursuant to Pasal 1603p KUHPerdata.
     */
    public function issuePaklaring(OffboardingClearance $clearance, User $signer, array $options = []): WorkExperienceLetter
    {
        $employee = $clearance->employee;
        $company = $clearance->company;

        $year = date('Y');
        $month = (int) date('n');
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $roman = $romanMonths[$month] ?? 'I';

        $count = WorkExperienceLetter::where('company_id', $company->id)
            ->whereYear('created_at', $year)
            ->count() + 1;

        $sequence = str_pad((string) $count, 3, '0', STR_PAD_LEFT);
        $letterNumber = "{$sequence}/HRD-PKL/{$roman}/{$year}";

        $startDate = $employee->join_date ? $employee->join_date->toDateString() : date('Y-m-d');
        $endDate = $clearance->last_working_day ? $clearance->last_working_day->toDateString() : date('Y-m-d');

        $positionName = $employee->position ? $employee->position->title : 'Staff';
        $departmentName = $employee->department ? $employee->department->name : 'Operasional';

        $letter = WorkExperienceLetter::updateOrCreate(
            [
                'tenant_id' => $company->tenant_id,
                'offboarding_clearance_id' => $clearance->id,
            ],
            [
                'company_id' => $company->id,
                'employee_id' => $employee->id,
                'letter_number' => $letterNumber,
                'signer_name' => $signer->name,
                'signer_position' => $options['signer_position'] ?? 'Human Resource Manager',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'last_position' => $positionName,
                'last_department' => $departmentName,
                'conduct_appraisal' => $options['conduct_appraisal'] ?? 'very_good',
                'achievement_summary' => $options['achievement_summary'] ?? 'Telah menunjukkan integritas, loyalitas, dedikasi, serta kontribusi positif selama masa pengabdian di perusahaan.',
                'issued_date' => date('Y-m-d'),
            ]
        );

        return $letter;
    }
}
