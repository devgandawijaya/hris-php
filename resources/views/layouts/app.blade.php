<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — RiHRIS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#DC2626',
                        'primary-dark': '#991B1B',
                        slate: {
                            800: '#1E293B',
                            500: '#64748B',
                            50: '#F8FAFC',
                        }
                    },
                    maxWidth: {
                        'dashboard': '1440px',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="h-full antialiased flex flex-col bg-slate-50">

    {{-- Banner Impersonasi Super Admin --}}
    @if(session()->has('impersonator_super_admin_id'))
        <div class="bg-amber-400 text-slate-950 px-4 py-2 shadow-sm flex items-center justify-between z-50 sticky top-0 border-b border-amber-500 font-sans text-xs">
            <div class="flex items-center gap-2 font-bold">
                <i data-lucide="shield-alert" class="w-4 h-4 text-slate-950"></i>
                <span>Mode Impersonasi Super Admin Aktif: Mengelola tenant <u>{{ $currentTenant->name ?? 'Perusahaan' }}</u>.</span>
            </div>
            <form action="{{ route('super.admin.impersonate.leave') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-slate-950 hover:bg-slate-800 text-white font-bold px-2.5 py-1 rounded-md text-[11px] transition">
                    Kembali ke Super Admin
                </button>
            </form>
        </div>
    @endif

    {{-- Top Menu Bar (Header + Compact Mega Menu) --}}
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-sm"
            x-data="{ 
                megaMenu: null, 
                mobileOpen: false, 
                closeTimer: null,
                openMenu(menu) {
                    clearTimeout(this.closeTimer);
                    this.megaMenu = menu;
                },
                closeMenu() {
                    this.closeTimer = setTimeout(() => {
                        this.megaMenu = null;
                    }, 140);
                },
                cancelClose() {
                    clearTimeout(this.closeTimer);
                }
            }"
            @keydown.escape.window="megaMenu = null">

        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="flex items-center justify-between h-14 gap-2">
                
                {{-- Brand & Tenant Indicator --}}
                <div class="flex items-center gap-2.5 shrink-0">
                    <button type="button" @click="mobileOpen = !mobileOpen" class="xl:hidden p-1.5 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <a href="{{ auth()->user()->is_super_admin ? route('super.admin.dashboard') : route('company.dashboard') }}" class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#DC2626] flex items-center justify-center text-white shadow-sm shrink-0">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        </div>
                        <span class="text-lg font-black text-slate-900 tracking-tight">Ri<span class="text-[#DC2626]">HRIS</span></span>
                    </a>

                    @if(isset($currentTenant))
                        <div class="hidden 2xl:flex items-center gap-1.5 pl-2.5 border-l border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[11px] font-semibold text-slate-600 truncate max-w-[130px]">{{ $currentTenant->name }}</span>
                        </div>
                    @endif
                </div>

                {{-- Desktop Compact Mega Menu Navigation (Hover to Open) --}}
                <nav class="hidden xl:flex items-center gap-0.5 flex-1 justify-center px-1">
                    @if(auth()->user()->is_super_admin)
                        <a href="{{ route('super.admin.dashboard') }}"
                           class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('super.admin.dashboard') ? 'bg-red-50 text-red-600 font-bold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('super.admin.companies.index') }}"
                           class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('super.admin.companies.*') ? 'bg-red-50 text-red-600 font-bold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                            <span>Perusahaan</span>
                        </a>
                        <a href="{{ route('super.admin.plans.index') }}"
                           class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('super.admin.plans.*') ? 'bg-red-50 text-red-600 font-bold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                            <span>Paket SaaS</span>
                        </a>
                        <a href="{{ route('super.admin.health.index') }}"
                           class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('super.admin.health.*') ? 'bg-red-50 text-red-600 font-bold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                            <span>Kesehatan MRR</span>
                        </a>
                        <a href="{{ route('super.admin.cms.index') }}"
                           class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('super.admin.cms.index') ? 'bg-red-50 text-red-600 font-bold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                            <span>CMS Landing</span>
                        </a>
                        <a href="{{ route('super.admin.cms.leads.index') }}"
                           class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('super.admin.cms.leads.*') ? 'bg-red-50 text-red-600 font-bold' : 'text-slate-700 hover:bg-slate-100' }}">
                            <i data-lucide="inbox" class="w-3.5 h-3.5"></i>
                            <span>CRM Leads</span>
                        </a>
                    @else
                        {{-- 1. Dashboard Langsung --}}
                        <a href="{{ route('company.dashboard') }}"
                           @mouseenter="megaMenu = null"
                           class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('company.dashboard') ? 'bg-red-50 text-[#DC2626] font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                            <span>Dashboard</span>
                        </a>

                        {{-- 2. Mega Menu: Core HR & Waktu (Hover to Open) --}}
                        <div class="relative"
                             @mouseenter="openMenu('core')"
                             @mouseleave="closeMenu()">
                            <button type="button"
                                    @click="megaMenu = (megaMenu === 'core') ? null : 'core'"
                                    :class="megaMenu === 'core' ? 'bg-red-50 text-[#DC2626] font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium'"
                                    class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs transition">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                <span>Core HR & Waktu</span>
                                <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-150" :class="megaMenu === 'core' ? 'rotate-180' : ''"></i>
                            </button>
                        </div>

                        {{-- 3. Mega Menu: Payroll & Finansial (Hover to Open) --}}
                        <div class="relative"
                             @mouseenter="openMenu('payroll')"
                             @mouseleave="closeMenu()">
                            <button type="button"
                                    @click="megaMenu = (megaMenu === 'payroll') ? null : 'payroll'"
                                    :class="megaMenu === 'payroll' ? 'bg-red-50 text-[#DC2626] font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium'"
                                    class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs transition">
                                <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                <span>Payroll & Finansial</span>
                                <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-150" :class="megaMenu === 'payroll' ? 'rotate-180' : ''"></i>
                            </button>
                        </div>

                        {{-- 4. Mega Menu: Talenta & Karir (Hover to Open) --}}
                        <div class="relative"
                             @mouseenter="openMenu('talent')"
                             @mouseleave="closeMenu()">
                            <button type="button"
                                    @click="megaMenu = (megaMenu === 'talent') ? null : 'talent'"
                                    :class="megaMenu === 'talent' ? 'bg-red-50 text-[#DC2626] font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium'"
                                    class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs transition">
                                <i data-lucide="target" class="w-3.5 h-3.5"></i>
                                <span>Talenta & Karir</span>
                                <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-150" :class="megaMenu === 'talent' ? 'rotate-180' : ''"></i>
                            </button>
                        </div>

                        {{-- 5. Mega Menu: Kepatuhan & Hubungan (Hover to Open) --}}
                        <div class="relative"
                             @mouseenter="openMenu('compliance')"
                             @mouseleave="closeMenu()">
                            <button type="button"
                                    @click="megaMenu = (megaMenu === 'compliance') ? null : 'compliance'"
                                    :class="megaMenu === 'compliance' ? 'bg-red-50 text-[#DC2626] font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium'"
                                    class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs transition">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                <span>Kepatuhan & Hubungan</span>
                                <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-150" :class="megaMenu === 'compliance' ? 'rotate-180' : ''"></i>
                            </button>
                        </div>

                        {{-- 6. Mega Menu: Enterprise & Sistem (Hover to Open) --}}
                        <div class="relative"
                             @mouseenter="openMenu('enterprise')"
                             @mouseleave="closeMenu()">
                            <button type="button"
                                    @click="megaMenu = (megaMenu === 'enterprise') ? null : 'enterprise'"
                                    :class="megaMenu === 'enterprise' ? 'bg-red-50 text-[#DC2626] font-bold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium'"
                                    class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs transition">
                                <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                                <span>Enterprise & Sistem</span>
                                <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-150" :class="megaMenu === 'enterprise' ? 'rotate-180' : ''"></i>
                            </button>
                        </div>

                        {{-- 7. Portal Mandiri ESS --}}
                        <a href="{{ route('ess.dashboard') }}"
                           @mouseenter="megaMenu = null"
                           class="flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition ml-1">
                            <i data-lucide="user-check" class="w-3 h-3"></i>
                            <span>Portal ESS</span>
                        </a>
                    @endif
                </nav>

                {{-- Right Profile & Notifications --}}
                <div class="flex items-center gap-2 shrink-0">
                    {{-- Notifikasi --}}
                    <div class="relative" x-data="{ 
                        notifOpen: false, 
                        unreadCount: 0, 
                        notifications: [],
                        fetchNotifs() {
                            fetch('{{ route('notifications.index') }}', {
                                headers: { 'Accept': 'application/json' }
                            })
                            .then(r => r.json())
                            .then(data => {
                                this.unreadCount = data.unread_count || 0;
                                this.notifications = data.notifications || [];
                            })
                            .catch(e => console.error(e));
                        },
                        markAllRead() {
                            fetch('{{ route('notifications.read-all') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.json())
                            .then(() => {
                                this.unreadCount = 0;
                                this.notifications.forEach(n => n.is_read = true);
                            });
                        }
                    }" x-init="fetchNotifs()">
                        <button @click="notifOpen = !notifOpen; megaMenu = null" type="button" class="relative p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition" title="Pemberitahuan">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            <template x-if="unreadCount > 0">
                                <span class="absolute top-0.5 right-0.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-red-600 text-[8px] font-black text-white" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                            </template>
                        </button>

                        <div x-show="notifOpen" x-cloak @click.away="notifOpen = false" class="absolute right-0 mt-2 w-80 sm:w-88 rounded-xl bg-white border border-slate-200 shadow-2xl p-3 z-50 transform origin-top-right transition-all">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-xs text-slate-900">Pemberitahuan</span>
                                    <template x-if="unreadCount > 0">
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black bg-red-100 text-red-700" x-text="unreadCount + ' Baru'"></span>
                                    </template>
                                </div>
                                <button @click="markAllRead" type="button" class="text-[10px] font-bold text-slate-500 hover:text-red-600 transition">
                                    Tandai Dibaca
                                </button>
                            </div>

                            <div class="mt-2 max-h-64 overflow-y-auto space-y-1.5 text-xs custom-scrollbar">
                                <template x-for="n in notifications" :key="n.id">
                                    <div class="p-2 rounded-lg border transition" :class="n.is_read ? 'bg-slate-50/60 border-slate-100' : 'bg-red-50/40 border-red-200'">
                                        <div class="flex items-start justify-between gap-1.5">
                                            <span class="font-bold text-slate-900 text-[11px]" x-text="n.title"></span>
                                            <span class="w-1.5 h-1.5 rounded-full shrink-0 mt-1" :class="n.is_read ? 'bg-transparent' : 'bg-red-600'"></span>
                                        </div>
                                        <p class="text-[10px] text-slate-600 mt-0.5" x-text="n.message"></p>
                                    </div>
                                </template>
                                <template x-if="notifications.length === 0">
                                    <div class="py-5 text-center text-slate-400 text-xs">
                                        Tidak ada pemberitahuan baru.
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- User Profile Pill --}}
                    <div class="text-right hidden sm:block">
                        <div class="text-[11px] font-bold text-slate-900 leading-tight">{{ auth()->user()->name }}</div>
                        <div class="text-[9px] text-slate-500 leading-tight">
                            {{ auth()->user()->is_super_admin ? 'Super Admin' : (auth()->user()->roles->first()?->name ?? 'User') }}
                        </div>
                    </div>

                    <div class="w-7 h-7 rounded-full bg-red-100 text-[#DC2626] font-bold flex items-center justify-center text-[10px] border border-red-200 shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Keluar">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- COMPACT FLOATING MEGA MENU CARD (NO EXCESSIVE HORIZONTAL BLEEDING)        --}}
            {{-- ========================================================================= --}}
            <div x-show="megaMenu !== null" x-cloak
                 @mouseenter="cancelClose()"
                 @mouseleave="closeMenu()"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1.5"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1.5"
                 class="absolute top-full left-4 right-4 sm:left-6 sm:right-6 lg:left-8 lg:right-8 bg-white border border-slate-200/90 rounded-2xl shadow-xl shadow-slate-900/10 z-50 overflow-hidden ring-1 ring-slate-900/5 mt-0.5">

                <div class="p-4 sm:p-5">

                    {{-- 1. MEGA MENU: CORE HR & WAKTU --}}
                    <div x-show="megaMenu === 'core'" class="grid grid-cols-1 md:grid-cols-3 gap-5 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        {{-- Kolom 1: Manajemen SDM & Struktur --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-red-50 text-red-600"><i data-lucide="users" class="w-3.5 h-3.5"></i></span>
                                <span>Manajemen SDM</span>
                            </div>
                            <div class="space-y-2">
                                {{-- Parent 1 --}}
                                <div class="group">
                                    <a href="{{ route('company.employees.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="user-check" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Data Karyawan</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-500 font-normal">Induk</span>
                                    </a>
                                    {{-- Sub Menus --}}
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.organization.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Struktur Departemen & Jabatan</span>
                                        </a>
                                        <a href="{{ route('company.orgchart.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Bagan Hirarki Organisasi Visual</span>
                                        </a>
                                    </div>
                                </div>

                                {{-- Parent 2 --}}
                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.clearances.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="user-x" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Clearance & Paklaring</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 font-normal">Offboarding</span>
                                    </a>
                                    <div class="pl-6 text-[11px] text-slate-500">
                                        <span>Serah terima tanggung jawab & surat pengalaman</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 2: Presensi, Shift & Geofencing --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-blue-50 text-blue-600"><i data-lucide="clock" class="w-3.5 h-3.5"></i></span>
                                <span>Presensi & Kehadiran</span>
                            </div>
                            <div class="space-y-2">
                                {{-- Parent 1 --}}
                                <div class="group">
                                    <a href="{{ route('company.attendance.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Presensi Harian GPS</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 font-normal">Geofence</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.attendance.corrections') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Koreksi Absensi (Lupa Clock-in)</span>
                                        </a>
                                        <a href="{{ route('company.qr.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Kios QR Touchless Lobi Kantor</span>
                                        </a>
                                    </div>
                                </div>

                                {{-- Parent 2 --}}
                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.attendance.shifts') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Shift & Pola Jadwal</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.rosters.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Pola Roster 24/7 (3 Shift Rotasi)</span>
                                        </a>
                                        <a href="{{ route('company.fwa.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Jadwal Fleksibel (WFH / Hybrid)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 3: Cuti & Lembur Statutori --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-amber-50 text-amber-600"><i data-lucide="calendar-off" class="w-3.5 h-3.5"></i></span>
                                <span>Cuti & Beban Lembur</span>
                            </div>
                            <div class="space-y-2">
                                {{-- Parent 1 --}}
                                <div class="group">
                                    <a href="{{ route('company.leave.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="calendar-check" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Cuti & Izin Karyawan</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-normal">12 Hari</span>
                                    </a>
                                    <div class="pl-6 text-[11px] text-slate-500">
                                        <span>Cuti tahunan, izin sakit, melahirkan & alasan penting</span>
                                    </div>
                                </div>

                                {{-- Parent 2 --}}
                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.overtime.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="timer" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Lembur (PP 35/2021)</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-red-50 text-red-600 font-normal">1/173</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.spl.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Surat Perintah Lembur (Batas 4 Jam)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. MEGA MENU: PAYROLL & FINANSIAL --}}
                    <div x-show="megaMenu === 'payroll'" class="grid grid-cols-1 md:grid-cols-3 gap-5 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        {{-- Kolom 1: Penggajian & Skala Upah --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-emerald-50 text-emerald-600"><i data-lucide="banknote" class="w-3.5 h-3.5"></i></span>
                                <span>Penggajian & Skala Upah</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.payroll.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="calculator" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Periode Penggajian</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 font-normal">Auto THP</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.components.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Komponen Upah & Potongan</span>
                                        </a>
                                        <a href="{{ route('company.disbursal.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Disbursal Bank (Batch BCA/Mandiri)</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.wage-scales.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="scale" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Struktur & Skala Upah</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 font-normal">Permenaker 1/17</span>
                                    </a>
                                    <div class="pl-6 text-[11px] text-slate-500">
                                        <span>Golongan jabatan, rentang upah min, mid, maks</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 2: Pajak PPh 21, BPJS & THR --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-purple-50 text-purple-600"><i data-lucide="receipt" class="w-3.5 h-3.5"></i></span>
                                <span>Pajak, BPJS & THR</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.tax.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>PPh 21 TER (PMK 168)</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 font-normal">Kat. A, B, C</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.taxes.ebupot.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>e-Bupot 21/26 & Formulir 1721-A1</span>
                                        </a>
                                        <a href="{{ route('company.fringe-benefits.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Pajak Natura Fasilitas (PMK 66)</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('thr-disbursements.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="gift" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>THR Statutori & Denda 5%</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 font-normal">Permenaker 6</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.thr.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Kalkulator Prorata THR Keagamaan</span>
                                        </a>
                                        <a href="{{ route('company.compliance.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Audit Kepatuhan UMK & Batas BPJS</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 3: Klaim, Fasilitas & Pinjaman --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-cyan-50 text-cyan-600"><i data-lucide="wallet" class="w-3.5 h-3.5"></i></span>
                                <span>Klaim, Pinjaman & Manfaat</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.reimbursements.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="receipt" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Reimbursement Biaya</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.business-trips.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Perjalanan Dinas (SPPD & Uang Muka)</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.loans.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="coins" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Kasbon & Finansial</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.cooperative.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Koperasi Simpan Pinjam & SHU</span>
                                        </a>
                                        <a href="{{ route('flex-benefits.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Cafeteria Flex-Benefits (Poin)</span>
                                        </a>
                                        <a href="{{ route('esop.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Opsi Saham ESOP (4-Yr Vesting)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. MEGA MENU: TALENTA & KARIR --}}
                    <div x-show="megaMenu === 'talent'" class="grid grid-cols-1 md:grid-cols-3 gap-5 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        {{-- Kolom 1: Rekrutmen & Onboarding --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-indigo-50 text-indigo-600"><i data-lucide="briefcase" class="w-3.5 h-3.5"></i></span>
                                <span>Rekrutmen & Onboarding</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.recruitment.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="user-plus" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Rekrutmen & ATS</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 font-normal">Pipeline</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.ijp.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Bursa Karir Internal (IJP)</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.onboarding.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Onboarding & Inventaris</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.mentorship.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Buddy Mentorship 90 Hari</span>
                                        </a>
                                        <a href="{{ route('company.asset-handover.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>BAST Aset & Cabut SSO Akses</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 2: Penilaian Kinerja & Pembelajaran (L&D) --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-rose-50 text-rose-600"><i data-lucide="target" class="w-3.5 h-3.5"></i></span>
                                <span>Kinerja & Pembelajaran</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.performance.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="award" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Kinerja (KPI & OKR)</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-rose-50 text-rose-700 font-normal">30/70</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.appraisals360.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Evaluasi 360° & Program PIP</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.training.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Pelatihan & Sertifikasi</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.kirkpatrick.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Evaluasi Kirkpatrick (Level 1-4)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 3: Suksesi & Transisi Karir --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-teal-50 text-teal-600"><i data-lucide="git-pull-request" class="w-3.5 h-3.5"></i></span>
                                <span>Suksesi & Transisi Karir</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.skills.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Matriks Kompetensi</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-teal-50 text-teal-700 font-normal">Skill Gap</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.succession.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Rencana Suksesi (Ready Now)</span>
                                        </a>
                                        <a href="{{ route('company.mutations.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Mutasi & Promosi Karir (SK)</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.terminations.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="file-minus" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Pesangon PHK (PP 35)</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-red-50 text-red-600 font-normal">UP/UPMK</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('vss.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Pensiun Dini Sukarela (VSS & DPLK)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. MEGA MENU: KEPATUHAN & HUBUNGAN --}}
                    <div x-show="megaMenu === 'compliance'" class="grid grid-cols-1 md:grid-cols-3 gap-5 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        {{-- Kolom 1: K3 & Keselamatan Kerja --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-red-50 text-red-600"><i data-lucide="shield" class="w-3.5 h-3.5"></i></span>
                                <span>K3 & Keselamatan</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.k3.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>K3 & Klaim JKK BPJS</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-red-50 text-red-600 font-normal">STMB</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('p2k3.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Panitia P2K3 Disnaker Triwulan</span>
                                        </a>
                                        <a href="{{ route('cob-insurance.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Asuransi CoB BPJS (First-Payer)</span>
                                        </a>
                                        <a href="{{ route('ergonomics.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Ergonomi K3 & Beban Digital</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 2: Hubungan Industrial & Serikat --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-amber-50 text-amber-600"><i data-lucide="scale" class="w-3.5 h-3.5"></i></span>
                                <span>Hubungan Industrial</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.disciplinary.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="file-warning" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Surat Peringatan (SP 1-3)</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 font-normal">Pasal 161</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.grievances.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Whistleblowing & Aduan Anonim</span>
                                        </a>
                                        <a href="{{ route('ppks.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Satgas PPKS & Ramah Perempuan</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.regulations.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="book-marked" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Peraturan Perusahaan (PP)</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-normal">2 Tahun</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.bipartite.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Forum LKS Bipartit (1:1 Pengusaha-Staf)</span>
                                        </a>
                                        <a href="{{ route('unions.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Serikat Pekerja & PKB (UU 21/2000)</span>
                                        </a>
                                        <a href="{{ route('company.disputes.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Mediasi Tripartit PHI (UU 2/2004)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 3: Regulasi, Alih Daya, TKA & Kontrak --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-emerald-50 text-emerald-600"><i data-lucide="file-check-2" class="w-3.5 h-3.5"></i></span>
                                <span>Regulasi, Kemitraan & Kontrak</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.wltk.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>WLTK Kemnaker (UU 7/81)</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.disability-audit.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Audit Kuota Disabilitas (1% Swasta)</span>
                                        </a>
                                        <a href="{{ route('company.expatriates.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Izin TKA & RPTKA (PP 34/2021)</span>
                                        </a>
                                        <a href="{{ route('company.jkp.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Manfaat JKP BPJS Kehilangan Kerja</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('outsourcing.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="truck" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Alih Daya & TUPE (PP 35)</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('internships.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Pemagangan Dalam Negeri (Permenaker 6)</span>
                                        </a>
                                        <a href="{{ route('company.digital-contracts.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Kontrak Digital TTE (UU ITE) & PKWT</span>
                                        </a>
                                        <a href="{{ route('company.probation.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Evaluasi Probasi PKWTT (3 Bulan)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. MEGA MENU: ENTERPRISE, ESG & SISTEM --}}
                    <div x-show="megaMenu === 'enterprise'" class="grid grid-cols-1 md:grid-cols-3 gap-5 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        {{-- Kolom 1: Analitik Eksekutif & Otomasi --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-blue-50 text-blue-600"><i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i></span>
                                <span>Analitik & Otomasi</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.analytics.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="pie-chart" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Eksekutif Analitik C-Level</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.analytics.advanced.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Turnover & Bradford Absensi Index</span>
                                        </a>
                                        <a href="{{ route('company.workflows.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Workflow Approval Berjenjang</span>
                                        </a>
                                        <a href="{{ route('company.cron.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Automasi Cron Jobs (H-30 Kontrak)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 2: Layanan Staf, Engagement & ESG --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-emerald-50 text-emerald-600"><i data-lucide="leaf" class="w-3.5 h-3.5"></i></span>
                                <span>Engagement & ESG Lingkungan</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('company.announcements.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="megaphone" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Papan Pengumuman & Alert</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.helpdesk.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>HR Helpdesk Layanan Surat/Paklaring</span>
                                        </a>
                                        <a href="{{ route('company.engagement.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Survei eNPS (-100 s.d +100) & Kudos</span>
                                        </a>
                                        <a href="{{ route('company.notifications.hub.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Hub Notifikasi Email & WhatsApp</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('esg.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="leaf" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>ESG Sustainability (POJK 51)</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('carbon-commute.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Emisi Komuter Scope 3 GHG Protocol</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 3: Privasi Data, DR & Keamanan --}}
                        <div class="md:pl-5 space-y-3 pt-3 md:pt-0">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">
                                <span class="p-1 rounded-md bg-slate-100 text-slate-700"><i data-lucide="lock" class="w-3.5 h-3.5"></i></span>
                                <span>Privasi & Keamanan</span>
                            </div>
                            <div class="space-y-2">
                                <div class="group">
                                    <a href="{{ route('pdp.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Privasi UU PDP 27/2022</span>
                                        </div>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-red-50 text-red-700 font-normal">DSAR</span>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('disaster-recovery.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Disaster Recovery Multi-Region (JKT-SIN)</span>
                                        </a>
                                        <a href="{{ route('zero-trust.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Zero Trust HSM AES-256 Enkripsi</span>
                                        </a>
                                        <a href="{{ route('company.security.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>Audit Trail Keamanan & IP Whitelist</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="group pt-1 border-t border-slate-50">
                                    <a href="{{ route('company.billing.index') }}" class="flex items-center justify-between text-xs font-semibold text-slate-800 hover:text-red-600 py-1 transition">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="credit-card" class="w-3.5 h-3.5 text-slate-400 group-hover:text-red-600"></i>
                                            <span>Paket & Langganan SaaS</span>
                                        </div>
                                    </a>
                                    <div class="pl-6 space-y-0.5 text-[11px] text-slate-500">
                                        <a href="{{ route('company.integrations.index') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>REST API Keys & Webhook</span>
                                        </a>
                                        <a href="{{ route('company.integrations.openapi') }}" class="flex items-center gap-1.5 py-0.5 hover:text-red-600 transition font-bold text-red-600">
                                            <span class="w-1 h-1 rounded-full bg-red-500"></span>
                                            <span>OpenAPI 3.1 Developer Portal</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Compact Status Bar Footer --}}
                <div class="px-5 py-2.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span class="font-medium text-slate-600">20 Phase Terintegrasi: Kepatuhan Penuh Hukum Ketenagakerjaan Indonesia</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] text-slate-400">Arahkan kursor untuk berpindah • ESC untuk menutup</span>
                        <button type="button" @click="megaMenu = null" class="text-slate-400 hover:text-slate-600 font-bold text-[11px]">
                            Tutup
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- MOBILE NAVIGATION DRAWER (ACCORDION BASED)               --}}
        {{-- ======================================================== --}}
        <div x-show="mobileOpen" x-cloak class="xl:hidden fixed inset-0 z-50 flex">
            <div @click="mobileOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            <div class="relative w-full max-w-xs bg-white h-full shadow-2xl flex flex-col z-10 overflow-y-auto">
                <div class="flex items-center justify-between p-3.5 border-b border-slate-200">
                    <span class="text-sm font-bold text-slate-900">Navigasi RiHRIS</span>
                    <button @click="mobileOpen = false" class="p-1 text-slate-500 hover:text-slate-800 rounded-lg">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-3.5 space-y-3 text-xs" x-data="{ mobileSection: 'core' }">
                    @if(auth()->user()->is_super_admin)
                        <div class="space-y-1">
                            <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Super Admin</div>
                            <a href="{{ route('super.admin.dashboard') }}" class="block px-2.5 py-1.5 rounded-lg font-semibold text-slate-700 hover:bg-slate-100">Dashboard</a>
                            <a href="{{ route('super.admin.companies.index') }}" class="block px-2.5 py-1.5 rounded-lg font-semibold text-slate-700 hover:bg-slate-100">Perusahaan</a>
                            <a href="{{ route('super.admin.plans.index') }}" class="block px-2.5 py-1.5 rounded-lg font-semibold text-slate-700 hover:bg-slate-100">Paket SaaS</a>
                            <a href="{{ route('super.admin.health.index') }}" class="block px-2.5 py-1.5 rounded-lg font-semibold text-slate-700 hover:bg-slate-100">Kesehatan MRR</a>
                            <a href="{{ route('super.admin.cms.index') }}" class="block px-2.5 py-1.5 rounded-lg font-semibold text-slate-700 hover:bg-slate-100">CMS Landing</a>
                            <a href="{{ route('super.admin.cms.leads.index') }}" class="block px-2.5 py-1.5 rounded-lg font-semibold text-slate-700 hover:bg-slate-100">CRM Leads</a>
                        </div>
                    @else
                        <a href="{{ route('company.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg font-bold bg-red-50 text-red-600">
                            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                            <span>Dashboard Utama</span>
                        </a>

                        <div class="space-y-1.5">
                            {{-- Section 1 --}}
                            <div>
                                <button @click="mobileSection = mobileSection === 'core' ? null : 'core'" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg font-bold text-slate-800 bg-slate-50">
                                    <span>Core HR & Waktu</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5" :class="mobileSection === 'core' ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="mobileSection === 'core'" class="pl-3 py-1 space-y-1">
                                    <a href="{{ route('company.employees.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Data Karyawan</a>
                                    <a href="{{ route('company.organization.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Struktur Organisasi</a>
                                    <a href="{{ route('company.attendance.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Presensi Harian GPS</a>
                                    <a href="{{ route('company.attendance.corrections') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Koreksi Presensi</a>
                                    <a href="{{ route('company.attendance.shifts') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Shift & Kalender</a>
                                    <a href="{{ route('company.qr.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Kios QR Presensi</a>
                                    <a href="{{ route('company.leave.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Cuti & Izin</a>
                                    <a href="{{ route('company.overtime.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Lembur (PP 35)</a>
                                    <a href="{{ route('company.spl.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Surat Perintah Lembur</a>
                                </div>
                            </div>

                            {{-- Section 2 --}}
                            <div>
                                <button @click="mobileSection = mobileSection === 'payroll' ? null : 'payroll'" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg font-bold text-slate-800 bg-slate-50">
                                    <span>Payroll & Finansial</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5" :class="mobileSection === 'payroll' ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="mobileSection === 'payroll'" class="pl-3 py-1 space-y-1">
                                    <a href="{{ route('company.payroll.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Periode Penggajian</a>
                                    <a href="{{ route('company.components.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Komponen Gaji</a>
                                    <a href="{{ route('company.wage-scales.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Struktur Skala Upah</a>
                                    <a href="{{ route('company.tax.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">PPh 21 TER</a>
                                    <a href="{{ route('company.taxes.ebupot.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">e-Bupot & 1721-A1</a>
                                    <a href="{{ route('thr-disbursements.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">THR Statutori</a>
                                    <a href="{{ route('company.reimbursements.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Reimbursement</a>
                                    <a href="{{ route('company.loans.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Kasbon & Pinjaman</a>
                                </div>
                            </div>

                            {{-- Section 3 --}}
                            <div>
                                <button @click="mobileSection = mobileSection === 'talent' ? null : 'talent'" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg font-bold text-slate-800 bg-slate-50">
                                    <span>Talenta & Karir</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5" :class="mobileSection === 'talent' ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="mobileSection === 'talent'" class="pl-3 py-1 space-y-1">
                                    <a href="{{ route('company.recruitment.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Rekrutmen ATS</a>
                                    <a href="{{ route('company.onboarding.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Onboarding & Aset</a>
                                    <a href="{{ route('company.performance.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Kinerja KPI/OKR</a>
                                    <a href="{{ route('company.appraisals360.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Evaluasi 360 & PIP</a>
                                    <a href="{{ route('company.training.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Pelatihan L&D</a>
                                    <a href="{{ route('company.skills.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Skill Matrix</a>
                                    <a href="{{ route('company.succession.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Rencana Suksesi</a>
                                    <a href="{{ route('company.mutations.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Mutasi & Promosi</a>
                                    <a href="{{ route('company.terminations.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Pesangon PHK</a>
                                </div>
                            </div>

                            {{-- Section 4 --}}
                            <div>
                                <button @click="mobileSection = mobileSection === 'compliance' ? null : 'compliance'" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg font-bold text-slate-800 bg-slate-50">
                                    <span>Kepatuhan & Hubungan</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5" :class="mobileSection === 'compliance' ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="mobileSection === 'compliance'" class="pl-3 py-1 space-y-1">
                                    <a href="{{ route('company.k3.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">K3 & Klaim JKK</a>
                                    <a href="{{ route('company.disciplinary.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Surat Peringatan</a>
                                    <a href="{{ route('company.grievances.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Aduan Anonim</a>
                                    <a href="{{ route('company.wltk.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">WLTK Kemnaker</a>
                                    <a href="{{ route('outsourcing.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Alih Daya PP 35</a>
                                    <a href="{{ route('unions.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Serikat & PKB</a>
                                </div>
                            </div>

                            {{-- Section 5 --}}
                            <div>
                                <button @click="mobileSection = mobileSection === 'enterprise' ? null : 'enterprise'" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg font-bold text-slate-800 bg-slate-50">
                                    <span>Enterprise & Sistem</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5" :class="mobileSection === 'enterprise' ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="mobileSection === 'enterprise'" class="pl-3 py-1 space-y-1">
                                    <a href="{{ route('company.analytics.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Analitik C-Level</a>
                                    <a href="{{ route('company.workflows.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Workflow Approval</a>
                                    <a href="{{ route('esg.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">ESG Sustainability</a>
                                    <a href="{{ route('pdp.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Privasi UU PDP</a>
                                    <a href="{{ route('disaster-recovery.index') }}" class="block py-0.5 text-slate-600 hover:text-red-600">Disaster Recovery</a>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200">
                            <a href="{{ route('ess.dashboard') }}" class="flex items-center justify-center gap-1.5 p-2 rounded-lg font-bold bg-emerald-600 text-white text-xs">
                                <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                <span>Portal Mandiri ESS</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </header>

    {{-- Main Outer Container: Full Width Canvas --}}
    <div class="flex-1 max-w-[1440px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-5">
        <main class="w-full">
            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5 shadow-sm">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2.5 shadow-sm">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Script inisialisasi icon Lucide --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
        document.addEventListener('alpine:initialized', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
