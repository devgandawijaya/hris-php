<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Triwulan P2K3 - {{ $report->quarter }}/{{ $report->reporting_year }}</title>
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
            <span class="text-xs text-slate-500 font-medium">Laporan Resmi Panitia Pembina Keselamatan & Kesehatan Kerja (P2K3)</span>
            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-black transition">
                Cetak Dokumen
            </button>
        </div>

        <!-- Official Header -->
        <div class="border-b-2 border-slate-900 pb-4 text-center space-y-1">
            <h1 class="text-base font-black uppercase text-slate-900 tracking-wide">
                LAPORAN KEGIATAN PANITIA PEMBINA KESELAMATAN & KESEHATAN KERJA (P2K3)
            </h1>
            <p class="text-xs font-bold text-slate-700">PERIODE: {{ $report->quarter }} TAHUN {{ $report->reporting_year }}</p>
            <p class="text-[10px] text-slate-500">Sesuai Ketentuan Permenaker No. PER-04/MEN/1987 Pasal 12 jo. UU No. 1 Tahun 1970</p>
        </div>

        <!-- Kepada Yth -->
        <div class="text-xs space-y-1 text-slate-700">
            <div>Kepada Yth.</div>
            <div class="font-bold">Kepala Dinas Tenaga Kerja dan Transmigrasi Setempat</div>
            <div>Up. Pengawas Ketenagakerjaan Bidang K3</div>
        </div>

        <!-- Bagian I: Data Perusahaan -->
        <div class="space-y-2 text-xs">
            <h3 class="font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1">I. Data Umum Perusahaan</h3>
            <table class="w-full">
                <tr>
                    <td class="w-48 text-slate-500 py-0.5">1. Nama Perusahaan</td>
                    <td class="font-bold py-0.5">: {{ $report->committee->company->name ?? 'PT DEMO INDONESIA' }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 py-0.5">2. No. SK P2K3 Perusahaan</td>
                    <td class="font-mono py-0.5">: {{ $report->committee->decree_number }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 py-0.5">3. No. Pengesahan Disnaker</td>
                    <td class="font-mono py-0.5">: {{ $report->committee->disnaker_ratification_number ?? 'Dalam Proses Penerbitan' }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 py-0.5">4. Tanggal Pembentukan</td>
                    <td class="py-0.5">: {{ \Carbon\Carbon::parse($report->committee->formation_date)->format('d F Y') }}</td>
                </tr>
            </table>
        </div>

        <!-- Bagian II: Susunan Lembaga K3 -->
        <div class="space-y-2 text-xs">
            <h3 class="font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1">II. Susunan Pengurus P2K3</h3>
            <table class="w-full">
                <tr>
                    <td class="w-48 text-slate-500 py-0.5">1. Ketua (Unsur Manajemen)</td>
                    <td class="font-bold py-0.5">: {{ $report->committee->chairman_name }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 py-0.5">2. Sekretaris (Ahli K3 Umum)</td>
                    <td class="font-bold py-0.5">: {{ $report->committee->secretary_ahli_k3_name }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 py-0.5">3. Nomor SKP Ahli K3 Kemnaker</td>
                    <td class="font-mono font-bold text-rose-700 py-0.5">: {{ $report->committee->ahli_k3_skp_number }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 py-0.5">4. Total Anggota Komite</td>
                    <td class="py-0.5">: {{ $report->committee->total_committee_members }} Orang (Manajemen & Wakil Pekerja)</td>
                </tr>
            </table>
        </div>

        <!-- Bagian III: Ringkasan Kegiatan Triwulan -->
        <div class="space-y-2 text-xs">
            <h3 class="font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1">III. Rekapitulasi Program Kerja K3 Triwulan Ini</h3>
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div>
                    <span class="text-slate-500 block">Inspeksi Keselamatan Kerja:</span>
                    <strong class="text-base text-slate-900">{{ $report->workplace_inspections_count }} Kali Kegiatan</strong>
                    <span class="text-[10px] text-slate-400 block">Area operasional, pabrik & perkantoran</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Pelatihan & Safety Induction:</span>
                    <strong class="text-base text-slate-900">{{ $report->safety_trainings_count }} Sesi Dilaksanakan</strong>
                    <span class="text-[10px] text-slate-400 block">Pencegahan kebakaran, P3K, ergonomi</span>
                </div>
            </div>
        </div>

        <!-- Bagian IV: Analisis Insiden & Kecelakaan -->
        <div class="space-y-2 text-xs">
            <h3 class="font-bold uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-1">IV. Laporan Statistik Kecelakaan Kerja & Penyakit Akibat Kerja (PAK)</h3>
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                <span class="font-bold text-slate-800 block">Keterangan / Resume Keselamatan Kerja:</span>
                <p class="text-slate-700 leading-relaxed">{{ $report->incident_summary }}</p>
                <div class="pt-2 text-[10px] text-slate-400">
                    Target K3 Perusahaan: Nihil Kecelakaan Fatal (*Zero Accident Commitment*).
                </div>
            </div>
        </div>

        <!-- Bukti Penerimaan Disnaker -->
        @if($report->disnaker_receipt_number)
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex justify-between items-center">
                <span><strong>No. Tanda Terima Pengesahan Disnaker:</strong> {{ $report->disnaker_receipt_number }}</span>
                <span class="font-mono text-[11px] font-bold uppercase text-emerald-700">TERDAFTAR RESMI</span>
            </div>
        @endif

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 text-xs text-center pt-8">
            <div class="space-y-16">
                <div class="space-y-0.5">
                    <span class="text-slate-500 block">Mengetahui,</span>
                    <strong class="text-slate-800 block">Ketua P2K3 Perusahaan</strong>
                </div>
                <div class="border-t border-slate-300 pt-1">
                    <strong class="block text-slate-900">{{ $report->committee->chairman_name }}</strong>
                    <span class="text-[10px] text-slate-400">Pimpinan Perusahaan</span>
                </div>
            </div>
            <div class="space-y-16">
                <div class="space-y-0.5">
                    <span class="text-slate-500 block">Dibuat oleh,</span>
                    <strong class="text-slate-800 block">Sekretaris P2K3 (Ahli K3 Umum)</strong>
                </div>
                <div class="border-t border-slate-300 pt-1">
                    <strong class="block text-slate-900">{{ $report->committee->secretary_ahli_k3_name }}</strong>
                    <span class="text-[10px] text-rose-600 font-mono font-bold">No. SKP: {{ $report->committee->ahli_k3_skp_number }}</span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
