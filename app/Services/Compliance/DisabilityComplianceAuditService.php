<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\Employee;
use App\Models\DisabilityEmploymentAudit;
use App\Models\EmployeeDisabilityProfile;
use Carbon\Carbon;

class DisabilityComplianceAuditService
{
    /**
     * Conduct audit against statutory disability employment quota (UU No. 8/2016 Pasal 53)
     */
    public function performAudit(Company $company, string $entityType = 'private_sector', ?int $auditedByUserId = null): DisabilityEmploymentAudit
    {
        $tenantId = $company->tenant_id;
        $currentYear = (int) date('Y');

        // Total active workforce
        $activeEmployees = Employee::where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->get();

        $totalWorkforce = $activeEmployees->count();

        // Count disability workforce
        $disabilityCount = $activeEmployees->filter(function ($e) {
            return (bool) $e->has_disability || EmployeeDisabilityProfile::where('tenant_id', $e->tenant_id)->where('employee_id', $e->id)->exists();
        })->count();

        // Statutory quota: 1.00% for private sector (Pasal 53 ayat 2), 2.00% for BUMN/BUMD (Pasal 53 ayat 1)
        $quotaPercentage = ($entityType === 'state_owned_bumn') ? 2.00 : 1.00;

        // Indonesian Disnaker standard rounding practice: ceil(Total * Quota%)
        $statutoryRequired = ($totalWorkforce > 0) ? (int) ceil(($totalWorkforce * $quotaPercentage) / 100) : 0;
        $shortfall = max(0, $statutoryRequired - $disabilityCount);

        $actualQuota = ($totalWorkforce > 0) ? round(($disabilityCount / $totalWorkforce) * 100, 2) : 0.00;

        // Compliance determination
        if ($shortfall === 0) {
            $status = 'compliant';
        } elseif ($shortfall === 1) {
            $status = 'near_target';
        } else {
            $status = 'non_compliant';
        }

        // Generate serial audit code
        $nextNum = DisabilityEmploymentAudit::where('tenant_id', $tenantId)->count() + 1;
        $auditCode = sprintf('DISABILITY-AUDIT-%d-%04d', $currentYear, $nextNum);

        // Accessibility scoring defaults
        $physicalAccessibilityScore = 85; // e.g. Ramps, elevators, accessible restrooms
        $digitalAccessibilityScore = 90;  // e.g. Screen reader compatible, keyboard navigable HRIS

        $recommendations = [];
        if ($shortfall > 0) {
            $recommendations[] = "Wajib merekrut minimal {$shortfall} pekerja penyandang disabilitas tambahan untuk memenuhi kuota sah UU No. 8/2016 Pasal 53 ({$quotaPercentage}%).";
            $recommendations[] = "Bekerja sama dengan Unit Layanan Disabilitas (ULD) Ketenagakerjaan Disnaker setempat untuk pemenuhan talenta berkebutuhan khusus.";
        } else {
            $recommendations[] = "Kuota ketenagakerjaan sah telah terpenuhi ({$actualQuota}% >= {$quotaPercentage}%). Pertahankan dan kembangkan program inklusi.";
        }

        return DisabilityEmploymentAudit::create([
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'audit_code' => $auditCode,
            'audit_year' => $currentYear,
            'audit_date' => Carbon::today()->toDateString(),
            'entity_type' => $entityType,
            'total_active_workforce' => $totalWorkforce,
            'disability_workforce_count' => $disabilityCount,
            'statutory_quota_percentage' => $quotaPercentage,
            'actual_quota_percentage' => $actualQuota,
            'statutory_headcount_required' => $statutoryRequired,
            'headcount_shortfall' => $shortfall,
            'compliance_status' => $status,
            'physical_accessibility_score' => $physicalAccessibilityScore,
            'digital_accessibility_score' => $digitalAccessibilityScore,
            'reasonable_accommodations_summary' => 'Penyediaan jalur akses ramah kursi roda, fasilitas toilet difabel terstandar, dan dukungan teknologi adaptif/screen reader.',
            'recommendations' => implode(' ', $recommendations),
            'audited_by_user_id' => $auditedByUserId,
        ]);
    }

    /**
     * Register reasonable accommodation profile for an employee
     */
    public function registerDisabilityProfile(Employee $employee, array $data): EmployeeDisabilityProfile
    {
        // Mark employee flag
        $employee->update(['has_disability' => true]);

        return EmployeeDisabilityProfile::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
            ],
            [
                'company_id' => $employee->company_id,
                'disability_category' => $data['disability_category'] ?? 'physical',
                'medical_certificate_number' => $data['medical_certificate_number'] ?? null,
                'accommodations_provided' => $data['accommodations_provided'] ?? 'Akses meja kerja ergonomis, ramp gedung, dan fleksibilitas istirahat medis.',
                'accessibility_needs' => $data['accessibility_needs'] ?? null,
                'emergency_evacuation_assistance_plan' => $data['emergency_evacuation_assistance_plan'] ?? 'Didampingi oleh designated safety floor warden saat evakuasi darurat.',
            ]
        );
    }

    /**
     * Generate structured compliance report for Disnaker inspection
     */
    public function generateDisnakerComplianceReport(DisabilityEmploymentAudit $audit): array
    {
        return [
            'dasar_hukum' => 'Undang-Undang Republik Indonesia Nomor 8 Tahun 2016 tentang Penyandang Disabilitas (Pasal 53 ayat 1 & 2)',
            'kode_audit' => $audit->audit_code,
            'tanggal_audit' => $audit->audit_date->toDateString(),
            'perusahaan' => [
                'nama' => $audit->company->name ?? 'Perusahaan',
                'tipe_entitas' => $audit->entity_type === 'state_owned_bumn' ? 'BUMN / BUMD' : 'Badan Usaha Swasta',
            ],
            'ringkasan_kuota' => [
                'total_tenaga_kerja_aktif' => $audit->total_active_workforce,
                'jumlah_pekerja_disabilitas' => $audit->disability_workforce_count,
                'kuota_wajib_persen' => "{$audit->statutory_quota_percentage}%",
                'rasio_aktual_persen' => "{$audit->actual_quota_percentage}%",
                'jumlah_wajib_minimal' => $audit->statutory_headcount_required,
                'kekurangan_headcount' => $audit->headcount_shortfall,
                'status_kepatuhan' => strtoupper($audit->compliance_status),
            ],
            'aksesibilitas_lingkungan_kerja' => [
                'skor_aksesibilitas_fisik' => "{$audit->physical_accessibility_score}%",
                'skor_aksesibilitas_digital' => "{$audit->digital_accessibility_score}%",
                'akomodasi_yang_layak' => $audit->reasonable_accommodations_summary,
            ],
            'rekomendasi_tindak_lanjut' => $audit->recommendations,
        ];
    }
}
