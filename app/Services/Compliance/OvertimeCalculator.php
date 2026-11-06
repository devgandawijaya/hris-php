<?php

namespace App\Services\Compliance;

class OvertimeCalculator
{
    /**
     * Calculate overtime pay per PP 35/2021.
     * Hourly wage = 1 / 173 * Monthly Fixed Wage.
     *
     * @param float $monthlyFixedWage
     * @param float $hours
     * @param bool $isHoliday
     * @param int $workDaysPerWeek (5 or 6)
     * @return array
     */
    public function calculate(
        float $monthlyFixedWage, 
        float $hours, 
        bool $isHoliday = false, 
        int $workDaysPerWeek = 5
    ): array {
        if ($monthlyFixedWage <= 0 || $hours <= 0) {
            return [
                'hourly_rate' => 0.0,
                'overtime_hours' => 0.0,
                'multiplier_total' => 0.0,
                'overtime_pay' => 0.0,
            ];
        }

        $hourlyRate = round($monthlyFixedWage / 173, 2);
        $multiplierTotal = 0.0;

        if (!$isHoliday) {
            // Normal workday: Jam ke-1 x 1.5, jam berikutnya x 2.0
            if ($hours <= 1.0) {
                $multiplierTotal = $hours * 1.5;
            } else {
                $multiplierTotal = 1.5 + (($hours - 1.0) * 2.0);
            }
        } else {
            // Hari Libur Resmi / Istirahat Mingguan
            if ($workDaysPerWeek === 5) {
                // 5 hari kerja: jam 1 s.d 8 x 2.0, jam 9 x 3.0, jam 10-12 x 4.0
                if ($hours <= 8.0) {
                    $multiplierTotal = $hours * 2.0;
                } elseif ($hours <= 9.0) {
                    $multiplierTotal = (8.0 * 2.0) + (($hours - 8.0) * 3.0);
                } else {
                    $multiplierTotal = (8.0 * 2.0) + (1.0 * 3.0) + (($hours - 9.0) * 4.0);
                }
            } else {
                // 6 hari kerja: jam 1 s.d 7 x 2.0, jam 8 x 3.0, jam 9-10 x 4.0
                if ($hours <= 7.0) {
                    $multiplierTotal = $hours * 2.0;
                } elseif ($hours <= 8.0) {
                    $multiplierTotal = (7.0 * 2.0) + (($hours - 7.0) * 3.0);
                } else {
                    $multiplierTotal = (7.0 * 2.0) + (1.0 * 3.0) + (($hours - 8.0) * 4.0);
                }
            }
        }

        $overtimePay = round($hourlyRate * $multiplierTotal, 2);

        return [
            'hourly_rate' => $hourlyRate,
            'overtime_hours' => $hours,
            'multiplier_total' => $multiplierTotal,
            'overtime_pay' => $overtimePay,
        ];
    }
}

