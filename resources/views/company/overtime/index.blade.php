@extends('layouts.app')

@section('title', 'Persetujuan Lembur (PP 35/2021)')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Surat Perintah Lembur (SPL)</h1>
            <p class="text-xs text-slate-500 mt-1">Perhitungan otomatis upah lembur sesuai rumus regulasi resmi PP No. 35 Tahun 2021 (1/173 upah sebulan).</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800">Antrean SPL Lembur Masuk</span>
            <div class="flex items-center gap-2 text-xs">
                <a href="{{ route('company.overtime.index') }}" class="px-2.5 py-1 rounded-lg {{ !request('status') ? 'bg-[#DC2626] text-white font-bold' : 'bg-slate-100 text-slate-600' }}">Semua</a>
                <a href="{{ route('company.overtime.index', ['status' => 'pending']) }}" class="px-2.5 py-1 rounded-lg {{ request('status') === 'pending' ? 'bg-[#DC2626] text-white font-bold' : 'bg-slate-100 text-slate-600' }}">Pending</a>
                <a href="{{ route('company.overtime.index', ['status' => 'approved']) }}" class="px-2.5 py-1 rounded-lg {{ request('status') === 'approved' ? 'bg-[#DC2626] text-white font-bold' : 'bg-slate-100 text-slate-600' }}">Disetujui</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Tanggal Lembur</th>
                        <th class="px-5 py-3.5">Durasi Jam</th>
                        <th class="px-5 py-3.5">Kategori Hari</th>
                        <th class="px-5 py-3.5">Tujuan & Alasan Pekerjaan</th>
                        <th class="px-5 py-3.5">Estimasi Upah PP 35</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($overtimes as $ot)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $ot->employee?->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $ot->employee?->department?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $ot->date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900 font-mono">
                                {{ $ot->hours }} Jam
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $ot->is_holiday ? 'bg-purple-50 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $ot->is_holiday ? 'Hari Libur / Istirahat' : 'Hari Kerja Biasa' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-700 max-w-xs">
                                {{ $ot->reason }}
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-emerald-600">
                                Rp {{ number_format($ot->calculated_pay, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($ot->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                @elseif($ot->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Disetujui</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-1.5 whitespace-nowrap">
                                @if($ot->status === 'pending')
                                    <form action="{{ route('company.overtime.approve', $ot->id) }}" method="POST" class="inline" onsubmit="return confirm('Setujui lembur ini dan hitung upah otomatis?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                                            Setujui
                                        </button>
                                    </form>

                                    <form action="{{ route('company.overtime.reject', $ot->id) }}" method="POST" class="inline" onsubmit="return confirm('Tolak permohonan lembur?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition">
                                            Tolak
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-12 text-center text-slate-400">Tidak ada pengajuan lembur yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($overtimes->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $overtimes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
