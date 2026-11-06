@extends('layouts.app')

@section('title', 'Rekap Presensi Harian')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Monitoring Presensi Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Pantau presensi masuk (clock in), pulang (clock out), dan keterlambatan real-time.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('company.attendance.export', ['date' => $selectedDate]) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="download" class="w-3.5 h-3.5 text-emerald-600"></i>
                Ekspor Rekap (CSV)
            </a>
            <a href="{{ route('company.attendance.corrections') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                Koreksi Presensi
            </a>
        </div>
    </div>

    <!-- Daily Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500">Total Karyawan Aktif</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalEmployees }}</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-emerald-600">Hadir Tepat Waktu</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $presentCount }}</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-amber-600">Terlambat (> 15m)</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $lateCount }}</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-rose-600">Belum / Tidak Hadir</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $absentCount }}</div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('company.attendance.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Pilih Tanggal Presensi</label>
                <input type="date" name="date" value="{{ $selectedDate }}"
                       class="w-full rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800">
            </div>

            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Departemen</label>
                <select name="department_id" class="w-full rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800">
                    <option value="">Semua</option>
                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Late</option>
                    <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-end">
                <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Departemen</th>
                        <th class="px-5 py-3.5">Waktu Masuk</th>
                        <th class="px-5 py-3.5">Waktu Pulang</th>
                        <th class="px-5 py-3.5">Jam Efektif</th>
                        <th class="px-5 py-3.5">Status Presensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $rec->employee?->full_name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $rec->employee?->employee_number }}</div>
                            </td>
                            <td class="px-5 py-4">
                                {{ $rec->employee?->department?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 font-mono font-semibold text-slate-800">
                                {{ $rec->clock_in ?? '-' }}
                            </td>
                            <td class="px-5 py-4 font-mono font-semibold text-slate-800">
                                {{ $rec->clock_out ?? '-' }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-900">
                                {{ $rec->work_hours }} Jam
                            </td>
                            <td class="px-5 py-4">
                                @if($rec->status === 'present')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Hadir Tepat Waktu</span>
                                @elseif($rec->status === 'late')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Terlambat ({{ $rec->late_minutes }}m)</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">{{ ucfirst($rec->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="clock" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                Tidak ada rekaman presensi pada tanggal {{ date('d M Y', strtotime($selectedDate)) }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $records->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
