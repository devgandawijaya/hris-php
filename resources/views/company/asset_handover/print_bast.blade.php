<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BAST Pengembalian Aset - {{ $bast->bast_number }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #111; margin: 40px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 14pt; margin: 0; text-transform: uppercase; font-weight: bold; }
        .header h2 { font-size: 16pt; margin: 4px 0; text-transform: uppercase; letter-spacing: 1px; }
        .header p { font-size: 10pt; margin: 0; color: #444; }
        .title-block { text-align: center; margin: 25px 0 15px 0; }
        .title-block h3 { font-size: 13pt; margin: 0; text-decoration: underline; text-transform: uppercase; }
        .title-block p { font-size: 11pt; margin: 2px 0; font-family: monospace; }
        .parties-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .parties-table td { padding: 4px 2px; vertical-align: top; font-size: 11pt; }
        .items-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .items-table th, .items-table td { border: 1px solid #333; padding: 6px 8px; font-size: 10pt; }
        .items-table th { background-color: #f2f2f2; text-align: left; }
        .signatures { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; font-size: 11pt; }
        .sig-space { height: 70px; }
        .qr-placeholder { font-family: monospace; font-size: 8pt; color: #666; border: 1px dashed #999; padding: 6px; display: inline-block; margin-top: 10px; }
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
        <h1>{{ $bast->company->legal_name ?? $bast->company->name }}</h1>
        <h2>Berita Acara Serah Terima Aset</h2>
        <p>{{ $bast->company->address ?? 'Kantor Pusat' }}, {{ $bast->company->city }}, {{ $bast->company->province }} | NPWP: {{ $bast->company->npwp ?? '-' }}</p>
    </div>

    <div class="title-block">
        <h3>BERITA ACARA SERAH TERIMA PENGEMBALIAN ASET PERUSAHAAN</h3>
        <p>Nomor: {{ $bast->bast_number }}</p>
    </div>

    <p style="text-align: justify; text-indent: 30px;">
        Pada hari ini, tanggal <strong>{{ $bast->handover_date->translatedFormat('d F Y') }}</strong>, bertempat di kantor {{ $bast->company->name }}, yang bertanda tangan di bawah ini:
    </p>

    <table class="parties-table">
        <tr>
            <td style="width: 25px;"><strong>I.</strong></td>
            <td style="width: 140px;">Nama Lengkap</td>
            <td style="width: 15px;">:</td>
            <td><strong>{{ $bast->employee->full_name }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td>NIK Karyawan</td>
            <td>:</td>
            <td>{{ $bast->employee->employee_number }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Departemen / Jabatan</td>
            <td>:</td>
            <td>{{ $bast->employee->department->name ?? '-' }} / {{ $bast->employee->position->title ?? 'Staff' }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3" style="padding-top: 4px; font-style: italic;">
                Selanjutnya disebut sebagai <strong>"PIHAK PERTAMA"</strong> (Karyawan yang Menyerahkan Aset).
            </td>
        </tr>
        <tr><td colspan="4" style="height: 10px;"></td></tr>
        <tr>
            <td><strong>II.</strong></td>
            <td>Nama Lengkap</td>
            <td>:</td>
            <td><strong>{{ $bast->officer_name }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td>Jabatan Petugas</td>
            <td>:</td>
            <td>{{ $bast->officer_position }}</td>
        </tr>
        <tr>
            <td></td>
            <td>Mewakili Entitas</td>
            <td>:</td>
            <td>{{ $bast->company->legal_name ?? $bast->company->name }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3" style="padding-top: 4px; font-style: italic;">
                Selanjutnya disebut sebagai <strong>"PIHAK KEDUA"</strong> (Petugas Penerima Aset Perusahaan).
            </td>
        </tr>
    </table>

    <p style="text-align: justify;">
        Dengan ini menyatakan bahwa PIHAK PERTAMA telah menyerahkan inventaris aset milik perusahaan kepada PIHAK KEDUA, dan PIHAK KEDUA telah memeriksa serta menerima kembali inventaris tersebut dengan rincian sebagai berikut:
    </p>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th>Nama Barang / Aset</th>
                <th>Nomor Seri / Tag</th>
                <th>Kondisi Fisik</th>
                <th>Nilai Ganti Rugi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bast->items as $idx => $it)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td><strong>{{ $it->asset_name }}</strong></td>
                    <td style="font-family: monospace;">{{ $it->serial_number ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $it->condition_on_return)) }}</td>
                    <td style="text-align: right;">Rp {{ number_format($it->compensation_amount, 0, ',', '.') }}</td>
                    <td>{{ $it->notes ?? '-' }}</td>
                </tr>
            @endforeach
            @if($bast->total_compensation_due > 0)
                <tr style="font-weight: bold; background: #fafafa;">
                    <td colspan="4" style="text-align: right;">Total Beban Ganti Rugi:</td>
                    <td style="text-align: right; color: #b91c1c;">Rp {{ number_format($bast->total_compensation_due, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <p style="text-align: justify;">
        Demikian Berita Acara Serah Terima ini dibuat secara sah dan ditandatangani untuk dipergunakan sebagai bukti pemenuhan kewajiban inventarisasi dan dasar penyelesaian administrasi hak serta kewajiban ketenagakerjaan.
    </p>

    <table class="signatures">
        <tr>
            <td>
                <div>PIHAK PERTAMA,</div>
                <div style="font-size: 10pt; color: #555;">Karyawan yang Menyerahkan</div>
                <div class="sig-space">
                    @if($bast->employee_signature_token)
                        <div class="qr-placeholder">
                            [TTE VALID: {{ substr(md5($bast->employee_signature_token), 0, 12) }}]<br>
                            {{ $bast->signed_at ? $bast->signed_at->format('d/m/Y') : '' }}
                        </div>
                    @endif
                </div>
                <div><strong>({{ $bast->employee->full_name }})</strong></div>
            </td>
            <td>
                <div>PIHAK KEDUA,</div>
                <div style="font-size: 10pt; color: #555;">Petugas Penerima Aset</div>
                <div class="sig-space">
                    @if($bast->officer_signature_token)
                        <div class="qr-placeholder">
                            [TTE VALID: {{ substr(md5($bast->officer_signature_token), 0, 12) }}]<br>
                            {{ $bast->signed_at ? $bast->signed_at->format('d/m/Y') : '' }}
                        </div>
                    @endif
                </div>
                <div><strong>({{ $bast->officer_name }})</strong></div>
            </td>
        </tr>
    </table>

</body>
</html>
