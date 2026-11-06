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
    </style>
</head>
<body class="h-full antialiased flex flex-col" x-data="{ sidebarOpen: false }">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Mobile toggle & Brand -->
                <div class="flex items-center gap-4">
                    <button type="button" @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>

                    <a href="{{ auth()->user()->is_super_admin ? route('super.admin.dashboard') : route('company.dashboard') }}" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#DC2626] flex items-center justify-center text-white shadow-sm">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xl font-black text-slate-900">Ri<span class="text-[#DC2626]">HRIS</span></span>
                    </a>

                    <!-- Current Tenant Badge -->
                    @if(isset($currentTenant))
                        <div class="hidden sm:flex items-center gap-2 ml-4 pl-4 border-l border-slate-200">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            <span class="text-xs font-semibold text-slate-700">{{ $currentTenant->name }}</span>
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-red-50 text-[#DC2626] border border-red-200">
                                {{ $currentTenant->status }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Right Profile & Logout -->
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-500">
                            {{ auth()->user()->is_super_admin ? 'Super Administrator' : (auth()->user()->roles->first()?->name ?? 'User') }}
                        </div>
                    </div>

                    <div class="w-9 h-9 rounded-full bg-red-100 text-[#DC2626] font-bold flex items-center justify-center text-xs border border-red-200">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Keluar">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Outer Container: Centered max-w-[1440px] -->
    <div class="flex-1 max-w-[1440px] w-full mx-auto px-4 sm:px-6 lg:px-8 flex py-6 gap-8">

        <!-- Sidebar Navigation (Desktop Fixed, Mobile Drawer) -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar Body -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white lg:bg-transparent border-r lg:border-r-0 border-slate-200 p-6 lg:p-0 flex flex-col justify-between transition-transform duration-200 ease-in-out shrink-0 overflow-y-auto">

            <div>
                <!-- Mobile Drawer Header -->
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200 lg:hidden">
                    <span class="text-base font-bold text-slate-900">Menu Navigasi</span>
                    <button @click="sidebarOpen = false" class="p-1 text-slate-500 hover:text-slate-800">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <nav class="space-y-1.5 text-xs font-semibold">
                    @if(auth()->user()->is_super_admin)
                        <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Super Admin Platform</div>
                        <a href="{{ route('super.admin.dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('super.admin.dashboard') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard Platform
                        </a>
                        <a href="{{ route('super.admin.companies.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('super.admin.companies.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                            Kelola Perusahaan
                        </a>
                        <a href="{{ route('super.admin.cms.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('super.admin.cms.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                            CMS Landing Page
                        </a>
                    @else
                        <!-- General HR Management -->
                        <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Ruang Kerja HR</div>
                        <a href="{{ route('company.dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.dashboard') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard Utama
                        </a>

                        <!-- Karyawan (Phase 2) -->
                        <div class="pt-3 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Manajemen SDM</div>
                        <a href="{{ route('company.employees.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.employees.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            Data Karyawan
                        </a>
                        <a href="{{ route('company.organization.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.organization.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="network" class="w-4 h-4"></i>
                            Struktur Organisasi
                        </a>

                        <!-- Presensi & Jadwal (Phase 2) -->
                        <div class="pt-3 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Waktu & Kehadiran</div>
                        <a href="{{ route('company.attendance.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.attendance.index') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            Presensi Harian
                        </a>
                        <a href="{{ route('company.attendance.corrections') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.attendance.corrections') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="check-square" class="w-4 h-4"></i>
                            Koreksi Presensi
                        </a>
                        <a href="{{ route('company.attendance.shifts') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.attendance.shifts') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            Shift & Kalender Kerja
                        </a>

                        <!-- Cuti & Lembur (Phase 2) -->
                        <div class="pt-3 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Izin & Beban Kerja</div>
                        <a href="{{ route('company.leave.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.leave.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="calendar-off" class="w-4 h-4"></i>
                            Cuti & Izin
                        </a>
                        <a href="{{ route('company.overtime.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.overtime.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="timer" class="w-4 h-4"></i>
                            Lembur (PP 35/2021)
                        </a>

                        <!-- Payroll, BPJS & Pajak (Phase 3) -->
                        <div class="pt-3 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Payroll & Kepatuhan</div>
                        <a href="{{ route('company.payroll.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.payroll.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="banknote" class="w-4 h-4"></i>
                            Periode Penggajian
                        </a>
                        <a href="{{ route('company.components.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.components.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="sliders" class="w-4 h-4"></i>
                            Komponen Gaji
                        </a>
                        <a href="{{ route('company.thr.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.thr.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="gift" class="w-4 h-4"></i>
                            Kalkulasi THR Keagamaan
                        </a>
                        <a href="{{ route('company.tax.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.tax.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            PPh 21 & Bukti Potong 1721-A1
                        </a>

                        <!-- Biaya & Kontrak (Phase 3) -->
                        <div class="pt-3 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Klaim & Legalitas</div>
                        <a href="{{ route('company.reimbursements.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.reimbursements.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                            Reimbursement & Biaya
                        </a>
                        <a href="{{ route('company.contracts.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.contracts.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="file-signature" class="w-4 h-4"></i>
                            Manajemen Kontrak PKWT
                        </a>

                        <!-- Talenta & Pengembangan SDM (Phase 4) -->
                        <div class="pt-3 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Manajemen Talenta</div>
                        <a href="{{ route('company.recruitment.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.recruitment.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                            Rekrutmen & Pelamar
                        </a>
                        <a href="{{ route('company.onboarding.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.onboarding.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                            Onboarding & Aset Kantor
                        </a>
                        <a href="{{ route('company.performance.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.performance.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="target" class="w-4 h-4"></i>
                            Manajemen Kinerja (KPI/OKR)
                        </a>
                        <a href="{{ route('company.training.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('company.training.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                            Pelatihan & Sertifikasi
                        </a>

                        <!-- Portal ESS -->
                        <div class="pt-3 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Portal Karyawan</div>
                        <a href="{{ route('ess.dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('ess.*') ? 'bg-[#DC2626] text-white shadow-sm shadow-red-500/20' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                            Employee Self-Service
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Footer Compliance badge -->
            <div class="p-3.5 mt-6 rounded-xl bg-white border border-slate-200/80 shadow-sm text-center shrink-0">
                <div class="text-[11px] font-bold text-slate-800">PP 35/2021 & PPh 21 TER</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Sistem Patuh Regulasi ID</div>
            </div>
        </aside>

        <!-- Main Content Area (Centered) -->
        <main class="flex-1 min-w-0">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
