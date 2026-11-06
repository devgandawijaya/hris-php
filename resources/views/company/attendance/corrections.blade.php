@extends('layouts.app')

@section('title', 'Persetujuan Koreksi Presensi')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Koreksi Presensi Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Tinjau dan setujui permohonan perbaikan jam clock in / clock out yang lupa dicatat oleh karyawan.</p>
        </div>
        <div>
            <a href="{{ route('company.attendance.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Kembali ke Presensi Harian
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Tanggal Koreksi</th>
                        <th class="px-5 py-3.5">Jam Masuk Dikoreksi</th>
                        <th class="px-5 py-3.5">Jam Pulang Dikoreksi</th>
                        <th class="px-5 py-3.5">Alasan Karyawan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($corrections as $cor)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $cor->employee?->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $cor->employee?->department?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $cor->date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-emerald-600">
                                {{ $cor->corrected_clock_in ?? '-' }}
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-blue-600">
                                {{ $cor->corrected_clock_out ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-slate-700 max-w-xs">
                                {{ $cor->reason }}
                            </td>
                            <td class="px-5 py-4">
                                @if($cor->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pending Review</span>
                                @elseif($cor->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Disetujui</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-1.5 whitespace-nowrap">
                                @if($cor->status === 'pending')
                                    <form action="{{ route('company.attendance.corrections.approve', $cor->id) }}" method="POST" class="inline" onsubmit="return confirm('Setujui perbaikan presensi ini?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                                            Setujui
                                        </button>
                                    </form>

                                    <!-- Reject Dialog Form -->
                                    <form action="{{ route('company.attendance.corrections.reject', $cor->id) }}" method="POST" class="inline" onsubmit="return prompt('Masukkan alasan penolakan:') ? true : false;">
                                        @csrf
                                        <input type="hidden" name="rejection_reason" value="Tidak sesuai dengan bukti aktivitas">
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
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="check-circle-2" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                Tidak ada permohonan koreksi presensi yang tertunda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($corrections->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $corrections->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
