<?php

namespace App\Services\Compliance;

use App\Models\CobInsurancePolicy;
use App\Models\Employee;
use InvalidArgumentException;

class CobInsuranceService
{
    /**
     * Register or update a commercial health insurance policy for CoB with BPJS Kesehatan.
     */
    public function registerPolicy(
        Employee $employee,
        string $insuranceCompanyName,
        string $policyNumber,
        string $bpjsHealthCardNumber,
        string $planType = 'Platinum Executive',
        float $annualLimit = 100000000.00,
        string $cobMechanism = 'bpjs_first_payer'
    ): CobInsurancePolicy {
        return CobInsurancePolicy::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
            ],
            [
                'insurance_company_name' => $insuranceCompanyName,
                'policy_number' => $policyNumber,
                'plan_type' => $planType,
                'bpjs_health_card_number' => $bpjsHealthCardNumber,
                'cob_claim_mechanism' => $cobMechanism,
                'annual_limit_amount' => $annualLimit,
                'current_usage_amount' => 0.00,
                'status' => 'active',
            ]
        );
    }

    /**
     * Coordinate and process a hospital claim using CoB (BPJS Kesehatan first payer + Commercial Top-up).
     *
     * @param CobInsurancePolicy $policy
     * @param float $totalHospitalBill
     * @param float $bpjsCoveredAmount
     * @param bool $applyUsage Whether to deduct from the policy limit
     * @return array
     */
    public function coordinateClaim(
        CobInsurancePolicy $policy,
        float $totalHospitalBill,
        float $bpjsCoveredAmount,
        bool $applyUsage = true
    ): array {
        if ($totalHospitalBill <= 0) {
            throw new InvalidArgumentException('Total hospital bill must be greater than zero.');
        }

        $bpjsPayment = min($totalHospitalBill, max(0.0, $bpjsCoveredAmount));
        $excessCost = round($totalHospitalBill - $bpjsPayment, 2);

        $remainingLimit = max(0.0, (float) $policy->annual_limit_amount - (float) $policy->current_usage_amount);

        $commercialCoverage = min($excessCost, $remainingLimit);
        $patientCopay = round($excessCost - $commercialCoverage, 2);

        if ($applyUsage && $commercialCoverage > 0) {
            $policy->current_usage_amount = (float) $policy->current_usage_amount + $commercialCoverage;
            $policy->save();
        }

        return [
            'policy_id' => $policy->id,
            'insurance_company' => $policy->insurance_company_name,
            'policy_number' => $policy->policy_number,
            'total_hospital_bill' => $totalHospitalBill,
            'bpjs_first_payer_paid' => $bpjsPayment,
            'excess_cost_sharing' => $excessCost,
            'commercial_topup_paid' => $commercialCoverage,
            'patient_copay_amount' => $patientCopay,
            'policy_remaining_limit' => max(0.0, (float) $policy->annual_limit_amount - (float) $policy->current_usage_amount),
            'cob_status' => $patientCopay == 0.0 ? 'fully_covered' : 'partial_copay_required',
        ];
    }
}
