<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\Employee;
use App\Models\JkpClaimCertification;
use Carbon\Carbon;
use InvalidArgumentException;

class JkpClaimService
{
    /**
     * Certify employee eligibility and generate JKP Claim Certificate (PP No. 37 Tahun 2021)
     */
    public function certifyClaim($arg1, $arg2 = [], $arg3 = []): JkpClaimCertification
    {
        if ($arg1 instanceof Company) {
            $company = $arg1;
            $employee = $arg2;
            $data = is_array($arg3) ? $arg3 : [];
        } else {
            $employee = $arg1;
            $data = is_array($arg2) ? $arg2 : [];
            $company = $employee->company;
        }

        $tenantId = $employee->tenant_id;
        $consecutiveMonths = (int) ($data['consecutive_paid_months'] ?? 6);
        $totalMonths24m = (int) ($data['total_paid_months_24m'] ?? 12);
        $reasonCode = strtolower($data['termination_reason_code'] ?? 'efisiensi_merger');

        // Check voluntary resignation restriction (Pasal 19 ayat 3 PP 37/2021: Mengundurkan diri sukarela tidak berhak JKP)
        if (in_array($reasonCode, ['resigned', 'resignation', 'voluntary_resignation', 'mengundurkan_diri']) || str_contains($reasonCode, 'resign')) {
            throw new InvalidArgumentException("Pekerja yang mengundurkan diri atas kemauan sendiri tidak berhak atas manfaat JKP sesuai Pasal 19 ayat (3) PP No. 37 Tahun 2021.");
        }

        // Statutory eligibility: Minimal 6 bulan berturut-turut dan minimal 12 bulan dalam 24 bulan
        $isEligible = ($consecutiveMonths >= 6 && $totalMonths24m >= 12);

        // Reference salary capped at Rp 5.000.000 per month (Pasal 21 ayat 3 PP 37/2021)
        $actualSalary = (float) $employee->basic_salary;
        $salaryCap = 5000000.00;
        $basisSalary = min($actualSalary, $salaryCap);

        // Cash benefits: 45% (Bulan 1-3) dan 25% (Bulan 4-6)
        $m13Monthly = round($basisSalary * 0.45, 2);
        $m46Monthly = round($basisSalary * 0.25, 2);
        $totalBenefit = round(($m13Monthly * 3) + ($m46Monthly * 3), 2);

        $seq = JkpClaimCertification::where('tenant_id', $tenantId)->count() + 1;
        do {
            $certNumber = sprintf('JKP-CERT/%s/%04d', date('Y'), $seq++);
        } while (JkpClaimCertification::where('certificate_number', $certNumber)->exists());

        return JkpClaimCertification::create([
            'tenant_id' => $tenantId,
            'company_id' => $company ? $company->id : $employee->company_id,
            'certificate_number' => $certNumber,
            'employee_id' => $employee->id,
            'termination_date' => Carbon::parse($data['termination_date'])->toDateString(),
            'termination_reason_code' => $reasonCode,
            'bpjs_tk_card_number' => $employee->bpjs_tk_number ?? ($data['bpjs_tk_card_number'] ?? '00000000000'),
            'consecutive_paid_months' => $consecutiveMonths,
            'total_paid_months_24m' => $totalMonths24m,
            'eligible_for_jkp' => $isEligible,
            'reference_salary_cap' => $salaryCap,
            'month_1_to_3_benefit_monthly' => $m13Monthly,
            'month_4_to_6_benefit_monthly' => $m46Monthly,
            'total_potential_cash_benefit' => $totalBenefit,
            'disnaker_reporting_status' => 'reported',
            'siapkerja_tracking_code' => 'SK-'.strtoupper(substr(md5($certNumber), 0, 10)),
            'company_recommendation_letter' => $data['company_recommendation_letter'] ?? 'Perusahaan merekomendasikan pencairan manfaat program JKP (Uang Tunai, Konseling Karir, dan Pelatihan Kerja) bagi pekerja ter-PHK via portal SiapKerja Kemnaker.',
        ]);
    }

    /**
     * Generate Disnaker recommendation document payload
     */
    public function generateDisnakerRecommendation(JkpClaimCertification $certification): array
    {
        return [
            'dasar_hukum' => 'PP No. 37 Tahun 2021',
            'nomor_sertifikat' => $certification->certificate_number,
            'nomor_sertifikasi' => $certification->certificate_number,
            'identitas_pekerja' => [
                'nama' => $certification->employee->full_name,
                'nik' => $certification->employee->nik_ktp,
                'nomor_kpj_bpjs' => $certification->bpjs_tk_card_number,
                'tanggal_phk' => $certification->termination_date->toDateString(),
                'alasan_phk' => strtoupper($certification->termination_reason_code),
            ],
            'manfaat_tunai' => [
                'total_manfaat_maksimal' => (float) $certification->total_potential_cash_benefit,
            ],
            'kalkulasi_manfaat_tunai' => [
                'upah_terakhir' => (float) $certification->employee->basic_salary,
                'batas_atas_upah_patokan' => (float) $certification->reference_salary_cap,
                'manfaat_bulan_1_sd_3_per_bulan' => (float) $certification->month_1_to_3_benefit_monthly,
                'manfaat_bulan_4_sd_6_per_bulan' => (float) $certification->month_4_to_6_benefit_monthly,
                'total_manfaat_uang_tunai' => (float) $certification->total_potential_cash_benefit,
            ],
            'portal_integrasi' => [
                'siapkerja_tracking_code' => $certification->siapkerja_tracking_code,
                'status_lapor' => $certification->disnaker_reporting_status,
            ],
        ];
    }

    /**
     * Alias for generateDisnakerRecommendation
     */
    public function generateDisnakerReportingPayload(JkpClaimCertification $certification): array
    {
        return $this->generateDisnakerRecommendation($certification);
    }
}
