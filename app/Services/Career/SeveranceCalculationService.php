<?php

namespace App\Services\Career;

use App\Models\Employee;
use App\Models\EmployeeTermination;
use App\Models\User;
use App\Services\Notification\AppNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SeveranceCalculationService
{
    public function __construct(
        protected AppNotificationService $notificationService
    ) {}

    /**
     * Calculate statutory Indonesian severance package per PP No. 35/2021 & Pasal 156 UU Cipta Kerja
     */
    public function calculateSeverance(
        Employee $employee,
        string $terminationType,
        string|Carbon $terminationDate,
        ?float $overrideSalary = null,
        int $remainingLeaveDays = 0,
        float $separationPay = 0
    ): array {
        $joinDate = Carbon::parse($employee->join_date);
        $termDate = Carbon::parse($terminationDate);

        // Calculate service duration
        $totalMonths = (int) $joinDate->diffInMonths($termDate);
        $yearsOfService = (int) ($totalMonths / 12);
        $monthsOfService = (int) ($totalMonths % 12);

        $lastSalary = $overrideSalary ?? (float) $employee->base_salary;

        // 1. Base Uang Pesangon (Pasal 40 ayat 2 PP 35/2021)
        $baseSeveranceMonths = match (true) {
            $totalMonths < 12 => 1,
            $totalMonths < 24 => 2,
            $totalMonths < 36 => 3,
            $totalMonths < 48 => 4,
            $totalMonths < 60 => 5,
            $totalMonths < 72 => 6,
            $totalMonths < 84 => 7,
            $totalMonths < 96 => 8,
            default => 9, // 8 tahun atau lebih = 9 bulan upah
        };

        // 2. Base Uang Penghargaan Masa Kerja / UPMK (Pasal 40 ayat 3 PP 35/2021)
        $baseUpmkMonths = match (true) {
            $yearsOfService < 3 => 0,
            $yearsOfService < 6 => 2,
            $yearsOfService < 9 => 3,
            $yearsOfService < 12 => 4,
            $yearsOfService < 15 => 5,
            $yearsOfService < 18 => 6,
            $yearsOfService < 21 => 7,
            $yearsOfService < 24 => 8,
            default => 10, // 24 tahun atau lebih = 10 bulan upah
        };

        // 3. Multiplier based on Termination Type (Pasal 41 - 52 PP 35/2021)
        $severanceMultiplier = match ($terminationType) {
            'resignation' => 0.0,
            'sp3_violation', 'efficiency', 'closing' => 0.5,
            'pension' => 1.75,
            'medical', 'deceased' => 2.0,
            default => 1.0, // 'other', mutual agreement
        };

        // If resignation, employee does not receive UPMK by statutory law
        $appliedUpmkMonths = ($terminationType === 'resignation') ? 0 : $baseUpmkMonths;

        $severanceAmount = round($baseSeveranceMonths * $lastSalary * $severanceMultiplier, 2);
        $upmkAmount = round($appliedUpmkMonths * $lastSalary, 2);

        // 4. Uang Penggantian Hak / UPH (Pasal 40 ayat 4 PP 35/2021)
        // Standard formula: (Remaining leave / 21 working days) * monthly wage
        $uphAmount = round(($remainingLeaveDays / 21) * $lastSalary, 2);

        // 5. Total
        $totalSeverance = round($severanceAmount + $upmkAmount + $uphAmount + $separationPay, 2);

        return [
            'years_of_service' => $yearsOfService,
            'months_of_service' => $monthsOfService,
            'total_months' => $totalMonths,
            'last_salary' => $lastSalary,
            'base_severance_months' => $baseSeveranceMonths,
            'severance_multiplier' => $severanceMultiplier,
            'severance_amount' => $severanceAmount,
            'base_upmk_months' => $appliedUpmkMonths,
            'upmk_amount' => $upmkAmount,
            'remaining_leave_days' => $remainingLeaveDays,
            'uph_amount' => $uphAmount,
            'separation_pay_amount' => $separationPay,
            'total_severance' => $totalSeverance,
        ];
    }

    /**
     * Record an employee termination and official severance settlement
     */
    public function recordTermination(Employee $employee, array $data, ?User $author = null): EmployeeTermination
    {
        return DB::transaction(function () use ($employee, $data, $author) {
            $calc = $this->calculateSeverance(
                $employee,
                $data['termination_type'],
                $data['termination_date'],
                isset($data['last_salary']) ? (float)$data['last_salary'] : null,
                (int)($data['remaining_leave_days'] ?? 0),
                (float)($data['separation_pay_amount'] ?? 0)
            );

            // Generate official letter numbering: SK-PHK/{YEAR}/{001}
            $year = Carbon::parse($data['termination_date'])->format('Y');
            $count = EmployeeTermination::where('tenant_id', $employee->tenant_id)
                ->whereYear('termination_date', $year)
                ->count() + 1;
            $letterNumber = sprintf("SK-PHK/%s/%03d", $year, $count);

            $termination = EmployeeTermination::create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'letter_number' => $letterNumber,
                'termination_type' => $data['termination_type'],
                'termination_date' => $data['termination_date'],
                'years_of_service' => $calc['years_of_service'],
                'months_of_service' => $calc['months_of_service'],
                'last_salary' => $calc['last_salary'],
                'severance_multiplier' => $calc['severance_multiplier'],
                'severance_amount' => $calc['severance_amount'],
                'upmk_amount' => $calc['upmk_amount'],
                'uph_amount' => $calc['uph_amount'],
                'separation_pay_amount' => $calc['separation_pay_amount'],
                'total_severance' => $calc['total_severance'],
                'status' => $data['status'] ?? 'approved',
                'notes' => $data['notes'] ?? null,
                'approved_by_user_id' => $author?->id,
            ]);

            // Update employee status to inactive/terminated
            $employee->update([
                'status' => 'inactive',
                'resigned_date' => $data['termination_date'],
            ]);

            return $termination;
        });
    }

    /**
     * Mark severance payout as settled
     */
    public function markAsPaid(EmployeeTermination $termination): EmployeeTermination
    {
        $termination->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $termination;
    }
}

