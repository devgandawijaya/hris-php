<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formulir 1721-A1 — {{ $reconciliation->employee->full_name }} — Tahun {{ $reconciliation->tax_year }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0f172a;
            background-color: #f8fafc;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 1rem !important;
            }
            @page {
                size: A4;
                margin: 1.2cm;
            }
        }
    </style>
</head>
<body class="py-8 px-4 sm:px-6">

    <!-- Top Action Bar (Screen Only) -->
    <div class="max-w-[840px] mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Rekonsiliasi
        </a>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Cetak Formulir 1721-A1
            </button>
        </div>
    </div>

    <!-- Official 1721-A1 Form Sheet -->
    <div class="max-w-[840px] mx-auto bg-white rounded-2xl border-2 border-slate-900 shadow-xl p-8 print-container text-xs">

        <!-- DJP Header -->
        <div class="border-b-2 border-slate-900 pb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-900 text-white flex items-center justify-center font-black text-xl">
                    DJP
                </div>
                <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">KEMENTERIAN KEUANGAN REPUBLIK INDONESIA</div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">DIREKTORAT JENDERAL PAJAK</div>
                    <div class="text-sm font-black text-slate-900 uppercase mt-0.5">BUKTI PEMOTONGAN PAJAK PENGHASILAN PASAL 21</div>
                    <div class="text-[11px] font-semibold text-slate-600">BAGI PEGAWAI TETAP ATAU PENERIMA PENSIUN BERKALA</div>
                </div>
            </div>

            <div class="text-right border-l-2 border-slate-900 pl-6">
                <div class="text-base font-black text-slate-900">FORMULIR 1721 - A1</div>
                <div class="text-[11px] font-bold text-slate-700 mt-1">TAHUN PAJAK: {{ $reconciliation->tax_year }}</div>
                <div class="text-[10px] text-slate-500">MASA PAJAK: 01 - 12</div>
            </div>
        </div>

        <!-- Section A: Identitas Penerima Penghasilan -->
        <div class="mt-4 border border-slate-900 rounded-lg p-3 bg-slate-50/60">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-900 mb-2 border-b border-slate-200 pb-1">
                A. IDENTITAS PENERIMA PENGHASILAN YANG DIPOTONG
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 text-[11px]">
                <div><span class="text-slate-500">1. NPWP / NIK:</span> <strong class="text-slate-900">{{ $reconciliation->employee->npwp ?: $reconciliation->employee->nik_ktp }}</strong></div>
                <div><span class="text-slate-500">4. Status PTKP:</span> <strong class="text-slate-900">{{ $reconciliation->employee->tax_ptkp_status ?? 'TK/0' }}</strong></div>
                <div><span class="text-slate-500">2. Nama Lengkap:</span> <strong class="text-slate-900">{{ $reconciliation->employee->full_name }}</strong></div>
                <div><span class="text-slate-500">5. Nama Jabatan:</span> <strong class="text-slate-900">{{ $reconciliation->employee->position?->title ?? '-' }}</strong></div>
                <div class="col-span-2"><span class="text-slate-500">3. Jenis Kelamin:</span> <strong class="text-slate-900 uppercase">{{ $reconciliation->employee->gender }}</strong></div>
            </div>
        </div>

        <!-- Section B: Rincian Penghasilan & Perhitungan PPh 21 -->
        <div class="mt-4 border border-slate-900 rounded-lg overflow-hidden">
            <div class="bg-slate-900 text-white font-black text-[10px] uppercase tracking-wider px-3 py-1.5">
                B. RINCIAN PENGHASILAN DAN PENGHITUNGAN PPH PASAL 21
            </div>

            <table class="w-full border-collapse text-[11px]">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300 font-bold text-slate-700">
                        <th class="px-3 py-1.5 text-left w-12">NO</th>
                        <th class="px-3 py-1.5 text-left">URAIAN</th>
                        <th class="px-3 py-1.5 text-right w-44">JUMLAH (RUPIAH)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr class="bg-slate-50 font-bold text-slate-800"><td colspan="3" class="px-3 py-1">KODE PENGHASILAN BRUTO:</td></tr>
                    <tr>
                        <td class="px-3 py-1 text-slate-500">1.</td>
                        <td class="px-3 py-1 text-slate-700">Gaji Pokok / Pensiun atau THT/JHT</td>
                        <td class="px-3 py-1 text-right font-semibold">{{ number_format($reconciliation->gross_income_annual - ($reconciliation->reconciliation_data['thr_total'] ?? 0), 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-1 text-slate-500">2.</td>
                        <td class="px-3 py-1 text-slate-700">Tantiem, Bonus, Gratifikasi, Jasa Produksi, dan THR</td>
                        <td class="px-3 py-1 text-right font-semibold">{{ number_format($reconciliation->reconciliation_data['thr_total'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="font-bold bg-slate-50/80">
                        <td class="px-3 py-1.5 text-slate-900">3.</td>
                        <td class="px-3 py-1.5 text-slate-900">Jumlah Penghasilan Bruto (1 + 2)</td>
                        <td class="px-3 py-1.5 text-right text-slate-900">{{ number_format($reconciliation->gross_income_annual, 0, ',', '.') }}</td>
                    </tr>

                    <tr class="bg-slate-50 font-bold text-slate-800"><td colspan="3" class="px-3 py-1">PENGURANG PENGHASILAN BRUTO:</td></tr>
                    <tr>
                        <td class="px-3 py-1 text-slate-500">4.</td>
                        <td class="px-3 py-1 text-slate-700">Biaya Jabatan (5% dari Bruto, Maks. Rp 6.000.000/tahun)</td>
                        <td class="px-3 py-1 text-right">{{ number_format($reconciliation->biaya_jabatan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-1 text-slate-500">5.</td>
                        <td class="px-3 py-1 text-slate-700">Iuran Pensiun atau Iuran THT / JHT</td>
                        <td class="px-3 py-1 text-right">{{ number_format($reconciliation->bpjs_deductions_annual, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="font-bold bg-slate-50/80">
                        <td class="px-3 py-1.5 text-slate-900">6.</td>
                        <td class="px-3 py-1.5 text-slate-900">Jumlah Pengurang (4 + 5)</td>
                        <td class="px-3 py-1.5 text-right text-slate-900">{{ number_format($reconciliation->biaya_jabatan + $reconciliation->bpjs_deductions_annual, 0, ',', '.') }}</td>
                    </tr>

                    <tr class="bg-slate-50 font-bold text-slate-800"><td colspan="3" class="px-3 py-1">PENGHITUNGAN PPH PASAL 21:</td></tr>
                    <tr>
                        <td class="px-3 py-1 text-slate-500">7.</td>
                        <td class="px-3 py-1 text-slate-700">Jumlah Penghasilan Neto Setahun (3 - 6)</td>
                        <td class="px-3 py-1 text-right font-semibold">{{ number_format($reconciliation->net_income_annual, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-1 text-slate-500">8.</td>
                        <td class="px-3 py-1 text-slate-700">Penghasilan Tidak Kena Pajak (PTKP {{ $reconciliation->employee->tax_ptkp_status ?? 'TK/0' }})</td>
                        <td class="px-3 py-1 text-right font-semibold">{{ number_format($reconciliation->ptkp_annual, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-1 text-slate-500">9.</td>
                        <td class="px-3 py-1 text-slate-700">Penghasilan Kena Pajak (PKP) Setahun (7 - 8)</td>
                        <td class="px-3 py-1 text-right font-bold">{{ number_format($reconciliation->pkp, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-blue-50/60 font-bold text-blue-900">
                        <td class="px-3 py-1.5">10.</td>
                        <td class="px-3 py-1.5">PPh Pasal 21 Terutang Setahun (Tarif Pasal 17 UU HPP)</td>
                        <td class="px-3 py-1.5 text-right">{{ number_format($reconciliation->pph21_annual_due, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-1 text-slate-500">11.</td>
                        <td class="px-3 py-1 text-slate-700">PPh Pasal 21 yang telah dipotong bulanan (TER PP 58/2023)</td>
                        <td class="px-3 py-1 text-right">{{ number_format($reconciliation->pph21_ter_paid_total, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-amber-100/70 font-black text-amber-950">
                        <td class="px-3 py-2 text-sm">12.</td>
                        <td class="px-3 py-2 text-sm">PPh Pasal 21 Kurang / (Lebih) Dipotong Masa Desember (10 - 11)</td>
                        <td class="px-3 py-2 text-right text-sm">Rp {{ number_format($reconciliation->pph21_december_tax, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section C: Identitas Pemotong Pajak & Tanda Tangan -->
        <div class="mt-4 border border-slate-900 rounded-lg p-4 bg-slate-50/60">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-900 mb-2 border-b border-slate-200 pb-1">
                C. IDENTITAS PEMOTONG PAJAK
            </div>
            <div class="grid grid-cols-2 gap-4 text-[11px]">
                <div>
                    <div><span class="text-slate-500">1. NPWP Pemotong:</span> <strong class="text-slate-900">{{ $company?->npwp ?? '01.234.567.8-012.000' }}</strong></div>
                    <div class="mt-1"><span class="text-slate-500">2. Nama Pemotong:</span> <strong class="text-slate-900 uppercase">{{ $company?->name ?? 'PT Solusi Digital Nusantara' }}</strong></div>
                    <div class="mt-1"><span class="text-slate-500">3. Tanggal Pemotongan:</span> <strong class="text-slate-900">31 Desember {{ $reconciliation->tax_year }}</strong></div>
                </div>

                <div class="text-center">
                    <div class="text-slate-500 font-semibold mb-10">Tanda Tangan dan Cap Perusahaan:</div>
                    <div class="font-bold text-slate-900 border-t border-slate-300 pt-1 inline-block px-10">
                        Pengurus / Kuasa Wajib Pajak
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
