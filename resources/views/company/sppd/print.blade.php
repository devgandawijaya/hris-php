<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Perintah Perjalanan Dinas - {{ $order->sppd_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            margin: 20px 30px;
            font-size: 13px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #111827;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title {
            text-align: center;
            margin: 20px 0 15px 0;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 15px;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .doc-title p {
            margin: 4px 0 0 0;
            font-weight: bold;
        }
        table.sppd-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.sppd-table th, table.sppd-table td {
            border: 1px solid #374151;
            padding: 8px 10px;
            vertical-align: top;
        }
        table.sppd-table td.col-num {
            width: 30px;
            text-align: center;
            font-weight: bold;
        }
        table.sppd-table td.col-label {
            width: 250px;
        }
        .signatures {
            width: 100%;
            margin-top: 30px;
        }
        .sign-box {
            width: 45%;
            float: right;
            text-align: center;
        }
        .sign-box-left {
            width: 45%;
            float: left;
            text-align: center;
        }
        .sign-space {
            height: 70px;
        }
        .print-btn-bar {
            background: #f3f4f6;
            padding: 10px;
            text-align: right;
            margin-bottom: 20px;
        }
        @media print {
            .print-btn-bar { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

<div class="print-btn-bar">
    <button onclick="window.print()" style="padding: 8px 16px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
        🖨️ Cetak Dokumen SPPD
    </button>
</div>

<div class="header">
    <div class="company-name">{{ $order->company->legal_name ?? $order->company->name }}</div>
    <div>{{ $order->company->address ?? 'Gedung Wisma Niaga Lt. 12, Jl. Jend. Sudirman Kav. 25, Jakarta Selatan' }}</div>
    <div>NPWP: {{ $order->company->npwp ?? '01.234.567.8-012.000' }} | Telp: {{ $order->company->phone ?? '(021) 5250001' }}</div>
</div>

<div class="doc-title">
    <h2>SURAT PERINTAH PERJALANAN DINAS (SPPD)</h2>
    <p>Nomor: {{ $order->sppd_number }}</p>
</div>

<table class="sppd-table">
    <tr>
        <td class="col-num">1.</td>
        <td class="col-label">Pejabat Berwenang yang Memberi Perintah</td>
        <td>{{ $order->approver ? $order->approver->name : 'Direktur / HR Manager' }}</td>
    </tr>
    <tr>
        <td class="col-num">2.</td>
        <td class="col-label">Nama Pegawai yang Diperintahkan</td>
        <td><strong>{{ $order->employee->full_name }}</strong></td>
    </tr>
    <tr>
        <td class="col-num">3.</td>
        <td class="col-label">
            a. Nomor Induk Pegawai (NIP)<br>
            b. Pangkat / Golongan Upah<br>
            c. Jabatan / Departemen
        </td>
        <td>
            a. {{ $order->employee->employee_number }}<br>
            b. {{ $order->employee->jobGrade ? $order->employee->jobGrade->grade_code . ' (' . $order->employee->jobGrade->name . ')' : 'Pelaksana Standar' }}<br>
            c. {{ $order->employee->position ? $order->employee->position->title : 'Staff' }} / {{ $order->employee->department ? $order->employee->department->name : '-' }}
        </td>
    </tr>
    <tr>
        <td class="col-num">4.</td>
        <td class="col-label">Maksud dan Tujuan Perjalanan Dinas</td>
        <td>{{ $order->purpose }}</td>
    </tr>
    <tr>
        <td class="col-num">5.</td>
        <td class="col-label">Alat Angkut / Moda Transportasi yang Digunakan</td>
        <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $order->transport_type) }}</td>
    </tr>
    <tr>
        <td class="col-num">6.</td>
        <td class="col-label">
            a. Tempat Berangkat (Asal)<br>
            b. Tempat Tujuan Kedinasan
        </td>
        <td>
            a. {{ $order->origin_city }}<br>
            b. <strong>{{ $order->destination_city }}</strong>
        </td>
    </tr>
    <tr>
        <td class="col-num">7.</td>
        <td class="col-label">
            a. Lamanya Perjalanan Dinas<br>
            b. Tanggal Berangkat<br>
            c. Tanggal Harus Kembali
        </td>
        <td>
            a. {{ $order->duration_days }} ({{ $order->duration_days }} Hari Kerja)<br>
            b. {{ $order->departure_date->translatedFormat('d F Y') }}<br>
            c. {{ $order->return_date->translatedFormat('d F Y') }}
        </td>
    </tr>
    <tr>
        <td class="col-num">8.</td>
        <td class="col-label">Pembebanan Anggaran</td>
        <td>
            a. Instansi / PT: {{ $order->company->name }}<br>
            b. Mata Anggaran: Biaya Operasional Perjalanan Dinas (OPEX)
        </td>
    </tr>
    <tr>
        <td class="col-num">9.</td>
        <td class="col-label">Keterangan Tambahan</td>
        <td>{{ $order->notes ?? 'Segala ketentuan dan instruksi keselamatan perjalanan dinas tunduk pada Peraturan Perusahaan.' }}</td>
    </tr>
</table>

<div class="signatures">
    <div class="sign-box-left">
        <div>Mengetahui / Tiba di Tempat Tujuan:</div>
        <div style="font-size: 11px; color: #4b5563;">Pejabat / Penanggung Jawab Mitra / Cabang</div>
        <div class="sign-space"></div>
        <div style="text-decoration: underline; font-weight: bold;">( .................................................. )</div>
        <div style="font-size: 11px;">Cap & Tanda Tangan Kedatangan</div>
    </div>

    <div class="sign-box">
        <div>Dikeluarkan di: {{ $order->origin_city }}</div>
        <div>Pada tanggal: {{ $order->created_at->translatedFormat('d F Y') }}</div>
        <div style="font-weight: bold; margin-top: 5px;">Pejabat yang Memberi Perintah,</div>
        <div class="sign-space"></div>
        <div style="text-decoration: underline; font-weight: bold;">{{ $order->approver ? $order->approver->name : 'HR Director / Manager' }}</div>
        <div style="font-size: 11px;">NIP: {{ $order->approver ? 'EMP-' . str_pad($order->approver->id, 4, '0', STR_PAD_LEFT) : 'MGMT' }}</div>
    </div>
    <div style="clear: both;"></div>
</div>

</body>
</html>
