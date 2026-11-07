<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrak Kerja {{ $contract->contract_number }} - {{ $company->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            background: #fff;
            margin: 0;
            padding: 40px;
            line-height: 1.6;
            font-size: 14px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .company-addr {
            font-size: 12px;
            color: #4b5563;
        }
        .doc-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0 5px 0;
            text-decoration: underline;
        }
        .doc-no {
            text-align: center;
            font-size: 12px;
            margin-bottom: 20px;
        }
        .party-block {
            margin-bottom: 15px;
        }
        .party-table {
            width: 100%;
            margin-top: 5px;
            margin-left: 15px;
        }
        .party-table td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 13px;
        }
        .party-table td.label {
            width: 160px;
        }
        .article {
            margin-top: 15px;
        }
        .article-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .sign-box {
            border: 1px dashed #6b7280;
            height: 90px;
            margin: 10px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: monospace;
            font-size: 11px;
            background: #f9fafb;
        }
        .legal-footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #d1d5db;
            font-size: 10px;
            color: #6b7280;
            font-family: sans-serif;
        }
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #dc2626;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            font-family: sans-serif;
        }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn">🖨️ Cetak Dokumen</button>

    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="company-addr">{{ $company->address ?? 'Gedung Perkantoran Pusat' }} | NPWP: {{ $company->npwp ?? '00.000.000.0-000.000' }}</div>
    </div>

    <div class="doc-title">
        SURAT PERJANJIAN KERJA
        @if($contract->contract_type === 'PKWTT')
            WAKTU TIDAK TERTENTU (PKWTT)
        @else
            WAKTU TERTENTU (PKWT)
        @endif
    </div>
    <div class="doc-no">Nomor: {{ $contract->contract_number }}</div>

    <p>Pada hari ini, tanggal <strong>{{ \Carbon\Carbon::parse($contract->start_date)->translatedFormat('d F Y') }}</strong>, telah dibuat dan disepakati perjanjian kerja antara pihak-pihak di bawah ini:</p>

    <div class="party-block">
        <strong>1. PIHAK PERTAMA (PEMBERI KERJA):</strong>
        <table class="party-table">
            <tr><td class="label">Nama Perusahaan</td><td>: {{ $company->name }}</td></tr>
            <tr><td class="label">Alamat</td><td>: {{ $company->address ?? '-' }}</td></tr>
            <tr><td class="label">Diwakili Oleh</td><td>: {{ $contract->company_signer_name ?? 'Direksi / Authorized Officer' }}</td></tr>
            <tr><td class="label">Jabatan</td><td>: {{ $contract->company_signer_title ?? 'Authorized Signer' }}</td></tr>
        </table>
    </div>

    <div class="party-block">
        <strong>2. PIHAK KEDUA (PEKERJA):</strong>
        <table class="party-table">
            <tr><td class="label">Nama Lengkap</td><td>: {{ $contract->employee->name ?? '-' }}</td></tr>
            <tr><td class="label">Nomor Induk Karyawan</td><td>: {{ $contract->employee->employee_code ?? '-' }}</td></tr>
            <tr><td class="label">Jabatan / Posisi</td><td>: {{ $contract->job_title ?? $contract->employee->position ?? 'Staff' }}</td></tr>
            <tr><td class="label">Lokasi Kerja</td><td>: {{ $contract->work_location ?? 'Kantor Perusahaan' }}</td></tr>
        </table>
    </div>

    <p>Kedua belah pihak telah bersepakat untuk mengadakan ikatan kerja dengan ketentuan pasal-pasal berikut:</p>

    <div class="article">
        <div class="article-title">Pasal 1: Jangka Waktu Perjanjian</div>
        <p>
            Perjanjian ini berlaku efektif terhitung sejak tanggal <strong>{{ \Carbon\Carbon::parse($contract->start_date)->translatedFormat('d F Y') }}</strong>
            @if($contract->end_date)
                sampai dengan tanggal <strong>{{ \Carbon\Carbon::parse($contract->end_date)->translatedFormat('d F Y') }}</strong>.
            @else
                untuk waktu tidak tertentu (PKWTT) sesuai ketentuan Undang-Undang Ketenagakerjaan.
            @endif
        </p>
    </div>

    <div class="article">
        <div class="article-title">Pasal 2: Kompensasi dan Remunerasi</div>
        <p>
            PIHAK KEDUA berhak menerima remunerasi bulanan berupa:
            <br>• Gaji Pokok: <strong>Rp {{ number_format($contract->basic_salary, 0, ',', '.') }}</strong>
            <br>• Tunjangan Tetap: <strong>Rp {{ number_format($contract->allowances, 0, ',', '.') }}</strong>
            <br>Pembayaran dilakukan setiap akhir bulan kalender ke rekening payroll bank terdaftar.
        </p>
    </div>

    <div class="article">
        <div class="article-title">Pasal 3: Jaminan Sosial dan Perlindungan Tenaga Kerja</div>
        <p>
            PIHAK PERTAMA mendaftarkan PIHAK KEDUA dalam program BPJS Ketenagakerjaan (JKK, JKM, JHT, JP) dan BPJS Kesehatan sesuai ketentuan perundang-undangan Republik Indonesia.
        </p>
    </div>

    @if($contract->special_clauses)
    <div class="article">
        <div class="article-title">Pasal 4: Klausul Khusus</div>
        <p>{!! nl2br(e($contract->special_clauses)) !!}</p>
    </div>
    @endif

    <div class="article">
        <div class="article-title">Pasal {{ $contract->special_clauses ? '5' : '4' }}: Legalitas Tanda Tangan Elektronik</div>
        <p>
            Dokumen ini ditandatangani secara elektronik sesuai dengan ketentuan Undang-Undang Republik Indonesia Nomor 1 Tahun 2024 tentang Perubahan Kedua atas UU No. 11/2008 tentang Informasi dan Transaksi Elektronik (UU ITE) dan PP No. 71 Tahun 2019.
        </p>
    </div>

    <table class="signature-table">
        <tr>
            <td>
                <strong>PIHAK PERTAMA</strong><br>{{ $company->name }}
                <div class="sign-box">
                    @if($contract->company_signed_at)
                        <strong>[SIGNED DIGITALLY]</strong>
                        <span>{{ $contract->company_signer_name }}</span>
                        <span>{{ $contract->company_signed_at }}</span>
                        <span>IP: {{ $contract->company_signer_ip }}</span>
                    @else
                        <span>[Belum Ditandatangani]</span>
                    @endif
                </div>
                <strong>{{ $contract->company_signer_name ?? 'Direktur' }}</strong>
            </td>
            <td>
                <strong>PIHAK KEDUA</strong><br>PEKERJA
                <div class="sign-box">
                    @if($contract->employee_signed_at)
                        <strong>[SIGNED DIGITALLY]</strong>
                        <span>{{ $contract->employee->name }}</span>
                        <span>{{ $contract->employee_signed_at }}</span>
                        <span>IP: {{ $contract->employee_signer_ip }}</span>
                    @else
                        <span>[Belum Ditandatangani]</span>
                    @endif
                </div>
                <strong>{{ $contract->employee->name ?? 'Karyawan' }}</strong>
            </td>
        </tr>
    </table>

    <div class="legal-footer">
        <div><strong>Cryptographic Integrity Hash (SHA256):</strong> {{ $contract->document_integrity_hash ?? 'N/A' }}</div>
        <div>Dokumen sah diterbitkan oleh sistem RiHRIS SaaS Cloud HCM. Dicetak pada {{ now()->format('d/m/Y H:i:s T') }}.</div>
    </div>

</body>
</html>
