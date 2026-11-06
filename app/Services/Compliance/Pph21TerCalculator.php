<?php

namespace App\Services\Compliance;

class Pph21TerCalculator
{
    /**
     * Determine TER Category (A, B, or C) from PTKP status.
     */
    public function getCategory(string $ptkpStatus): string
    {
        $mapping = config('compliance.ter_category_mapping', []);
        return $mapping[$ptkpStatus] ?? 'A';
    }

    /**
     * Calculate monthly PPh 21 using Tarif Efektif Rata-Rata (TER).
     *
     * @param float $grossMonthlyWage
     * @param string $ptkpStatus (e.g., 'TK/0', 'K/1')
     * @param bool $hasNpwp
     * @return array ['category' => 'A', 'rate' => 0.05, 'tax' => 250000.00]
     */
    public function calculateMonthly(float $grossMonthlyWage, string $ptkpStatus = 'TK/0', bool $hasNpwp = true): array
    {
        if ($grossMonthlyWage <= 0) {
            return [
                'category' => 'A',
                'rate' => 0.0,
                'tax' => 0.0,
            ];
        }

        $category = $this->getCategory($ptkpStatus);
        $terTableKey = 'compliance.ter_table_' . strtolower($category);
        $terTable = config($terTableKey, []);

        $rate = 0.0;
        foreach ($terTable as $tier) {
            if ($grossMonthlyWage <= $tier['max']) {
                $rate = $tier['rate'];
                break;
            }
        }

        // Apply 20% surcharge if employee has no NPWP and configured
        $effectiveRate = $rate;
        if (!$hasNpwp && $rate > 0) {
            $effectiveRate = $rate * 1.20;
        }

        $taxAmount = round($grossMonthlyWage * $effectiveRate, 2);

        return [
            'category' => $category,
            'rate' => $rate,
            'effective_rate' => $effectiveRate,
            'tax' => $taxAmount,
        ];
    }
}

