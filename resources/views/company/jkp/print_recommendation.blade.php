<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Bukti PHK & Hak JKP - {{ $certification->certificate_number }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.5; color: #111; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 13pt; margin: 0; text-transform: uppercase; font-weight: bold; }
        .header h2 { font-size: 15pt; margin: 4px 0; text-transform: uppercase; letter-spacing: 1px; }
        .header p { font-size: 9.5pt; margin: 0; color: #444; }
        .title-block { text-align: center; margin: 20px 0 15px 0; }
        .title-block h3 { font-size: 12pt; margin: 0; text-decoration: underline; text-transform: uppercase; }
        .title-block p { font-size: 10pt; margin: 2px 0; font-family: monospace; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 4px; vertical-align: top; font-size: 10.5pt; }
        .benefit-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .benefit-table th, .benefit-table td { border: 1px solid #333; padding: 6px 10px; font-size: 10pt; }
        .benefit-table th { background-color: #f2f2f2; text-align: left; }
        .signatures { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; font-size: 10.5pt; }
        .sig-space { height: 65px; }
        @media print {
            body { margin: 15mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #DC2626; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Cetak Dokumen
        </button>
    </div>

    <div class="header">
        <h1>{{ $certification->company->legal_name ?? $certification->company->name }}</h1>
        <h2>Surat Rekomendasi Hak Klaim JKP</h2>
        <p>{{ $certification->company->address ?? 'Kantor Pusat' }}, {{ $certification->company->city }}, {{ $certification->company->province }} | NPWP: {{ $certification->company->npwp ?? '-' }}</p>
    </div>

    <div class="title-block">
        <h3>SURAT KETERANGAN BUKTI PHK & SERTIFIKASI HAK KLAIM JKP</h3>
        <p>Nomor: {{ $certification->certificate_number }}</p>
    </div>

    <p style="text-align: justify; text-indent: 30px;">
        Pimpinan {{ $certification->company->legal_name ?? $certification->company->name }}, dengan ini menerangkan secara sah bahwa pekerja yang tercantum di bawah ini:
    </p>

    <table class="info-table">
        <tr>
            <td style="width: 190px;">Nama Lengkap</td>
            <td style="width: 15px;">:</td>
            <td><strong>{{ $certification->employee->full_name }}</strong></td>
        </tr>
        <tr>
            <td>Nomor Induk Kependudukan (NIK)</td>
            <td>:</td>
            <td>{{ $certification->employee->nik_ktp }}</td>
        </tr>
        <tr>
            <td>Nomor KPJ BPJS Ketenagakerjaan</td>
            <td>:</td>
            <td>{{ $certification->bpjs_tk_card_number }}</td>
        </tr>
        <tr>
            <td>Jabatan Terakhir</td>
            <td>:</td>
            <td>{{ $certification->employee->position->title ?? 'Staff' }} (Departemen {{ $certification->employee->department->name ?? '-' }})</td>
        </tr>
        <tr>
            <td>Tanggal Efektif PHK</td>
            <td>:</td>
            <td><strong>{{ $certification->termination_date->translatedFormat('d F Y') }}</strong></td>
        </tr>
        <tr>
            <td>Alasan Yuridis PHK</td>
            <td>:</td>
            <td>{{ strtoupper(str_replace('_', ' ', $certification->termination_reason_code)) }}</td>
        </tr>
        <tr>
            <td>Kode Integrasi SiapKerja Kemnaker</td>
            <td>:</td>
            <td style="font-family: monospace; font-weight: bold;">{{ $certification->siapkerja_tracking_code }}</td>
        </tr>
    </table>

    <p style="text-align: justify;">
        Benar telah terdaftar sebagai peserta aktif program Jaminan Kehilangan Pekerjaan (JKP) dan memenuhi kualifikasi masa iur sesuai ketentuan <strong>Pasal 19 dan Pasal 21 Peraturan Pemerintah No. 37 Tahun 2021</strong>, dengan rincian taksiran hak manfaat uang tunai sebagai berikut:
    </p>

    <table class="benefit-table">
        <thead>
            <tr>
                <th>Komponen Manfaat JKP</th>
                <th>Dasar Kalkulasi Statutori</th>
                <th>Nominal Manfaat</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Upah Pokok Terakhir</td>
                <td>Upah dasar pelaporan iuran</td>
                <td>Rp {{ number_format($certification->employee->basic_salary, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Batas Atas Upah Patokan JKP</td>
                <td>Pasal 21 ayat (3) PP 37/2021 (Maks. Rp 5.000.000)</td>
                <td>Rp {{ number_format($certification->reference_salary_cap, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Manfaat Uang Tunai Bulan 1 s/d 3</td>
                <td>45% x Upah Patokan (Per Bulan)</td>
                <td>Rp {{ number_format($certification->month_1_to_3_benefit_monthly, 0, ',', '.') }} / bln</td>
            </tr>
            <tr>
                <td>Manfaat Uang Tunai Bulan 4 s/d 6</td>
                <td>25% x Upah Patokan (Per Bulan)</td>
                <td>Rp {{ number_format($certification->month_4_to_6_benefit_monthly, 0, ',', '.') }} / bln</td>
            </tr>
            <tr style="font-weight: bold; background: #fafafa;">
                <td>Total Potensi Manfaat Uang Tunai</td>
                <td>(3 x 45%) + (3 x 25%) = 210% Upah Patokan</td>
                <td style="color: #047857;">Rp {{ number_format($certification->total_potential_cash_benefit, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="text-align: justify;">
        Demikian Surat Rekomendasi ini diterbitkan kepada pekerja yang bersangkutan untuk dipergunakan sebagai kelengkapan dokumen pengajuan klaim manfaat JKP (uang tunai, informasi lowongan kerja, dan pelatihan vokasi) pada Dinas Tenaga Kerja dan portal resmi Kementerian Ketenagakerjaan Republik Indonesia.
    </p>

    <table class="signatures">
        <tr>
            <td>
                <div>Pekerja yang Bersangkutan,</div>
                <div class="sig-space"></div>
                <div><strong>({{ $certification->employee->full_name }})</strong></div>
            </td>
            <td>
                <div>Diterbitkan oleh Pemberi Kerja,</div>
                <div style="font-weight: bold;">{{ $certification->company->legal_name ?? $certification->company->name }}</div>
                <div class="sig-space"></div>
                <div><strong>({{ auth()->user()->name ?? 'HR & Legal Manager' }})</strong></div>
            </td>
        </tr>
    </table>

</body>
</html>
