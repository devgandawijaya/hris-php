<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir 1721-A1 - {{ $record->nomor_bupot }} - {{ $record->penerima_nama }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            margin: 15px 25px;
            font-size: 11px;
            line-height: 1.3;
        }
        .form-border {
            border: 2px solid #000;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
        }
        .header-title h1 {
            font-size: 13px;
            margin: 0;
            text-transform: uppercase;
        }
        .header-title h2 {
            font-size: 11px;
            margin: 2px 0;
            font-weight: normal;
        }
        .form-code {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }
        .section-header {
            background: #e5e7eb;
            font-weight: bold;
            padding: 3px 6px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            margin: 6px 0 4px 0;
            font-size: 11px;
        }
        table.grid-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.grid-table td {
            padding: 2px 5px;
            vertical-align: top;
        }
        table.calc-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.calc-table th, table.calc-table td {
            border: 1px solid #000;
            padding: 2px 5px;
        }
        table.calc-table th {
            background: #f3f4f6;
            text-align: center;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .sign-area {
            width: 100%;
            margin-top: 10px;
        }
        .sign-box {
            width: 250px;
            float: right;
            text-align: center;
        }
        .sign-space {
            height: 50px;
        }
        .print-btn-bar {
            background: #f3f4f6;
            padding: 8px;
            text-align: right;
            margin-bottom: 15px;
            font-family: Arial, sans-serif;
        }
        @media print {
            .print-btn-bar { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

<div class="print-btn-bar">
    <button onclick="window.print()" style="padding: 6px 14px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
        🖨️ Cetak Formulir 1721-A1 Resmi
    </button>
</div>

<div class="form-border">
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 20%; font-size: 10px;">
                <strong>KEMENTERIAN KEUANGAN RI</strong><br>
                DIREKTORAT JENDERAL PAJAK
            </td>
            <td class="header-title" style="width: 60%;">
                <h1>BUKTI PEMOTONGAN PAJAK PENGHASILAN PASAL 21 BAGI PEGAWAI TETAP ATAU PENERIMA PENSIUN ATAU TUNJANGAN HARI TUA/JAMINAN HARI TUA BERKALA</h1>
                <h2>(PER-2/PJ/2024)</h2>
            </td>
            <td class="form-code" style="width: 20%;">
                FORMULIR 1721 - A1<br>
                <span style="font-size: 11px; font-weight: normal;">Tahun: <strong>{{ $record->tax_year }}</strong></span><br>
                <span style="font-size: 9px; font-weight: normal;">Masa: <strong>{{ sprintf('%02d', $record->masa_perolehan_awal) }} - {{ sprintf('%02d', $record->masa_perolehan_akhir) }}</strong></span>
            </td>
        </tr>
    </table>

    <div style="font-weight: bold; margin-bottom: 4px;">
        Nomor Bukti Potong: <span class="font-mono">{{ $record->nomor_bupot }}</span>
    </div>

    <!-- Bagian A: Pemotong Pajak -->
    <div class="section-header">A. IDENTITAS PEMOTONG PAJAK</div>
    <table class="grid-table">
        <tr>
            <td style="width: 180px;">1. NPWP Pemotong</td>
            <td style="width: 10px;">:</td>
            <td class="font-mono"><strong>{{ $record->pemotong_npwp }}</strong></td>
        </tr>
        <tr>
            <td>2. Nama Pemotong</td>
            <td>:</td>
            <td><strong>{{ $record->pemotong_nama }}</strong></td>
        </tr>
    </table>

    <!-- Bagian B: Penerima Penghasilan -->
    <div class="section-header">B. IDENTITAS PENERIMA PENGHASILAN YANG DIPOTONG</div>
    <table class="grid-table">
        <tr>
            <td style="width: 180px;">1. NPWP Penerima</td>
            <td style="width: 10px;">:</td>
            <td class="font-mono"><strong>{{ $record->penerima_npwp ?: '-' }}</strong></td>
            <td style="width: 140px;">4. Status PTKP</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $record->status_ptkp }}</strong> (Tanggungan: {{ $record->jumlah_tanggungan }})</td>
        </tr>
        <tr>
            <td>2. NIK Penerima (KTP)</td>
            <td>:</td>
            <td class="font-mono"><strong>{{ $record->penerima_nik }}</strong></td>
            <td>5. Nama Jabatan</td>
            <td>:</td>
            <td>{{ $record->employee->position ? $record->employee->position->title : 'Pegawai Tetap' }}</td>
        </tr>
        <tr>
            <td>3. Nama Lengkap</td>
            <td>:</td>
            <td><strong>{{ $record->penerima_nama }}</strong></td>
            <td>6. Jenis Kelamin</td>
            <td>:</td>
            <td>{{ $record->employee->gender === 'female' ? 'Perempuan' : 'Laki-Laki' }}</td>
        </tr>
    </table>

    <!-- Bagian C: Rincian Penghasilan dan Perhitungan PPh Pasal 21 -->
    <div class="section-header">C. RINCIAN PENGHASILAN DAN PENGHITUNGAN PPh PASAL 21</div>
    <table class="calc-table">
        <thead>
            <tr>
                <th style="width: 35px;">NO.</th>
                <th>URAIAN RINCIAN KOMPONEN PENGHASILAN DAN PENGURANG</th>
                <th style="width: 160px;">JUMLAH (RUPIAH)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background: #fafafa; font-weight: bold;">
                <td colspan="2">KODE OBJEK PAJAK: 21-100-01 (Pegawai Tetap) — PENGHASILAN BRUTO:</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">1.</td>
                <td>Gaji Pokok / Pensiun atau THT / JHT</td>
                <td class="text-right font-mono">{{ number_format($record->gaji_pensiun_tht, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2.</td>
                <td>Tunjangan PPh</td>
                <td class="text-right font-mono">{{ number_format($record->tunjangan_pph, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3.</td>
                <td>Tunjangan Lainnya, Uang Lembur, dan sebagainya</td>
                <td class="text-right font-mono">{{ number_format($record->tunjangan_lain_lembur, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">4.</td>
                <td>Honorarium dan Imbalan Lain Sejenisnya</td>
                <td class="text-right font-mono">{{ number_format($record->honorarium_imbalan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">5.</td>
                <td>Premi Asuransi yang dibayar Pemberi Kerja (JKK & JKM BPJS)</td>
                <td class="text-right font-mono">{{ number_format($record->premi_asuransi_jkk_jkm, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">6.</td>
                <td>Penerimaan dalam bentuk Natura dan/atau Kenikmatan Lainnya</td>
                <td class="text-right font-mono">{{ number_format($record->natura_kenikmatan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">7.</td>
                <td>Tantiem, Bonus, Gratifikasi, Jasa Produksi, dan THR</td>
                <td class="text-right font-mono">{{ number_format($record->tantiem_bonus_thr, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold; background: #f9fafb;">
                <td class="text-center">8.</td>
                <td>JUMLAH PENGHASILAN BRUTO (1 s.d. 7)</td>
                <td class="text-right font-mono">Rp {{ number_format($record->penghasilan_bruto, 0, ',', '.') }}</td>
            </tr>

            <tr style="background: #fafafa; font-weight: bold;">
                <td colspan="2">PENGURANGAN:</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">9.</td>
                <td>Biaya Jabatan / Biaya Pensiun (5% x Bruto, maks. Rp 6.000.000 / thn)</td>
                <td class="text-right font-mono">{{ number_format($record->biaya_jabatan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">10.</td>
                <td>Iuran Pensiun atau Iuran THT / JHT yang dibayar sendiri (BPJS TK Pekerja)</td>
                <td class="text-right font-mono">{{ number_format($record->iuran_pensiun_jht, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold; background: #f9fafb;">
                <td class="text-center">11.</td>
                <td>JUMLAH PENGURANGAN (9 + 10)</td>
                <td class="text-right font-mono">Rp {{ number_format($record->total_pengurang, 0, ',', '.') }}</td>
            </tr>

            <tr style="background: #fafafa; font-weight: bold;">
                <td colspan="2">PENGHITUNGAN PPh PASAL 21:</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center">12.</td>
                <td>Jumlah Penghasilan Neto (8 - 11)</td>
                <td class="text-right font-mono">{{ number_format($record->penghasilan_neto, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">13.</td>
                <td>Penghasilan Neto Masa Sebelumnya (jika pindah kerja)</td>
                <td class="text-right font-mono">{{ number_format($record->penghasilan_neto_masa_sebelumnya, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="text-center">14.</td>
                <td>Jumlah Penghasilan Neto untuk Penghitungan PPh Pasal 21 (Disetahunkan)</td>
                <td class="text-right font-mono">{{ number_format($record->jumlah_penghasilan_neto_disetahunkan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">15.</td>
                <td>Penghasilan Tidak Kena Pajak (PTKP: {{ $record->status_ptkp }})</td>
                <td class="text-right font-mono">{{ number_format($record->ptkp, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="text-center">16.</td>
                <td>Penghasilan Kena Pajak (PKP) Setahun / Disetahunkan (14 - 15)</td>
                <td class="text-right font-mono">{{ number_format($record->pkp, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="text-center">17.</td>
                <td>PPh Pasal 21 atas Penghasilan Kena Pajak Setahun / Disetahunkan</td>
                <td class="text-right font-mono">Rp {{ number_format($record->pph21_terutang_setahun, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">18.</td>
                <td>PPh Pasal 21 yang Telah Dipotong Masa Pajak Sebelumnya</td>
                <td class="text-right font-mono">0</td>
            </tr>
            <tr style="font-weight: bold; background: #eff6ff;">
                <td class="text-center">19.</td>
                <td>PPh PASAL 21 TERUTANG (17 - 18)</td>
                <td class="text-right font-mono">Rp {{ number_format($record->pph21_terutang_setahun, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">20.</td>
                <td>PPh Pasal 21 yang Telah Dipotong dan Dilunasi (PPh 21 TER Masa 1-12)</td>
                <td class="text-right font-mono">Rp {{ number_format($record->pph21_telah_dipotong, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold;">
                <td class="text-center">21.</td>
                <td>PPh PASAL 21 KURANG / (LEBIH) DIPOTONG (19 - 20)</td>
                <td class="text-right font-mono">Rp {{ number_format($record->pph21_kurang_lebih_bayar, 0, ',', '.') }} (NIHIL)</td>
            </tr>
        </tbody>
    </table>

    <!-- Bagian D: Tanda Tangan Pemotong -->
    <div class="sign-area">
        <div class="sign-box">
            <div>Dibuat di: {{ $record->company->city ?? 'Jakarta' }}</div>
            <div>Tanggal: {{ $record->published_at ? $record->published_at->translatedFormat('d F Y') : date('d F Y') }}</div>
            <div style="margin-top: 4px; font-weight: bold;">Pemotong Pajak / Kuasa,</div>
            <div class="sign-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">{{ $record->pemotong_nama }}</div>
            <div style="font-size: 10px;">NPWP: {{ $record->pemotong_npwp }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>

</body>
</html>
