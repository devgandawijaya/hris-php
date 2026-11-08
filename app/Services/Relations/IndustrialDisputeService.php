<?php

namespace App\Services\Relations;

use App\Models\Company;
use App\Models\TripartiteIndustrialDispute;
use Carbon\Carbon;
use InvalidArgumentException;

class IndustrialDisputeService
{
    /**
     * Record an escalated industrial dispute under UU No. 2 Tahun 2004 (PPHI)
     */
    public function recordDispute(Company $company, array $data): TripartiteIndustrialDispute
    {
        $tenantId = $company->tenant_id;
        $validTypes = ['perselisihan_hak', 'perselisihan_kepentingan', 'perselisihan_phk', 'perselisihan_antar_serikat'];

        if (!in_array($data['dispute_type'], $validTypes)) {
            throw new InvalidArgumentException("Jenis perselisihan tidak valid. Harus salah satu dari 4 klasifikasi UU No. 2/2004.");
        }

        $seq = TripartiteIndustrialDispute::where('tenant_id', $tenantId)->count() + 1;
        do {
            $caseNumber = sprintf('PHI-CASE/%s/%04d', date('Y'), $seq++);
        } while (TripartiteIndustrialDispute::where('case_number', $caseNumber)->exists());

        return TripartiteIndustrialDispute::create([
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'case_number' => $caseNumber,
            'dispute_type' => $data['dispute_type'],
            'employee_id' => $data['employee_id'] ?? null,
            'employee_name_external' => $data['employee_name_external'] ?? null,
            'bipartite_meeting_id' => $data['bipartite_meeting_id'] ?? null,
            'disnaker_mediator_name' => $data['disnaker_mediator_name'],
            'disnaker_office' => $data['disnaker_office'],
            'mediation_date' => Carbon::parse($data['mediation_date'])->toDateString(),
            'dispute_chronology' => $data['dispute_chronology'],
            'company_response' => 'pending',
            'worker_response' => 'pending',
            'settlement_status' => 'mediation_stage',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Record written recommendation from Disnaker Mediator (Anjuran Tertulis Mediator)
     */
    public function recordMediatorRecommendation(TripartiteIndustrialDispute $dispute, string $recommendationText): TripartiteIndustrialDispute
    {
        $dispute->update([
            'mediator_written_recommendation' => $recommendationText,
            'settlement_status' => 'mediation_stage',
        ]);

        return $dispute->fresh();
    }

    /**
     * Record both parties' responses to mediator recommendation
     */
    public function recordPartyResponses(TripartiteIndustrialDispute $dispute, string $companyResponse, string $workerResponse, ?string $jointAgreementNumber = null): TripartiteIndustrialDispute
    {
        // Both parties agree -> Peaceful Agreement (Perjanjian Bersama / PB)
        if ($companyResponse === 'accepted' && $workerResponse === 'accepted') {
            $newStatus = 'peaceful_agreement_pb';
        } elseif ($companyResponse === 'rejected' || $workerResponse === 'rejected') {
            $newStatus = 'escalated_to_phi_court';
        } else {
            $newStatus = 'mediation_stage';
        }

        $dispute->update([
            'company_response' => $companyResponse,
            'worker_response' => $workerResponse,
            'settlement_status' => $newStatus,
            'joint_agreement_number' => $jointAgreementNumber,
        ]);

        return $dispute->fresh();
    }

    /**
     * Alias for responding to mediator recommendation
     */
    public function respondToRecommendation(TripartiteIndustrialDispute $dispute, string $companyResponse, string $workerResponse, ?string $jointAgreementNumber = null): TripartiteIndustrialDispute
    {
        return $this->recordPartyResponses($dispute, $companyResponse, $workerResponse, $jointAgreementNumber);
    }

    /**
     * Generate PPHI docket for court / Disnaker
     */
    public function generateDisputeFilingDocket(TripartiteIndustrialDispute $dispute): array
    {
        return [
            'dasar_hukum' => 'UU No. 2 Tahun 2004 tentang PPHI',
            'nomor_perkara' => $dispute->case_number,
            'jenis_perselisihan' => strtoupper($dispute->dispute_type),
            'status_penyelesaian' => $dispute->settlement_status === 'peaceful_agreement_pb'
                ? 'PERDAMAIAN TERCAPAI (PERJANJIAN BERSAMA / PB)'
                : 'DILIMPAHKAN KE PENGADILAN HUBUNGAN INDUSTRIAL (PHI)',
            'nomor_akta_pb' => $dispute->joint_agreement_number,
        ];
    }
}
