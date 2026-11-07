<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip THR Statutori - {{ $disbursement->employee->full_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 min-h-screen text-slate-800">

    <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl border border-slate-200 shadow-sm print:border-none print:shadow-none space-y-6">

        <!-- No Print Bar -->
        <div class="no-print flex justify-between items-center border-b border-slate-100 pb-4">
            <span class="text-xs text-slate-500 font-medium">Pratinjau Slip Tunjangan Hari Raya (THR) Statutori</span>
            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-black transition">
                Cetak Dokumen
            </button>
        </div>

        <!-- Header -->
        <div class="border-b-2 border-slate-900 pb-4 flex justify-between items-start">
            <div>
                <h1 class="text-lg font-black uppercase text-slate-900">{{ $disbursement->company->name ?? 'PT DEMO INDONESIA' }}</h1>
                <p class="text-xs text-slate-500">Bukti Resmi Pencairan Tunjangan Hari Raya Keagamaan</p>
                <p class="text-[10px] text-slate-400 mt-1">Sesuai Permenaker No. 6 Tahun 2016 jo. PP 58/2023</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-mono font-bold text-slate-900 block">THR-{{ $disbursement->tax_year }}-{{ str_pad($disbursement->id, 5, '0', STR_PAD_LEFT) }}</span>
                <span class="text-[11px] text-slate-500 block">Tahun Pajak {{ $disbursement->tax_year }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $disbursement->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    Status: {{ $disbursement->status }}
                </span>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-xl border border-slate-100">
            <div class="space-y-1">
                <div><span class="text-slate-400 w-24 inline-block">Nama Karyawan:</span> <strong class="text-slate-800">{{ $disbursement->employee->full_name }}</strong></div>
                <div><span class="text-slate-400 w-24 inline-block">NIK / ID:</span> <span class="font-mono">{{ $disbursement->employee->nik }}</span></div>
                <div><span class="text-slate-400 w-24 inline-block">Departemen:</span> {{ $disbursement->employee->department->name ?? '-' }}</div>
            </div>
            <div class="space-y-1">
                <div><span class="text-slate-400 w-28 inline-block">Hari Raya:</span> <strong class="uppercase font-mono">{{ str_replace('_', ' ', $disbursement->religious_holiday_type) }}</strong></div>
                <div><span class="text-slate-400 w-28 inline-block">Tgl Mulai Bekerja:</span> {{ \Carbon\Carbon::parse($disbursement->employee->join_date)->format('d M Y') }}</div>
                <div><span class="text-slate-400 w-28 inline-block">Masa Kerja (Tenure):</span> <strong>{{ $disbursement->tenure_months }} Bulan</strong></div>
            </div>
        </div>

        <!-- Calculation Breakdown Table -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Rincian Perhitungan Hak & Pemotongan Pajak</h3>
            <table class="w-full text-xs text-slate-700 border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-[11px] font-bold uppercase text-slate-600">
                    <tr>
                        <th class="py-2.5 px-3 text-left">Komponen / Formula</th>
                        <th class="py-2.5 px-3 text-right">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-900 block">Upah Dasar (Gaji Pokok + Tunjangan Tetap)</span>
                            <span class="text-[10px] text-slate-400">Sesuai Pasal 3 ayat 2 Permenaker No. 6/2016</span>
                        </td>
                        <td class="py-2.5 px-3 text-right font-semibold">Rp {{ number_format($disbursement->regular_wage_basis, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-900 block">Faktor Pro-rata Masa Kerja ({{ $disbursement->tenure_months }}/12)</span>
                            <span class="text-[10px] text-slate-400">Pengali: {{ number_format($disbursement->prorata_multiplier, 4) }}</span>
                        </td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">Rp {{ number_format($disbursement->calculated_thr_amount, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3 text-rose-700">
                            <span class="font-medium block">Potongan PPh Pasal 21 TER (PP 58/2023)</span>
                            <span class="text-[10px] text-rose-500">Tarif Efektif Rata-Rata pada masa pajak pencairan</span>
                        </td>
                        <td class="py-2.5 px-3 text-right font-semibold text-rose-700">- Rp {{ number_format($disbursement->ter_tax_deduction, 2, ',', '.') }}</td>
                    </tr>
                    @if($disbursement->late_penalty_interest > 0)
                        <tr class="bg-rose-50/70">
                            <td class="py-2.5 px-3 text-rose-900">
                                <span class="font-bold block">Kompensasi Denda Keterlambatan H-7 (5%)</span>
                                <span class="text-[10px] text-rose-600">Pasal 10 Permenaker No. 6/2016 (Batas: {{ \Carbon\Carbon::parse($disbursement->statutory_payment_deadline)->format('d M Y') }})</span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-rose-900">+ Rp {{ number_format($disbursement->late_penalty_interest, 2, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="bg-slate-50 font-bold text-slate-900 text-sm">
                        <td class="py-3 px-3 uppercase">Total THR Bersih Diterima (Net)</td>
                        <td class="py-3 px-3 text-right text-emerald-700 text-base">Rp {{ number_format($disbursement->net_thr_paid, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Compliance Notes -->
        <div class="text-[10px] text-slate-500 bg-slate-50 p-3 rounded-lg space-y-1">
            <p><strong>Catatan Kepatuhan Regulasi:</strong></p>
            <p>1. Tunjangan Hari Raya Keagamaan diberikan 1 (satu) kali dalam 1 (satu) tahun sesuai dengan hari raya keagamaan masing-masing pekerja.</p>
            <p>2. Bukti slip ini dicetak secara otomatis oleh Sistem HRIS & Payroll Perusahaan yang terintegrasi dengan peraturan ketenagakerjaan Republik Indonesia.</p>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 text-xs text-center pt-4">
            <div class="space-y-12">
                <span class="text-slate-500 block">Pemberi Kerja / HRD Department</span>
                <span class="font-bold border-t border-slate-300 pt-1 block text-slate-800">( Divisi Human Capital )</span>
            </div>
            <div class="space-y-12">
                <span class="text-slate-500 block">Penerima Manfaat (Karyawan)</span>
                <span class="font-bold border-t border-slate-300 pt-1 block text-slate-800">( {{ $disbursement->employee->full_name }} )</span>
            </div>
        </div>

    </div>

</body>
</html>
