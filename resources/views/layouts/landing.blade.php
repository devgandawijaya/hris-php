<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings['seo_title'] ?? 'RiHRIS — Software HRIS SaaS Multi-Company Indonesia' }}</title>
    <meta name="description" content="{{ $settings['seo_description'] ?? 'Platform HRIS SaaS Indonesia: Absensi GPS, Cuti, Payroll, PPh 21 TER, BPJS.' }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind & Alpine Scripts -->
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
                        'landing': '1200px',
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
<body class="bg-white text-slate-800 antialiased flex flex-col min-h-screen">

    <!-- Top Navigation Bar (Centered container max-w-landing) -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#991B1B] to-[#DC2626] flex items-center justify-center text-white shadow-md shadow-red-500/20">
                        <i data-lucide="layers" class="w-6 h-6 stroke-[2.2]"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-black tracking-tight text-slate-900">Ri<span class="text-[#DC2626]">HRIS</span></span>
                        <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">SaaS Indonesia</span>
                    </div>
                </a>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-sm font-medium text-slate-600 hover:text-[#DC2626] transition">Fitur Utama</a>
                    <a href="#modules" class="text-sm font-medium text-slate-600 hover:text-[#DC2626] transition">Modul HRIS</a>
                    <a href="#why-us" class="text-sm font-medium text-slate-600 hover:text-[#DC2626] transition">Kepatuhan Hukum</a>
                    <a href="#pricing" class="text-sm font-medium text-slate-600 hover:text-[#DC2626] transition">Paket Harga</a>
                    <a href="#faq" class="text-sm font-medium text-slate-600 hover:text-[#DC2626] transition">FAQ</a>
                    <a href="#contact" class="text-sm font-medium text-slate-600 hover:text-[#DC2626] transition">Kontak</a>
                </nav>

                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center gap-4">
                    @auth
                        @if(auth()->user()->is_super_admin)
                            <a href="{{ route('super.admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm">
                                <i data-lucide="shield" class="w-4 h-4 text-red-400"></i>
                                Super Admin Panel
                            </a>
                        @else
                            <a href="{{ route('company.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                Buka Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 hover:text-[#DC2626] px-3 py-2 transition">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#DC2626] hover:bg-[#991B1B] shadow-sm shadow-red-600/30 transition">
                            Coba Gratis 14 Hari
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 hover:text-slate-900 focus:outline-none">
                    <i data-lucide="menu" class="w-6 h-6" x-show="!mobileMenuOpen"></i>
                    <i data-lucide="x" class="w-6 h-6" x-show="mobileMenuOpen" x-cloak></i>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div class="md:hidden bg-white border-b border-slate-200 px-4 pt-2 pb-6 space-y-3" x-show="mobileMenuOpen" x-cloak x-transition>
            <a href="#features" @click="mobileMenuOpen = false" class="block py-2 text-base font-medium text-slate-700">Fitur Utama</a>
            <a href="#modules" @click="mobileMenuOpen = false" class="block py-2 text-base font-medium text-slate-700">Modul HRIS</a>
            <a href="#why-us" @click="mobileMenuOpen = false" class="block py-2 text-base font-medium text-slate-700">Kepatuhan Hukum</a>
            <a href="#pricing" @click="mobileMenuOpen = false" class="block py-2 text-base font-medium text-slate-700">Paket Harga</a>
            <a href="#faq" @click="mobileMenuOpen = false" class="block py-2 text-base font-medium text-slate-700">FAQ</a>
            <a href="#contact" @click="mobileMenuOpen = false" class="block py-2 text-base font-medium text-slate-700">Kontak</a>
            <div class="pt-4 border-t border-slate-100 flex flex-col gap-2">
                @auth
                    <a href="{{ route('company.dashboard') }}" class="w-full text-center py-2.5 rounded-lg text-sm font-medium bg-[#DC2626] text-white">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-lg text-sm font-medium border border-slate-300 text-slate-700">Masuk Akun</a>
                    <a href="{{ route('register') }}" class="w-full text-center py-2.5 rounded-lg text-sm font-semibold bg-[#DC2626] text-white shadow-sm">Coba Gratis 14 Hari</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Yield -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer (Centered container max-w-landing) -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-12 border-t border-slate-800">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                <!-- Col 1: Brand & Bio -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#DC2626] flex items-center justify-center text-white">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <span class="text-2xl font-black text-white">Ri<span class="text-[#DC2626]">HRIS</span></span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        {{ $settings['site_tagline'] ?? 'Platform Human Resource Information System terintegrasi multi-perusahaan dengan kalkulasi PPh 21 TER, BPJS, dan kepatuhan hukum ketenagakerjaan Indonesia.' }}
                    </p>
                    <div class="flex items-center gap-3 text-slate-400 pt-2">
                        <span class="inline-flex items-center gap-1.5 text-xs bg-slate-800 px-3 py-1 rounded-full text-emerald-400 border border-slate-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            100% Indonesian Compliance
                        </span>
                    </div>
                </div>

                <!-- Col 2: Solusi Produk -->
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-200 mb-4">Solusi & Modul</h4>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><a href="#modules" class="hover:text-white transition">Employee Master Data</a></li>
                        <li><a href="#modules" class="hover:text-white transition">Presensi GPS & Swafoto</a></li>
                        <li><a href="#modules" class="hover:text-white transition">Payroll & Slip Gaji PDF</a></li>
                        <li><a href="#modules" class="hover:text-white transition">Kalkulator PPh 21 TER 2024</a></li>
                        <li><a href="#modules" class="hover:text-white transition">BPJS Kesehatan & Ketenagakerjaan</a></li>
                        <li><a href="#modules" class="hover:text-white transition">Uang Kompensasi PKWT PP 35/2021</a></li>
                    </ul>
                </div>

                <!-- Col 3: Navigasi Cepat -->
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-200 mb-4">Tautan</h4>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><a href="#pricing" class="hover:text-white transition">Paket Berlangganan</a></li>
                        <li><a href="#why-us" class="hover:text-white transition">Keunggulan Multi-Tenant</a></li>
                        <li><a href="#faq" class="hover:text-white transition">Pertanyaan Umum (FAQ)</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Masuk Akun Perusahaan</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Registrasi Perusahaan Baru</a></li>
                    </ul>
                </div>

                <!-- Col 4: Informasi Kontak Legal -->
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-200 mb-4">Kantor Pusat</h4>
                    <p class="text-sm text-slate-400 leading-relaxed mb-3">
                        {{ $settings['contact_address'] ?? 'One Pacific Place Suite 1500, SCBD Jakarta Selatan 12190' }}
                    </p>
                    <div class="space-y-1.5 text-sm text-slate-400">
                        <p class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-red-500"></i>
                            {{ $settings['contact_email'] ?? 'halo@rihris.id' }}
                        </p>
                        <p class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4 text-red-500"></i>
                            {{ $settings['contact_phone'] ?? '+62 21 8062 8900' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} RiHRIS SaaS Indonesia. Seluruh hak cipta dilindungi undang-undang.</p>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-slate-400">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-slate-400">Syarat & Ketentuan</a>
                    <a href="#" class="hover:text-slate-400">Keamanan Data</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>

