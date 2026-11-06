<?php

namespace App\Services\Compliance;

class PkwtCompensationCalculator
{
    /**
     * Calculate PKWT Compensation per PP 35/2021 Article 61A.
     * Formula: (Masa Kerja dalam Bulan / 12) * 1 Bulan Upah.
     *
     * @param float $monthlyFixedWage
     * @param int $monthsOfService
     * @return array
     */
    public function calculate(float $monthlyFixedWage, int $monthsOfService): array
    {
        // Eligible if at least 1 month of service
        if ($monthlyFixedWage <= 0 || $monthsOfService < 1) {
            return [
                'is_eligible' => false,
                'months_of_service' => $monthsOfService,
                'monthly_wage' => $monthlyFixedWage,
                'compensation_amount' => 0.0,
            ];
        }

        $compensationAmount = round(($monthsOfService / 12.0) * $monthlyFixedWage, 2);

        return [
            'is_eligible' => true,
            'months_of_service' => $monthsOfService,
            'monthly_wage' => $monthlyFixedWage,
            'compensation_amount' => $compensationAmount,
        ];
    }
}

