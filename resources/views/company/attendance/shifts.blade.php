@extends('layouts.app')

@section('title', 'Shift & Kalender Kerja')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'shifts' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Shift & Kalender Kerja</h1>
            <p class="text-xs text-slate-500 mt-1">Konfigurasi jadwal jam kerja harian dan hari libur nasional / cuti bersama SKB 3 Menteri.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('company.attendance.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Kembali ke Presensi
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-px">
        <button type="button" @click="activeTab = 'shifts'"
                :class="activeTab === 'shifts' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="clock" class="w-4 h-4"></i>
            Daftar Shift Kerja ({{ $shifts->count() }})
        </button>

        <button type="button" @click="activeTab = 'holidays'"
                :class="activeTab === 'holidays' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            Hari Libur Nasional & Cuti Bersama ({{ $holidays->count() }})
        </button>
    </div>

    <!-- TAB 1: SHIFTS -->
    <div x-show="activeTab === 'shifts'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-red-600"></i>
                Tambah Jadwal Shift Baru
            </h2>

            <form action="{{ route('company.attendance.shifts.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                @csrf
                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Shift *</label>
                    <input type="text" name="name" required placeholder="Shift Pagi / Reguler" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Masuk (Format 24 Jam) *</label>
                    <input type="time" name="start_time" value="08:30" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Pulang *</label>
                    <input type="time" name="end_time" value="17:30" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 font-mono">
                </div>
                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Shift
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Nama Shift</th>
                        <th class="px-5 py-3">Jam Masuk</th>
                        <th class="px-5 py-3">Jam Pulang</th>
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shifts as $s)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-slate-900">{{ $s->name }}</td>
                            <td class="px-5 py-3.5 font-mono font-semibold text-slate-800">{{ $s->start_time }} WIB</td>
                            <td class="px-5 py-3.5 font-mono font-semibold text-slate-800">{{ $s->end_time }} WIB</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $s->is_night_shift ? 'bg-purple-50 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $s->is_night_shift ? 'Shift Malam' : 'Shift Normal' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('company.attendance.shifts.delete', $s->id) }}" method="POST" onsubmit="return confirm('Hapus shift ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada jadwal shift.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: HOLIDAYS -->
    <div x-show="activeTab === 'holidays'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-red-600"></i>
                Tambah Hari Libur Nasional / Cuti Bersama
            </h2>

            <form action="{{ route('company.attendance.holidays.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                @csrf
                <div class="sm:col-span-5">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Hari Libur *</label>
                    <input type="text" name="name" required placeholder="Hari Raya Idul Fitri 1447 H" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Libur *</label>
                    <input type="date" name="date" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800">
                </div>
                <div class="sm:col-span-3 flex items-end">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Hari Libur
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Nama Hari Libur</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($holidays as $h)
                        <tr>
                            <td class="px-5 py-3.5 font-bold text-slate-900 font-mono">{{ $h->date->format('d M Y') }}</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $h->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $h->is_joint_leave ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $h->is_joint_leave ? 'Cuti Bersama' : 'Libur Nasional Resmi' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('company.attendance.holidays.delete', $h->id) }}" method="POST" onsubmit="return confirm('Hapus hari libur ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada catatan hari libur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
