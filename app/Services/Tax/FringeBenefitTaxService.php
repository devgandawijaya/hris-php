<?php

namespace App\Services\Tax;

use App\Models\Company;
use App\Models\Employee;
use App\Models\FringeBenefit;

class FringeBenefitTaxService
{
    /**
     * Standard Non-Taxable Thresholds pursuant to PMK No. 66/2023.
     */
    public const THRESHOLDS = [
        'groceries_vouchers' => 3000000.00,       // Bingkisan makanan/minuman non-hari raya: bebas s/d Rp 3 jt/bln
        'housing_apartment_rental' => 0.00,       // Tempat tinggal non-daerah tertentu: objek penuh jika dibayar korporat
        'company_car_facility' => 0.00,           // Kendaraan dinas direksi/manajemen
        'club_membership' => 0.00,                // Keanggotaan klub olahraga/golf
        'travel_vacation_facility' => 0.00,       // Fasilitas cuti/rekreasi
        'other' => 0.00,
    ];

    /**
     * Record a fringe benefit and compute taxable natura value.
     */
    public function recordBenefit(Company $company, Employee $employee, array $data): FringeBenefit
    {
        $benefitType = $data['benefit_type'] ?? 'other';
        $grossMarketValue = (float) ($data['gross_market_value'] ?? 0);
        $threshold = (float) ($data['non_taxable_threshold'] ?? (self::THRESHOLDS[$benefitType] ?? 0.00));

        // Taxable Natura = max(0, Gross Market Value - Non-Taxable Threshold)
        $taxableNaturaValue = max(0.00, $grossMarketValue - $threshold);
        $isTaxable = $taxableNaturaValue > 0;

        return FringeBenefit::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
            'tax_year' => (int) ($data['tax_year'] ?? date('Y')),
            'period_month' => (int) ($data['period_month'] ?? date('n')),
            'benefit_type' => $benefitType,
            'description' => $data['description'],
            'gross_market_value' => $grossMarketValue,
            'non_taxable_threshold' => $threshold,
            'taxable_natura_value' => $taxableNaturaValue,
            'is_taxable_object' => $isTaxable,
            'fiscal_notes' => $data['fiscal_notes'] ?? 'Dihitung berdasarkan batasan PMK No. 66/2023 Pasal 3-6.',
        ]);
    }

    /**
     * Compute total taxable natura to be added to gross income for monthly PPh 21.
     */
    public function getMonthlyNaturaTaxAddition(Employee $employee, int $year, int $month): float
    {
        return (float) FringeBenefit::where('employee_id', $employee->id)
            ->where('tax_year', $year)
            ->where('period_month', $month)
            ->where('is_taxable_object', true)
            ->sum('taxable_natura_value');
    }
}
