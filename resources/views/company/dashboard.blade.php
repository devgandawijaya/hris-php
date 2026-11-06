@extends('layouts.app')

@section('title', 'Company Dashboard: ' . ($company->name ?? 'HRIS'))

@section('content')
<div class="space-y-6">

    <!-- Top Greeting Banner -->
    <div class="bg-gradient-to-r from-red-600 via-red-700 to-red-800 rounded-2xl p-6 sm:p-8 text-white shadow-lg shadow-red-600/10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold backdrop-blur-sm">
                    <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                    {{ $company->name ?? 'Perusahaan' }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-black mt-2 tracking-tight">
                    Selamat Datang, {{ auth()->user()->name }}!
                </h1>
                <p class="text-xs sm:text-sm text-red-100 mt-1 max-w-xl">
                    Sistem mengawasi operasional harian presensi geofencing, alur pengajuan cuti berjenjang, dan kalkulasi PPh 21 TER 2024.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('ess.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-white text-red-700 hover:bg-red-50 text-xs font-bold transition shadow-sm inline-flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    Buka Portal ESS
                </a>
            </div>
        </div>
    </div>

    <!-- Stat Cards 4-Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500">Total Karyawan</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalEmployees }}</div>
                <div class="text-[11px] text-emerald-600 font-medium mt-0.5">{{ $activeEmployees }} Karyawan Aktif</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500">Kehadiran Hari Ini</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $presentCount }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Clock In Terverifikasi</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500">Terlambat Hari Ini</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $lateCount }}</div>
                <div class="text-[11px] text-amber-600 font-medium mt-0.5">> 15 Menit Toleransi</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500">Permohonan Cuti Pending</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $pendingLeaveRequests->count() }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Menunggu Approval Atasan</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="calendar-off" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- 2 Column Section: Pending Approvals & Expiring Contracts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Pending Leave Approvals -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-red-600"></i>
                    Permohonan Cuti Terbaru
                </h2>
                <span class="text-xs text-slate-400">Alur Persetujuan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Karyawan</th>
                            <th class="px-3 py-2.5">Jenis Cuti</th>
                            <th class="px-3 py-2.5">Durasi</th>
                            <th class="px-3 py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingLeaveRequests as $lr)
                            <tr>
                                <td class="px-3 py-3 font-semibold text-slate-900">
                                    {{ $lr->employee?->full_name }}
                                </td>
                                <td class="px-3 py-3 font-medium text-slate-700">
                                    {{ $lr->leaveType?->name }}
                                </td>
                                <td class="px-3 py-3">
                                    {{ $lr->total_days }} Hari ({{ $lr->start_date->format('d/m') }} - {{ $lr->end_date->format('d/m') }})
                                </td>
                                <td class="px-3 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">Tidak ada pengajuan cuti yang tertunda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Contract Expiration Reminders (PP 35/2021) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="briefcase" class="w-4 h-4 text-amber-500"></i>
                    Jatuh Tempo PKWT (< 60 Hari)
                </h2>
                <span class="text-[10px] font-bold text-slate-400 uppercase">PP 35/2021</span>
            </div>

            <div class="space-y-3">
                @forelse($expiringContracts as $emp)
                    <div class="p-3.5 rounded-xl bg-amber-50/60 border border-amber-200/80 text-xs">
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <span>{{ $emp->full_name }}</span>
                            <span class="text-[11px] text-amber-700 font-mono">{{ $emp->end_date?->format('d M Y') }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1 flex justify-between items-center">
                            <span>NIK: {{ $emp->employee_number }}</span>
                            <span class="text-amber-800 font-semibold">{{ now()->diffInDays($emp->end_date, false) }} hari tersisa</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        <i data-lucide="check-circle-2" class="w-6 h-6 mx-auto mb-1.5 text-emerald-500"></i>
                        Semua kontrak kerja PKWT dalam kondisi aman.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Department Distribution Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-4 flex items-center gap-2">
            <i data-lucide="layers" class="w-4 h-4 text-slate-600"></i>
            Distribusi Karyawan Per Departemen
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @forelse($departments as $dept)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                    <div class="text-xs text-slate-500">{{ $dept->name }}</div>
                    <div class="text-xl font-black text-slate-900 mt-1">{{ $dept->employees_count }} Karyawan</div>
                </div>
            @empty
                <div class="col-span-4 text-center py-4 text-xs text-slate-400">Belum ada departemen.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection

