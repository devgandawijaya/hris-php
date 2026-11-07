<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\PpksComplaintCase;
use App\Models\FemaleWorkerFacilityCheck;
use Carbon\Carbon;
use InvalidArgumentException;

class PpksProtectionService
{
    /**
     * File a confidential harassment/violence complaint under Kepmenaker No. 88/2023 & UU TPKS No. 12/2022.
     */
    public function fileComplaint(Company $company, array $data): PpksComplaintCase
    {
        $ticket = 'PPKS-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $isAnonymous = (bool) ($data['is_anonymous'] ?? true);
        $pseudonym = $isAnonymous ? 'Saksi/Korban Terlindungi-' . rand(100, 999) : ($data['complainant_pseudonym'] ?? 'Pelapor Terverifikasi');

        return PpksComplaintCase::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'ticket_number' => $ticket,
            'incident_date' => $data['incident_date'] ?? Carbon::now()->toDateString(),
            'allegation_type' => $data['allegation_type'] ?? 'verbal',
            'is_anonymous' => $isAnonymous,
            'complainant_pseudonym' => $pseudonym,
            'investigator_satgas_lead' => $data['investigator_satgas_lead'] ?? 'Ketua Satgas PPKS Perusahaan',
            'victim_support_measures' => $data['victim_support_measures'] ?? 'Pemberian pendampingan psikologis dan jaminan perlindungan tanpa retaliasi.',
            'status' => 'received',
        ]);
    }

    /**
     * Update case investigation status and disciplinary sanctions.
     */
    public function updateInvestigation(PpksComplaintCase $case, string $status, ?string $sanctionRecommendation = null, ?string $supportMeasures = null): PpksComplaintCase
    {
        $updateData = ['status' => $status];
        if ($sanctionRecommendation) {
            $updateData['sanction_recommendation'] = $sanctionRecommendation;
        }
        if ($supportMeasures) {
            $updateData['victim_support_measures'] = $supportMeasures;
        }

        $case->update($updateData);

        return $case;
    }

    /**
     * Conduct audit of female worker statutory facilities under Pasal 76 UU 13/2003 & Permenkes 15/2013.
     */
    public function auditFemaleWorkerFacilities(Company $company, array $data, ?int $auditedByUserId = null): FemaleWorkerFacilityCheck
    {
        return FemaleWorkerFacilityCheck::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'inspection_date' => $data['inspection_date'] ?? Carbon::now()->toDateString(),
            'lactation_room_standard_met' => (bool) ($data['lactation_room_standard_met'] ?? true),
            'night_shift_transport_provided' => (bool) ($data['night_shift_transport_provided'] ?? true),
            'night_shift_nutritious_food_provided' => (bool) ($data['night_shift_nutritious_food_provided'] ?? true),
            'daycare_subsidy_available' => (bool) ($data['daycare_subsidy_available'] ?? false),
            'compliance_notes' => $data['compliance_notes'] ?? null,
            'audited_by_user_id' => $auditedByUserId,
        ]);
    }

    /**
     * Generate Disnaker statutory gender compliance report.
     */
    public function generateDisnakerGenderComplianceReport(Company $company): array
    {
        $latestCheck = $company->femaleWorkerFacilityChecks()->latest('inspection_date')->first();
        $casesCount = $company->ppksComplaintCases()->count();
        $resolvedCount = $company->ppksComplaintCases()->where('status', 'resolved')->count();

        $isFullyCompliant = $latestCheck && $latestCheck->lactation_room_standard_met && $latestCheck->night_shift_transport_provided && $latestCheck->night_shift_nutritious_food_provided;

        return [
            'perusahaan' => $company->name,
            'dasar_hukum' => 'Kepmenaker No. 88 Tahun 2023 & Pasal 76 UU No. 13 Tahun 2003',
            'satgas_ppks_aktif' => true,
            'total_kasus_ditangani' => $casesCount,
            'total_kasus_selesai' => $resolvedCount,
            'fasilitas_ruang_laktasi' => $latestCheck ? ($latestCheck->lactation_room_standard_met ? 'MEMENUHI STANDAR' : 'BELUM MEMENUHI') : 'BELUM DIAUDIT',
            'angkutan_antar_jemput_malam' => $latestCheck ? ($latestCheck->night_shift_transport_provided ? 'TERSEDIA' : 'TIDAK TERSEDIA') : 'BELUM DIAUDIT',
            'makanan_bergizi_shift_malam' => $latestCheck ? ($latestCheck->night_shift_nutritious_food_provided ? 'TERSEDIA' : 'TIDAK TERSEDIA') : 'BELUM DIAUDIT',
            'status_kepatuhan_keseluruhan' => $isFullyCompliant ? 'PATUH PENUH' : 'PERLU PERBAIKAN',
        ];
    }
}
