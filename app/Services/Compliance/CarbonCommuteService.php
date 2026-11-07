<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeCommuteProfile;
use App\Models\CommuteEmissionAudit;
use InvalidArgumentException;

class CarbonCommuteService
{
    /**
     * Standard GHG Protocol DEFRA/IPCC Commuting Emission Factors (kg CO2e per passenger km).
     */
    public const EMISSION_FACTORS = [
        'car_gasoline' => 0.192,
        'motorcycle_gasoline' => 0.103,
        'electric_vehicle' => 0.053,
        'public_transit' => 0.041,
        'bicycle_walking' => 0.000,
    ];

    /**
     * Register or update an employee's commute profile and compute weekly emissions.
     */
    public function updateEmployeeCommute(
        Employee $employee,
        float $oneWayDistanceKm,
        string $commuteMode,
        int $weeklyOfficeDays = 3,
        int $weeklyRemoteDays = 2
    ): EmployeeCommuteProfile {
        if (!array_key_exists($commuteMode, self::EMISSION_FACTORS)) {
            throw new InvalidArgumentException("Moda transportasi '{$commuteMode}' tidak dikenal dalam basis data emisi.");
        }

        if ($oneWayDistanceKm < 0) {
            throw new InvalidArgumentException('Jarak tempuh tidak boleh negatif.');
        }

        $factor = self::EMISSION_FACTORS[$commuteMode];
        $dailyRoundtripKm = $oneWayDistanceKm * 2;

        $weeklyEmissions = round($dailyRoundtripKm * $weeklyOfficeDays * $factor, 2);
        $weeklyAvoided = round($dailyRoundtripKm * $weeklyRemoteDays * $factor, 2);

        return EmployeeCommuteProfile::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
            ],
            [
                'home_to_office_distance_km' => $oneWayDistanceKm,
                'primary_commute_mode' => $commuteMode,
                'weekly_office_days' => $weeklyOfficeDays,
                'weekly_remote_days' => $weeklyRemoteDays,
                'weekly_emissions_kg_co2' => $weeklyEmissions,
                'weekly_avoided_emissions_kg_co2' => $weeklyAvoided,
            ]
        );
    }

    /**
     * Run company-wide Scope 3 Commuting Carbon Audit for a specific year (50 working weeks).
     */
    public function runAnnualCommuteAudit(Company $company, int $auditYear): CommuteEmissionAudit
    {
        $profiles = EmployeeCommuteProfile::where('company_id', $company->id)->get();
        $participatingCount = $profiles->count();

        if ($participatingCount === 0) {
            return CommuteEmissionAudit::updateOrCreate(
                ['tenant_id' => $company->tenant_id, 'company_id' => $company->id, 'audit_year' => $auditYear],
                ['status' => 'completed']
            );
        }

        $annualWeeks = 50;
        $totalDistanceKm = 0.0;
        $totalGrossKg = 0.0;
        $totalAvoidedKg = 0.0;

        foreach ($profiles as $profile) {
            $factor = self::EMISSION_FACTORS[$profile->primary_commute_mode] ?? 0.103;
            $dailyRoundtrip = (float) $profile->home_to_office_distance_km * 2;
            $totalCommuteDaysWeekly = $profile->weekly_office_days + $profile->weekly_remote_days;

            $totalDistanceKm += ($dailyRoundtrip * $profile->weekly_office_days * $annualWeeks);
            $totalGrossKg += ($dailyRoundtrip * $totalCommuteDaysWeekly * $factor * $annualWeeks);
            $totalAvoidedKg += ((float) $profile->weekly_avoided_emissions_kg_co2 * $annualWeeks);
        }

        $grossTonnes = round($totalGrossKg / 1000, 2);
        $avoidedTonnes = round($totalAvoidedKg / 1000, 2);
        $netTonnes = round(max(0.0, $grossTonnes - $avoidedTonnes), 2);

        return CommuteEmissionAudit::updateOrCreate(
            [
                'tenant_id' => $company->tenant_id,
                'company_id' => $company->id,
                'audit_year' => $auditYear,
            ],
            [
                'participating_employees_count' => $participatingCount,
                'total_annual_commute_distance_km' => round($totalDistanceKm, 2),
                'gross_emissions_ton_co2e' => $grossTonnes,
                'avoided_emissions_wfh_ton_co2e' => $avoidedTonnes,
                'net_commute_emissions_ton_co2e' => $netTonnes,
                'status' => 'completed',
            ]
        );
    }
}
