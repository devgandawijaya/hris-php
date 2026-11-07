<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Pelatihan - {{ $certificate->recipient_name }} - {{ $certificate->certificate_number }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        body {
            font-family: 'Georgia', serif;
            color: #1e293b;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .cert-container {
            width: 960px;
            height: 640px;
            background: #ffffff;
            border: 12px solid #0f172a;
            position: relative;
            padding: 40px;
            box-sizing: border-box;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .cert-inner-border {
            border: 2px solid #b45309;
            height: 100%;
            padding: 30px;
            box-sizing: border-box;
            position: relative;
        }
        .cert-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: #b45309;
            color: #fff;
            padding: 4px 20px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: sans-serif;
            font-weight: bold;
            border-radius: 4px;
        }
        .company-name {
            font-size: 16px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #64748b;
            margin-top: 10px;
            font-family: sans-serif;
        }
        .cert-title {
            font-size: 32px;
            font-weight: bold;
            color: #0f172a;
            margin: 15px 0 5px 0;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .cert-subtitle {
            font-size: 14px;
            color: #b45309;
            font-style: italic;
            margin-bottom: 25px;
        }
        .cert-awarded-to {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #64748b;
            font-family: sans-serif;
        }
        .recipient-name {
            font-size: 30px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
            display: inline-block;
            padding: 0 40px 5px 40px;
            margin: 10px 0 15px 0;
        }
        .cert-desc {
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
            max-width: 700px;
            margin: 0 auto;
        }
        .training-title {
            font-weight: bold;
            color: #0f172a;
            font-size: 17px;
        }
        .footer-table {
            width: 100%;
            position: absolute;
            bottom: 30px;
            left: 0;
            padding: 0 40px;
            box-sizing: border-box;
        }
        .footer-table td {
            vertical-align: bottom;
            width: 33.33%;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            width: 180px;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 12px;
            font-weight: bold;
            font-family: sans-serif;
        }
        .security-stamp {
            font-family: monospace;
            font-size: 9px;
            color: #64748b;
            line-height: 1.4;
            text-align: center;
        }
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #b45309;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            font-family: sans-serif;
        }
        @media print {
            body { padding: 0; background: none; }
            .cert-container { box-shadow: none; width: 100%; height: 100vh; border-width: 8px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn">🖨️ Cetak E-Sertifikat</button>

    <div class="cert-container">
        <div class="cert-inner-border">
            <div class="cert-badge">Verified Digital Credential</div>

            <div class="company-name">{{ $company->name }}</div>
            <div class="cert-title">Sertifikat Kelulusan</div>
            <div class="cert-subtitle">Certificate of Competency & Completion</div>

            <div class="cert-awarded-to">Diberikan Dengan Bangga Kepada:</div>
            <div class="recipient-name">{{ $certificate->recipient_name }}</div>

            <div class="cert-desc">
                Telah berhasil menyelesaikan dan memenuhi seluruh standar evaluasi kompetensi pada program pelatihan:
                <br>
                <span class="training-title">"{{ $certificate->training_title }}"</span>
                <br>
                Diselenggarakan pada tanggal {{ \Carbon\Carbon::parse($certificate->issued_at)->translatedFormat('d F Y') }} dengan predikat kelulusan memuaskan.
            </div>

            <table class="footer-table">
                <tr>
                    <td style="text-align: center;">
                        <div style="height: 45px;"></div>
                        <div class="signature-line">
                            {{ $company->name }}<br>
                            <span style="font-size: 10px; color: #64748b; font-weight: normal;">Lead Instructor / Trainer</span>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <div class="security-stamp">
                            <strong style="color: #0f172a;">No: {{ $certificate->certificate_number }}</strong><br>
                            Hash: {{ substr($certificate->verification_hash, 0, 16) }}...<br>
                            Verifikasi Kredensial RiHRIS SaaS
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <div style="height: 45px;"></div>
                        <div class="signature-line">
                            Head of Human Capital<br>
                            <span style="font-size: 10px; color: #64748b; font-weight: normal;">Learning & Development Director</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
