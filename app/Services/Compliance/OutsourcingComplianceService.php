<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\OutsourcingVendor;
use App\Models\OutsourcingContract;
use App\Models\VendorWorkerAssignment;
use InvalidArgumentException;

class OutsourcingComplianceService
{
    /**
     * Register a new outsourcing vendor under PP No. 35/2021 Pasal 18.
     * Vendor must be an Indonesian PT with OSS NIB business license.
     */
    public function registerVendor(Company $company, array $data): OutsourcingVendor
    {
        if (empty($data['name']) || empty($data['nib_oss_license'])) {
            throw new InvalidArgumentException('Nama perusahaan rekanan dan NIB OSS Kemnaker wajib diisi.');
        }

        $vendorCode = $data['vendor_code'] ?? 'VND-' . strtoupper(substr(md5(uniqid()), 0, 6));

        return OutsourcingVendor::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'vendor_code' => $vendorCode,
            'name' => $data['name'],
            'legal_entity_type' => $data['legal_entity_type'] ?? 'PT',
            'nib_oss_license' => $data['nib_oss_license'],
            'disnaker_registration_number' => $data['disnaker_registration_number'] ?? null,
            'pic_name' => $data['pic_name'] ?? 'Vendor PIC',
            'pic_contact' => $data['pic_contact'] ?? '-',
            'management_fee_percentage' => $data['management_fee_percentage'] ?? 10.00,
            'status' => 'active',
        ]);
    }

    /**
     * Create an outsourcing contract with mandatory TUPE protection under Putusan MK No. 27/PUU-IX/2011.
     */
    public function createContract(OutsourcingVendor $vendor, array $data): OutsourcingContract
    {
        $contractNumber = $data['contract_number'] ?? 'SPK-OUT/' . date('Y') . '/' . rand(1000, 9999);
        $headcountQuota = (int) ($data['headcount_quota'] ?? 10);
        $tupeProtection = (bool) ($data['tupe_protection_guarantee'] ?? true);

        return OutsourcingContract::create([
            'tenant_id' => $vendor->tenant_id,
            'outsourcing_vendor_id' => $vendor->id,
            'contract_number' => $contractNumber,
            'service_type' => $data['service_type'] ?? 'security',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'tupe_protection_guarantee' => $tupeProtection,
            'headcount_quota' => $headcountQuota,
            'monthly_billing_amount' => $data['monthly_billing_amount'] ?? 0.00,
            'status' => 'active',
        ]);
    }

    /**
     * Assign a worker under outsourcing contract, validating BPJS and minimum wage compliance.
     */
    public function assignWorker(OutsourcingContract $contract, array $data, float $applicableUmk = 5000000.00): VendorWorkerAssignment
    {
        $monthlyWage = (float) ($data['monthly_wage_paid'] ?? 0.00);
        $isWageCompliant = $monthlyWage >= $applicableUmk;

        return VendorWorkerAssignment::create([
            'tenant_id' => $contract->tenant_id,
            'outsourcing_contract_id' => $contract->id,
            'worker_name' => $data['worker_name'],
            'nik_ktp' => $data['nik_ktp'],
            'assigned_role' => $data['assigned_role'] ?? 'Staff Alih Daya',
            'bpjs_tk_registered' => (bool) ($data['bpjs_tk_registered'] ?? true),
            'bpjs_kes_registered' => (bool) ($data['bpjs_kes_registered'] ?? true),
            'monthly_wage_paid' => $monthlyWage,
            'wage_compliant_with_umk' => $isWageCompliant,
            'status' => 'active',
        ]);
    }

    /**
     * Calculate monthly billing invoice including base wages and management fee.
     */
    public function calculateMonthlyBilling(OutsourcingContract $contract): array
    {
        $assignments = $contract->workerAssignments()->where('status', 'active')->get();
        $totalBaseWages = (float) $assignments->sum('monthly_wage_paid');
        $feePercentage = (float) $contract->vendor->management_fee_percentage;
        $managementFee = round($totalBaseWages * ($feePercentage / 100), 2);
        $grandTotal = $totalBaseWages + $managementFee;

        return [
            'contract_number' => $contract->contract_number,
            'active_headcount' => $assignments->count(),
            'total_base_wages' => $totalBaseWages,
            'management_fee_percentage' => $feePercentage,
            'management_fee_amount' => $managementFee,
            'grand_total_billing' => $grandTotal,
        ];
    }
}
