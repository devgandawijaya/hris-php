@extends('layouts.app')

@section('title', 'e-Bupot 21/26 & Formulir 1721-A1 (PER-2/PJ/2024)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">e-Bupot 21/26 & Formulir 1721-A1</h1>
            <p class="text-sm text-slate-500 mt-1">Penyusunan Bukti Potong PPh 21 Tahunan (1721-A1) resmi dan ekspor skema impor masal e-Bupot 21/26 DJP Online.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('company.taxes.ebupot.export', ['year' => $taxYear]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Ekspor CSV e-Bupot 21/26 (DJP Online)
            </a>
            <form method="POST" action="{{ route('company.taxes.ebupot.generate') }}">
                @csrf
                <input type="hidden" name="year" value="{{ $taxYear }}">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Generate / Sinkronkan 1721-A1 (Th. {{ $taxYear }})
                </button>
            </form>
        </div>
    </div>

    <!-- Year Filter Bar & Stats -->
    <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tahun Pajak:</span>
            <div class="flex gap-1">
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <a href="{{ route('company.taxes.ebupot.index', ['year' => $y]) }}" class="px-3 py-1 rounded text-xs font-bold {{ $taxYear == $y ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                        {{ $y }}
                    </a>
                @endfor
            </div>
        </div>
        <div class="text-xs text-slate-400">
            Standar Peraturan Direktur Jenderal Pajak No. PER-2/PJ/2024
        </div>
    </div>

    <!-- KPI Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Penghasilan Bruto</p>
            <h3 class="text-xl font-bold text-slate-800 mt-1 font-mono">Rp {{ number_format($stats['total_gross'], 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 mt-1">Akumulasi gaji, bonus & THR</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total PPh 21 Terutang</p>
            <h3 class="text-xl font-bold text-red-600 mt-1 font-mono">Rp {{ number_format($stats['total_pph21'], 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 mt-1">Setahun masa pajak {{ $taxYear }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Formulir Terbit</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['generated_count'] }} Dokumen</h3>
            <p class="text-xs text-slate-400 mt-1">Siap unduh oleh pegawai di ESS</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cakupan Karyawan</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['coverage_pct'] }}%</h3>
            <p class="text-xs text-slate-400 mt-1">Dari total karyawan perusahaan</p>
        </div>
    </div>

    <!-- 1721-A1 Records Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-800">Daftar Bukti Pemotongan PPh Pasal 21 (Formulir 1721-A1)</h2>
            <span class="text-xs text-slate-400">Pegawai Tetap & Penerima Pensiun Berkala</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Nomor Bukti Potong</th>
                        <th class="px-5 py-3">Penerima Penghasilan</th>
                        <th class="px-5 py-3">Identitas Pajak (NPWP/NIK)</th>
                        <th class="px-5 py-3 text-center">Status PTKP</th>
                        <th class="px-5 py-3 text-right">Penghasilan Bruto</th>
                        <th class="px-5 py-3 text-right">PPh 21 Terutang</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3 font-mono font-bold text-red-600 text-xs">
                                {{ $rec->nomor_bupot }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-800">{{ $rec->penerima_nama }}</div>
                                <div class="text-xs text-slate-400">{{ $rec->employee->department ? $rec->employee->department->name : '-' }}</div>
                            </td>
                            <td class="px-5 py-3 text-xs font-mono">
                                <div>NPWP: {{ $rec->penerima_npwp ?: '-' }}</div>
                                <div class="text-slate-400">NIK: {{ $rec->penerima_nik }}</div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700">{{ $rec->status_ptkp }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono">
                                Rp {{ number_format($rec->penghasilan_bruto, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono font-bold text-slate-800">
                                Rp {{ number_format($rec->pph21_terutang_setahun, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <a href="{{ route('company.taxes.ebupot.form1721a1', $rec) }}" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Lihat / Cetak 1721-A1
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                Belum ada formulir 1721-A1 untuk tahun pajak {{ $taxYear }}. Klik tombol "Generate / Sinkronkan" di atas untuk menyusun secara otomatis dari rekonsiliasi payroll.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
