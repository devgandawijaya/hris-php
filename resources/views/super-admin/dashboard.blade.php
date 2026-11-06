@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Ringkasan Platform SaaS RiHRIS</h1>
            <p class="text-xs text-slate-500 mt-1">Pantau seluruh entitas perusahaan penyewa, kesehatan sistem, dan status lisensi.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('super.admin.companies.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-red-600"></i>
                Kelola Perusahaan
            </a>
            <a href="{{ route('super.admin.cms.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="globe" class="w-4 h-4"></i>
                Kelola Landing CMS
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500">Total Perusahaan</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalCompanies }}</div>
                <div class="text-[11px] text-emerald-600 font-medium mt-0.5">{{ $activeTenants }} Aktif • {{ $trialTenants }} Masa Trial</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center">
                <i data-lucide="building-2" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500">Total Karyawan Terdaftar</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalEmployees }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Seluruh Tenant Multi-Company</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500">Akun Pengguna Sistem</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalUsers }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Admin & Karyawan ESS</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500">Perusahaan Ditangguhkan</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $suspendedTenants }}</div>
                <div class="text-[11px] text-rose-600 font-medium mt-0.5">Akses Nonaktif / Suspended</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- 2 Column Layout: Recent Registrations & Audit Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Recent Company Registrations -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-red-600"></i>
                    Perusahaan Terdaftar Terbaru
                </h2>
                <a href="{{ route('super.admin.companies.index') }}" class="text-xs font-semibold text-[#DC2626] hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Nama Perusahaan</th>
                            <th class="px-3 py-2.5">Subdomain Slug</th>
                            <th class="px-3 py-2.5">Paket Aktif</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-3 py-2.5 rounded-r-lg text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentTenants as $t)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-3 py-3 font-semibold text-slate-900">{{ $t->name }}</td>
                                <td class="px-3 py-3 font-mono text-[11px] text-slate-500">{{ $t->slug }}</td>
                                <td class="px-3 py-3 font-medium text-slate-700">
                                    {{ $t->activeSubscription?->plan?->name ?? 'Free Trial' }}
                                </td>
                                <td class="px-3 py-3">
                                    @if($t->status === 'active')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                    @elseif($t->status === 'trial')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Trial</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">{{ $t->status }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <a href="{{ route('super.admin.companies.show', $t->id) }}" class="text-xs text-[#DC2626] hover:underline font-semibold">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">Belum ada tenant terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Recent Platform Audit Activity -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                    Aktivitas Audit Keamanan
                </h2>
                <span class="text-[11px] text-slate-400">Log Real-Time</span>
            </div>

            <div class="space-y-3.5">
                @forelse($recentAuditLogs as $log)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
                        <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                            <span class="font-bold text-slate-800">{{ $log->action }}</span>
                            <span>{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-slate-600">
                            User: <span class="font-semibold">{{ $log->user?->name ?? 'System' }}</span>
                        </div>
                        @if($log->ip_address)
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">IP: {{ $log->ip_address }}</div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Belum ada rekaman audit log.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

