@extends('layouts.app')

@section('title', 'Detail Sertifikasi Klaim JKP - ' . $certification->certificate_number)

@section('content')
<div class="space-y-6">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('company.jkp.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar Klaim JKP
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('company.jkp.print', $certification->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition inline-flex items-center gap-1.5 shadow-sm">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                Cetak Surat Rekomendasi JKP
            </a>
        </div>
    </div>

    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Sertifikasi Manfaat PP No. 37/2021
                </span>
                <h1 class="text-xl font-black text-slate-900 mt-1">{{ $certification->certificate_number }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pekerja: <strong>{{ $certification->employee->full_name }}</strong> (NIK: {{ $certification->employee->nik_ktp }}) | No. KPJ: {{ $certification->bpjs_tk_card_number }}</p>
            </div>

            <div class="text-right">
                @if($certification->eligible_for_jkp)
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        Memenuhi Syarat Manfaat JKP
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                        Tidak Memenuhi Syarat Masa Iur
                    </div>
                @endif
                <div class="text-[10px] text-slate-400 mt-1">Tanggal PHK: {{ $certification->termination_date->format('d F Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Benefits Breakdown Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Card 1: Dasar Penetapan Upah & Batas Cap -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="calculator" class="w-4 h-4 text-blue-600"></i>
                Dasar Upah & Masa Iur
            </h2>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Upah Pokok Karyawan</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($certification->employee->basic_salary, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Batas Atas Upah Patokan JKP (Pasal 21)</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($certification->reference_salary_cap, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Masa Iur Berturut-turut</span>
                    <span class="font-semibold text-slate-700">{{ $certification->consecutive_paid_months }} Bulan</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Total Iur dalam 24 Bulan</span>
                    <span class="font-semibold text-slate-700">{{ $certification->total_paid_months_24m }} Bulan</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Kalkulasi Manfaat Tunai -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="banknote" class="w-4 h-4 text-emerald-600"></i>
                Kalkulasi Manfaat Tunai JKP
            </h2>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Bulan 1 s/d 3 (45% Upah Patokan)</span>
                    <span class="font-mono font-bold text-emerald-700">Rp {{ number_format($certification->month_1_to_3_benefit_monthly, 0, ',', '.') }} / bulan</span>
                    <span class="text-[10px] text-slate-400 block">Subtotal: Rp {{ number_format($certification->month_1_to_3_benefit_monthly * 3, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Bulan 4 s/d 6 (25% Upah Patokan)</span>
                    <span class="font-mono font-bold text-emerald-700">Rp {{ number_format($certification->month_4_to_6_benefit_monthly, 0, ',', '.') }} / bulan</span>
                    <span class="text-[10px] text-slate-400 block">Subtotal: Rp {{ number_format($certification->month_4_to_6_benefit_monthly * 3, 0, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Total Potensi Manfaat Uang Tunai</span>
                    <span class="text-lg font-black text-slate-900 font-mono">Rp {{ number_format($certification->total_potential_cash_benefit, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Integrasi SiapKerja Kemnaker -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="globe" class="w-4 h-4 text-purple-600"></i>
                Portal SiapKerja Kemnaker
            </h2>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Kode Pelacakan SiapKerja</span>
                    <span class="font-mono font-bold text-purple-800 text-sm">{{ $certification->siapkerja_tracking_code }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Status Pelaporan Disnaker</span>
                    <span class="font-semibold text-slate-700">{{ strtoupper($certification->disnaker_reporting_status) }}</span>
                </div>
                <div class="pt-1">
                    <p class="text-[11px] text-slate-500">
                        Pekerja dapat mengakses portal <strong>siapkerja.kemnaker.go.id</strong> untuk mengklaim manfaat tunai bulanan, konseling pasar kerja, dan pelatihan kerja vokasi.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Recommendation Box -->
    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Rekomendasi Resmi Pemberi Kerja</h3>
        <p class="text-xs text-slate-600 italic">
            "{{ $certification->company_recommendation_letter }}"
        </p>
    </div>

</div>
@endsection
