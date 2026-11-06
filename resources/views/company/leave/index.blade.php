@extends('layouts.app')

@section('title', 'Manajemen Cuti & Izin')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'requests' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Cuti & Izin Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Persetujuan permohonan cuti berjenjang, monitoring saldo cuti tahunan, dan master tipe cuti.</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-px">
        <button type="button" @click="activeTab = 'requests'"
                :class="activeTab === 'requests' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="calendar-off" class="w-4 h-4"></i>
            Daftar Permohonan Cuti
        </button>

        <button type="button" @click="activeTab = 'types'"
                :class="activeTab === 'types' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="settings" class="w-4 h-4"></i>
            Master Tipe Cuti ({{ $types->count() }})
        </button>
    </div>

    <!-- TAB 1: REQUESTS -->
    <div x-show="activeTab === 'requests'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-800">Antrean Permohonan Cuti Masuk</span>
                <div class="flex items-center gap-2 text-xs">
                    <a href="{{ route('company.leave.index') }}" class="px-2.5 py-1 rounded-lg {{ !request('status') ? 'bg-[#DC2626] text-white font-bold' : 'bg-slate-100 text-slate-600' }}">Semua</a>
                    <a href="{{ route('company.leave.index', ['status' => 'pending']) }}" class="px-2.5 py-1 rounded-lg {{ request('status') === 'pending' ? 'bg-[#DC2626] text-white font-bold' : 'bg-slate-100 text-slate-600' }}">Pending</a>
                    <a href="{{ route('company.leave.index', ['status' => 'approved']) }}" class="px-2.5 py-1 rounded-lg {{ request('status') === 'approved' ? 'bg-[#DC2626] text-white font-bold' : 'bg-slate-100 text-slate-600' }}">Disetujui</a>
                </div>
            </div>

            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Tipe Cuti</th>
                        <th class="px-5 py-3.5">Rentang Tanggal</th>
                        <th class="px-5 py-3.5">Jumlah Hari</th>
                        <th class="px-5 py-3.5">Alasan Cuti</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $req->employee?->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $req->employee?->department?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $req->leaveType?->name }}
                            </td>
                            <td class="px-5 py-4 font-mono text-[11px]">
                                {{ $req->start_date->format('d M Y') }} s/d {{ $req->end_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $req->total_days }} Hari Kerja
                            </td>
                            <td class="px-5 py-4 text-slate-600 max-w-xs">
                                {{ $req->reason }}
                            </td>
                            <td class="px-5 py-4">
                                @if($req->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                @elseif($req->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Approved</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Rejected</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-1.5 whitespace-nowrap">
                                @if($req->status === 'pending')
                                    <form action="{{ route('company.leave.approve', $req->id) }}" method="POST" class="inline" onsubmit="return confirm('Setujui permohonan cuti ini?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                                            Setujui
                                        </button>
                                    </form>

                                    <form action="{{ route('company.leave.reject', $req->id) }}" method="POST" class="inline" onsubmit="return prompt('Alasan penolakan cuti:') ? true : false;">
                                        @csrf
                                        <input type="hidden" name="rejection_reason" value="Kebutuhan operasional proyek penting">
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
                        <tr><td colspan="7" class="py-12 text-center text-slate-400">Tidak ada pengajuan cuti yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- TAB 2: TYPES -->
    <div x-show="activeTab === 'types'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-red-600"></i>
                Tambah Jenis Cuti Baru
            </h2>

            <form action="{{ route('company.leave.types.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                @csrf
                <div class="sm:col-span-5">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Cuti *</label>
                    <input type="text" name="name" required placeholder="Cuti Menikah" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Unik *</label>
                    <input type="text" name="code" required placeholder="ML-01" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kuota Hari *</label>
                    <input type="number" name="days_allowed" value="3" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Tipe
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Nama Cuti</th>
                        <th class="px-5 py-3">Kuota Hari Resmi</th>
                        <th class="px-5 py-3">Status Upah</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($types as $t)
                        <tr>
                            <td class="px-5 py-3.5 font-mono text-slate-500 font-bold">{{ $t->code }}</td>
                            <td class="px-5 py-3.5 font-bold text-slate-900">{{ $t->name }}</td>
                            <td class="px-5 py-3.5 font-mono">{{ $t->days_allowed }} Hari</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $t->is_paid ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $t->is_paid ? 'Berbayar (Paid)' : 'Unpaid' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('company.leave.types.delete', $t->id) }}" method="POST" onsubmit="return confirm('Hapus tipe cuti ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada tipe cuti.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

