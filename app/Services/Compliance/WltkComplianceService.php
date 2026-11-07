<?php

namespace App\Services\Compliance;

use App\Models\Company;
use App\Models\Employee;
use App\Models\WltkReport;
use App\Models\CompanyRegulation;
use App\Models\BipartiteMeeting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WltkComplianceService
{
    /**
     * Generate or aggregate draft WLTK (Wajib Lapor Ketenagakerjaan UU No. 7/1981)
     */
    public function generateDraftReport(Company $company, int $year, string $period = 'annual'): WltkReport
    {
        $tenantId = $company->tenant_id;

        // Query active employees
        $activeEmployees = Employee::where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->get();

        $totalWorkers = $activeEmployees->count();

        // Citizenship & Gender breakdown
        $totalWniMale = $activeEmployees->filter(fn($e) => strtoupper($e->citizenship ?? 'WNI') === 'WNI' && strtolower($e->gender) === 'male')->count();
        $totalWniFemale = $activeEmployees->filter(fn($e) => strtoupper($e->citizenship ?? 'WNI') === 'WNI' && strtolower($e->gender) === 'female')->count();
        $totalWnaMale = $activeEmployees->filter(fn($e) => strtoupper($e->citizenship ?? 'WNI') === 'WNA' && strtolower($e->gender) === 'male')->count();
        $totalWnaFemale = $activeEmployees->filter(fn($e) => strtoupper($e->citizenship ?? 'WNI') === 'WNA' && strtolower($e->gender) === 'female')->count();

        // Employment contracts breakdown
        $totalPkwtt = $activeEmployees->filter(fn($e) => in_array(strtolower($e->employment_status), ['pkwtt', 'permanent']))->count();
        $totalPkwt = $activeEmployees->filter(fn($e) => in_array(strtolower($e->employment_status), ['pkwt', 'contract', 'probation']))->count();

        // Disability workforce
        $totalDisability = $activeEmployees->filter(fn($e) => (bool) $e->has_disability)->count();

        // Wage scales
        $lowestWage = (float) ($activeEmployees->min('basic_salary') ?? 0);
        $highestWage = (float) ($activeEmployees->max('basic_salary') ?? 0);

        // BPJS registration check
        $bpjsTkRegistered = $activeEmployees->filter(fn($e) => !empty($e->bpjs_tk_number))->count() > 0;
        $bpjsKesRegistered = $activeEmployees->filter(fn($e) => !empty($e->bpjs_kes_number))->count() > 0;

        // Check PP / PKB registration
        $hasPpOrPkb = CompanyRegulation::where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->where('status', 'approved_disnaker')
            ->exists();

        // Check LKS Bipartit
        $hasLksBipartit = BipartiteMeeting::where('tenant_id', $tenantId)
            ->where('company_id', $company->id)
            ->exists();

        // Generate report number
        $nextNumber = WltkReport::where('tenant_id', $tenantId)->where('reporting_year', $year)->count() + 1;
        $reportNumber = sprintf('WLTK/%d/%04d', $year, $nextNumber);

        $payload = [
            'company_info' => [
                'legal_name' => $company->legal_name ?? $company->name,
                'npwp' => $company->npwp,
                'address' => $company->address,
                'city' => $company->city,
                'province' => $company->province,
            ],
            'workforce_demographics' => [
                'total_workers' => $totalWorkers,
                'wni_male' => $totalWniMale,
                'wni_female' => $totalWniFemale,
                'wna_male' => $totalWnaMale,
                'wna_female' => $totalWnaFemale,
                'pkwtt' => $totalPkwtt,
                'pkwt' => $totalPkwt,
                'disability' => $totalDisability,
            ],
            'welfare_and_statutory' => [
                'lowest_wage' => $lowestWage,
                'highest_wage' => $highestWage,
                'bpjs_tk_compliance' => $bpjsTkRegistered,
                'bpjs_kes_compliance' => $bpjsKesRegistered,
                'has_pp_or_pkb' => $hasPpOrPkb,
                'has_lks_bipartit' => $hasLksBipartit,
            ],
            'generated_at' => now()->toIso8601String(),
        ];

        return WltkReport::create([
            'tenant_id' => $tenantId,
            'company_id' => $company->id,
            'report_number' => $reportNumber,
            'reporting_year' => $year,
            'report_period' => $period,
            'status' => 'draft',
            'company_registration_number' => $company->npwp ?? ('NIB-'.strtoupper(substr(md5($company->name), 0, 10))),
            'kbli_code' => '62012',
            'total_workers' => $totalWorkers,
            'total_wni_male' => $totalWniMale,
            'total_wni_female' => $totalWniFemale,
            'total_wna_male' => $totalWnaMale,
            'total_wna_female' => $totalWnaFemale,
            'total_pkwtt' => $totalPkwtt,
            'total_pkwt' => $totalPkwt,
            'total_disability_workers' => $totalDisability,
            'lowest_wage' => $lowestWage,
            'highest_wage' => $highestWage,
            'bpjs_tk_registered' => $bpjsTkRegistered,
            'bpjs_kes_registered' => $bpjsKesRegistered,
            'has_pp_or_pkb' => $hasPpOrPkb,
            'has_lks_bipartit' => $hasLksBipartit,
            'raw_payload_json' => $payload,
        ]);
    }

    /**
     * Mark WLTK report as submitted to Kemnaker online portal
     */
    public function markSubmitted(WltkReport $report, string $receiptNumber, ?string $submissionDate = null): WltkReport
    {
        $subDate = $submissionDate ? Carbon::parse($submissionDate) : Carbon::today();
        $validUntil = $subDate->copy()->addYear(); // Annual statutory validity per UU 7/1981

        $report->update([
            'status' => 'submitted_kemnaker',
            'disnaker_receipt_number' => $receiptNumber,
            'submission_date' => $subDate->toDateString(),
            'valid_until' => $validUntil->toDateString(),
        ]);

        return $report->fresh();
    }

    /**
     * Export structured JSON compliant with WLKP Online Kemnaker
     */
    public function exportKemnakerJson(WltkReport $report): array
    {
        return [
            'kemnaker_wlkp_version' => '2.4.0',
            'pelaporan' => [
                'nomor_laporan' => $report->report_number,
                'tahun_lapor' => $report->reporting_year,
                'periode' => $report->report_period,
                'tanggal_lapor' => $report->submission_date ? $report->submission_date->toDateString() : now()->toDateString(),
                'status_lapor' => $report->status,
                'nomor_tanda_terima_disnaker' => $report->disnaker_receipt_number,
            ],
            'perusahaan' => [
                'nama' => $report->company->name ?? 'Perusahaan',
                'badan_hukum' => $report->company->legal_name ?? $report->company->name,
                'npwp' => $report->company->npwp,
                'nib_wlkp' => $report->company_registration_number,
                'kbli' => $report->kbli_code,
                'alamat' => $report->company->address,
                'kabupaten_kota' => $report->company->city,
                'provinsi' => $report->company->province,
            ],
            'ketenagakerjaan' => [
                'total_tenaga_kerja' => $report->total_workers,
                'pekerja_wni' => [
                    'pria' => $report->total_wni_male,
                    'wanita' => $report->total_wni_female,
                    'total' => $report->total_wni_male + $report->total_wni_female,
                ],
                'pekerja_wna_tka' => [
                    'pria' => $report->total_wna_male,
                    'wanita' => $report->total_wna_female,
                    'total' => $report->total_wna_male + $report->total_wna_female,
                ],
                'hubungan_kerja' => [
                    'pkwtt_tetap' => $report->total_pkwtt,
                    'pkwt_kontrak' => $report->total_pkwt,
                ],
                'penyandang_disabilitas' => [
                    'jumlah' => $report->total_disability_workers,
                    'persentase' => $report->total_workers > 0 ? round(($report->total_disability_workers / $report->total_workers) * 100, 2) : 0,
                ],
            ],
            'pengupahan_dan_jaminan_sosial' => [
                'upah_terendah' => $report->lowest_wage,
                'upah_tertinggi' => $report->highest_wage,
                'bpjs_ketenagakerjaan' => $report->bpjs_tk_registered ? 'TERDAFTAR' : 'BELUM_TERDAFTAR',
                'bpjs_kesehatan' => $report->bpjs_kes_registered ? 'TERDAFTAR' : 'BELUM_TERDAFTAR',
                'peraturan_perusahaan_pkb' => $report->has_pp_or_pkb ? 'ADA_SAH' : 'BELUM_ADA',
                'lks_bipartit' => $report->has_lks_bipartit ? 'TERBENTUK' : 'BELUM_TERBENTUK',
            ],
        ];
    }

    /**
     * Export CSV format for offline Disnaker inspection submission
     */
    public function exportKemnakerCsv(WltkReport $report): string
    {
        $data = [
            ['FIELD', 'VALUE', 'KETERANGAN_REGULASI'],
            ['Nomor Laporan WLTK', $report->report_number, 'UU No. 7/1981'],
            ['Tahun Pelaporan', $report->reporting_year, 'Siklus Tahunan'],
            ['Nomor WLKP / NIB', $report->company_registration_number, 'Identitas Perusahaan'],
            ['Kode KBLI', $report->kbli_code, 'Klasifikasi Usaha'],
            ['Total Tenaga Kerja', $report->total_workers, 'Total Pekerja Aktif'],
            ['Pekerja WNI Pria', $report->total_wni_male, 'Warga Negara Indonesia'],
            ['Pekerja WNI Wanita', $report->total_wni_female, 'Warga Negara Indonesia'],
            ['Pekerja WNA/TKA Pria', $report->total_wna_male, 'Tenaga Kerja Asing'],
            ['Pekerja WNA/TKA Wanita', $report->total_wna_female, 'Tenaga Kerja Asing'],
            ['Hubungan Kerja PKWTT (Tetap)', $report->total_pkwtt, 'Pasal 56 UU 13/2003'],
            ['Hubungan Kerja PKWT (Kontrak)', $report->total_pkwt, 'PP 35/2021'],
            ['Pekerja Penyandang Disabilitas', $report->total_disability_workers, 'UU No. 8/2016 (Kuota Min 1%)'],
            ['Upah Pokok Terendah', number_format($report->lowest_wage, 2, '.', ''), 'Sesuai Standar UMK/UMP'],
            ['Upah Pokok Tertinggi', number_format($report->highest_wage, 2, '.', ''), 'Struktur & Skala Upah'],
            ['Kepesertaan BPJS Ketenagakerjaan', $report->bpjs_tk_registered ? 'YA' : 'TIDAK', 'Wajib UU 24/2011'],
            ['Kepesertaan BPJS Kesehatan', $report->bpjs_kes_registered ? 'YA' : 'TIDAK', 'Wajib UU 24/2011'],
            ['Peraturan Perusahaan / PKB', $report->has_pp_or_pkb ? 'ADA' : 'TIDAK', 'Pasal 108 UU 13/2003'],
            ['Lembaga Kerja Sama (LKS) Bipartit', $report->has_lks_bipartit ? 'ADA' : 'TIDAK', 'Pasal 106 UU 13/2003'],
            ['Status Pelaporan', $report->status, 'Draft / Submitted'],
            ['Nomor Bukti Terima Kemnaker', $report->disnaker_receipt_number ?? '-', 'Bukti Registrasi'],
        ];

        $output = fopen('php://temp', 'r+');
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Check reports due for statutory annual renewal within specified days
     */
    public function checkRenewalDueAlerts(int $tenantId, int $daysThreshold = 30): Collection
    {
        $today = Carbon::today();
        $targetDate = $today->copy()->addDays($daysThreshold);

        return WltkReport::where('tenant_id', $tenantId)
            ->whereNotNull('valid_until')
            ->whereBetween('valid_until', [$today->toDateString(), $targetDate->toDateString()])
            ->get();
    }
}
