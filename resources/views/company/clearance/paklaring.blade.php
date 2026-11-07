<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pengalaman Kerja - {{ $letter->letter_number }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            margin: 40px 50px;
            font-size: 14px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #111827;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .company-info {
            font-size: 12px;
            color: #4b5563;
            margin-top: 4px;
        }
        .doc-title {
            text-align: center;
            margin: 30px 0 20px 0;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 16px;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title p {
            margin: 5px 0 0 0;
            font-size: 13px;
        }
        .content {
            text-align: justify;
            margin-bottom: 20px;
        }
        table.employee-table {
            width: 100%;
            margin: 15px 0 20px 20px;
            border-collapse: collapse;
        }
        table.employee-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        table.employee-table td.label {
            width: 200px;
        }
        .signature-section {
            width: 100%;
            margin-top: 50px;
        }
        .sign-box {
            width: 300px;
            float: right;
            text-align: center;
        }
        .sign-space {
            height: 80px;
        }
        .legal-footer {
            margin-top: 60px;
            font-size: 11px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            font-style: italic;
        }
        .print-btn-bar {
            background: #f3f4f6;
            padding: 10px;
            text-align: right;
            margin-bottom: 25px;
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
    <button onclick="window.print()" style="padding: 8px 18px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
        🖨️ Cetak Surat Keterangan / Paklaring
    </button>
</div>

<div class="header">
    <div class="company-name">{{ $letter->company->legal_name ?? $letter->company->name }}</div>
    <div class="company-info">{{ $letter->company->address ?? 'Gedung Wisma Niaga Lt. 12, Jl. Jend. Sudirman Kav. 25, Jakarta Selatan' }}</div>
    <div class="company-info">NPWP: {{ $letter->company->npwp ?? '01.234.567.8-012.000' }} | Email: {{ $letter->company->email ?? 'hrd@rihris.co.id' }}</div>
</div>

<div class="doc-title">
    <h2>SURAT PENGALAMAN KERJA</h2>
    <p>Nomor: {{ $letter->letter_number }}</p>
</div>

<div class="content">
    <p>Yang bertanda tangan di bawah ini:</p>
    <table class="employee-table">
        <tr>
            <td class="label">Nama</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $letter->signer_name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>:</td>
            <td>{{ $letter->signer_position }}</td>
        </tr>
        <tr>
            <td class="label">Perusahaan</td>
            <td>:</td>
            <td>{{ $letter->company->legal_name ?? $letter->company->name }}</td>
        </tr>
    </table>

    <p>Dengan ini menerangkan dengan sesungguhnya bahwa:</p>
    <table class="employee-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $letter->employee->full_name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nomor Induk Pegawai</td>
            <td>:</td>
            <td>{{ $letter->employee->employee_number }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan Terakhir</td>
            <td>:</td>
            <td>{{ $letter->last_position }}</td>
        </tr>
        <tr>
            <td class="label">Departemen</td>
            <td>:</td>
            <td>{{ $letter->last_department }}</td>
        </tr>
        <tr>
            <td class="label">Masa Kerja</td>
            <td>:</td>
            <td>{{ $letter->start_date->translatedFormat('d F Y') }} s.d. {{ $letter->end_date->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <p>
        Adalah benar pernah bekerja di <strong>{{ $letter->company->legal_name ?? $letter->company->name }}</strong> dalam kurun waktu tersebut di atas.
        Selama masa pengabdiannya, yang bersangkutan telah menunjukkan integritas, loyalitas, dedikasi yang tinggi, serta prestasi kerja dengan predikat <strong>{{ strtoupper(str_replace('_', ' ', $letter->conduct_appraisal)) }}</strong>.
    </p>

    <p>
        {{ $letter->achievement_summary ?? 'Kami menyampaikan terima kasih dan penghargaan yang setinggi-tingginya atas segala jerih payah, kontribusi, dan kerjasama yang telah diberikan selama masa baktinya pada perusahaan.' }}
    </p>

    <p>
        Demikian Surat Keterangan Bekerja / Pengalaman Kerja ini dibuat dengan sebenarnya sesuai ketentuan Pasal 1603p Kitab Undang-Undang Hukum Perdata (KUHPerdata) untuk dapat dipergunakan sebagaimana mestinya.
    </p>
</div>

<div class="signature-section">
    <div class="sign-box">
        <div>Ditetapkan di: {{ $letter->company->city ?? 'Jakarta' }}</div>
        <div>Pada tanggal: {{ $letter->issued_date->translatedFormat('d F Y') }}</div>
        <div style="margin-top: 6px; font-weight: bold;">{{ $letter->company->legal_name ?? $letter->company->name }}</div>
        <div class="sign-space"></div>
        <div style="font-weight: bold; text-decoration: underline;">{{ $letter->signer_name }}</div>
        <div>{{ $letter->signer_position }}</div>
    </div>
    <div style="clear: both;"></div>
</div>

<div class="legal-footer">
    * Dokumen ini sah dan diterbitkan resmi secara digital melalui Sistem Informasi Sumber Daya Manusia RiHRIS SaaS Indonesia.
</div>

</body>
</html>
