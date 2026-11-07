@extends('layouts.app')

@section('title', 'Detail Laporan WLTK - ' . $report->report_number)

@section('content')
<div class="space-y-6">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('company.wltk.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar WLTK
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('company.wltk.export-json', $report->id) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 transition inline-flex items-center gap-1.5">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Ekspor JSON WLKP Kemnaker
            </a>
            <a href="{{ route('company.wltk.export-csv', $report->id) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 transition inline-flex items-center gap-1.5">
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                Ekspor CSV Inspeksi
            </a>
        </div>
    </div>

    <!-- Main Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                        {{ strtoupper($report->report_period) }}
                    </span>
                    <span class="text-xs text-slate-400">• UU No. 7/1981</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 mt-1">{{ $report->report_number }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Tahun Pelaporan: {{ $report->reporting_year }} | NIB/WLKP: {{ $report->company_registration_number }}</p>
            </div>

            <div class="text-right">
                @if($report->status === 'submitted_kemnaker')
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        Terdaftar di Kemnaker
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1">
                        No. Bukti: <span class="font-mono font-semibold">{{ $report->disnaker_receipt_number }}</span>
                    </div>
                    <div class="text-[10px] text-slate-400">
                        Masa Berlaku s/d: {{ $report->valid_until ? $report->valid_until->format('d F Y') : '-' }}
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        Status: Draft Sistem
                    </div>
                    <div class="text-[10px] text-slate-400 mt-1">Belum diserahkan ke Kemnaker</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Demographic Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Card 1: Profil Perusahaan -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-blue-600"></i>
                Identitas Badan Usaha
            </h2>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Badan Usaha</span>
                    <span class="font-bold text-slate-800">{{ $report->company->legal_name ?? $report->company->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">NPWP Perusahaan</span>
                    <span class="font-mono text-slate-700">{{ $report->company->npwp ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Kode KBLI (Klasifikasi Baku)</span>
                    <span class="font-mono font-bold text-slate-700">{{ $report->kbli_code }} (Aktivitas Pemrograman & TI)</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Alamat & Domisili</span>
                    <span class="text-slate-700">{{ $report->company->address ?? '-' }}, {{ $report->company->city }}, {{ $report->company->province }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Komposisi Tenaga Kerja -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-emerald-600"></i>
                Komposisi Tenaga Kerja
            </h2>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Total Tenaga Kerja Aktif:</span>
                    <span class="font-black text-slate-900">{{ number_format($report->total_workers) }} Orang</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">WNI (Pria / Wanita):</span>
                    <span class="font-semibold text-slate-700">{{ $report->total_wni_male }} ♂ / {{ $report->total_wni_female }} ♀</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">TKA / WNA (Pria / Wanita):</span>
                    <span class="font-semibold text-slate-700">{{ $report->total_wna_male }} ♂ / {{ $report->total_wna_female }} ♀</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Perjanjian Tetap (PKWTT):</span>
                    <span class="font-semibold text-blue-700">{{ $report->total_pkwtt }} Orang</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Perjanjian Kontrak (PKWT):</span>
                    <span class="font-semibold text-amber-700">{{ $report->total_pkwt }} Orang</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Pekerja Penyandang Disabilitas:</span>
                    <span class="font-bold text-emerald-700">{{ $report->total_disability_workers }} Orang</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Upah & Jaminan Sosial -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-purple-600"></i>
                Pengupahan & Fasilitas K3
            </h2>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Upah Pokok Terendah:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($report->lowest_wage, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Upah Pokok Tertinggi:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($report->highest_wage, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">BPJS Ketenagakerjaan:</span>
                    <span class="font-bold {{ $report->bpjs_tk_registered ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $report->bpjs_tk_registered ? 'TERDAFTAR' : 'BELUM' }}
                    </span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">BPJS Kesehatan:</span>
                    <span class="font-bold {{ $report->bpjs_kes_registered ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $report->bpjs_kes_registered ? 'TERDAFTAR' : 'BELUM' }}
                    </span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                    <span class="text-slate-500">Peraturan Perusahaan (PP/PKB):</span>
                    <span class="font-bold {{ $report->has_pp_or_pkb ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $report->has_pp_or_pkb ? 'TERVERIFIKASI' : 'BELUM ADA' }}
                    </span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Forum LKS Bipartit:</span>
                    <span class="font-bold {{ $report->has_lks_bipartit ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $report->has_lks_bipartit ? 'AKTIF' : 'BELUM DIBENTUK' }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Submission Receipt Form (if draft) -->
    @if($report->status === 'draft')
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 p-6">
            <h3 class="text-sm font-bold text-blue-900 mb-1">Catat Tanda Terima Pengesahan WLKP Kemnaker / Disnaker</h3>
            <p class="text-xs text-blue-700 mb-4">
                Setelah mengunggah berkas JSON atau menyerahkan berkas laporan WLTK ke Kementerian Ketenagakerjaan atau Dinas Tenaga Kerja setempat, masukkan nomor bukti pendaftaran di bawah ini untuk mengunci status laporan dan mengaktifkan masa berlaku sah 1 (satu) tahun.
            </p>

            <form action="{{ route('company.wltk.submit-receipt', $report->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-blue-900 mb-1">Nomor Tanda Terima Disnaker / WLKP</label>
                    <input type="text" name="disnaker_receipt_number" placeholder="Contoh: WLKP-REG-2026-98124" class="w-full text-xs rounded-xl border border-blue-300 px-3 py-2 bg-white" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-blue-900 mb-1">Tanggal Resmi Disubmit</label>
                    <input type="date" name="submission_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-blue-300 px-3 py-2 bg-white" required>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white hover:bg-blue-700 transition">
                        Konfirmasi & Kunci Laporan Sah
                    </button>
                </div>
            </form>
        </div>
    @endif

</div>
@endsection
