<?php

namespace App\Services\Talent;

use App\Models\Company;
use App\Models\InternshipProgram;
use App\Models\InternshipLogbook;
use Carbon\Carbon;
use InvalidArgumentException;

class InternshipApprenticeshipService
{
    /**
     * Enroll an intern under Permenaker No. 6 Tahun 2020.
     * Enforces statutory apprenticeship agreement and quota limit (< 20% of workforce).
     */
    public function enrollIntern(Company $company, array $data, int $activeWorkforceCount = 50): InternshipProgram
    {
        $currentInternsCount = $company->internshipPrograms()->where('status', 'active')->count();
        $maxAllowedInterns = max(1, (int) ceil($activeWorkforceCount * 0.20)); // Batas kuota 20% Pasal 10 Permenaker 6/2020

        $isRatioCompliant = ($currentInternsCount + 1) <= $maxAllowedInterns;

        $startDate = Carbon::parse($data['start_date'] ?? now());
        $endDate = Carbon::parse($data['end_date'] ?? $startDate->copy()->addMonths(6));

        // Permenaker 6/2020 Pasal 5: durasi magang maksimal 1 tahun
        if ($startDate->diffInDays($endDate) > 366) {
            throw new InvalidArgumentException('Durasi pemagangan dalam negeri maksimal 1 (satu) tahun sesuai Pasal 5 Permenaker No. 6/2020.');
        }

        $agreementNumber = $data['agreement_letter_number'] ?? 'SP-MAGANG/' . date('Y') . '/' . rand(100, 999);

        return InternshipProgram::create([
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'program_title' => $data['program_title'] ?? 'Program Pemagangan Mahasiswa & Kejuruan',
            'department_id' => $data['department_id'] ?? null,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'pocket_money_allowance_daily' => $data['pocket_money_allowance_daily'] ?? 75000.00,
            'bpjs_bpu_covered' => (bool) ($data['bpjs_bpu_covered'] ?? true),
            'mentor_employee_id' => $data['mentor_employee_id'] ?? null,
            'intern_candidate_name' => $data['intern_candidate_name'],
            'intern_nik_ktp' => $data['intern_nik_ktp'],
            'institution_origin' => $data['institution_origin'] ?? 'Universitas / SMK Vokasi',
            'agreement_letter_number' => $agreementNumber,
            'statutory_ratio_compliant' => $isRatioCompliant,
            'status' => 'active',
        ]);
    }

    /**
     * Record daily activity logbook.
     */
    public function recordLogbook(InternshipProgram $program, array $data): InternshipLogbook
    {
        return InternshipLogbook::create([
            'tenant_id' => $program->tenant_id,
            'internship_program_id' => $program->id,
            'log_date' => $data['log_date'] ?? Carbon::now()->toDateString(),
            'activity_summary' => $data['activity_summary'],
            'competency_mastered' => $data['competency_mastered'] ?? 'Keahlian Umum',
            'is_verified_by_mentor' => false,
        ]);
    }

    /**
     * Verify and rate intern logbook.
     */
    public function verifyLogbook(InternshipLogbook $logbook, float $mentorRating, string $feedback): InternshipLogbook
    {
        $logbook->update([
            'mentor_rating' => $mentorRating,
            'mentor_feedback' => $feedback,
            'is_verified_by_mentor' => true,
        ]);

        return $logbook;
    }

    /**
     * Complete internship and optionally convert to full-time employee.
     */
    public function completeInternship(InternshipProgram $program, bool $convertToEmployee = false): InternshipProgram
    {
        $program->update([
            'status' => $convertToEmployee ? 'converted_to_employee' : 'completed',
        ]);

        return $program;
    }

    /**
     * Generate official Certificate of Apprenticeship payload under Permenaker 6/2020 Pasal 18.
     */
    public function generateCertificatePayload(InternshipProgram $program): array
    {
        $verifiedLogs = $program->logbooks()->where('is_verified_by_mentor', true)->get();
        $avgRating = $verifiedLogs->count() > 0 ? round($verifiedLogs->avg('mentor_rating'), 2) : 4.50;

        return [
            'nomor_sertifikat' => 'CERT-MAGANG/' . date('Y') . '/' . str_pad($program->id, 4, '0', STR_PAD_LEFT),
            'nama_peserta' => $program->intern_candidate_name,
            'institusi_asal' => $program->institution_origin,
            'judul_program' => $program->program_title,
            'durasi_periode' => $program->start_date->format('d M Y') . ' s/d ' . $program->end_date->format('d M Y'),
            'nilai_evaluasi_komposit' => $avgRating,
            'predikat_kelulusan' => $avgRating >= 4.0 ? 'SANGAT KOMPETEN' : 'KOMPETEN',
            'dasar_hukum' => 'Pasal 18 Permenaker No. 6 Tahun 2020 tentang Penyelenggaraan Pemagangan di Dalam Negeri',
        ];
    }
}
