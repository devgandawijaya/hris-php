<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kepatuhan Kuota Disabilitas - {{ $audit->audit_code }}</title>
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
        .data-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 6px 10px; font-size: 10pt; }
        .data-table th { background-color: #f2f2f2; text-align: left; }
        .signatures { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; font-size: 10.5pt; }
        .sig-space { height: 65px; }
        .status-badge { display: inline-block; padding: 3px 8px; border: 1px solid #000; font-weight: bold; font-size: 10pt; text-transform: uppercase; }
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
        <h1>{{ $audit->company->legal_name ?? $audit->company->name }}</h1>
        <h2>Laporan Hasil Audit Ketenagakerjaan Inklusif</h2>
        <p>{{ $audit->company->address ?? 'Alamat Kantor' }}, {{ $audit->company->city }}, {{ $audit->company->province }} | NPWP: {{ $audit->company->npwp ?? '-' }}</p>
    </div>

    <div class="title-block">
        <h3>SURAT KETERANGAN HASIL AUDIT KUOTA PENYANDANG DISABILITAS</h3>
        <p>Nomor Registrasi: {{ $audit->audit_code }}</p>
    </div>

    <p style="text-align: justify; text-indent: 30px;">
        Berdasarkan amanat <strong>Undang-Undang Republik Indonesia Nomor 8 Tahun 2016 tentang Penyandang Disabilitas</strong> (Pasal 53 ayat 1 dan ayat 2) jo. <strong>Peraturan Pemerintah Nomor 60 Tahun 2020 tentang Unit Layanan Disabilitas Ketenagakerjaan</strong>, dengan ini diterbitkan hasil audit berkala pemenuhan kuota ketenagakerjaan terhadap badan usaha:
    </p>

    <table class="info-table">
        <tr>
            <td style="width: 200px;">Nama Perusahaan / Entitas</td>
            <td style="width: 15px;">:</td>
            <td><strong>{{ $audit->company->legal_name ?? $audit->company->name }}</strong></td>
        </tr>
        <tr>
            <td>Klasifikasi Badan Usaha</td>
            <td>:</td>
            <td>{{ $audit->entity_type === 'state_owned_bumn' ? 'BUMN / BUMD / Pemerintah' : 'Badan Usaha Swasta' }}</td>
        </tr>
        <tr>
            <td>Tanggal Pelaksanaan Audit</td>
            <td>:</td>
            <td>{{ $audit->audit_date->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Tahun Audit / Evaluasi</td>
            <td>:</td>
            <td>Tahun {{ $audit->audit_year }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>Indikator Kepatuhan Regulasi</th>
                <th>Ketetapan / Hasil Audit</th>
                <th>Keterangan Landasan Hukum</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Tenaga Kerja Aktif</td>
                <td><strong>{{ number_format($audit->total_active_workforce) }} Orang</strong></td>
                <td>Basis Populasi Audit</td>
            </tr>
            <tr>
                <td>Pekerja Penyandang Disabilitas</td>
                <td><strong>{{ $audit->disability_workforce_count }} Orang</strong></td>
                <td>Terverifikasi Medis / Profil</td>
            </tr>
            <tr>
                <td>Kewajiban Kuota Sah</td>
                <td><strong>{{ $audit->statutory_quota_percentage }}%</strong></td>
                <td>Pasal 53 UU No. 8/2016</td>
            </tr>
            <tr>
                <td>Rasio Aktual Ketenagakerjaan</td>
                <td><strong>{{ $audit->actual_quota_percentage }}%</strong></td>
                <td>Persentase Pemenuhan</td>
            </tr>
            <tr>
                <td>Jumlah Minimal yang Wajib Dipenuhi</td>
                <td><strong>{{ $audit->statutory_headcount_required }} Orang</strong></td>
                <td>Ketetapan Disnaker (Pembulatan ke Atas)</td>
            </tr>
            <tr>
                <td>Kekurangan Headcount (Shortfall)</td>
                <td><strong style="color: {{ $audit->headcount_shortfall > 0 ? '#b91c1c' : '#047857' }};">{{ $audit->headcount_shortfall }} Orang</strong></td>
                <td>{{ $audit->headcount_shortfall > 0 ? 'Wajib dipenuhi dalam masa rekrutmen' : 'Target sah terpenuhi' }}</td>
            </tr>
            <tr>
                <td>Status Akhir Kepatuhan</td>
                <td colspan="2">
                    <span class="status-badge">
                        {{ strtoupper($audit->compliance_status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td>Fasilitas Aksesibilitas Lingkungan</td>
                <td colspan="2">
                    Fisik: <strong>{{ $audit->physical_accessibility_score }}%</strong> | Digital: <strong>{{ $audit->digital_accessibility_score }}%</strong><br>
                    <span style="font-size: 9.5pt; color: #444;">{{ $audit->reasonable_accommodations_summary }}</span>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 15px; font-size: 10pt;">
        <strong>Rekomendasi Tindak Lanjut:</strong>
        <p style="margin: 2px 0; text-align: justify;">{{ $audit->recommendations }}</p>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div>Mengetahui,</div>
                <div style="font-weight: bold; margin-top: 2px;">Direksi / Pimpinan Perusahaan</div>
                <div class="sig-space"></div>
                <div><strong>({{ $audit->company->legal_name ?? $audit->company->name }})</strong></div>
            </td>
            <td>
                <div>Diverifikasi oleh,</div>
                <div style="font-weight: bold; margin-top: 2px;">Head of HR & Legal Compliance</div>
                <div class="sig-space"></div>
                <div><strong>({{ auth()->user()->name ?? 'HR Compliance Lead' }})</strong></div>
            </td>
        </tr>
    </table>

</body>
</html>
