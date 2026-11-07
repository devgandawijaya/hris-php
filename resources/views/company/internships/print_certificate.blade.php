<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Pemagangan - {{ $certificateData['nomor_sertifikat'] }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 30px;
            background: #fff;
            color: #111;
        }
        .cert-container {
            border: 8px double #0d9488;
            padding: 40px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        .header {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #555;
            margin-bottom: 10px;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin: 15px 0;
            border-bottom: 2px solid #0f766e;
            display: inline-block;
            padding-bottom: 5px;
        }
        .cert-number {
            font-family: monospace;
            font-size: 13px;
            color: #666;
            margin-bottom: 30px;
        }
        .statement {
            font-size: 15px;
            font-style: italic;
            margin-bottom: 15px;
        }
        .recipient-name {
            font-size: 26px;
            font-weight: bold;
            text-decoration: underline;
            color: #111;
            margin-bottom: 10px;
        }
        .institution {
            font-size: 14px;
            color: #444;
            margin-bottom: 25px;
        }
        .body-text {
            font-size: 15px;
            line-height: 1.8;
            margin: 0 auto 30px auto;
            max-width: 650px;
            text-align: justify;
        }
        .grade-box {
            display: inline-block;
            border: 2px solid #0f766e;
            padding: 10px 25px;
            border-radius: 6px;
            margin-bottom: 40px;
            background: #f0fdfa;
        }
        .grade-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0d9488;
            font-weight: bold;
        }
        .grade-value {
            font-size: 18px;
            font-weight: bold;
            color: #111;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding: 0 40px;
            text-align: center;
        }
        .sign-col {
            width: 220px;
        }
        .sign-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            font-weight: bold;
            font-size: 14px;
            padding-top: 5px;
        }
        .sign-title {
            font-size: 12px;
            color: #555;
        }
        .legal-footer {
            margin-top: 30px;
            font-size: 11px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-style: italic;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0f766e; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 13px;">
            Cetak / Ekspor PDF
        </button>
    </div>

    <div class="cert-container">
        <div class="header">{{ $internship->company->legal_name ?? $internship->company->name }}</div>
        <div class="title">Sertifikat Pemagangan</div>
        <div class="cert-number">Nomor: {{ $certificateData['nomor_sertifikat'] }}</div>

        <div class="statement">Diberikan secara resmi kepada:</div>
        <div class="recipient-name">{{ $certificateData['nama_peserta'] }}</div>
        <div class="institution">Asal Institusi: {{ $certificateData['institusi_asal'] }}</div>

        <div class="body-text">
            Telah menyelesaikan seluruh rangkaian kegiatan dan kurikulum pada <strong>{{ $certificateData['judul_program'] }}</strong> yang diselenggarakan oleh {{ $internship->company->name }} terhitung sejak tanggal <strong>{{ $certificateData['durasi_periode'] }}</strong>, dengan menunjukkan dedikasi, kedisiplinan kerja, dan kompetensi yang diakui.
        </div>

        <div class="grade-box">
            <div class="grade-title">Predikat Kelulusan Pemagangan</div>
            <div class="grade-value">{{ $certificateData['predikat_kelulusan'] }} (Skor: {{ $certificateData['nilai_evaluasi_komposit'] }}/5.0)</div>
        </div>

        <div class="signatures">
            <div class="sign-col">
                <div class="sign-title">Instruktur / Pembimbing Lapangan</div>
                <div class="sign-line">{{ $internship->mentor->full_name ?? 'Instruktur Senior' }}</div>
                <div class="sign-title">{{ $internship->mentor->position->title ?? 'Pembimbing Teknis' }}</div>
            </div>
            <div class="sign-col">
                <div class="sign-title">Direktur / Kepala Divisi SDM</div>
                <div class="sign-line">Dewi Sartika, S.Psi., M.M.</div>
                <div class="sign-title">Head of People & Culture</div>
            </div>
        </div>

        <div class="legal-footer">
            {{ $certificateData['dasar_hukum'] }}
        </div>
    </div>
</body>
</html>
