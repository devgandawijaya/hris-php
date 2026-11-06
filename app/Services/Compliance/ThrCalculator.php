<?php

namespace App\Services\Compliance;

use App\Models\Employee;
use App\Models\ThrRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ThrCalculator
{
    /**
     * Calculate THR entitlement for an employee per Permenaker No. 6/2016.
     *
     * @param Employee $employee
     * @param Carbon|string|null $asOfDate
     * @return array
     */
    public function calculateForEmployee(Employee $employee, Carbon|string|null $asOfDate = null): array
    {
        $asOf = $asOfDate ? Carbon::parse($asOfDate) : Carbon::today();
        $joinDate = Carbon::parse($employee->join_date);

        // Calculate continuous months of service up to the as-of date
        $monthsOfService = $joinDate->diffInMonths($asOf);

        // Fetch basic salary + fixed allowances
        $basicSalary = (float) $employee->basic_salary;
        $fixedAllowances = 0.0;

        if ($employee->relationLoaded('salaryComponents') || method_exists($employee, 'salaryComponents')) {
            $fixedAllowances = (float) $employee->salaryComponents()
                ->where('is_active', true)
                ->whereHas('salaryComponent', function ($q) {
                    $q->where('type', 'allowance')->where('is_fixed', true);
                })
                ->sum('amount');
        }

        $baseWage = $basicSalary + $fixedAllowances;

        // Less than 1 month: not eligible
        if ($monthsOfService < 1 || $baseWage <= 0) {
            return [
                'is_eligible' => false,
                'months_of_service' => $monthsOfService,
                'base_wage' => $baseWage,
                'thr_amount' => 0.0,
                'formula' => 'Masa kerja < 1 bulan (tidak berhak THR)',
            ];
        }

        // 12 months or more: 1 month wage
        if ($monthsOfService >= 12) {
            $thrAmount = round($baseWage, 2);
            $formula = '1 x Upah Sebulan (Masa kerja >= 12 bulan)';
        } else {
            // 1 to < 12 months: Pro-rata (months / 12 * baseWage)
            $thrAmount = round(($monthsOfService / 12.0) * $baseWage, 2);
            $formula = "({$monthsOfService} / 12) x Upah Sebulan (Pro-rata)";
        }

        return [
            'is_eligible' => true,
            'months_of_service' => $monthsOfService,
            'base_wage' => $baseWage,
            'thr_amount' => $thrAmount,
            'formula' => $formula,
        ];
    }

    /**
     * Generate bulk THR records for all active employees of a tenant for a given year.
     *
     * @param int $tenantId
     * @param int $year
     * @param Carbon|string $paymentDate
     * @return array
     */
    public function generateTenantThr(int $tenantId, int $year, Carbon|string $paymentDate): array
    {
        $payDate = Carbon::parse($paymentDate);

        return DB::transaction(function () use ($tenantId, $year, $payDate) {
            $employees = Employee::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->with(['salaryComponents.salaryComponent'])
                ->get();

            $records = [];
            $totalThr = 0;
            $eligibleCount = 0;

            foreach ($employees as $employee) {
                $calc = $this->calculateForEmployee($employee, $payDate);

                if ($calc['is_eligible'] && $calc['thr_amount'] > 0) {
                    $record = ThrRecord::updateOrCreate(
                        [
                            'tenant_id' => $tenantId,
                            'employee_id' => $employee->id,
                            'year' => $year,
                        ],
                        [
                            'months_of_service' => $calc['months_of_service'],
                            'base_wage' => $calc['base_wage'],
                            'thr_amount' => $calc['thr_amount'],
                            'payment_date' => $payDate->format('Y-m-d'),
                            'status' => 'approved',
                        ]
                    );

                    $records[] = $record;
                    $totalThr += $calc['thr_amount'];
                    $eligibleCount++;
                }
            }

            return [
                'year' => $year,
                'payment_date' => $payDate->format('Y-m-d'),
                'total_eligible_employees' => $eligibleCount,
                'total_thr_amount' => round($totalThr, 2),
                'records' => $records,
            ];
        });
    }
}
