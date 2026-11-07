<?php

namespace App\Services\Relations;

use App\Models\Company;
use App\Models\LaborUnion;
use App\Models\PkbNegotiation;
use App\Models\UnionDuesDeduction;
use App\Models\Employee;
use Carbon\Carbon;
use InvalidArgumentException;

class PkbNegotiationService
{
    /**
     * Register a recognized labor union under UU No. 21/2000.
     */
    public function registerUnion(Company $company, array $data, int $totalWorkforce = 100): LaborUnion
    {
        if (empty($data['union_name']) || empty($data['registration_number'])) {
            throw new InvalidArgumentException('Nama serikat pekerja dan nomor pencatatan Disnaker wajib diisi.');
        }

        $members = (int) ($data['total_verified_members'] ?? 0);
        $percentage = $totalWorkforce > 0 ? round(($members / $totalWorkforce) * 100, 2) : 0.00;
        $hasBargainingRights = $percentage >= 50.00; // Pasal 119 UU 13/2003

        return LaborUnion::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'union_name' => $data['union_name'],
            'registration_number' => $data['registration_number'],
            'chairperson_name' => $data['chairperson_name'] ?? 'Ketua Pengurus Unit Kerja',
            'total_verified_members' => $members,
            'workforce_percentage' => $percentage,
            'has_bargaining_rights' => $hasBargainingRights,
            'is_active' => true,
        ]);
    }

    /**
     * Verify bargaining rights of a union or coalition under Pasal 119 UU 13/2003.
     */
    public function verifyBargainingRights(LaborUnion $union, int $totalWorkforce): array
    {
        $percentage = $totalWorkforce > 0 ? round(($union->total_verified_members / $totalWorkforce) * 100, 2) : 0.00;
        $hasRights = $percentage >= 50.00;

        $union->update([
            'workforce_percentage' => $percentage,
            'has_bargaining_rights' => $hasRights,
        ]);

        return [
            'union_name' => $union->union_name,
            'total_verified_members' => $union->total_verified_members,
            'total_workforce' => $totalWorkforce,
            'membership_percentage' => $percentage,
            'has_bargaining_rights' => $hasRights,
            'legal_basis' => 'Pasal 119 ayat 1 UU No. 13/2003 jo. Permenaker No. 28/2014',
        ];
    }

    /**
     * Initiate PKB Negotiation with max 30 working days target under Permenaker 28/2014 Pasal 24.
     */
    public function initiatePkbNegotiation(Company $company, LaborUnion $union, array $data): PkbNegotiation
    {
        if (!$union->has_bargaining_rights) {
            throw new InvalidArgumentException('Serikat pekerja belum memenuhi kuota keterwakilan minimum 50% untuk merundingkan PKB.');
        }

        $startDate = Carbon::parse($data['negotiation_start_date'] ?? now());
        $targetEndDate = Carbon::parse($data['negotiation_target_end_date'] ?? $startDate->copy()->addDays(30));

        $draftCode = $data['draft_code'] ?? 'PKB-DRAFT/' . date('Y') . '/' . rand(100, 999);

        return PkbNegotiation::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'labor_union_id' => $union->id,
            'pkb_period_years' => $data['pkb_period_years'] ?? (date('Y') . '-' . (date('Y') + 2)),
            'draft_code' => $draftCode,
            'negotiation_start_date' => $startDate->toDateString(),
            'negotiation_target_end_date' => $targetEndDate->toDateString(),
            'employer_delegation_lead' => $data['employer_delegation_lead'] ?? 'Direktur HR & Legal',
            'union_delegation_lead' => $data['union_delegation_lead'] ?? $union->chairperson_name,
            'status' => 'in_progress',
            'summary_clauses' => $data['summary_clauses'] ?? null,
        ]);
    }

    /**
     * Register Check-Off System union dues deduction with written employee consent.
     */
    public function registerDuesCheckoff(LaborUnion $union, Employee $employee, float $amount, string $consentLetterNumber): UnionDuesDeduction
    {
        if (empty($consentLetterNumber)) {
            throw new InvalidArgumentException('Surat kuasa tertulis pemotongan upah (check-off) wajib dilampirkan.');
        }

        return UnionDuesDeduction::create([
            'tenant_id' => $union->tenant_id,
            'labor_union_id' => $union->id,
            'employee_id' => $employee->id,
            'monthly_dues_amount' => $amount,
            'consent_letter_number' => $consentLetterNumber,
            'is_active' => true,
        ]);
    }

    /**
     * Finalize PKB Registration at Disnaker.
     */
    public function finalizePkbRegistration(PkbNegotiation $negotiation, string $disnakerDecreeNumber): PkbNegotiation
    {
        $negotiation->update([
            'status' => 'registered_at_disnaker',
            'disnaker_decree_number' => $disnakerDecreeNumber,
        ]);

        return $negotiation;
    }
}
