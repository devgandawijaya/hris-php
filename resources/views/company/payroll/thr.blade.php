@extends('layouts.app')

@section('title', 'Kalkulasi THR Keagamaan (Permenaker No. 6/2016)')

@section('content')
<div class="space-y-6" x-data="{ openCalcModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tunjangan Hari Raya (THR)</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-[#DC2626] border border-red-200">
                    Permenaker No. 6/2016
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Perhitungan otomatis hak THR karyawan tetap & kontrak berdasarkan masa kerja dan upah sebulan.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('company.thr.index') }}" method="GET" class="flex items-center gap-2">
                <select name="year" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    @for($y = date('Y') + 1; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>
            <button @click="openCalcModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                Hitung THR Otomatis
            </button>
        </div>
    </div>

    <!-- Statutory Explainer Banner -->
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
        <div>
            <strong class="font-bold">Ketentuan Regulasi Permenaker No. 6/2016:</strong>
            <p class="mt-0.5 text-amber-800 leading-relaxed">
                Pekerja dengan masa kerja <strong>&ge; 12 bulan</strong> memperoleh 1 bulan upah (Gaji Pokok + Tunjangan Tetap). Pekerja dengan masa kerja <strong>1 s.d. &lt; 12 bulan</strong> berhak secara proporsional dengan rumus: <code>(Masa Kerja / 12) &times; Upah Sebulan</code>. Pembayaran wajib diserahkan paling lambat 7 hari sebelum hari raya keagamaan.
            </p>
        </div>
    </div>

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Penerima Berhak</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_recipients']) }} Karyawan</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Masa kerja minimal 1 bulan</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Anggaran THR</div>
                <div class="text-2xl font-black text-[#DC2626] mt-1">Rp {{ number_format($stats['total_thr_amount'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Kewajiban THR Tahun {{ $selectedYear }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-50 text-[#DC2626] flex items-center justify-center">
                <i data-lucide="gift" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Realisasi Dibayarkan</div>
                <div class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Status Lunas (Paid)</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Table Records -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Daftar Penerima THR Keagamaan Tahun {{ $selectedYear }}</h2>
            <span class="text-xs text-slate-500">Total: {{ $records->count() }} Data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Tanggal Masuk</th>
                        <th class="px-5 py-3.5">Masa Kerja</th>
                        <th class="px-5 py-3.5 text-right">Upah Pokok + Tunjangan Tetap</th>
                        <th class="px-5 py-3.5">Formula Perhitungan</th>
                        <th class="px-5 py-3.5 text-right font-black text-[#DC2626]">Besaran THR</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $rec->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $rec->employee->employee_number }} • {{ $rec->employee->department?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $rec->employee->join_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $rec->months_of_service }} Bulan
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-slate-900">
                                Rp {{ number_format($rec->base_wage, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($rec->months_of_service >= 12)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        1 Bulan Upah Penuh (&ge; 12 bln)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Pro-rata ({{ $rec->months_of_service }}/12)
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right font-black text-[#DC2626]">
                                Rp {{ number_format($rec->thr_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($rec->status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Disetujui
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($rec->status !== 'paid')
                                    <form action="{{ route('company.thr.pay', $rec->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-[11px] hover:bg-emerald-700 transition">
                                            Tandai Lunas
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium">Telah Dibayar</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="gift" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Belum ada perhitungan THR untuk tahun {{ $selectedYear }}.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol <strong>"Hitung THR Otomatis"</strong> di atas untuk memproses hak THR seluruh pekerja.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Kalkulasi THR -->
    <div x-show="openCalcModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openCalcModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Jalankan Kalkulasi THR Keagamaan</h3>
                <button @click="openCalcModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.thr.generate') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Pembukuan THR <span class="text-red-500">*</span></label>
                    <input type="number" name="year" required min="2020" max="2050" value="{{ $selectedYear }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Cut-Off / Pembayaran THR <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" required value="{{ now()->toDateString() }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-[11px] text-slate-400 mt-1">Masa kerja akan dihitung otomatis dari tanggal masuk sampai tanggal ini.</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openCalcModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Jalankan Kalkulasi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

