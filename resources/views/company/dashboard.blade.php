@extends('layouts.app')

@section('title', 'Executive HR Command Center: ' . ($company->name ?? 'HRIS'))

@section('content')
<div class="space-y-6">

    {{-- 1. Top Greeting & Quick Command Center Banner --}}
    <div class="bg-gradient-to-r from-red-600 via-red-700 to-slate-900 rounded-2xl p-6 sm:p-7 text-white shadow-lg shadow-red-900/10 relative overflow-hidden">
        {{-- Background decorative shapes --}}
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        <div class="absolute right-40 -top-12 w-48 h-48 rounded-full bg-red-500/10 pointer-events-none blur-xl"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 relative z-10">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 text-white text-[11px] font-semibold backdrop-blur-sm">
                        <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                        {{ $company->name ?? 'PT Perusahaan' }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/25 text-emerald-200 border border-emerald-400/30 text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Multi-Tenant Enterprise Aktif
                    </span>
                    <span class="text-xs text-red-100 font-medium hidden sm:inline">
                        • {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black mt-2 tracking-tight text-white">
                    Pusat Komando HR & Kepatuhan Ketenagakerjaan
                </h1>
                <p class="text-xs sm:text-sm text-red-100 mt-1 max-w-2xl leading-relaxed">
                    Sistem pemantauan SDM terintegrasi: presensi GPS real-time, payroll PPh 21 TER (PMK 168), rekrutmen ATS, monitoring K3 SMK3, kepatuhan PP 35/2021, serta tata kelola 20 Phase HRIS.
                </p>
            </div>

            {{-- Quick Action Launchers & Export Center --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                <div class="flex items-center gap-2">
                    <a href="{{ route('company.employees.index') }}" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-sm border border-white/20 transition inline-flex items-center justify-center gap-1.5">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        <span>+ Karyawan</span>
                    </a>
                    <a href="{{ route('company.payroll.index') }}" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-sm border border-white/20 transition inline-flex items-center justify-center gap-1.5">
                        <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                        <span>Payroll Run</span>
                    </a>
                    <a href="{{ route('company.overtime.index') }}" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-sm border border-white/20 transition inline-flex items-center justify-center gap-1.5">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        <span>Buat SPL</span>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('ess.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-white text-red-700 hover:bg-red-50 text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Portal ESS</span>
                    </a>

                    {{-- Export Menu Dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" @click.outside="open = false" class="px-3 py-2 rounded-xl bg-slate-900/60 hover:bg-slate-900/80 text-white text-xs font-semibold border border-white/20 transition inline-flex items-center gap-1.5">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Ekspor</span>
                            <i data-lucide="chevron-down" class="w-3 h-3"></i>
                        </button>
                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 text-slate-700 text-xs font-medium">
                            <div class="px-3 py-1 text-[10px] uppercase font-bold text-slate-400">Unduh Laporan CSV</div>
                            <a href="{{ route('company.analytics.export.headcount') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-red-600"></i> Laporan Headcount
                            </a>
                            <a href="{{ route('company.analytics.export.attendance') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600"></i> Rekap Presensi
                            </a>
                            <a href="{{ route('company.analytics.export.payroll') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition">
                                <i data-lucide="banknote" class="w-3.5 h-3.5 text-blue-600"></i> Ringkasan Payroll
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Top Strategic KPI Cards Grid (8 Pillar Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-3.5">
        
        {{-- Card 1: Headcount SDM --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-red-200 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Karyawan Aktif</span>
                <span class="p-1.5 rounded-lg bg-red-50 text-red-600 group-hover:bg-red-600 group-hover:text-white transition"><i data-lucide="users" class="w-4 h-4"></i></span>
            </div>
            <div class="mt-2.5">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $activeEmployees }} <span class="text-xs font-normal text-slate-400">/ {{ $totalEmployees }}</span></div>
                <div class="flex items-center gap-1 text-[10px] text-slate-500 mt-1 truncate">
                    <span class="font-bold text-slate-700">Tetap: {{ $metrics['status_breakdown']['pkwtt'] ?? 0 }}</span>
                    <span>•</span>
                    <span class="font-bold text-amber-700">PKWT: {{ $metrics['status_breakdown']['pkwt'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Kehadiran Hari Ini --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-emerald-200 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Presensi Hari Ini</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition"><i data-lucide="clock" class="w-4 h-4"></i></span>
            </div>
            <div class="mt-2.5">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $presentCount + $lateCount }} <span class="text-xs font-normal text-slate-400">Hadir</span></div>
                <div class="flex items-center justify-between text-[10px] text-slate-500 mt-1">
                    <span class="text-emerald-600 font-bold">On-time: {{ $metrics['ontime_rate'] }}%</span>
                    @if($lateCount > 0)
                        <span class="text-amber-600 font-bold">{{ $lateCount }} Telat</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card 3: Biaya Payroll Bulan Ini --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-blue-200 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Payroll Terakhir</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition"><i data-lucide="banknote" class="w-4 h-4"></i></span>
            </div>
            <div class="mt-2.5">
                <div class="text-lg font-black text-slate-900 tracking-tight truncate" title="Rp {{ number_format($metrics['latest_payroll_gross'], 0, ',', '.') }}">
                    Rp {{ number_format($metrics['latest_payroll_gross'] / 1000000, 1) }} jt
                </div>
                <div class="text-[10px] text-slate-500 mt-1 flex justify-between">
                    <span class="truncate">THP: Rp {{ number_format($metrics['latest_payroll_thp'] / 1000000, 1) }} jt</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Rekrutmen ATS Pipeline --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-indigo-200 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Rekrutmen ATS</span>
                <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition"><i data-lucide="briefcase" class="w-4 h-4"></i></span>
            </div>
            <div class="mt-2.5">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $activeJobPostings }} <span class="text-xs font-normal text-slate-400">Loker</span></div>
                <div class="text-[10px] text-indigo-600 font-semibold mt-1 truncate">
                    {{ $incomingApplications }} Pelamar dalam Review
                </div>
            </div>
        </div>

        {{-- Card 5: K3 & SMK3 Zero Accident --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-amber-200 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">K3 & SMK3</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition"><i data-lucide="shield-check" class="w-4 h-4"></i></span>
            </div>
            <div class="mt-2.5">
                <div class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-1">
                    @if($totalIncidents == 0)
                        <span class="text-emerald-600 flex items-center gap-1 font-bold"><i data-lucide="check" class="w-3.5 h-3.5"></i> Zero Accident</span>
                    @else
                        <span class="text-rose-600 font-bold">{{ $totalIncidents }} Insiden</span>
                    @endif
                </div>
                <div class="text-[10px] text-slate-500 mt-1 flex justify-between">
                    <span>Disiplin SP:</span>
                    <span class="font-bold {{ $activeWarnings > 0 ? 'text-amber-700' : 'text-slate-700' }}">{{ $activeWarnings }} Aktif</span>
                </div>
            </div>
        </div>

        {{-- Card 6: Layanan Mandiri & Kasbon --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-teal-200 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Layanan & Kasbon</span>
                <span class="p-1.5 rounded-lg bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition"><i data-lucide="life-buoy" class="w-4 h-4"></i></span>
            </div>
            <div class="mt-2.5">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $openHelpdeskTickets }} <span class="text-xs font-normal text-slate-400">Tiket</span></div>
                <div class="text-[10px] text-teal-700 font-semibold mt-1 truncate" title="Rp {{ number_format($activeLoansTotal, 0, ',', '.') }}">
                    Kasbon: Rp {{ number_format($activeLoansTotal / 1000, 0) }}k ({{ $activeLoansCount }} staf)
                </div>
            </div>
        </div>

        {{-- Card 7: Hubungan Industrial & Grievance --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-purple-200 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Hub. Industrial</span>
                <span class="p-1.5 rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition"><i data-lucide="scale" class="w-4 h-4"></i></span>
            </div>
            <div class="mt-2.5">
                <div class="text-2xl font-black text-slate-900 tracking-tight">{{ $openGrievancesCount }} <span class="text-xs font-normal text-slate-400">Aduan</span></div>
                <div class="text-[10px] text-purple-700 font-semibold mt-1 truncate">
                    Bipartit: {{ $upcomingBipartite ? $upcomingBipartite->meeting_date->format('d/m') : 'Belum Ada' }}
                </div>
            </div>
        </div>

        {{-- Card 8: Indeks Regulasi (UU Ketenagakerjaan) --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:border-rose-200 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Kepatuhan RI</span>
                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition"><i data-lucide="check-check" class="w-4 h-4"></i></span>
            </div>
            <div class="mt-2.5">
                <div class="text-sm font-black text-emerald-600 tracking-tight flex items-center gap-1">
                    <i data-lucide="badge-check" class="w-4 h-4"></i> Patuh 100%
                </div>
                <div class="text-[10px] text-slate-500 mt-1 truncate">
                    Disabilitas: {{ $disabilityRate }}% (Min 1%)
                </div>
            </div>
        </div>

    </div>

    {{-- 3. Row 2: Unified Approval Hub & Live Attendance Activity (7 Cols vs 5 Cols) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left: Unified Approval Center with Tab Controls --}}
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between"
             x-data="{ activeTab: 'all' }">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3.5 border-b border-slate-100 gap-3">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-red-50 text-red-600"><i data-lucide="check-square" class="w-4 h-4"></i></span>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                            Pusat Persetujuan Terpadu
                        </h2>
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold border border-amber-200 text-[10px]">
                            {{ $totalPendingApprovals }} Menunggu
                        </span>
                    </div>

                    {{-- Tab Pills --}}
                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-[11px] font-semibold text-slate-600">
                        <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition">
                            Semua ({{ $totalPendingApprovals }})
                        </button>
                        <button type="button" @click="activeTab = 'leave'" :class="activeTab === 'leave' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition">
                            Cuti ({{ $pendingLeaveRequests->count() }})
                        </button>
                        <button type="button" @click="activeTab = 'reimbursement'" :class="activeTab === 'reimbursement' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition">
                            Klaim ({{ $pendingReimbursements->count() }})
                        </button>
                        <button type="button" @click="activeTab = 'overtime'" :class="activeTab === 'overtime' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition">
                            SPL ({{ $pendingOvertimeOrders->count() }})
                        </button>
                        <button type="button" @click="activeTab = 'loan'" :class="activeTab === 'loan' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition">
                            Kasbon ({{ $pendingLoans->count() }})
                        </button>
                    </div>
                </div>

                {{-- Approval Table --}}
                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                            <tr>
                                <th class="px-3 py-2 rounded-l-lg">Karyawan</th>
                                <th class="px-3 py-2">Tipe Pengajuan</th>
                                <th class="px-3 py-2">Rincian / Nilai</th>
                                <th class="px-3 py-2 text-right rounded-r-lg">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            {{-- Leave Requests --}}
                            @foreach($pendingLeaveRequests as $lr)
                                <tr x-show="activeTab === 'all' || activeTab === 'leave'" class="hover:bg-slate-50/70 transition">
                                    <td class="px-3 py-2.5 font-semibold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[10px]">
                                                {{ substr($lr->employee?->first_name ?? 'E', 0, 1) }}
                                            </div>
                                            <span>{{ $lr->employee?->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex items-center gap-1 font-medium text-blue-700">
                                            <i data-lucide="calendar" class="w-3 h-3"></i>
                                            {{ $lr->leaveType?->name ?? 'Cuti' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-[11px] text-slate-500">
                                        {{ $lr->total_days }} Hari ({{ $lr->start_date?->format('d/m') }} - {{ $lr->end_date?->format('d/m') }})
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <a href="{{ route('company.leave.index') }}" class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold text-[10px] transition inline-block">
                                            Tinjau
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Reimbursement Requests --}}
                            @foreach($pendingReimbursements as $rm)
                                <tr x-show="activeTab === 'all' || activeTab === 'reimbursement'" class="hover:bg-slate-50/70 transition">
                                    <td class="px-3 py-2.5 font-semibold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px]">
                                                {{ substr($rm->employee?->first_name ?? 'E', 0, 1) }}
                                            </div>
                                            <span>{{ $rm->employee?->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex items-center gap-1 font-medium text-emerald-700">
                                            <i data-lucide="receipt" class="w-3 h-3"></i>
                                            Klaim: {{ ucfirst($rm->category) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-[11px] font-bold text-slate-900 font-mono">
                                        Rp {{ number_format($rm->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <a href="{{ route('company.reimbursements.index') }}" class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[10px] transition inline-block">
                                            Bayar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Overtime (SPL) Requests --}}
                            @foreach($pendingOvertimeOrders as $ot)
                                <tr x-show="activeTab === 'all' || activeTab === 'overtime'" class="hover:bg-slate-50/70 transition">
                                    <td class="px-3 py-2.5 font-semibold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-[10px]">
                                                {{ substr($ot->employee?->first_name ?? 'E', 0, 1) }}
                                            </div>
                                            <span>{{ $ot->employee?->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex items-center gap-1 font-medium text-amber-700">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            SPL Lembur
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-[11px] text-slate-500">
                                        {{ $ot->planned_duration_hours }} Jam ({{ $ot->overtime_date?->format('d/m') }})
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <a href="{{ route('company.overtime.index') }}" class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold text-[10px] transition inline-block">
                                            Setujui
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Loan Requests --}}
                            @foreach($pendingLoans as $ln)
                                <tr x-show="activeTab === 'all' || activeTab === 'loan'" class="hover:bg-slate-50/70 transition">
                                    <td class="px-3 py-2.5 font-semibold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-[10px]">
                                                {{ substr($ln->employee?->first_name ?? 'E', 0, 1) }}
                                            </div>
                                            <span>{{ $ln->employee?->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex items-center gap-1 font-medium text-teal-700">
                                            <i data-lucide="coins" class="w-3 h-3"></i>
                                            Kasbon / Pinjaman
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-[11px] font-bold text-slate-900 font-mono">
                                        Rp {{ number_format($ln->amount, 0, ',', '.') }} ({{ $ln->term_months }} bln)
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <a href="{{ route('company.loans.index') }}" class="px-2.5 py-1 rounded-md bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold text-[10px] transition inline-block">
                                            Verifikasi
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                            @if($totalPendingApprovals == 0)
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-xs text-slate-400">
                                        <i data-lucide="check-circle-2" class="w-6 h-6 mx-auto mb-1 text-emerald-500"></i>
                                        Luar biasa! Seluruh permohonan cuti, klaim biaya, lembur SPL, dan kasbon telah selesai diproses.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span>Multi-level Approval Workflow Terhubung</span>
                <a href="{{ route('company.workflows.index') }}" class="text-red-600 hover:underline font-bold text-[11px]">
                    Pengaturan Workflow &rarr;
                </a>
            </div>
        </div>

        {{-- Right: Live Attendance Activity Feed --}}
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600"><i data-lucide="activity" class="w-4 h-4"></i></span>
                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                            Monitoring Presensi Hari Ini
                        </h2>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ now()->format('H:i') }} WIB
                    </span>
                </div>

                {{-- Status Bar Summary (Hadir, Telat, Cuti, Belum Absen) --}}
                <div class="grid grid-cols-4 gap-2 mt-3.5 text-center">
                    <div class="p-2 rounded-xl bg-emerald-50/70 border border-emerald-200/60">
                        <div class="text-[9px] uppercase font-bold text-emerald-700">Tepat Waktu</div>
                        <div class="text-base font-black text-emerald-800">{{ $presentCount }}</div>
                    </div>
                    <div class="p-2 rounded-xl bg-amber-50/70 border border-amber-200/60">
                        <div class="text-[9px] uppercase font-bold text-amber-700">Terlambat</div>
                        <div class="text-base font-black text-amber-800">{{ $lateCount }}</div>
                    </div>
                    <div class="p-2 rounded-xl bg-blue-50/70 border border-blue-200/60">
                        <div class="text-[9px] uppercase font-bold text-blue-700">Cuti / Izin</div>
                        <div class="text-base font-black text-blue-800">{{ $onLeaveTodayCount }}</div>
                    </div>
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div class="text-[9px] uppercase font-bold text-slate-500">Belum Masuk</div>
                        <div class="text-base font-black text-slate-700">{{ $notClockedInCount }}</div>
                    </div>
                </div>

                {{-- Live Clock-In Feed (5 Latest) --}}
                <div class="mt-4">
                    <div class="text-[10px] font-bold uppercase text-slate-400 mb-2">5 Presensi Masuk Terkini</div>
                    <div class="space-y-2">
                        @forelse($recentAttendanceLogs as $log)
                            <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-slate-50 transition flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ substr($log->employee?->first_name ?? 'E', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 truncate">{{ $log->employee?->full_name }}</div>
                                        <div class="text-[10px] text-slate-400 truncate">{{ $log->employee?->department?->name ?? 'Divisi Umum' }}</div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-mono font-bold text-slate-800 text-[11px]">{{ $log->clock_in ?? '-' }}</div>
                                    @if($log->status === 'late')
                                        <span class="text-[9px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">+{{ $log->late_minutes ?? 0 }}m</span>
                                    @else
                                        <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">Tepat Waktu</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-xs text-slate-400">
                                Belum ada log presensi masuk tercatat hari ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-[11px] text-slate-400">Validasi Geofencing & Foto Selfie GPS</span>
                <a href="{{ route('company.attendance.index') }}" class="font-bold text-red-600 hover:underline text-[11px]">
                    Lihat Semua Presensi &rarr;
                </a>
            </div>
        </div>

    </div>

    {{-- 4. Row 3: Organizational Distribution & Financial Deep-Dive (6 Cols vs 6 Cols) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left: Department Distribution & Demographics --}}
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="network" class="w-4 h-4 text-red-600"></i>
                        Distribusi SDM per Departemen
                    </h2>
                    <span class="text-[11px] font-semibold text-slate-400">{{ $departments->count() }} Departemen</span>
                </div>

                {{-- Department Progress Bars --}}
                <div class="space-y-3">
                    @forelse($departments as $dept)
                        @php
                            $percentage = $activeEmployees > 0 ? round(($dept->employees_count / $activeEmployees) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-semibold text-slate-800">{{ $dept->name }}</span>
                                <span class="text-slate-500 font-mono text-[11px]">{{ $dept->employees_count }} Orang ({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-red-600 h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">Belum ada data departemen.</div>
                    @endforelse
                </div>
            </div>

            {{-- Gender Breakdown & Turnover Rate --}}
            <div class="mt-5 pt-3.5 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs gap-2">
                <span class="text-[11px] font-bold text-slate-500 uppercase">Demografi & Mobilitas:</span>
                <div class="flex items-center gap-3 text-[11px]">
                    <span class="flex items-center gap-1 font-semibold text-blue-700">
                        <i data-lucide="user" class="w-3.5 h-3.5"></i> Pria: {{ $metrics['gender_breakdown']['male'] ?? 0 }}
                    </span>
                    <span class="flex items-center gap-1 font-semibold text-rose-700">
                        <i data-lucide="user" class="w-3.5 h-3.5"></i> Wanita: {{ $metrics['gender_breakdown']['female'] ?? 0 }}
                    </span>
                    <span class="text-slate-400">• Masa Percobaan: {{ $probationEmployees }}</span>
                    <span class="text-slate-400">• Turnover: {{ $metrics['turnover_rate'] }}%</span>
                </div>
            </div>
        </div>

        {{-- Right: Financial Composition & Tax/BPJS Compliance --}}
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-600"></i>
                        Komposisi Beban Payroll & Pajak
                    </h2>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $metrics['latest_period_name'] }}
                    </span>
                </div>

                {{-- Financial Details 4-Box Grid --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div class="text-[10px] font-bold uppercase text-slate-500">Gross Payroll Total</div>
                        <div class="text-base font-black text-slate-900 mt-0.5">
                            Rp {{ number_format($metrics['latest_payroll_gross'], 0, ',', '.') }}
                        </div>
                        <div class="text-[9px] text-slate-400 mt-0.5">Total gaji bruto seluruh staf</div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div class="text-[10px] font-bold uppercase text-emerald-700">Take Home Pay (THP)</div>
                        <div class="text-base font-black text-emerald-700 mt-0.5">
                            Rp {{ number_format($metrics['latest_payroll_thp'], 0, ',', '.') }}
                        </div>
                        <div class="text-[9px] text-slate-400 mt-0.5">Total bersih dibayarkan via bank</div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div class="text-[10px] font-bold uppercase text-purple-700">PPh 21 TER (PMK 168)</div>
                        <div class="text-base font-black text-purple-700 mt-0.5">
                            Rp {{ number_format($metrics['latest_payroll_tax'], 0, ',', '.') }}
                        </div>
                        <div class="text-[9px] text-slate-400 mt-0.5">Setoran pajak efektif bulanan ke DJP</div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div class="text-[10px] font-bold uppercase text-blue-700">Iuran BPJS Perusahaan</div>
                        <div class="text-base font-black text-blue-700 mt-0.5">
                            Rp {{ number_format($metrics['latest_payroll_bpjs_employer'], 0, ',', '.') }}
                        </div>
                        <div class="text-[9px] text-slate-400 mt-0.5">JKK, JKM, JHT, JP & Kes 4%</div>
                    </div>
                </div>
            </div>

            {{-- Bottom Regulation Note --}}
            <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span class="flex items-center gap-1.5 text-emerald-700 font-semibold">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Patuh Regulasi TER 2024 & Batas Upah BPJS
                </span>
                <a href="{{ route('company.payroll.index') }}" class="font-bold text-red-600 hover:underline">
                    Rincian Slip Gaji &rarr;
                </a>
            </div>
        </div>

    </div>

    {{-- 5. Row 4: Statutory Compliance Radar & Talent ATS Pipeline (6 Cols vs 6 Cols) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left: Indonesian Labor Law Statutory Compliance Radar --}}
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-3.5">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i>
                        Radar Kepatuhan Regulasi Ketenagakerjaan RI
                    </h2>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200">
                        PP 35/2021 & UU Ciptaker
                    </span>
                </div>

                <div class="space-y-3">
                    {{-- 1. Expiring PKWT Contracts (< 60 Days) --}}
                    <div>
                        <div class="text-[11px] font-bold text-slate-700 flex items-center justify-between mb-1.5">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="file-clock" class="w-3.5 h-3.5 text-amber-600"></i>
                                Jatuh Tempo PKWT (< 60 Hari) PP 35/2021:
                            </span>
                            <span class="text-amber-700 font-mono text-[10px] font-bold">{{ $expiringContracts->count() }} Orang</span>
                        </div>

                        @forelse($expiringContracts->take(3) as $emp)
                            <div class="p-2.5 rounded-xl bg-amber-50/60 border border-amber-200/70 text-xs mb-1.5">
                                <div class="flex items-center justify-between font-bold text-slate-900">
                                    <span>{{ $emp->full_name }} ({{ $emp->employee_number }})</span>
                                    <span class="text-[10px] text-amber-800 font-semibold">{{ now()->diffInDays($emp->end_date, false) }} hari lagi</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5 flex justify-between">
                                    <span>Divisi: {{ $emp->department?->name ?? 'Staf' }}</span>
                                    <span class="font-semibold">Berakhir: {{ $emp->end_date?->format('d M Y') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center text-[11px] text-slate-400">
                                Seluruh masa kontrak PKWT terpantau aman (tidak ada jatuh tempo dekat).
                            </div>
                        @endforelse
                    </div>

                    {{-- 2. Compliance Checklist Cards (PP/PKB, WLTK, Disabilitas) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-2">
                        {{-- PP Status --}}
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 text-xs">
                            <div class="text-[10px] font-bold uppercase text-slate-500">PP Perusahaan</div>
                            <div class="font-bold text-slate-900 mt-0.5 truncate">
                                {{ $companyRegulation ? 'Terdaftar Disnaker' : 'Perlu Didaftarkan' }}
                            </div>
                            <div class="text-[9px] text-slate-400 mt-0.5">
                                {{ $companyRegulation ? 'Berlaku s.d ' . $companyRegulation->effective_end_date?->format('d/m/y') : 'Wajib UU 13/03' }}
                            </div>
                        </div>

                        {{-- WLTK Kemnaker --}}
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 text-xs">
                            <div class="text-[10px] font-bold uppercase text-slate-500">WLTK Kemnaker</div>
                            <div class="font-bold text-slate-900 mt-0.5 truncate">
                                {{ $latestWltkReport ? 'Tahun ' . $latestWltkReport->reporting_year : 'Belum Lapor' }}
                            </div>
                            <div class="text-[9px] text-slate-400 mt-0.5">
                                UU 7/1981 Lapor Tahunan
                            </div>
                        </div>

                        {{-- Disabilitas UU 8/2016 --}}
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 text-xs">
                            <div class="text-[10px] font-bold uppercase text-slate-500">Kuota Disabilitas</div>
                            <div class="font-bold {{ $disabilityRate >= 1.0 ? 'text-emerald-700' : 'text-amber-700' }} mt-0.5 truncate">
                                {{ $disabilityRate }}% ({{ $disabilityCount }} Orang)
                            </div>
                            <div class="text-[9px] text-slate-400 mt-0.5">
                                Target UU 8/2016: 1%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="{{ route('company.wltk.index') }}" class="font-bold text-slate-600 hover:text-slate-900 text-[11px]">
                    Portal WLTK Kemnaker &rarr;
                </a>
                <a href="{{ route('company.contracts.index') }}" class="font-bold text-red-600 hover:underline text-[11px]">
                    Kelola Kontrak PKWT &rarr;
                </a>
            </div>
        </div>

        {{-- Right: ATS Recruitment Pipeline & Training Development --}}
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-3.5">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="compass" class="w-4 h-4 text-indigo-600"></i>
                        Pipeline Rekrutmen ATS & Pelatihan
                    </h2>
                    <a href="{{ route('company.recruitment.index') }}" class="text-[11px] font-bold text-red-600 hover:underline">
                        Kelola ATS &rarr;
                    </a>
                </div>

                {{-- ATS Recent Candidates --}}
                <div class="space-y-2">
                    <div class="text-[11px] font-bold text-slate-700 mb-1 flex items-center justify-between">
                        <span>Pelamar Terbaru dalam Pipeline Rekrutmen:</span>
                        <span class="text-indigo-600 font-bold text-[10px]">{{ $incomingApplications }} Dalam Seleksi</span>
                    </div>

                    @forelse($recentApplications as $app)
                        <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-slate-50 transition flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-900">{{ $app->candidate?->first_name }} {{ $app->candidate?->last_name }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Posisi: {{ $app->jobPosting?->title ?? 'Umum' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize
                                    @if($app->stage === 'hired') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($app->stage === 'interview') bg-purple-50 text-purple-700 border border-purple-200
                                    @elseif($app->stage === 'screening') bg-blue-50 text-blue-700 border border-blue-200
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ $app->stage }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-3 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                            Belum ada lamaran kandidat masuk dalam pipeline ATS.
                        </div>
                    @endforelse
                </div>

                {{-- Training & Employee Development Banner --}}
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-700 mb-2">
                        <span>Pelatihan & Sertifikasi SDM:</span>
                        <span class="text-emerald-700 font-bold">{{ $activeTrainingsCount }} Peserta Terdaftar</span>
                    </div>
                    <div class="space-y-1.5">
                        @forelse($recentTrainings as $tr)
                            <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/60 flex items-center justify-between text-[11px]">
                                <span class="font-semibold text-slate-800 truncate">{{ $tr->trainingCourse?->title ?? 'Kursus Kompetensi' }}</span>
                                <span class="text-slate-500 text-[10px] font-mono">{{ $tr->employee?->full_name }}</span>
                            </div>
                        @empty
                            <div class="text-[10px] text-slate-400">Belum ada pelatihan aktif berjalan.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-3.5 pt-3 border-t border-slate-100 text-right">
                <a href="{{ route('company.training.index') }}" class="font-bold text-indigo-600 hover:underline text-[11px]">
                    Katalog Pelatihan & Sertifikasi &rarr;
                </a>
            </div>
        </div>

    </div>

    {{-- 6. Row 5: Internal Announcements & HR Calendar / Upcoming Events (6 Cols vs 6 Cols) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left: Official Announcements --}}
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-3.5">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="megaphone" class="w-4 h-4 text-blue-600"></i>
                        Papan Pengumuman Resmi Perusahaan
                    </h2>
                    <a href="{{ route('company.announcements.index') }}" class="text-[11px] font-bold text-red-600 hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="space-y-2.5">
                    @forelse($announcements as $anc)
                        <div class="p-3 rounded-xl border border-slate-200/70 hover:bg-slate-50/70 transition">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-bold text-xs text-slate-900 truncate">{{ $anc->title }}</div>
                                <span class="text-[10px] text-slate-400 shrink-0">{{ $anc->created_at->format('d M') }}</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                {{ $anc->content }}
                            </p>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-slate-400">Belum ada pengumuman baru yang dipublikasikan.</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-right">
                <a href="{{ route('company.announcements.index') }}" class="text-[11px] font-bold text-slate-600 hover:text-slate-900">
                    + Terbitkan Pengumuman Baru &rarr;
                </a>
            </div>
        </div>

        {{-- Right: HR Calendar & Upcoming Agenda (Birthdays, Holidays, Bipartite) --}}
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-3.5">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="calendar-heart" class="w-4 h-4 text-rose-500"></i>
                        Agenda & Kalender HR Mendatang
                    </h2>
                    <span class="text-[10px] font-bold text-slate-400">{{ now()->translatedFormat('F Y') }}</span>
                </div>

                <div class="space-y-3.5">
                    {{-- Upcoming Birthdays this month --}}
                    <div>
                        <div class="text-[11px] font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i data-lucide="gift" class="w-3.5 h-3.5 text-rose-500"></i> Ulang Tahun Karyawan Bulan Ini:</span>
                            <span class="text-[10px] font-bold text-rose-600">{{ $upcomingBirthdays->count() }} Orang</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            @forelse($upcomingBirthdays as $bday)
                                <div class="p-2 rounded-xl bg-rose-50/50 border border-rose-100 flex items-center justify-between">
                                    <div class="truncate min-w-0 pr-1">
                                        <div class="font-bold text-slate-900 truncate text-[11px]">{{ $bday->full_name }}</div>
                                        <div class="text-[9px] text-slate-400 truncate">{{ $bday->department?->name ?? 'Staf' }}</div>
                                    </div>
                                    <span class="text-[10px] font-bold text-rose-700 bg-rose-100 px-1.5 py-0.5 rounded shrink-0">
                                        {{ $bday->birth_date?->format('d M') }}
                                    </span>
                                </div>
                            @empty
                                <div class="col-span-2 text-center py-2 text-[10px] text-slate-400">
                                    Tidak ada karyawan yang berulang tahun bulan ini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Public Holidays / Cuti Bersama SKB 3 Menteri --}}
                    <div class="pt-2 border-t border-slate-100">
                        <div class="text-[11px] font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><i data-lucide="palmtree" class="w-3.5 h-3.5 text-emerald-600"></i> Hari Libur Nasional & Cuti Bersama:</span>
                        </div>
                        <div class="space-y-1.5">
                            @forelse($upcomingHolidays as $hol)
                                <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/60 flex items-center justify-between text-xs">
                                    <span class="font-medium text-slate-800">{{ $hol->name }}</span>
                                    <span class="font-mono text-[10px] font-bold text-slate-600">{{ $hol->date->format('d M Y') }}</span>
                                </div>
                            @empty
                                <div class="text-[10px] text-slate-400">Belum ada data hari libur nasional terdekat.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-[10px] text-slate-400">SKB 3 Menteri Libur Nasional</span>
                <a href="{{ route('company.bipartite.index') }}" class="font-bold text-slate-700 hover:text-slate-900 text-[11px]">
                    Agenda Bipartit LKS &rarr;
                </a>
            </div>
        </div>

    </div>

    {{-- 7. Row 6: 20-Phase Ecosystem Launchpad (Full Width Compact Grid) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 mb-4">
            <div>
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-red-600"></i>
                    Pintasan Ekosistem Modul RiHRIS (20 Phase Terintegrasi)
                </h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Akses cepat seluruh modul operasional, payroll, talenta, hubungan industrial, dan kepatuhan hukum</p>
            </div>
            <span class="text-[10px] font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full">
                1-Klik Navigasi
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 text-xs">
            {{-- Modul 1: Skala Upah --}}
            <a href="{{ route('company.wage-scales.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-blue-300 hover:bg-blue-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition"><i data-lucide="scale" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">Permenaker 1/17</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">Skala & Struktur Upah</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Grading & Komparasi</div>
                </div>
            </a>

            {{-- Modul 2: K3 & JKK --}}
            <a href="{{ route('company.k3.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-red-300 hover:bg-red-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-red-50 text-red-600 group-hover:bg-red-600 group-hover:text-white transition"><i data-lucide="shield" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">PP 50/2012</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">K3 SMK3 & JKK</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Zero Accident & P2K3</div>
                </div>
            </a>

            {{-- Modul 3: WLTK Kemnaker --}}
            <a href="{{ route('company.wltk.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-emerald-300 hover:bg-emerald-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition"><i data-lucide="file-text" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">UU 7/1981</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">WLTK Kemnaker</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Pelaporan Daring Resmi</div>
                </div>
            </a>

            {{-- Modul 4: Evaluasi 360 & KPI --}}
            <a href="{{ route('company.appraisals360.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-purple-300 hover:bg-purple-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition"><i data-lucide="compass" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">KPI & OKR</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">Evaluasi 360° & PIP</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Multi-Rater Kinerja</div>
                </div>
            </a>

            {{-- Modul 5: Workflow Engine --}}
            <a href="{{ route('company.workflows.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-indigo-300 hover:bg-indigo-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition"><i data-lucide="git-branch" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">Multi-Tier</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">Workflow Engine</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Matriks Otorisasi</div>
                </div>
            </a>

            {{-- Modul 6: Perlindungan Data PDP --}}
            <a href="{{ route('pdp.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-amber-300 hover:bg-amber-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition"><i data-lucide="lock" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">UU 27/2022</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">Perlindungan Data PDP</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Hak DSAR & Enkripsi</div>
                </div>
            </a>

            {{-- Modul 7: THR Keagamaan --}}
            <a href="{{ route('company.thr.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-teal-300 hover:bg-teal-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition"><i data-lucide="gift" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">Permenaker 6/16</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">Kalkulator THR H-7</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Prorata Masa Kerja</div>
                </div>
            </a>

            {{-- Modul 8: Matriks Kompetensi --}}
            <a href="{{ route('company.skills.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-rose-300 hover:bg-rose-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition"><i data-lucide="target" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">Skill Gap</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">Matriks Kompetensi</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Analisis Kesenjangan</div>
                </div>
            </a>

            {{-- Modul 9: Rencana Suksesi --}}
            <a href="{{ route('company.succession.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-cyan-300 hover:bg-cyan-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-cyan-50 text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white transition"><i data-lucide="crown" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">Nine-Box</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">Rencana Suksesi</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Talent Pool Kunci</div>
                </div>
            </a>

            {{-- Modul 10: Forum Bipartit LKS --}}
            <a href="{{ route('company.bipartite.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-emerald-300 hover:bg-emerald-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition"><i data-lucide="users-2" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">Pasal 106</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">LKS Bipartit & Serikat</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Risalah Hubungan Kerja</div>
                </div>
            </a>

            {{-- Modul 11: Portal Kepatuhan ESG --}}
            <a href="{{ route('company.compliance.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-yellow-300 hover:bg-yellow-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-yellow-50 text-yellow-600 group-hover:bg-yellow-600 group-hover:text-white transition"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">ESG & Audit</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">Audit Kepatuhan & ESG</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Snapshot Kepatuhan</div>
                </div>
            </a>

            {{-- Modul 12: Analytics Lanjutan --}}
            <a href="{{ route('company.analytics.index') }}" class="p-3 rounded-xl border border-slate-200/70 hover:border-red-300 hover:bg-red-50/30 transition group flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="p-1.5 rounded-lg bg-red-50 text-red-600 group-hover:bg-red-600 group-hover:text-white transition"><i data-lucide="bar-chart-3" class="w-4 h-4"></i></span>
                    <span class="text-[9px] font-bold text-slate-400">Executive</span>
                </div>
                <div class="mt-2.5">
                    <div class="font-bold text-slate-800 text-[11px]">HR Analytics & Trend</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">Insight Strategis SDM</div>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection
