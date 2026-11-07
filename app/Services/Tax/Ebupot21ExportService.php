<?php

namespace App\Services\Tax;

use App\Models\Company;
use App\Models\Employee;
use App\Models\TaxForm1721A1Record;
use App\Models\TaxReconciliation;
use Carbon\Carbon;

class Ebupot21ExportService
{
    /**
     * Generate or compile Form 1721-A1 record for an employee for a given tax year.
     */
    public function generateForm1721A1(Employee $employee, int $taxYear): TaxForm1721A1Record
    {
        $company = $employee->company;
        $reconciliation = TaxReconciliation::where('employee_id', $employee->id)
            ->where('tax_year', $taxYear)
            ->first();

        $grossAnnual = $reconciliation ? (float) $reconciliation->gross_income_annual : ((float) $employee->basic_salary * 12);
        $biayaJabatan = $reconciliation ? (float) $reconciliation->biaya_jabatan : min(6000000.00, $grossAnnual * 0.05);
        $bpjsDeduction = $reconciliation ? (float) $reconciliation->bpjs_deductions_annual : (((float) $employee->basic_salary * 0.03) * 12);
        $totalPengurang = $biayaJabatan + $bpjsDeduction;
        $neto = $reconciliation ? (float) $reconciliation->net_income_annual : max(0.00, $grossAnnual - $totalPengurang);
        $ptkp = $reconciliation ? (float) $reconciliation->ptkp_annual : 54000000.00;
        $pkp = $reconciliation ? (float) $reconciliation->pkp : max(0.00, floor(($neto - $ptkp) / 1000) * 1000);
        $pph21Annual = $reconciliation ? (float) $reconciliation->pph21_annual_due : 0.00;
        $pph21Paid = $reconciliation ? (float) $reconciliation->pph21_ter_paid_total : 0.00;

        // Sequence number e-Bupot 21/26
        $sequence = str_pad((string) $employee->id, 7, '0', STR_PAD_LEFT);
        $nomorBupot = "1.1-12." . substr((string) $taxYear, 2) . "-{$sequence}";

        $record = TaxForm1721A1Record::updateOrCreate(
            [
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'tax_year' => $taxYear,
            ],
            [
                'company_id' => $company->id,
                'nomor_bupot' => $nomorBupot,
                'pemotong_npwp' => $company->npwp ?? '01.234.567.8-012.000',
                'pemotong_nama' => $company->legal_name ?? $company->name,
                'penerima_npwp' => $employee->npwp,
                'penerima_nik' => $employee->nik_ktp ?? '3171012345670001',
                'penerima_nama' => $employee->full_name,
                'penerima_alamat' => $employee->address ?? 'DKI Jakarta',
                'status_ptkp' => $employee->tax_ptkp_status ?? 'TK/0',
                'jumlah_tanggungan' => $this->parsePtkpDependents($employee->tax_ptkp_status),
                'masa_perolehan_awal' => 1,
                'masa_perolehan_akhir' => 12,
                'gaji_pensiun_tht' => round($grossAnnual * 0.85, 2),
                'tunjangan_pph' => 0.00,
                'tunjangan_lain_lembur' => round($grossAnnual * 0.05, 2),
                'honorarium_imbalan' => 0.00,
                'premi_asuransi_jkk_jkm' => round($grossAnnual * 0.02, 2),
                'natura_kenikmatan' => 0.00,
                'tantiem_bonus_thr' => round($grossAnnual * 0.08, 2),
                'penghasilan_bruto' => $grossAnnual,
                'biaya_jabatan' => $biayaJabatan,
                'iuran_pensiun_jht' => $bpjsDeduction,
                'total_pengurang' => $totalPengurang,
                'penghasilan_neto' => $neto,
                'penghasilan_neto_masa_sebelumnya' => 0.00,
                'jumlah_penghasilan_neto_disetahunkan' => $neto,
                'ptkp' => $ptkp,
                'pkp' => $pkp,
                'pph21_terutang_setahun' => $pph21Annual,
                'pph21_telah_dipotong' => $pph21Paid,
                'pph21_kurang_lebih_bayar' => 0.00,
                'status' => 'published',
                'published_at' => now(),
            ]
        );

        return $record;
    }

    /**
     * Generate DJP Online e-Bupot 21/26 bulk import CSV format according to PER-2/PJ/2024.
     */
    public function generateEbupotCsv(Company $company, int $taxYear): string
    {
        $records = TaxForm1721A1Record::where('company_id', $company->id)
            ->where('tax_year', $taxYear)
            ->get();

        $rows = [];
        // Header standard DJP e-Bupot 21/26 1721-A1
        $rows[] = [
            'MasaPajak',
            'TahunPajak',
            'Pembetulan',
            'NomorBuktiPotong',
            'NPWPPemotong',
            'NamaPemotong',
            'IdentitasPenerima',
            'NPWPPenerima',
            'NIKPenerima',
            'NamaPenerima',
            'StatusPTKP',
            'JumlahTanggungan',
            'MasaAwal',
            'MasaAkhir',
            'PenghasilanBruto',
            'BiayaJabatan',
            'IuranPensiunJHT',
            'PenghasilanNeto',
            'PTKP',
            'PKP',
            'PPh21Terutang',
            'PPh21Dipotong',
        ];

        foreach ($records as $rec) {
            $rows[] = [
                '12',
                (string) $rec->tax_year,
                '0',
                $rec->nomor_bupot,
                preg_replace('/[^0-9]/', '', $rec->pemotong_npwp),
                $rec->pemotong_nama,
                !empty($rec->penerima_npwp) ? 'NPWP' : 'NIK',
                preg_replace('/[^0-9]/', '', $rec->penerima_npwp ?? ''),
                $rec->penerima_nik,
                $rec->penerima_nama,
                $rec->status_ptkp,
                (string) $rec->jumlah_tanggungan,
                (string) $rec->masa_perolehan_awal,
                (string) $rec->masa_perolehan_akhir,
                number_format((float) $rec->penghasilan_bruto, 0, '', ''),
                number_format((float) $rec->biaya_jabatan, 0, '', ''),
                number_format((float) $rec->iuran_pensiun_jht, 0, '', ''),
                number_format((float) $rec->penghasilan_neto, 0, '', ''),
                number_format((float) $rec->ptkp, 0, '', ''),
                number_format((float) $rec->pkp, 0, '', ''),
                number_format((float) $rec->pph21_terutang_setahun, 0, '', ''),
                number_format((float) $rec->pph21_telah_dipotong, 0, '', ''),
            ];
        }

        $output = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    private function parsePtkpDependents(?string $status): int
    {
        if (!$status) {
            return 0;
        }
        $parts = explode('/', $status);
        return isset($parts[1]) ? (int) $parts[1] : 0;
    }
}
