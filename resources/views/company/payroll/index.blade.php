@extends('layouts.app')

@section('title', 'Periode Penggajian (Payroll)')

@section('content')
<div class="space-y-6" x-data="{ openCreateModal: false }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Penggajian & Kepatuhan Pajak</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola siklus payroll bulanan, integrasi lembur PP 35/2021, BPJS 5 program, dan PPh 21 TER.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('company.components.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-sm">
                <i data-lucide="sliders" class="w-4 h-4 text-slate-500"></i>
                Komponen Gaji
            </a>
            <button @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Buat Periode Baru
            </button>
        </div>
    </div>

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Periode Payroll</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalPeriods) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Siklus tercatat dalam sistem</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="calendar" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Gaji Terbayar</div>
                <div class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalPaidOut, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Akumulasi Take Home Pay dibayarkan</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Periode Berjalan</div>
                <div class="text-lg font-black text-slate-900 mt-1 truncate max-w-[200px]">
                    {{ $activeRun ? $activeRun->name : 'Tidak Ada' }}
                </div>
                <div class="text-[11px] font-semibold text-amber-600 mt-0.5">
                    {{ $activeRun ? 'Status: ' . strtoupper($activeRun->status) : 'Semua periode telah selesai' }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="play-circle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <h2 class="text-base font-bold text-slate-900">Daftar Periode Penggajian</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('company.payroll.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
                <a href="{{ route('company.payroll.index', ['status' => 'draft']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'draft' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Draft</a>
                <a href="{{ route('company.payroll.index', ['status' => 'processed']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'processed' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Processed</a>
                <a href="{{ route('company.payroll.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'approved' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Approved</a>
                <a href="{{ route('company.payroll.index', ['status' => 'paid']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'paid' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Paid</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Nama Periode</th>
                        <th class="px-5 py-3.5">Rentang Tanggal</th>
                        <th class="px-5 py-3.5">Tanggal Pembayaran</th>
                        <th class="px-5 py-3.5">Jumlah Karyawan</th>
                        <th class="px-5 py-3.5">Total Take Home Pay</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($periods as $period)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-bold text-slate-900">
                                <a href="{{ route('company.payroll.show', $period->id) }}" class="hover:text-[#DC2626] transition flex items-center gap-2">
                                    <i data-lucide="receipt" class="w-4 h-4 text-slate-400"></i>
                                    {{ $period->name }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $period->start_date->format('d M Y') }} — {{ $period->end_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-slate-600 font-medium">
                                {{ $period->payment_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-slate-700 font-semibold">
                                {{ $period->payrolls_count }} Karyawan
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900">
                                Rp {{ number_format($period->total_take_home_pay, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($period->status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Lunas (Paid)
                                    </span>
                                @elseif($period->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Disetujui (Approved)
                                    </span>
                                @elseif($period->status === 'processed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Terkalkulasi (Processed)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('company.payroll.show', $period->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition">
                                    <span>Kelola</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Belum ada periode penggajian.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "Buat Periode Baru" di pojok kanan atas untuk memulai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periods->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $periods->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Create Periode Baru -->
    <div x-show="openCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openCreateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Buat Periode Penggajian Baru</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tentukan nama periode dan rentang tanggal kerja.</p>
                </div>
                <button @click="openCreateModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.payroll.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Periode Payroll <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Gaji Bulan Januari 2026"
                           value="Gaji Bulan {{ now()->isoFormat('MMMM Y') }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai (Cut-Off Awal) <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required value="{{ now()->startOfMonth()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai (Cut-Off Akhir) <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required value="{{ now()->endOfMonth()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pembayaran (Pay Date) <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" required value="{{ now()->endOfMonth()->toDateString() }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-[11px] text-slate-400 mt-1">Tanggal efektif pencairan dana ke rekening karyawan.</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                        Simpan & Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

