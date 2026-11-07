@extends('layouts.app')

@section('title', 'Detail Izin RPTKA - ' . $permit->rptka_number)

@section('content')
<div class="space-y-6">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('company.expatriates.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar Izin TKA
        </a>
    </div>

    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                    Pengesahan RPTKA Kemnaker
                </span>
                <h1 class="text-xl font-black text-slate-900 mt-1">{{ $permit->rptka_number }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">TKA: <strong>{{ $permit->employee->full_name }}</strong> (Paspor: {{ $permit->passport_number }}) | Jabatan: {{ $permit->job_position_title }}</p>
            </div>

            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $permit->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                    Status: {{ $permit->status }}
                </span>
                <div class="text-[10px] text-slate-400 mt-1">Berlaku s/d: {{ $permit->rptka_expiry_date->format('d F Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Card 1: Dokumen Legal & Imigrasi -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="file-badge" class="w-4 h-4 text-blue-600"></i>
                Dokumen & Masa Berlaku
            </h2>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nomor Paspor</span>
                    <span class="font-mono font-bold text-slate-800">{{ $permit->passport_number }}</span>
                    <span class="text-[10px] text-slate-400 block">Berlaku s/d: {{ $permit->passport_expiry_date->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Tanggal Pengesahan RPTKA</span>
                    <span class="font-semibold text-slate-700">{{ $permit->rptka_approval_date->format('d F Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Tanggal Berakhir RPTKA</span>
                    <span class="font-semibold text-rose-600">{{ $permit->rptka_expiry_date->format('d F Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Izin Tinggal Terbatas (ITAS)</span>
                    <span class="font-mono text-slate-700">{{ $permit->itas_vitas_number ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: DKPTKA PNBP -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
                Kompensasi DKPTKA (PNBP)
            </h2>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Tarif Kompensasi Bulanan</span>
                    <span class="font-mono font-black text-slate-900 text-sm">USD {{ number_format($permit->dkptka_monthly_amount_usd, 2) }}</span>
                    <span class="text-[10px] text-slate-400 block">Per bulan per jabatan kerja</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Bukti Setor Kas Negara (NTPN)</span>
                    <span class="font-mono font-semibold text-emerald-700">{{ $permit->dkptka_payment_proof ?? 'Tercatat dalam pembukuan' }}</span>
                </div>
                <div class="pt-2">
                    <p class="text-[11px] text-slate-500">
                        Dana Kompensasi Penggunaan TKA disetorkan ke Kas Negara sebagai Penerimaan Negara Bukan Pajak (PNBP) Kementerian Ketenagakerjaan.
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 3: TKI Pendamping & Alih Teknologi -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                TKI Pendamping (Pasal 11)
            </h2>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Karyawan WNI Pendamping</span>
                    <span class="font-bold text-slate-800">{{ $permit->companionEmployee->full_name ?? 'Belum Ditunjuk' }}</span>
                    @if($permit->companionEmployee)
                        <span class="text-[10px] text-slate-400 block">{{ $permit->companionEmployee->department->name ?? 'Dept' }} - {{ $permit->companionEmployee->position->title ?? 'Staff' }}</span>
                    @endif
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Rencana Alih Teknologi & Pengetahuan</span>
                    <p class="text-slate-600 mt-0.5 text-[11px]">{{ $permit->technology_transfer_plan ?? '-' }}</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Status Update Form -->
    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Perbarui Status Operasional Izin</h3>
        <form action="{{ route('company.expatriates.update-status', $permit->id) }}" method="POST" class="flex items-center gap-3">
            @csrf
            <select name="status" class="text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white">
                <option value="active" {{ $permit->status === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="expiring_soon" {{ $permit->status === 'expiring_soon' ? 'selected' : '' }}>Mendekati Jatuh Tempo</option>
                <option value="expired" {{ $permit->status === 'expired' ? 'selected' : '' }}>Kadaluarsa (Perlu Perpanjangan)</option>
                <option value="repatriated" {{ $permit->status === 'repatriated' ? 'selected' : '' }}>Repatriasi / Selesai Kontrak (EPO)</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition">
                Simpan Status
            </button>
        </form>
    </div>

</div>
@endsection
