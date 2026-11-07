<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK Pengangkatan Karyawan Tetap - {{ $evaluation->employee->name ?? 'Karyawan' }}</title>
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
        .sk-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0 5px 0;
        }
        .sk-subtitle {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .sk-no {
            text-align: center;
            font-size: 12px;
            margin-bottom: 25px;
        }
        .preamble-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .preamble-table td {
            vertical-align: top;
            padding: 4px 0;
        }
        .preamble-table td.header-col {
            width: 120px;
            font-weight: bold;
        }
        .decide-title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin: 20px 0 15px 0;
            letter-spacing: 2px;
        }
        .dictum-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .dictum-table td {
            vertical-align: top;
            padding: 5px 0;
        }
        .dictum-table td.dictum-col {
            width: 120px;
            font-weight: bold;
        }
        .emp-table {
            width: 100%;
            margin: 5px 0 10px 15px;
        }
        .emp-table td {
            padding: 2px 0;
            font-size: 13.5px;
        }
        .emp-table td.label {
            width: 150px;
        }
        .signature-block {
            margin-top: 50px;
            float: right;
            width: 250px;
            text-align: center;
        }
        .sign-space {
            height: 80px;
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

    <button onclick="window.print()" class="print-btn">🖨️ Cetak Surat Keputusan</button>

    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="company-addr">{{ $company->address ?? 'Gedung Kantor Pusat' }} | NPWP: {{ $company->npwp ?? '-' }}</div>
    </div>

    <div class="sk-title">SURAT KEPUTUSAN DIREKSI</div>
    <div class="sk-subtitle">TENTANG PENGANGKATAN KARYAWAN TETAP (PKWTT)</div>
    <div class="sk-no">Nomor: {{ $evaluation->sk_number ?? 'SK/DIR/PKWTT/'.date('Ym').'/'.str_pad($evaluation->id, 4, '0', STR_PAD_LEFT) }}</div>

    <table class="preamble-table">
        <tr>
            <td class="header-col">MENIMBANG</td>
            <td style="width: 20px;">:</td>
            <td>
                a. Bahwa sehubungan dengan telah diselesaikannya masa percobaan kerja (probation) dengan hasil evaluasi yang memenuhi syarat standar kompetensi dan budaya perusahaan;<br>
                b. Bahwa Saudara/i yang bersangkutan dipandang cakap dan memenuhi kualifikasi untuk diangkat sebagai Karyawan Tetap (PKWTT).
            </td>
        </tr>
        <tr>
            <td class="header-col">MENGINGAT</td>
            <td style="width: 20px;">:</td>
            <td>
                1. Pasal 60 Undang-Undang Republik Indonesia Nomor 13 Tahun 2003 tentang Ketenagakerjaan;<br>
                2. Peraturan Pemerintah Nomor 35 Tahun 2021 tentang Perjanjian Kerja Waktu Tertentu, Alih Daya, Waktu Kerja dan Waktu Istirahat, dan Pemutusan Hubungan Kerja;<br>
                3. Peraturan Perusahaan (PP) dan Kebijakan Manajemen {{ $company->name }}.
            </td>
        </tr>
    </table>

    <div class="decide-title">MEMUTUSKAN:</div>

    <table class="dictum-table">
        <tr>
            <td class="dictum-col">MENETAPKAN</td>
            <td style="width: 20px;">:</td>
            <td><strong>SURAT KEPUTUSAN DIREKSI TENTANG PENGANGKATAN SEBAGAI KARYAWAN TETAP (PKWTT).</strong></td>
        </tr>
        <tr>
            <td class="dictum-col">PERTAMA</td>
            <td style="width: 20px;">:</td>
            <td>
                Mengangkat terhitung sejak tanggal penetapan keputusan ini:
                <table class="emp-table">
                    <tr><td class="label">Nama Pegawai</td><td>: <strong>{{ $evaluation->employee->name ?? '-' }}</strong></td></tr>
                    <tr><td class="label">Nomor Induk Karyawan</td><td>: {{ $evaluation->employee->employee_code ?? '-' }}</td></tr>
                    <tr><td class="label">Jabatan Baru</td><td>: <strong>{{ $evaluation->new_job_title ?? $evaluation->employee->position ?? 'Karyawan' }}</strong></td></tr>
                    <tr><td class="label">Nilai Evaluasi Probation</td><td>: {{ $evaluation->final_score }} / 100.00 (Lulus Standar Kompetensi)</td></tr>
                </table>
                Sebagai <strong>Karyawan Tetap (PKWTT)</strong> pada {{ $company->name }}.
            </td>
        </tr>
        <tr>
            <td class="dictum-col">KEDUA</td>
            <td style="width: 20px;">:</td>
            <td>
                Memberikan hak remunerasi, perlindungan program BPJS Ketenagakerjaan dan BPJS Kesehatan, serta hak-hak normatif ketenagakerjaan lainnya sesuai dengan skala upah dan ketentuan yang berlaku di Perusahaan.
            </td>
        </tr>
        <tr>
            <td class="dictum-col">KETIGA</td>
            <td style="width: 20px;">:</td>
            <td>
                Surat Keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan apabila di kemudian hari terdapat kekeliruan dalam keputusan ini, akan diadakan perbaikan sebagaimana mestinya.
            </td>
        </tr>
    </table>

    <div class="signature-block">
        <div>Ditetapkan di : {{ $company->city ?? 'Jakarta' }}</div>
        <div>Pada tanggal : {{ \Carbon\Carbon::parse($evaluation->probation_end_date)->translatedFormat('d F Y') }}</div>
        <div style="margin-top: 10px; font-weight: bold;">{{ $company->name }}</div>
        <div class="sign-space"></div>
        <div style="font-weight: bold; text-decoration: underline;">{{ $evaluation->evaluator_name ?? 'Direktur Human Resources' }}</div>
        <div style="font-size: 12px; color: #4b5563;">Direksi / Authorized Representative</div>
    </div>

</body>
</html>
