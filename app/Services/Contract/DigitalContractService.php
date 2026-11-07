<?php

namespace App\Services\Contract;

use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\User;
use Carbon\Carbon;

class DigitalContractService
{
    /**
     * Draft an Indonesian digital employment contract with statutory clauses.
     */
    public function draftContract(Employee $employee, array $data): EmploymentContract
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = !empty($data['end_date']) ? Carbon::parse($data['end_date']) : null;
        $type = $data['type'] ?? 'pkwt';
        $wage = (float) ($data['monthly_wage'] ?? $employee->basic_salary);

        // Standard sequence number
        $year = $startDate->year;
        $monthRoman = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ][$startDate->month] ?? 'I';

        $seq = str_pad((string) (EmploymentContract::where('tenant_id', $employee->tenant_id)->whereYear('start_date', $year)->count() + 1), 3, '0', STR_PAD_LEFT);
        $typeUpper = strtoupper($type);
        $contractNumber = "{$seq}/HRD-{$typeUpper}/{$monthRoman}/{$year}";

        $contractBody = $data['contract_body'] ?? $this->generateStandardClauses($employee, $type, $startDate, $endDate, $wage);

        $contract = new EmploymentContract();
        $contract->tenant_id = $employee->tenant_id;
        $contract->employee_id = $employee->id;
        $contract->contract_number = $data['contract_number'] ?? $contractNumber;
        $contract->type = $type;
        $contract->start_date = $startDate->toDateString();
        $contract->end_date = $endDate ? $endDate->toDateString() : null;
        $contract->probation_end_date = !empty($data['probation_end_date']) ? Carbon::parse($data['probation_end_date'])->toDateString() : null;
        $contract->monthly_wage = $wage;
        $contract->status = 'active';
        $contract->contract_body = $contractBody;
        $contract->signer_name = $data['signer_name'] ?? 'Direktur Utama / HR Director';
        $contract->signer_position = $data['signer_position'] ?? 'Direktur Operasional & SDM';
        $contract->notes = $data['notes'] ?? null;
        $contract->save();

        return $contract;
    }

    /**
     * Sign the contract as employee (Pihak Kedua).
     */
    public function signByEmployee(EmploymentContract $contract, string $signatureData, string $ipAddress): EmploymentContract
    {
        $contract->employee_signature = $signatureData;
        $contract->employee_signed_at = now();
        $contract->employee_sign_ip = $ipAddress;

        $this->verifyAndSealIfComplete($contract);
        $contract->save();

        return $contract;
    }

    /**
     * Sign the contract as company representative (Pihak Pertama).
     */
    public function signByCompany(EmploymentContract $contract, string $signatureData, User $signer, string $ipAddress): EmploymentContract
    {
        $contract->company_signature = $signatureData;
        $contract->company_signed_at = now();
        $contract->company_sign_ip = $ipAddress;
        $contract->signer_name = $signer->name;

        $this->verifyAndSealIfComplete($contract);
        $contract->save();

        return $contract;
    }

    /**
     * If both parties signed, seal with SHA256 cryptographic verification token.
     */
    protected function verifyAndSealIfComplete(EmploymentContract $contract): void
    {
        if ($contract->employee_signed_at && $contract->company_signed_at) {
            $payload = implode('|', [
                $contract->id,
                $contract->contract_number,
                $contract->employee_id,
                $contract->employee_signed_at->toIso8601String(),
                $contract->company_signed_at->toIso8601String(),
                $contract->monthly_wage,
            ]);

            $contract->signed_hash = hash('sha256', $payload);
            $contract->is_digitally_signed = true;
        }
    }

    /**
     * Generate standard statutory contract clauses in Indonesian.
     */
    public function generateStandardClauses(Employee $employee, string $type, Carbon $start, ?Carbon $end, float $wage): string
    {
        $durationText = $end ? "terhitung sejak tanggal {$start->translatedFormat('d F Y')} sampai dengan {$end->translatedFormat('d F Y')}" : "terhitung sejak tanggal {$start->translatedFormat('d F Y')} untuk jangka waktu tidak tertentu (PKWTT)";
        $typeTitle = $type === 'pkwtt' ? 'Perjanjian Kerja Waktu Tidak Tertentu (PKWTT)' : 'Perjanjian Kerja Waktu Tertentu (PKWT)';

        return <<<HTML
<h3>PERJANJIAN KERJA {$typeTitle}</h3>
<p>Pada hari ini, disepakati perjanjian kerja antara Perusahaan (Pihak Pertama) dan Pegawai (Pihak Kedua):</p>
<p><strong>Nama Pegawai:</strong> {$employee->full_name}<br><strong>NIK KTP:</strong> {$employee->nik_ktp}<br><strong>Jabatan:</strong> {$employee->position?->title}</p>
<h4>PASAL 1: TUGAS DAN PENEMPATAN</h4>
<p>Pihak Pertama mempekerjakan Pihak Kedua pada unit kerja {$employee->department?->name} dengan kewajiban mematuhi seluruh Standard Operating Procedure (SOP) dan Kode Etik Perusahaan.</p>
<h4>PASAL 2: JANGKA WAKTU PERJANJIAN</h4>
<p>Perjanjian ini berlaku {$durationText} sesuai dengan ketentuan PP No. 35 Tahun 2021.</p>
<h4>PASAL 3: UPAH DAN JAMINAN SOSIAL</h4>
<p>Pihak Kedua berhak menerima upah pokok sebesar Rp " . number_format($wage, 0, ',', '.') . " per bulan, serta diikutsertakan dalam program jaminan sosial BPJS Ketenagakerjaan dan BPJS Kesehatan.</p>
<h4>PASAL 4: KERAHASIAAN INFORMASI & INTEGRITAS DATA</h4>
<p>Pihak Kedua wajib menjaga kerahasiaan data perusahaan, kode sumber, rahasia dagang, serta data pribadi pengguna sesuai UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi dan UU ITE.</p>
HTML;
    }
}
