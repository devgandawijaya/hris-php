<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Risalah Mediasi Hubungan Industrial - {{ $dispute->case_number }}</title>
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
        .signatures { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures td { width: 33.3%; text-align: center; vertical-align: top; font-size: 10pt; }
        .sig-space { height: 65px; }
        .badge { display: inline-block; padding: 3px 8px; border: 1px solid #000; font-weight: bold; font-size: 9.5pt; text-transform: uppercase; }
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
        <h1>{{ $dispute->company->legal_name ?? $dispute->company->name }}</h1>
        <h2>Risalah Perkara Perselisihan Hubungan Industrial</h2>
        <p>{{ $dispute->company->address ?? 'Kantor Pusat' }}, {{ $dispute->company->city }}, {{ $dispute->company->province }} | NPWP: {{ $dispute->company->npwp ?? '-' }}</p>
    </div>

    <div class="title-block">
        <h3>BERITA ACARA KEMAJUAN PENYELESAIAN PERSELISIHAN HUBUNGAN INDUSTRIAL</h3>
        <p>Nomor Registrasi Kasus: {{ $dispute->case_number }}</p>
    </div>

    <p style="text-align: justify; text-indent: 30px;">
        Berdasarkan ketentuan <strong>Undang-Undang Republik Indonesia Nomor 2 Tahun 2004 tentang Penyelesaian Perselisihan Hubungan Industrial (PPHI)</strong>, dengan ini dicatat rincian jalannya proses mediasi tripartit:
    </p>

    <table class="info-table">
        <tr>
            <td style="width: 220px;">Jenis Perselisihan</td>
            <td style="width: 15px;">:</td>
            <td><strong>{{ strtoupper(str_replace('_', ' ', $dispute->dispute_type)) }}</strong></td>
        </tr>
        <tr>
            <td>Pihak Pengusaha (Pemberi Kerja)</td>
            <td>:</td>
            <td>{{ $dispute->company->legal_name ?? $dispute->company->name }}</td>
        </tr>
        <tr>
            <td>Pihak Pekerja / Buruh</td>
            <td>:</td>
            <td><strong>{{ $dispute->employee ? $dispute->employee->full_name : $dispute->employee_name_external }}</strong></td>
        </tr>
        <tr>
            <td>Mediator Hubungan Industrial</td>
            <td>:</td>
            <td>{{ $dispute->disnaker_mediator_name }}</td>
        </tr>
        <tr>
            <td>Instansi Ketenagakerjaan</td>
            <td>:</td>
            <td>{{ $dispute->disnaker_office }}</td>
        </tr>
        <tr>
            <td>Tanggal Sidang Mediasi</td>
            <td>:</td>
            <td>{{ $dispute->mediation_date->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Status Penyelesaian Terakhir</td>
            <td>:</td>
            <td>
                <span class="badge">
                    {{ strtoupper(str_replace('_', ' ', $dispute->settlement_status)) }}
                </span>
            </td>
        </tr>
        @if($dispute->joint_agreement_number)
            <tr>
                <td>Nomor Akta Pendaftaran PB (PHI)</td>
                <td>:</td>
                <td style="font-family: monospace; font-weight: bold;">{{ $dispute->joint_agreement_number }}</td>
            </tr>
        @endif
    </table>

    <div style="margin-top: 15px;">
        <strong>Duduk Perkara & Kronologi:</strong>
        <p style="text-align: justify; margin: 4px 0 15px 0;">{{ $dispute->dispute_chronology }}</p>
    </div>

    @if($dispute->mediator_written_recommendation)
        <div style="margin-top: 15px; background: #fafafa; border: 1px solid #ccc; padding: 10px;">
            <strong>Anjuran Tertulis Mediator Hubungan Industrial:</strong>
            <p style="text-align: justify; margin: 4px 0 0 0;">{{ $dispute->mediator_written_recommendation }}</p>
        </div>
    @endif

    <table class="signatures">
        <tr>
            <td>
                <div>Pihak Pengusaha,</div>
                <div class="sig-space"></div>
                <div><strong>({{ auth()->user()->name ?? 'Kuasa Pengusaha' }})</strong></div>
            </td>
            <td>
                <div>Pihak Pekerja / Serikat,</div>
                <div class="sig-space"></div>
                <div><strong>({{ $dispute->employee ? $dispute->employee->full_name : $dispute->employee_name_external }})</strong></div>
            </td>
            <td>
                <div>Mediator Hubungan Industrial,</div>
                <div class="sig-space"></div>
                <div><strong>({{ $dispute->disnaker_mediator_name }})</strong></div>
            </td>
        </tr>
    </table>

</body>
</html>
