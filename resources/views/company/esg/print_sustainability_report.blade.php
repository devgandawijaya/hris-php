<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keberlanjutan ESG Ketenagakerjaan - Tahun {{ $snapshot->reporting_year }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 min-h-screen text-slate-800">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl border border-slate-200 shadow-sm print:border-none print:shadow-none space-y-6">

        <!-- No Print Bar -->
        <div class="no-print flex justify-between items-center border-b border-slate-100 pb-4">
            <span class="text-xs text-slate-500 font-medium">Laporan Resmi Kinerja Keberlanjutan Ketenagakerjaan (POJK 51/2017)</span>
            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-black transition">
                Cetak Dokumen
            </button>
        </div>

        <!-- Official Header -->
        <div class="border-b-2 border-slate-900 pb-4 flex justify-between items-start">
            <div>
                <h1 class="text-lg font-black uppercase text-slate-900">{{ $snapshot->company->name ?? 'PT DEMO INDONESIA' }}</h1>
                <p class="text-xs font-bold text-slate-700">PERNYATAAN KEBERLANJUTAN ASPEK SOSIAL & KETENAGAKERJAAN</p>
                <p class="text-[10px] text-slate-500">Berdasarkan Peraturan OJK No. 51/POJK.03/2017 jo. Standar GRI 401, 403, 404, 405</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-mono font-bold text-slate-900 block">ESG-POJK51-{{ $snapshot->reporting_year }}</span>
                <span class="text-[11px] text-slate-500 block">Tahun Buku {{ $snapshot->reporting_year }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                    Status: {{ $snapshot->status }}
                </span>
            </div>
        </div>

        <!-- Table of Metrics -->
        <div class="space-y-4 text-xs">
            <h3 class="font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1">
                Tabel Pengungkapan Standar GRI & POJK 51/2017
            </h3>

            <table class="w-full text-slate-700 border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-[11px] font-bold uppercase text-slate-600">
                    <tr>
                        <th class="py-2.5 px-3 text-left">Indikator Kinerja Keberlanjutan</th>
                        <th class="py-2.5 px-3 text-center">Standar Acuan</th>
                        <th class="py-2.5 px-3 text-right">Realisasi Tahun {{ $snapshot->reporting_year }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-900 block">Rasio Upah Setara Gender (Gender Pay Gap Ratio)</span>
                            <span class="text-[10px] text-slate-400">Rasio rata-rata upah pokok perempuan terhadap laki-laki</span>
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono text-[11px]">GRI 405-2</td>
                        <td class="py-2.5 px-3 text-right font-black text-slate-900">{{ number_format($snapshot->gender_pay_gap_ratio, 4) }} (Ideal: 1.0000)</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-900 block">Keterwakilan Perempuan di Angkatan Kerja</span>
                            <span class="text-[10px] text-slate-400">Persentase tenaga kerja wanita aktif</span>
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono text-[11px]">GRI 405-1a</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-800">{{ number_format($snapshot->female_workforce_pct, 2) }}%</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-900 block">Kepemimpinan Wanita (Manajerial & Level Lead)</span>
                            <span class="text-[10px] text-slate-400">Keterlibatan perempuan dalam posisi pengambil keputusan</span>
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono text-[11px]">GRI 405-1b</td>
                        <td class="py-2.5 px-3 text-right font-bold text-indigo-700">{{ number_format($snapshot->female_leadership_pct, 2) }}%</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-900 block">Diversitas Dewan Direksi & Komisaris (Board)</span>
                            <span class="text-[10px] text-slate-400">Proporsi anggota direksi wanita</span>
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono text-[11px]">POJK 51 C.3</td>
                        <td class="py-2.5 px-3 text-right font-bold text-purple-700">{{ number_format($snapshot->board_diversity_pct, 2) }}%</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-900 block">Rata-rata Jam Pelatihan per Karyawan</span>
                            <span class="text-[10px] text-slate-400">Investasi pengembangan kompetensi SDM</span>
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono text-[11px]">GRI 404-1</td>
                        <td class="py-2.5 px-3 text-right font-bold text-teal-700">{{ number_format($snapshot->avg_training_hours_per_employee, 2) }} Jam/Orang</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-900 block">Tingkat Pergantian Karyawan (Annual Turnover)</span>
                            <span class="text-[10px] text-slate-400">Rasio pemisahan hubungan kerja sukarela & non-sukarela</span>
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono text-[11px]">GRI 401-1</td>
                        <td class="py-2.5 px-3 text-right font-bold text-rose-700">{{ number_format($snapshot->annual_turnover_rate, 2) }}%</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-900 block">Tingkat Cedera Hilang Waktu Kerja (LTIFR)</span>
                            <span class="text-[10px] text-slate-400">Frekuensi insiden K3 per satu juta jam kerja</span>
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono text-[11px]">GRI 403-9</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">{{ number_format($snapshot->lost_time_injury_frequency_rate, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Notes -->
        <div class="text-[10px] text-slate-500 bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-1">
            <span class="font-bold text-slate-700 block">Catatan & Pernyataan Direksi:</span>
            <p>{{ $snapshot->sustainability_notes }}</p>
            <p>Perusahaan berkomitmen menjaga lingkungan kerja yang inklusif, nondiskriminatif, aman, dan berorientasi pada keberlanjutan jangka panjang selaras dengan Tujuan Pembangunan Berkelanjutan (SDG 5 Kesetaraan Gender & SDG 8 Pekerjaan Layak).</p>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 text-xs text-center pt-8">
            <div class="space-y-16">
                <div class="space-y-0.5">
                    <span class="text-slate-500 block">Diverifikasi oleh,</span>
                    <strong class="text-slate-800 block">Human Capital & ESG Lead</strong>
                </div>
                <div class="border-t border-slate-300 pt-1">
                    <strong class="block text-slate-900">( Komite Keberlanjutan Perusahaan )</strong>
                </div>
            </div>
            <div class="space-y-16">
                <div class="space-y-0.5">
                    <span class="text-slate-500 block">Disahkan oleh,</span>
                    <strong class="text-slate-800 block">Direktur Utama / Chief Executive Officer</strong>
                </div>
                <div class="border-t border-slate-300 pt-1">
                    <strong class="block text-slate-900">( Dewan Direksi )</strong>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
