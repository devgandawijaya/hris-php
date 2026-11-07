<?php

namespace App\Services\Payroll;

use App\Models\Company;
use App\Models\Employee;
use App\Models\VoluntarySeparationProgram;
use App\Models\VssApplication;
use App\Models\DplkParticipation;
use Carbon\Carbon;
use InvalidArgumentException;

class VoluntarySeparationService
{
    /**
     * Create a voluntary separation scheme (VSS / Golden Handshake) program.
     */
    public function createProgram(Company $company, array $data): VoluntarySeparationProgram
    {
        $programCode = $data['program_code'] ?? 'VSS-' . date('Y') . '-' . rand(10, 99);
        $multiplier = (float) ($data['severance_multiplier'] ?? 1.50);

        return VoluntarySeparationProgram::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'program_code' => $programCode,
            'title' => $data['title'] ?? 'Program Pensiun Dini Sukarela (VSS)',
            'open_date' => $data['open_date'],
            'close_date' => $data['close_date'],
            'severance_multiplier' => $multiplier,
            'additional_golden_handshake_bonus' => $data['additional_golden_handshake_bonus'] ?? 0.00,
            'max_quota' => $data['max_quota'] ?? 20,
            'approved_applicants_count' => 0,
            'status' => 'open',
        ]);
    }

    /**
     * Calculate statutory severance package with VSS multiplier.
     * Uses PP 35/2021 formula multiplied by the program multiplier.
     */
    public function calculatePackage(VoluntarySeparationProgram $program, Employee $employee): array
    {
        $joinDate = $employee->join_date ? Carbon::parse($employee->join_date) : Carbon::now()->subYears(5);
        $tenureYears = max(1, (int) $joinDate->diffInYears(Carbon::now()));
        $basicSalary = (float) $employee->basic_salary;

        // PP 35/2021 baseline UP: 1-9 months based on tenure
        $upMonths = min(9, $tenureYears + 1);
        $baseSeverance = $upMonths * $basicSalary;

        $multiplier = (float) $program->severance_multiplier;
        $calculatedSeverance = round($baseSeverance * $multiplier, 2);
        $goldenHandshake = (float) $program->additional_golden_handshake_bonus;
        $totalPackage = $calculatedSeverance + $goldenHandshake;

        return [
            'employee_id' => $employee->id,
            'tenure_years' => $tenureYears,
            'last_basic_salary' => $basicSalary,
            'statutory_baseline_severance' => $baseSeverance,
            'program_multiplier' => $multiplier,
            'calculated_severance_amount' => $calculatedSeverance,
            'additional_bonus' => $goldenHandshake,
            'total_package_received' => $totalPackage,
        ];
    }

    /**
     * Submit an application for VSS.
     */
    public function applyForProgram(VoluntarySeparationProgram $program, Employee $employee): VssApplication
    {
        if ($program->status !== 'open') {
            throw new InvalidArgumentException('Program pensiun dini sukarela tidak sedang dibuka.');
        }

        if ($program->approved_applicants_count >= $program->max_quota) {
            throw new InvalidArgumentException('Kuota program pensiun dini sukarela telah terpenuhi.');
        }

        $calc = $this->calculatePackage($program, $employee);

        return VssApplication::create([
            'tenant_id' => $program->tenant_id,
            'voluntary_separation_program_id' => $program->id,
            'employee_id' => $employee->id,
            'application_date' => Carbon::now()->toDateString(),
            'tenure_years' => $calc['tenure_years'],
            'last_basic_salary' => $calc['last_basic_salary'],
            'calculated_severance_amount' => $calc['calculated_severance_amount'],
            'total_package_received' => $calc['total_package_received'],
            'status' => 'submitted',
        ]);
    }

    /**
     * Approve VSS Application.
     */
    public function approveApplication(VssApplication $application, int $approvedByUserId, ?string $effectiveDate = null): VssApplication
    {
        $application->update([
            'status' => 'approved',
            'approved_by_user_id' => $approvedByUserId,
            'settlement_effective_date' => $effectiveDate ?? Carbon::now()->addDays(15)->toDateString(),
        ]);

        $program = $application->program;
        $program->increment('approved_applicants_count');

        return $application;
    }

    /**
     * Register Employee in DPLK under UU No. 4/2023 (UU P2SK).
     */
    public function registerDplk(Company $company, Employee $employee, array $data): DplkParticipation
    {
        return DplkParticipation::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'dplk_provider_name' => $data['dplk_provider_name'] ?? 'DPLK Bank Mandiri',
            'account_number' => $data['account_number'] ?? ('DPLK-' . rand(100000, 999999)),
            'employer_contribution_percentage' => $data['employer_contribution_percentage'] ?? 5.00,
            'employee_contribution_percentage' => $data['employee_contribution_percentage'] ?? 2.50,
            'current_accumulated_fund' => $data['initial_balance'] ?? 0.00,
            'status' => 'active',
        ]);
    }
}
