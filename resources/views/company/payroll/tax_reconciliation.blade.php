@extends('layouts.app')

@section('title', 'Rekonsiliasi Pajak PPh 21 & Formulir 1721-A1')

@section('content')
<div class="space-y-6" x-data="{ openReconcileModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Rekonsiliasi Tahunan PPh 21</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">
                    PMK 168/2023 & Formulir 1721-A1
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Penyesuaian perhitungan PPh 21 Masa Pajak Terakhir (Desember) per UU HPP & pencetakan bukti potong 1721-A1.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('company.tax.index') }}" method="GET" class="flex items-center gap-2">
                <select name="year" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-white font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    @for($y = date('Y') + 1; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Tahun Pajak {{ $y }}</option>
                    @endfor
                </select>
            </form>
            <button @click="openReconcileModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Jalankan Rekonsiliasi Pajak
            </button>
        </div>
    </div>

    <!-- Explainer Alert -->
    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-start gap-3">
        <i data-lucide="shield-alert" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5"></i>
        <div>
            <strong class="font-bold">Mekanisme PPh 21 Bulanan TER vs Masa Pajak Terakhir (Desember):</strong>
            <p class="mt-0.5 text-blue-800 leading-relaxed">
                Pemotongan Masa Januari s.d. November menggunakan Tarif Efektif Rata-Rata (TER) PP 58/2023. Pada Masa Pajak Terakhir (Desember), seluruh penghasilan setahun dihitung ulang menggunakan tarif progresif Pasal 17 Ayat (1) Huruf a UU HPP setelah dikurangi Biaya Jabatan (5% max Rp 6.000.000/tahun), iuran pensiun BPJS, dan PTKP. Selisihnya merupakan PPh 21 Masa Terakhir.
            </p>
        </div>
    </div>

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Karyawan Direkonsiliasi</div>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total_employees']) }} Orang</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Tahun Pajak {{ $selectedYear }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Penghasilan Bruto Setahun</div>
            <div class="text-lg font-black text-slate-900 mt-1 truncate">Rp {{ number_format($stats['total_gross_annual'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Gaji + Tunjangan + THR</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PPh 21 Terutang Setahun</div>
            <div class="text-lg font-black text-slate-900 mt-1 truncate">Rp {{ number_format($stats['total_annual_tax_due'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Perhitungan Pasal 17 HPP</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Penyesuaian Masa Terakhir</div>
            <div class="text-lg font-black text-amber-600 mt-1 truncate">Rp {{ number_format($stats['total_december_tax'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">PPh 21 Setahun - TER Terpotong</div>
        </div>
    </div>

    <!-- Reconciled Employees Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Hasil Rekonsiliasi Formulir 1721-A1 Tahun {{ $selectedYear }}</h2>
            <span class="text-xs text-slate-500">Masa Pajak Terakhir</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-4 py-3.5">Karyawan & Status PTKP</th>
                        <th class="px-4 py-3.5 text-right">Penghasilan Bruto</th>
                        <th class="px-4 py-3.5 text-right">Biaya Jabatan (5%)</th>
                        <th class="px-4 py-3.5 text-right">Iuran Pensiun / JHT</th>
                        <th class="px-4 py-3.5 text-right">Penghasilan Neto</th>
                        <th class="px-4 py-3.5 text-right">PKP Dibulatkan</th>
                        <th class="px-4 py-3.5 text-right">PPh 21 Setahun</th>
                        <th class="px-4 py-3.5 text-right">TER Telah Dipotong</th>
                        <th class="px-4 py-3.5 text-right font-black text-amber-700">Masa Terakhir (Des)</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $rec->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $rec->employee->employee_number }} • PTKP: <strong class="text-slate-700">{{ $rec->employee->tax_ptkp_status ?? 'TK/0' }}</strong></div>
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-slate-900">
                                {{ number_format($rec->gross_income_annual, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-slate-600">
                                -{{ number_format($rec->biaya_jabatan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-slate-600">
                                -{{ number_format($rec->bpjs_deductions_annual, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-slate-800">
                                {{ number_format($rec->net_income_annual, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-slate-900">
                                {{ number_format($rec->pkp, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-blue-700">
                                {{ number_format($rec->pph21_annual_due, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-slate-600">
                                {{ number_format($rec->pph21_ter_paid_total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-amber-700">
                                Rp {{ number_format($rec->pph21_december_tax, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <a href="{{ route('company.tax.show', $rec->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-[11px] transition" title="Lihat Formulir 1721-A1">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-500"></i>
                                    <span>1721-A1</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-slate-400">
                                <i data-lucide="file-spreadsheet" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Belum ada hasil rekonsiliasi tahunan untuk Tahun Pajak {{ $selectedYear }}.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol <strong>"Jalankan Rekonsiliasi Pajak"</strong> untuk memproses perhitungan Masa Pajak Terakhir.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Jalankan Rekonsiliasi -->
    <div x-show="openReconcileModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openReconcileModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Rekonsiliasi Pajak PPh 21 Setahun</h3>
                <button @click="openReconcileModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.tax.reconcile') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Pajak <span class="text-red-500">*</span></label>
                    <input type="number" name="year" required min="2020" max="2050" value="{{ $selectedYear }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <p class="text-[11px] text-slate-400 mt-1">Sistem akan mengagregasi seluruh payroll dan THR pada tahun bersangkutan.</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openReconcileModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Mulai Rekonsiliasi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
