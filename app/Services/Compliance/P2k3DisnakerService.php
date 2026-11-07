<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\P2k3Committee;
use App\Models\P2k3QuarterlyReport;
use Carbon\Carbon;
use InvalidArgumentException;

class P2k3DisnakerService
{
    /**
     * Assess whether a company is legally mandated to establish a P2K3 committee
     * under Permenaker No. 04/1987 Pasal 2.
     */
    public function assessMandatoryCompliance(Company $company, int $activeWorkforceCount, bool $isHighRiskIndustry = false): array
    {
        $isMandatory = ($activeWorkforceCount >= 100) || $isHighRiskIndustry;

        return [
            'company_id' => $company->id,
            'active_workforce_count' => $activeWorkforceCount,
            'is_high_risk_industry' => $isHighRiskIndustry,
            'is_p2k3_mandatory' => $isMandatory,
            'legal_basis' => 'Permenaker No. 04/1987 Pasal 2 & UU No. 1/1970',
            'recommendation' => $isMandatory
                ? 'Perusahaan wajib membentuk P2K3 dengan Sekretaris bersertifikat Ahli K3 Umum Kemnaker.'
                : 'P2K3 bersifat anjuran jika di bawah 100 tenaga kerja dan risiko rendah.',
        ];
    }

    /**
     * Register or update a P2K3 Committee.
     */
    public function registerCommittee(
        Company $company,
        string $decreeNumber,
        string $chairmanName,
        string $secretaryAhliK3Name,
        string $ahliK3SkpNumber,
        Carbon|string $formationDate,
        ?string $disnakerRatificationNumber = null,
        int $totalMembers = 10
    ): P2k3Committee {
        if (empty(trim($ahliK3SkpNumber))) {
            throw new InvalidArgumentException('Sekretaris P2K3 wajib memiliki Nomor SKP Ahli K3 Umum yang sah dari Kemnaker.');
        }

        return P2k3Committee::updateOrCreate(
            [
                'tenant_id' => $company->tenant_id,
                'company_id' => $company->id,
            ],
            [
                'decree_number' => $decreeNumber,
                'disnaker_ratification_number' => $disnakerRatificationNumber,
                'formation_date' => Carbon::parse($formationDate)->toDateString(),
                'chairman_name' => $chairmanName,
                'secretary_ahli_k3_name' => $secretaryAhliK3Name,
                'ahli_k3_skp_number' => $ahliK3SkpNumber,
                'total_committee_members' => $totalMembers,
                'status' => 'active',
            ]
        );
    }

    /**
     * Generate or submit a quarterly report to Disnaker (Permenaker 04/1987 Pasal 12).
     */
    public function submitQuarterlyReport(
        P2k3Committee $committee,
        int $reportingYear,
        string $quarter,
        int $inspectionsCount = 3,
        int $trainingsCount = 2,
        ?string $incidentSummary = 'Zero Accident dicapai selama triwulan berjalan.',
        ?string $disnakerReceiptNumber = null
    ): P2k3QuarterlyReport {
        $validQuarters = ['Q1', 'Q2', 'Q3', 'Q4'];
        if (!in_array(strtoupper($quarter), $validQuarters)) {
            throw new InvalidArgumentException('Triwulan tidak valid. Gunakan Q1, Q2, Q3, atau Q4.');
        }

        $isSubmitted = !empty($disnakerReceiptNumber);

        return P2k3QuarterlyReport::updateOrCreate(
            [
                'tenant_id' => $committee->tenant_id,
                'p2k3_committee_id' => $committee->id,
                'reporting_year' => $reportingYear,
                'quarter' => strtoupper($quarter),
            ],
            [
                'workplace_inspections_count' => $inspectionsCount,
                'safety_trainings_count' => $trainingsCount,
                'incident_summary' => $incidentSummary,
                'disnaker_submission_date' => $isSubmitted ? Carbon::today()->toDateString() : null,
                'disnaker_receipt_number' => $disnakerReceiptNumber,
                'status' => $isSubmitted ? 'submitted_disnaker' : 'draft',
            ]
        );
    }
}
