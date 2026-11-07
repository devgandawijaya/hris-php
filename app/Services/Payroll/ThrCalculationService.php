<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\ThrDisbursement;
use Carbon\Carbon;
use InvalidArgumentException;

class ThrCalculationService
{
    /**
     * Calculate statutory THR for an employee according to Permenaker No. 6/2016.
     *
     * @param Employee $employee
     * @param int $taxYear
     * @param string $religiousHolidayType (idul_fitri, natal, nyepi, waisak, imlek)
     * @param Carbon|string $holidayDate
     * @param Carbon|string|null $actualPaymentDate
     * @return array
     */
    public function computeStatutoryThr(
        Employee $employee,
        int $taxYear,
        string $religiousHolidayType,
        Carbon|string $holidayDate,
        Carbon|string|null $actualPaymentDate = null
    ): array {
        $holiday = Carbon::parse($holidayDate);
        $statutoryDeadline = $holiday->copy()->subDays(7); // H-7 batas waktu wajib (Pasal 5 Permenaker 6/2016)
        $paymentDate = $actualPaymentDate ? Carbon::parse($actualPaymentDate) : $statutoryDeadline->copy();

        $joinDate = Carbon::parse($employee->join_date);
        $tenureMonths = $joinDate->gt($holiday) ? 0 : $joinDate->diffInMonths($holiday);

        // Fetch wage basis: Basic Salary + Fixed Allowances
        $basicSalary = (float) $employee->basic_salary;
        $fixedAllowances = 0.0;
        if (method_exists($employee, 'salaryComponents')) {
            $fixedAllowances = (float) $employee->salaryComponents()
                ->where('is_active', true)
                ->whereHas('salaryComponent', function ($q) {
                    $q->where('type', 'allowance')->where('is_fixed', true);
                })
                ->sum('amount');
        }
        $wageBasis = $basicSalary + $fixedAllowances;

        // Eligibility check (Pasal 2: Minimal 1 bulan kerja terus menerus)
        if ($tenureMonths < 1 || $wageBasis <= 0) {
            return [
                'is_eligible' => false,
                'tenure_months' => $tenureMonths,
                'prorata_multiplier' => 0.0,
                'regular_wage_basis' => $wageBasis,
                'calculated_thr_amount' => 0.0,
                'ter_tax_deduction' => 0.0,
                'late_penalty_interest' => 0.0,
                'net_thr_paid' => 0.0,
                'statutory_payment_deadline' => $statutoryDeadline->toDateString(),
                'actual_payment_date' => $paymentDate->toDateString(),
                'is_late' => false,
            ];
        }

        // Formula: Masa kerja >= 12 bulan = 1 bulan upah; 1 <= tenure < 12 = (tenure / 12) * upah
        if ($tenureMonths >= 12) {
            $multiplier = 1.0000;
            $calculatedThr = $wageBasis;
        } else {
            $multiplier = round($tenureMonths / 12, 4);
            $calculatedThr = round(($tenureMonths / 12) * $wageBasis, 2);
        }

        // Late penalty under Pasal 10 Permenaker 6/2016: 5% dari total THR jika bayar lewat H-7
        $isLate = $paymentDate->greaterThan($statutoryDeadline);
        $latePenalty = 0.0;
        if ($isLate) {
            $latePenalty = round($calculatedThr * 0.05, 2);
        }

        // PP 58/2023 & PMK 168/2023 TER Tax Estimation
        $terTax = $this->calculateTerTax($calculatedThr);

        $netThr = round($calculatedThr - $terTax + $latePenalty, 2);

        return [
            'is_eligible' => true,
            'tenure_months' => $tenureMonths,
            'prorata_multiplier' => $multiplier,
            'regular_wage_basis' => $wageBasis,
            'calculated_thr_amount' => $calculatedThr,
            'ter_tax_deduction' => $terTax,
            'late_penalty_interest' => $latePenalty,
            'net_thr_paid' => $netThr,
            'statutory_payment_deadline' => $statutoryDeadline->toDateString(),
            'actual_payment_date' => $paymentDate->toDateString(),
            'is_late' => $isLate,
        ];
    }

    /**
     * Record and store statutory THR disbursement record in database.
     */
    public function recordThrDisbursement(
        Employee $employee,
        int $taxYear,
        string $religiousHolidayType,
        Carbon|string $holidayDate,
        Carbon|string|null $actualPaymentDate = null,
        string $status = 'calculated'
    ): ThrDisbursement {
        $calculation = $this->computeStatutoryThr($employee, $taxYear, $religiousHolidayType, $holidayDate, $actualPaymentDate);

        if (!$calculation['is_eligible']) {
            throw new InvalidArgumentException('Employee is not eligible for statutory THR (masa kerja < 1 bulan).');
        }

        return ThrDisbursement::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'tax_year' => $taxYear,
                'religious_holiday_type' => $religiousHolidayType,
            ],
            [
                'tenure_months' => (int) round($calculation['tenure_months']),
                'prorata_multiplier' => $calculation['prorata_multiplier'],
                'regular_wage_basis' => $calculation['regular_wage_basis'],
                'calculated_thr_amount' => $calculation['calculated_thr_amount'],
                'ter_tax_deduction' => $calculation['ter_tax_deduction'],
                'net_thr_paid' => $calculation['net_thr_paid'],
                'statutory_payment_deadline' => $calculation['statutory_payment_deadline'],
                'actual_payment_date' => $calculation['actual_payment_date'],
                'late_penalty_interest' => $calculation['late_penalty_interest'],
                'status' => $status,
            ]
        );
    }

    /**
     * Approximate TER PPh 21 withholding rate under PP 58/2023.
     */
    protected function calculateTerTax(float $grossAmount): float
    {
        if ($grossAmount <= 5400000) {
            return 0.00;
        } elseif ($grossAmount <= 10000000) {
            return round($grossAmount * 0.02, 2);
        } elseif ($grossAmount <= 20000000) {
            return round($grossAmount * 0.05, 2);
        } else {
            return round($grossAmount * 0.09, 2);
        }
    }
}
