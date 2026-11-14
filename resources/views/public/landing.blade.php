@extends('layouts.landing')

@section('content')

<!-- ========================================== -->
<!-- 1. HERO SLIDER SECTION (Alpine.js Multi-Slide Carousel) -->
<!-- ========================================== -->
<section class="relative bg-gradient-to-b from-red-50/70 via-slate-50/40 to-white overflow-hidden pt-8 pb-16 lg:pt-14 lg:pb-24"
         x-data="{
             activeSlide: 0,
             totalSlides: {{ $sliders->count() > 0 ? $sliders->count() : 1 }},
             autoSlideTimer: null,
             startAutoSlide() {
                 this.stopAutoSlide();
                 this.autoSlideTimer = setInterval(() => {
                     this.nextSlide();
                 }, 7000);
             },
             stopAutoSlide() {
                 if (this.autoSlideTimer) {
                     clearInterval(this.autoSlideTimer);
                     this.autoSlideTimer = null;
                 }
             },
             nextSlide() {
                 this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
             },
             prevSlide() {
                 this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
             },
             goToSlide(index) {
                 this.activeSlide = index;
             }
         }"
         x-init="startAutoSlide()"
         @mouseenter="stopAutoSlide()"
         @mouseleave="startAutoSlide()">

    <!-- Decorative background elements -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-red-200/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -right-24 w-96 h-96 bg-amber-200/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-xs">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Slides Wrapper -->
        <div class="relative min-h-[520px] flex items-center">
            @forelse($sliders as $index => $slide)
                <div x-show="activeSlide === {{ $index }}"
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-98"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-300 absolute"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 -translate-y-4 scale-98"
                     class="w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center"
                     x-cloak>

                    <!-- Left: Slide Content -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-red-100/90 text-[#DC2626] text-xs font-bold tracking-wide">
                            <span class="w-2 h-2 rounded-full bg-[#DC2626] animate-pulse"></span>
                            @if($index === 0)
                                Arsitektur Multi-Perusahaan Terintegrasi
                            @elseif($index === 1)
                                Akurasi Pajak 100% Sesuai PMK 168/2023
                            @elseif($index === 2)
                                Perlindungan Hukum Ketenagakerjaan PP 35/2021
                            @elseif($index === 3)
                                Keselamatan Kerja SMK3 & Kepatuhan UU PDP
                            @else
                                Solusi HRIS Enterprise Indonesia No. 1
                            @endif
                        </div>

                        <h1 class="text-3xl sm:text-4xl lg:text-[42px] font-black text-slate-900 leading-[1.18] tracking-tight">
                            {{ $slide->title }}
                        </h1>

                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl">
                            {{ $slide->subtitle }}
                        </p>

                        <div class="flex flex-wrap items-center gap-3.5 pt-2">
                            <a href="{{ $slide->cta_url ?: route('register') }}"
                               class="inline-flex items-center gap-2.5 px-6 py-3.5 rounded-xl text-base font-bold text-white bg-[#DC2626] hover:bg-[#991B1B] shadow-lg shadow-red-600/30 transition transform hover:-translate-y-0.5">
                                {{ $slide->cta_text ?: 'Mulai Uji Coba Gratis 14 Hari' }}
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </a>
                            <a href="#demo-access"
                               class="inline-flex items-center gap-2 px-5 py-3.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 shadow-xs transition">
                                <i data-lucide="key" class="w-4 h-4 text-amber-500"></i>
                                Coba Akun Demo
                            </a>
                        </div>

                        <!-- Highlights Pills -->
                        <div class="pt-2 flex flex-wrap gap-y-2 gap-x-5 text-xs font-semibold text-slate-600">
                            @if($index === 1)
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> PPh 21 TER Kategori A, B, C</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> 5 Program BPJS TK</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Disbursal BCA & Mandiri</span>
                            @elseif($index === 2)
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> SHA-256 e-Seal Hash</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Kompensasi PKWT Otomatis</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Ekspor WLTK Kemnaker</span>
                            @elseif($index === 3)
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Investigasi Insiden K3 5-Why</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Brankas Rahasia PPKS</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Hak Subjek Data DSAR</span>
                            @else
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Geofence Anti-Fake GPS</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Shift Bergilir 24/7</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i> Multi-Anak Perusahaan</span>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Dynamic Slide Interactive Visual Card -->
                    <div class="lg:col-span-5">
                        <div class="relative rounded-2xl bg-gradient-to-tr from-slate-900 via-slate-800 to-slate-900 p-6 shadow-2xl border border-slate-700 text-white">

                            <!-- Visual Header Bar -->
                            <div class="flex items-center justify-between pb-4 border-b border-slate-700/80 mb-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                </div>
                                <span class="text-xs font-mono text-slate-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                    RiHRIS Cloud Core v2.4
                                </span>
                            </div>

                            <!-- SLIDE 0 / DEFAULT VISUAL: Multi-Company & GPS Attendance -->
                            @if($index === 0)
                                <div class="space-y-3.5">
                                    <div class="p-3.5 rounded-xl bg-slate-800/90 border border-slate-700">
                                        <div class="text-[11px] text-slate-400">Entitas Aktif Terpilih</div>
                                        <div class="text-sm font-bold text-white flex items-center justify-between mt-1">
                                            <span>PT Solusi Digital Nusantara</span>
                                            <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded font-mono">Tenant ID: 94</span>
                                        </div>
                                    </div>

                                    <div class="p-3.5 rounded-xl bg-slate-800/70 border border-slate-700/80 space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-slate-300 flex items-center gap-1.5">
                                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-red-400"></i>
                                                Geofence Presensi Kantor
                                            </span>
                                            <span class="text-emerald-400 font-semibold font-mono">Radius 50m (Valid)</span>
                                        </div>
                                        <div class="w-full bg-slate-700 h-2 rounded-full overflow-hidden">
                                            <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-2 rounded-full w-[92%]"></div>
                                        </div>
                                        <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                                            <span>Lat: -6.2254 | Long: 106.8091</span>
                                            <span>Mock-GPS: Clean</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                                        <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/80">
                                            <div class="text-[10px] text-slate-400">Shift Hari Ini</div>
                                            <div class="text-sm font-bold text-white mt-1">Shift Regular Pagi</div>
                                            <div class="text-[10px] text-slate-400">08:00 - 17:00 WIB</div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/80">
                                            <div class="text-[10px] text-slate-400">Status ESS</div>
                                            <div class="text-sm font-bold text-emerald-400 mt-1 flex items-center gap-1">
                                                <i data-lucide="camera" class="w-3.5 h-3.5"></i> Terverifikasi
                                            </div>
                                            <div class="text-[10px] text-slate-400">Swafoto Liveness OK</div>
                                        </div>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-red-950/40 border border-red-800/50 flex items-center gap-2.5">
                                        <i data-lucide="shield-check" class="w-5 h-5 text-red-400 shrink-0"></i>
                                        <p class="text-[11px] text-red-200">
                                            Data seluruh entitas anak perusahaan terisolasi otomatis via Global Eloquent Scope.
                                        </p>
                                    </div>
                                </div>

                            <!-- SLIDE 1: Payroll & PPh 21 TER PMK 168/2023 -->
                            @elseif($index === 1)
                                <div class="space-y-3.5">
                                    <div class="p-3.5 rounded-xl bg-slate-800/90 border border-slate-700 flex justify-between items-center">
                                        <div>
                                            <div class="text-[11px] text-slate-400">Gaji Pokok & Tunjangan Tetap</div>
                                            <div class="text-base font-bold text-white font-mono mt-0.5">Rp 12.000.000</div>
                                        </div>
                                        <span class="text-xs bg-red-500/20 text-red-300 px-2.5 py-1 rounded-lg font-bold border border-red-500/30">
                                            TER Kat. B (K/1)
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-700/70">
                                            <span class="text-[10px] text-slate-400">PPh 21 TER (4%)</span>
                                            <div class="text-sm font-bold text-rose-400 font-mono mt-0.5">- Rp 480.000</div>
                                        </div>
                                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-700/70">
                                            <span class="text-[10px] text-slate-400">BPJS TK JHT (2%)</span>
                                            <div class="text-sm font-bold text-rose-400 font-mono mt-0.5">- Rp 240.000</div>
                                        </div>
                                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-700/70">
                                            <span class="text-[10px] text-slate-400">BPJS TK JP (1% cap)</span>
                                            <div class="text-sm font-bold text-rose-400 font-mono mt-0.5">- Rp 100.423</div>
                                        </div>
                                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-700/70">
                                            <span class="text-[10px] text-slate-400">BPJS Kes (1% cap)</span>
                                            <div class="text-sm font-bold text-rose-400 font-mono mt-0.5">- Rp 120.000</div>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-xl bg-emerald-950/40 border border-emerald-800/50 flex justify-between items-center">
                                        <div>
                                            <div class="text-[10px] text-emerald-300 font-medium uppercase tracking-wider">Take Home Pay (THP)</div>
                                            <div class="text-lg font-extrabold text-emerald-400 font-mono">Rp 11.059.577</div>
                                        </div>
                                        <span class="text-xs bg-emerald-500/20 text-emerald-300 px-2.5 py-1 rounded font-semibold flex items-center gap-1">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i> e-Slip PDF
                                        </span>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-between text-[11px] text-slate-400">
                                        <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i> BCA Payroll Format</span>
                                        <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i> Mandiri MCM Ready</span>
                                    </div>
                                </div>

                            <!-- SLIDE 2: Digital Smart Contract & PP 35/2021 -->
                            @elseif($index === 2)
                                <div class="space-y-3.5">
                                    <div class="p-3.5 rounded-xl bg-slate-800/90 border border-slate-700 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] text-slate-400">Perjanjian Kerja Waktu Tertentu (PKWT)</span>
                                            <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded font-mono font-semibold">Tersertifikasi e-Seal</span>
                                        </div>
                                        <div class="text-sm font-bold text-white">No. 018/PKWT-HR/SDN/XI/2026</div>
                                        <div class="p-2 rounded bg-slate-900/80 border border-slate-700 font-mono text-[10px] text-amber-300 break-all">
                                            SHA-256: e8b9f1a23c4d5e6f7a8b9c0d1e2f3a4b...
                                        </div>
                                    </div>

                                    <div class="p-3.5 rounded-xl bg-slate-800/70 border border-slate-700/80 space-y-1.5">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-slate-300">Estimasi Uang Kompensasi PKWT</span>
                                            <span class="font-mono text-emerald-400 font-bold">Rp 8.000.000</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            Formula PP 35/2021: (12 Bulan / 12) &times; Upah Pokok Rp 8.000.000
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-700/70">
                                            <span class="text-[10px] text-slate-400">Kepatuhan WLTK</span>
                                            <div class="text-xs font-bold text-white mt-0.5 flex items-center gap-1">
                                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i> UU No. 7/1981
                                            </div>
                                        </div>
                                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-700/70">
                                            <span class="text-[10px] text-slate-400">Klaim JKP BPJS</span>
                                            <div class="text-xs font-bold text-white mt-0.5 flex items-center gap-1">
                                                <i data-lucide="file-check" class="w-3.5 h-3.5 text-blue-400"></i> E-Sertifikat Siap
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-slate-800/40 border border-slate-700 text-[11px] text-slate-300 flex items-center gap-2">
                                        <i data-lucide="qr-code" class="w-4 h-4 text-red-400 shrink-0"></i>
                                        <span>Dilengkapi QR Code validasi keabsahan dokumen Kemnaker.</span>
                                    </div>
                                </div>

                            <!-- SLIDE 3 / ENTERPRISE: K3, Satgas PPKS & UU PDP -->
                            @else
                                <div class="space-y-3.5">
                                    <div class="p-3.5 rounded-xl bg-slate-800/90 border border-slate-700 flex justify-between items-center">
                                        <div>
                                            <div class="text-[11px] text-slate-400">Status Sistem Manajemen K3 (SMK3)</div>
                                            <div class="text-sm font-bold text-emerald-400 flex items-center gap-1.5 mt-0.5">
                                                <i data-lucide="shield-check" class="w-4 h-4"></i> Zero Accident Year 2026
                                            </div>
                                        </div>
                                        <span class="text-[10px] bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded font-mono">PP 50/2012</span>
                                    </div>

                                    <div class="p-3 rounded-xl bg-slate-800/70 border border-slate-700/80 space-y-1.5">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-slate-300 flex items-center gap-1">
                                                <i data-lucide="lock" class="w-3.5 h-3.5 text-red-400"></i>
                                                Brankas Rahasia Satgas PPKS
                                            </span>
                                            <span class="text-[10px] bg-red-500/20 text-red-300 px-2 py-0.5 rounded font-mono">End-to-End Encrypted</span>
                                        </div>
                                        <p class="text-[10px] text-slate-400">Penanganan laporan pelecehan seksual terlindungi ketat mandat UU TPKS No. 12/2022.</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-700/70">
                                            <span class="text-[10px] text-slate-400">UU PDP No. 27/2022</span>
                                            <div class="text-xs font-bold text-white mt-0.5">DSAR Subject Rights</div>
                                        </div>
                                        <div class="p-2.5 rounded-lg bg-slate-800/60 border border-slate-700/70">
                                            <span class="text-[10px] text-slate-400">Koperasi Karyawan</span>
                                            <div class="text-xs font-bold text-white mt-0.5">Auto Slip Potongan</div>
                                        </div>
                                    </div>

                                    <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-700 text-[11px] text-slate-300 flex items-center gap-2">
                                        <i data-lucide="server" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                        <span>Disaster Recovery RTO &lt; 15 menit &amp; Zero-Trust RBAC.</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center w-full py-16">
                    <h1 class="text-4xl font-extrabold text-slate-900">RiHRIS SaaS Indonesia</h1>
                    <p class="mt-4 text-slate-600">Platform Human Resource Information System Modern Terintegrasi Regulasi Indonesia.</p>
                </div>
            @endforelse
        </div>

        <!-- Slider Navigation Controls & Thumbnail Tabs -->
        @if($sliders->count() > 1)
            <div class="pt-8 mt-4 border-t border-slate-200/80">
                <!-- Desktop Tabbed Buttons -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($sliders as $index => $slide)
                        <button type="button"
                                @click="goToSlide({{ $index }})"
                                class="p-3 rounded-xl border text-left transition-all duration-300 flex flex-col justify-between"
                                :class="activeSlide === {{ $index }} ? 'bg-white border-[#DC2626] shadow-md shadow-red-500/10 ring-1 ring-red-500/20' : 'bg-white/60 hover:bg-white border-slate-200 opacity-70 hover:opacity-100'">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#DC2626]">
                                    Pilar 0{{ $index + 1 }}
                                </span>
                                <div class="w-2 h-2 rounded-full" :class="activeSlide === {{ $index }} ? 'bg-[#DC2626]' : 'bg-slate-300'"></div>
                            </div>
                            <div class="text-xs font-bold text-slate-800 line-clamp-1">
                                {{ Str::limit($slide->title, 34) }}
                            </div>
                            <!-- Progress line when active -->
                            <div class="mt-2.5 w-full bg-slate-100 h-1 rounded-full overflow-hidden">
                                <div class="h-1 rounded-full bg-[#DC2626] transition-all"
                                     :class="activeSlide === {{ $index }} ? 'w-full duration-[7000ms]' : 'w-0 duration-300'"></div>
                            </div>
                        </button>
                    @endforeach
                </div>

                <!-- Slider Arrows and Dots on Mobile -->
                <div class="flex items-center justify-between pt-4 mt-2">
                    <div class="flex items-center gap-1.5">
                        <template x-for="i in totalSlides" :key="i">
                            <button type="button"
                                    @click="goToSlide(i - 1)"
                                    :class="activeSlide === (i - 1) ? 'w-7 bg-[#DC2626]' : 'w-2 bg-slate-300'"
                                    class="h-1.5 rounded-full transition-all duration-300"
                                    :aria-label="'Buka slide ' + i">
                            </button>
                        </template>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="prevSlide()" class="p-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shadow-xs" aria-label="Slide sebelumnya">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </button>
                        <button type="button" @click="nextSlide()" class="p-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shadow-xs" aria-label="Slide berikutnya">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>


<!-- ========================================== -->
<!-- 2. QUICK DEMO ACCESS SECTION (Langsung Coba Tanpa Registrasi) -->
<!-- ========================================== -->
<section id="demo-access" class="py-12 bg-slate-900 text-white relative overflow-hidden">
    <!-- Background Glow -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(220,38,38,0.25),rgba(255,255,255,0))]"></div>

    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                    Akses Instan Lingkungan Riil
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-2">
                    Coba Akun Demo Langsung (4 Peran Lengkap)
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-xl">
                    Jelajahi fungsionalitas RiHRIS dari sudut pandang Super Admin, Pemilik Perusahaan, HR Manager, hingga Karyawan biasa. Password: <code class="text-amber-300 font-mono font-bold">Password123#</code>.
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-700 transition shadow-md shadow-red-600/30">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    Halaman Login Lengkap
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Super Admin -->
            <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700 hover:border-red-500/60 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-base">
                            👑
                        </span>
                        <span class="text-[10px] font-mono bg-purple-500/10 text-purple-300 px-2 py-0.5 rounded border border-purple-500/20">
                            SaaS Super Admin
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-red-400 transition">Super Administrator</h3>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">
                        Kelola seluruh tenant multi-perusahaan, atur paket subscription, pantau audit log keamanan global, dan ubah konten CMS landing page.
                    </p>
                    <div class="mt-3 p-2 rounded bg-slate-900/90 border border-slate-700/80 text-[11px] font-mono text-slate-300">
                        superadmin@rihris.id
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-700">
                    <a href="{{ route('login', ['demo' => 'superadmin']) }}"
                       class="w-full block text-center py-2 px-3 rounded-lg text-xs font-bold bg-slate-700 group-hover:bg-purple-600 text-white transition">
                        Masuk Super Admin &rarr;
                    </a>
                </div>
            </div>

            <!-- 2. Company Owner -->
            <div class="p-5 rounded-2xl bg-slate-800/80 border border-red-500/40 ring-1 ring-red-500/30 hover:border-red-400 transition group flex flex-col justify-between relative">
                <div class="absolute -top-2.5 right-4 bg-red-600 text-white text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full">
                    Rekomendasi
                </div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-9 h-9 rounded-xl bg-red-500/20 text-red-400 flex items-center justify-center font-bold text-base">
                            🏢
                        </span>
                        <span class="text-[10px] font-mono bg-red-500/10 text-red-300 px-2 py-0.5 rounded border border-red-500/20">
                            PT Solusi Digital
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-red-400 transition">Company Owner</h3>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">
                        Akses penuh seluruh 20 modul: payroll PPh 21 TER, laporan K3, persetujuan direksi, smart contract PKWT, dan dashboard holding.
                    </p>
                    <div class="mt-3 p-2 rounded bg-slate-900/90 border border-slate-700/80 text-[11px] font-mono text-slate-300">
                        owner@solusidigital.id
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-700">
                    <a href="{{ route('login', ['demo' => 'owner']) }}"
                       class="w-full block text-center py-2 px-3 rounded-lg text-xs font-bold bg-red-600 hover:bg-red-700 text-white transition shadow-sm">
                        Masuk Company Owner &rarr;
                    </a>
                </div>
            </div>

            <!-- 3. HR Manager -->
            <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700 hover:border-red-500/60 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-base">
                            👔
                        </span>
                        <span class="text-[10px] font-mono bg-blue-500/10 text-blue-300 px-2 py-0.5 rounded border border-blue-500/20">
                            HR Operations
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-red-400 transition">HR Manager</h3>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">
                        Eksekusi penggajian bulanan, monitor geofence absensi GPS, kelola cuti, terbitkan SPKWT & SP, evaluasi probation, dan rekrutmen.
                    </p>
                    <div class="mt-3 p-2 rounded bg-slate-900/90 border border-slate-700/80 text-[11px] font-mono text-slate-300">
                        hr@solusidigital.id
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-700">
                    <a href="{{ route('login', ['demo' => 'hr']) }}"
                       class="w-full block text-center py-2 px-3 rounded-lg text-xs font-bold bg-slate-700 group-hover:bg-blue-600 text-white transition">
                        Masuk HR Manager &rarr;
                    </a>
                </div>
            </div>

            <!-- 4. Employee ESS -->
            <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700 hover:border-red-500/60 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-base">
                            👤
                        </span>
                        <span class="text-[10px] font-mono bg-emerald-500/10 text-emerald-300 px-2 py-0.5 rounded border border-emerald-500/20">
                            ESS Karyawan
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-red-400 transition">Budi Santoso (ESS)</h3>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">
                        Portal mandiri karyawan: swafoto presensi GPS, pengajuan izin & cuti online, unduh slip gaji terenkripsi, dan kasbon koperasi.
                    </p>
                    <div class="mt-3 p-2 rounded bg-slate-900/90 border border-slate-700/80 text-[11px] font-mono text-slate-300">
                        budi@solusidigital.id
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-700">
                    <a href="{{ route('login', ['demo' => 'employee']) }}"
                       class="w-full block text-center py-2 px-3 rounded-lg text-xs font-bold bg-slate-700 group-hover:bg-emerald-600 text-white transition">
                        Masuk Portal ESS &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ========================================== -->
<!-- 3. INTERACTIVE TAX & PAYROLL SIMULATOR (PPh 21 TER 2024 PMK 168) -->
<!-- ========================================== -->
<section id="tax-simulator" class="py-20 bg-slate-50 border-y border-slate-200"
         x-data="{
             grossSalary: 12000000,
             ptkpStatus: 'K/1',
             // TER Calculation Logic
             get terCategory() {
                 if (['TK/0', 'TK/1', 'K/0'].includes(this.ptkpStatus)) return 'A';
                 if (['TK/2', 'TK/3', 'K/1', 'K/2'].includes(this.ptkpStatus)) return 'B';
                 return 'C';
             },
             get terRate() {
                 let s = this.grossSalary;
                 let cat = this.terCategory;
                 if (cat === 'A') {
                     if (s <= 5400000) return 0;
                     if (s <= 5650000) return 0.25;
                     if (s <= 5950000) return 0.5;
                     if (s <= 6300000) return 0.75;
                     if (s <= 6750000) return 1.0;
                     if (s <= 7500000) return 1.25;
                     if (s <= 8550000) return 1.5;
                     if (s <= 9650000) return 1.75;
                     if (s <= 10050000) return 2.0;
                     if (s <= 10350000) return 2.25;
                     if (s <= 10700000) return 2.5;
                     if (s <= 12500000) return 3.0;
                     if (s <= 13750000) return 4.0;
                     if (s <= 15100000) return 5.0;
                     if (s <= 16950000) return 6.0;
                     if (s <= 19750000) return 7.0;
                     if (s <= 24150000) return 8.0;
                     if (s <= 26450000) return 9.0;
                     return 10.0;
                 } else if (cat === 'B') {
                     if (s <= 6200000) return 0;
                     if (s <= 6500000) return 0.25;
                     if (s <= 6850000) return 0.5;
                     if (s <= 7300000) return 0.75;
                     if (s <= 9200000) return 1.0;
                     if (s <= 10750000) return 1.5;
                     if (s <= 12550000) return 2.0;
                     if (s <= 14050000) return 3.0;
                     if (s <= 16300000) return 4.0;
                     if (s <= 18050000) return 5.0;
                     if (s <= 19750000) return 6.0;
                     if (s <= 21600000) return 7.0;
                     if (s <= 24150000) return 8.0;
                     return 9.0;
                 } else { // Cat C
                     if (s <= 6600000) return 0;
                     if (s <= 6950000) return 0.25;
                     if (s <= 7350000) return 0.5;
                     if (s <= 7800000) return 0.75;
                     if (s <= 8850000) return 1.0;
                     if (s <= 9800000) return 1.25;
                     if (s <= 10950000) return 1.5;
                     if (s <= 11200000) return 1.75;
                     if (s <= 12050000) return 2.0;
                     if (s <= 12950000) return 3.0;
                     if (s <= 14150000) return 4.0;
                     return 5.0;
                 }
             },
             get pph21Amount() {
                 return Math.round(this.grossSalary * (this.terRate / 100));
             },
             get bpjsJht() {
                 return Math.round(this.grossSalary * 0.02);
             },
             get bpjsJp() {
                 let cap = 10042300;
                 let base = Math.min(this.grossSalary, cap);
                 return Math.round(base * 0.01);
             },
             get bpjsKes() {
                 let cap = 12000000;
                 let base = Math.min(this.grossSalary, cap);
                 return Math.round(base * 0.01);
             },
             get totalDeductions() {
                 return this.pph21Amount + this.bpjsJht + this.bpjsJp + this.bpjsKes;
             },
             get takeHomePay() {
                 return this.grossSalary - this.totalDeductions;
             },
             formatRupiah(val) {
                 return 'Rp ' + Number(val).toLocaleString('id-ID');
             }
         }">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-[#DC2626]">
                <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                Simulasi Statutori Live
            </span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">
                Kalkulator Interaktif PPh 21 TER &amp; Potongan BPJS
            </h2>
            <p class="text-sm text-slate-600 mt-2">
                Coba langsung rumus resmi PP No. 58/2023 &amp; PMK No. 168/2023 yang ditanamkan secara presisi dalam mesin penggajian RiHRIS.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Controls -->
            <div class="lg:col-span-6 bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700">
                            Gaji Pokok + Tunjangan Tetap (Gross)
                        </label>
                        <span class="text-base font-extrabold text-[#DC2626] font-mono" x-text="formatRupiah(grossSalary)"></span>
                    </div>
                    <input type="range" min="3000000" max="40000000" step="500000"
                           x-model.number="grossSalary"
                           class="w-full h-2.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#DC2626]">
                    <div class="flex justify-between text-[11px] text-slate-400 mt-1">
                        <span>Rp 3 Juta (UMR)</span>
                        <span>Rp 20 Juta</span>
                        <span>Rp 40 Juta+</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Status PTKP (Penghasilan Tidak Kena Pajak)
                    </label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        <template x-for="st in ['TK/0', 'TK/1', 'K/0', 'K/1', 'K/2', 'K/3']" :key="st">
                            <button type="button"
                                    @click="ptkpStatus = st"
                                    :class="ptkpStatus === st ? 'bg-[#DC2626] text-white font-bold shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium'"
                                    class="py-2 rounded-lg text-xs transition"
                                    x-text="st">
                            </button>
                        </template>
                    </div>
                    <div class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center justify-between">
                        <span>Klasifikasi Kategori TER:</span>
                        <span class="font-bold text-slate-900 bg-white px-2.5 py-1 rounded border border-slate-200"
                              x-text="'Kategori ' + terCategory + ' (' + ptkpStatus + ')'"></span>
                    </div>
                </div>

                <!-- Compliance Notes -->
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 text-xs text-amber-900 space-y-1.5">
                    <div class="font-bold flex items-center gap-1.5 text-amber-950">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600"></i>
                        Ketentuan Batas Atas (Cap) Resmi Indonesia
                    </div>
                    <ul class="text-[11px] text-amber-800 list-disc list-inside space-y-0.5">
                        <li>BPJS Kesehatan pekerja: 1% dengan batas atas gaji Rp 12.000.000 (Max Rp 120.000).</li>
                        <li>BPJS Jaminan Pensiun: 1% dengan batas upah Rp 10.042.300 (Max Rp 100.423).</li>
                        <li>BPJS JHT pekerja: 2% dari upah riil tanpa batas atas.</li>
                    </ul>
                </div>
            </div>

            <!-- Right Results Breakdown -->
            <div class="lg:col-span-6 bg-slate-900 rounded-2xl p-6 sm:p-8 border border-slate-800 text-white shadow-xl space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i data-lucide="receipt" class="w-5 h-5 text-red-400"></i>
                        Rincian Slip Gaji Elektronik
                    </h3>
                    <span class="text-xs bg-emerald-500/20 text-emerald-300 px-2.5 py-1 rounded-full font-mono">
                        Tarif TER: <span x-text="terRate + '%'"></span>
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-2 border-b border-slate-800 text-slate-300">
                        <span>Gaji Bruto Bulanan</span>
                        <span class="font-mono text-white font-bold" x-text="formatRupiah(grossSalary)"></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800 text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                            PPh 21 TER Bulanan (<span x-text="terRate + '%'"></span>)
                        </span>
                        <span class="font-mono text-rose-400 font-semibold" x-text="'- ' + formatRupiah(pph21Amount)"></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800 text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                            BPJS Ketenagakerjaan JHT (2%)
                        </span>
                        <span class="font-mono text-rose-400 font-semibold" x-text="'- ' + formatRupiah(bpjsJht)"></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800 text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                            BPJS TK Jaminan Pensiun (1% cap)
                        </span>
                        <span class="font-mono text-rose-400 font-semibold" x-text="'- ' + formatRupiah(bpjsJp)"></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800 text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                            BPJS Kesehatan (1% cap)
                        </span>
                        <span class="font-mono text-rose-400 font-semibold" x-text="'- ' + formatRupiah(bpjsKes)"></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-800 text-slate-400">
                        <span>Total Potongan Karyawan</span>
                        <span class="font-mono text-rose-300 font-bold" x-text="'- ' + formatRupiah(totalDeductions)"></span>
                    </div>

                    <!-- THP Highlight Box -->
                    <div class="p-4 rounded-xl bg-gradient-to-r from-emerald-950/80 to-slate-900 border border-emerald-600/50 flex justify-between items-center mt-4">
                        <div>
                            <div class="text-[11px] text-emerald-300 font-bold uppercase tracking-wider">Take Home Pay (THP) Bersih</div>
                            <div class="text-2xl font-black text-emerald-400 font-mono mt-0.5" x-text="formatRupiah(takeHomePay)"></div>
                        </div>
                        <i data-lucide="check-circle" class="w-8 h-8 text-emerald-400 shrink-0"></i>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="{{ route('register') }}" class="w-full py-3 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-bold text-center block transition shadow-md shadow-red-600/30">
                        Terapkan Perhitungan Otomatis Ini Pada Perusahaan Anda &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ========================================== -->
<!-- 4. FEATURES SECTION (8 Core Architectural Pillars) -->
<!-- ========================================== -->
<section id="features" class="py-20 bg-white">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Keunggulan Utama</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Didesain Khusus untuk Standar Korporasi Indonesia</h2>
            <p class="text-sm text-slate-600 mt-3">
                Kombinasi isolasi data multi-anak perusahaan, otomatisasi regulasi ketenagakerjaan, dan perlindungan privasi data tingkat tinggi.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($features as $feat)
                <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-lg hover:border-red-300 transition duration-300 group flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center mb-5 group-hover:bg-[#DC2626] group-hover:text-white transition duration-300 shadow-xs">
                            <i data-lucide="{{ $feat->icon ?: 'check-circle-2' }}" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2 group-hover:text-[#DC2626] transition">{{ $feat->title }}</h3>
                        <p class="text-xs leading-relaxed text-slate-600">{{ $feat->description }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] font-semibold text-slate-400 group-hover:text-[#DC2626] transition">
                        <span>Standar RiHRIS</span>
                        <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center py-8 text-slate-400">Belum ada fitur terdaftar.</div>
            @endforelse
        </div>
    </div>
</section>


<!-- ========================================== -->
<!-- 5. MODULES EXPLORER (20-Phase Enterprise Suite) -->
<!-- ========================================== -->
<section id="modules" class="py-20 bg-slate-50 border-t border-slate-200"
         x-data="{
             activeCategory: 'all',
             matches(cat) {
                 return this.activeCategory === 'all' || this.activeCategory === cat;
             }
         }">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Ekosistem Terpadu 20 Fase</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Seluruh Siklus SDM Dalam Satu Platform</h2>
            <p class="text-sm text-slate-600 mt-3">
                Mulai dari rekrutmen awal, presensi geofence, penggajian PPh 21 TER, tata kelola K3 SMK3, hingga perlindungan kerahasiaan Satgas PPKS.
            </p>

            <!-- Module Filter Category Tabs -->
            <div class="mt-8 flex flex-wrap justify-center gap-2">
                <button type="button" @click="activeCategory = 'all'"
                        :class="activeCategory === 'all' ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs transition">
                    Semua Modul (20 Fase)
                </button>
                <button type="button" @click="activeCategory = 'core'"
                        :class="activeCategory === 'core' ? 'bg-[#DC2626] text-white font-bold shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs transition">
                    Presensi &amp; Core HR
                </button>
                <button type="button" @click="activeCategory = 'payroll'"
                        :class="activeCategory === 'payroll' ? 'bg-[#DC2626] text-white font-bold shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs transition">
                    Payroll &amp; Pajak
                </button>
                <button type="button" @click="activeCategory = 'legal'"
                        :class="activeCategory === 'legal' ? 'bg-[#DC2626] text-white font-bold shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs transition">
                    Hukum &amp; PP 35
                </button>
                <button type="button" @click="activeCategory = 'enterprise'"
                        :class="activeCategory === 'enterprise' ? 'bg-[#DC2626] text-white font-bold shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs transition">
                    K3, PPKS &amp; UU PDP
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($modules as $mod)
                <div class="rounded-2xl border border-slate-200/90 p-6 hover:border-red-400 hover:shadow-md transition duration-200 bg-white flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-xl bg-slate-100 text-[#DC2626] flex items-center justify-center group-hover:bg-[#DC2626] group-hover:text-white transition duration-200 shadow-xs">
                                <i data-lucide="{{ $mod->icon ?: 'briefcase' }}" class="w-5 h-5"></i>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                Aktif di RiHRIS
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2 group-hover:text-[#DC2626] transition">{{ $mod->name }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $mod->description }}</p>
                    </div>
                    <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-[#DC2626]">
                        <span>Tersedia di Semua Paket</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition transform"></i>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-8 text-slate-400">Belum ada modul terkonfigurasi.</div>
            @endforelse
        </div>
    </div>
</section>


<!-- ========================================== -->
<!-- 6. WHY CHOOSE US & REGULATORY COMPLIANCE -->
<!-- ========================================== -->
<section id="why-us" class="py-20 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white relative">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/30">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-red-400"></i>
                    Kepatuhan Regulasi Ketenagakerjaan RI
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold mt-3 leading-tight">
                    Lindungi Bisnis Anda dari Sanksi &amp; Denda Ketenagakerjaan
                </h2>
                <p class="mt-4 text-sm text-slate-300 leading-relaxed">
                    RiHRIS terus memperbarui logika mesin penggajian, waktu kerja, kontrak PKWT, dan jaminan sosial sesuai undang-undang dan peraturan menteri terbaru Republik Indonesia.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-800/50 border border-slate-800">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Tarif Efektif Rata-Rata (TER) PPh 21 - PP 58/2023 &amp; PMK 168/2023</h4>
                            <p class="text-xs text-slate-400 mt-1">Perhitungan otomatis masa bulanan (Kategori A, B, C) serta rekonsiliasi masa pajak terakhir (Desember) sesuai Pasal 17 UU HPP.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-800/50 border border-slate-800">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Uang Kompensasi PKWT PP 35/2021</h4>
                            <p class="text-xs text-slate-400 mt-1">Kalkulasi presisi kewajiban kompensasi berakhirnya masa kontrak kerja PKWT sesuai formula resmi masa kerja sebulan penuh.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-800/50 border border-slate-800">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Lembur Standar Resmi 1/173 Upah Sebulan</h4>
                            <p class="text-xs text-slate-400 mt-1">Penerbitan Surat Perintah Lembur (SPL) digital dengan perhitungan otomatis 1.5x jam pertama dan 2x jam selanjutnya.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-800/50 border border-slate-800">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Pelindungan Data Pribadi UU No. 27/2022 &amp; Satgas PPKS</h4>
                            <p class="text-xs text-slate-400 mt-1">Enkripsi data sensitif karyawan, portal pemenuhan hak DSAR, serta ruang pelaporan rahasia investigasi kekerasan seksual.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Comparison Matrix -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-5">
                <h3 class="text-base font-bold text-white flex items-center justify-between pb-3 border-b border-slate-800">
                    <span>Perbandingan Solusi HR</span>
                    <span class="text-xs text-red-400 font-mono">RiHRIS vs Tradisional</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 space-y-1">
                        <div class="font-bold text-white flex justify-between items-center">
                            <span>PPh 21 TER 2024 PMK 168</span>
                            <span class="text-emerald-400 font-mono">100% Otomatis</span>
                        </div>
                        <p class="text-[11px] text-slate-400">Software global tidak support TER; Excel rawan human-error tarif kategori.</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 space-y-1">
                        <div class="font-bold text-white flex justify-between items-center">
                            <span>Kompensasi PKWT PP 35/2021</span>
                            <span class="text-emerald-400 font-mono">Terintegrasi</span>
                        </div>
                        <p class="text-[11px] text-slate-400">Dihitung otomatis saat kontrak selesai &amp; dapat diekspor ke slip pengeluaran.</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 space-y-1">
                        <div class="font-bold text-white flex justify-between items-center">
                            <span>Laporan WLTK Kemnaker</span>
                            <span class="text-emerald-400 font-mono">1-Click Export</span>
                        </div>
                        <p class="text-[11px] text-slate-400">Format rekapitulasi data ketenagakerjaan siap lapor ke dinas terkait.</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 space-y-1">
                        <div class="font-bold text-white flex justify-between items-center">
                            <span>Batas Atas (Cap) BPJS Up-to-Date</span>
                            <span class="text-emerald-400 font-mono">Auto Capping</span>
                        </div>
                        <p class="text-[11px] text-slate-400">Mengikuti ketetapan Dewan Jaminan Sosial Nasional tanpa setting ulang.</p>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="{{ route('register') }}" class="w-full py-3 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-bold text-center block transition shadow-md shadow-red-600/30">
                        Coba Gratis Tanpa Risiko 14 Hari &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ========================================== -->
<!-- 7. PRICING PLANS SECTION -->
<!-- ========================================== -->
<section id="pricing" class="py-20 bg-slate-50">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8" x-data="{ billingCycle: 'monthly' }">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Paket Berlangganan</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Investasi Transparan Sesuai Skala Perusahaan</h2>
            <p class="text-sm text-slate-600 mt-3">Semua paket dilengkapi pembaruan otomatis regulasi hukum Indonesia.</p>

            <!-- Billing Toggle -->
            <div class="mt-6 inline-flex items-center p-1 bg-white rounded-xl border border-slate-200 shadow-xs">
                <button type="button"
                        @click="billingCycle = 'monthly'"
                        :class="billingCycle === 'monthly' ? 'bg-[#DC2626] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-4 py-1.5 rounded-lg text-xs font-semibold transition">
                    Bulanan
                </button>
                <button type="button"
                        @click="billingCycle = 'yearly'"
                        :class="billingCycle === 'yearly' ? 'bg-[#DC2626] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-4 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    Tahunan
                    <span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full font-bold">Hemat 2 Bulan</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($plans as $plan)
                <div class="bg-white rounded-2xl border {{ $plan->slug === 'professional' ? 'border-[#DC2626] ring-2 ring-red-500/20 shadow-lg' : 'border-slate-200 shadow-xs' }} p-6 flex flex-col justify-between relative hover:shadow-md transition">
                    @if($plan->slug === 'professional')
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-[#DC2626] text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full shadow-sm">
                            Paling Populer
                        </div>
                    @endif

                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $plan->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Hingga {{ $plan->max_employees >= 99999 ? 'Unlimited' : $plan->max_employees }} Karyawan</p>

                        <div class="mt-6 mb-6">
                            <div class="flex items-baseline gap-1" x-show="billingCycle === 'monthly'">
                                <span class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                                <span class="text-xs text-slate-500">/bln</span>
                            </div>
                            <div class="flex items-baseline gap-1" x-show="billingCycle === 'yearly'" x-cloak>
                                <span class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}</span>
                                <span class="text-xs text-slate-500">/thn</span>
                            </div>
                        </div>

                        <ul class="space-y-3 text-xs text-slate-600 border-t border-slate-100 pt-5">
                            <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> Maksimal {{ $plan->max_admins }} Admin</li>
                            <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> Storage {{ round($plan->storage_limit_mb / 1024, 1) }} GB Dokumen</li>
                            <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> Presensi GPS &amp; Selfie</li>
                            <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> Form Pengajuan Cuti</li>
                            @if($plan->slug !== 'free-trial')
                                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> PPh 21 TER Otomatis</li>
                                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> 5 Program BPJS TK</li>
                                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> Slip Gaji PDF</li>
                            @endif
                        </ul>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('register') }}" class="w-full text-center block py-2.5 px-4 rounded-xl text-xs font-semibold transition {{ $plan->slug === 'professional' ? 'bg-[#DC2626] text-white hover:bg-[#991B1B] shadow-md shadow-red-600/20' : 'bg-slate-100 text-slate-800 hover:bg-slate-200' }}">
                            {{ $plan->slug === 'free-trial' ? 'Coba Gratis Sekarang' : 'Pilih Paket' }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center py-8 text-slate-400">Belum ada paket langganan.</div>
            @endforelse
        </div>
    </div>
</section>


<!-- ========================================== -->
<!-- 8. CLIENT TESTIMONIALS -->
<!-- ========================================== -->
<section class="py-20 bg-white border-t border-slate-100">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Testimoni Klien</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Dipercaya Pemimpin SDM di Seluruh Indonesia</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($testimonials as $testi)
                <div class="p-6 sm:p-8 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-1 text-amber-400 mb-4">
                            @for($i = 0; $i < $testi->rating; $i++)
                                <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                            @endfor
                        </div>
                        <p class="text-xs sm:text-sm text-slate-700 italic leading-relaxed mb-6">
                            "{{ $testi->quote }}"
                        </p>
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-200/60">
                        <div class="w-10 h-10 rounded-full bg-red-100 text-[#DC2626] font-bold flex items-center justify-center text-sm">
                            {{ substr($testi->author_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $testi->author_name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $testi->author_title }} — {{ $testi->company_name }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center text-slate-400">Belum ada testimoni.</div>
            @endforelse
        </div>
    </div>
</section>


<!-- ========================================== -->
<!-- 9. FAQ ACCORDION SECTION (Alpine.js) -->
<!-- ========================================== -->
<section id="faq" class="py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Pusat Informasi</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Pertanyaan yang Sering Diajukan</h2>
            <p class="text-sm text-slate-600 mt-2">Segala hal yang perlu Anda ketahui tentang arsitektur dan kepatuhan RiHRIS.</p>
        </div>

        <div class="max-w-3xl mx-auto space-y-4" x-data="{ activeFaq: null }">
            @forelse($faqs as $index => $faq)
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs transition">
                    <button type="button"
                            @click="activeFaq = activeFaq === {{ $index }} ? null : {{ $index }}"
                            class="w-full px-6 py-4 text-left flex items-center justify-between text-sm font-bold text-slate-900 hover:text-[#DC2626] transition">
                        <span>{{ $faq->question }}</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200 shrink-0 ml-2" :class="activeFaq === {{ $index }} ? 'rotate-180 text-[#DC2626]' : ''"></i>
                    </button>
                    <div x-show="activeFaq === {{ $index }}" x-cloak x-collapse class="px-6 pb-5 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-50">
                        <div class="pt-3">{{ $faq->answer }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center text-slate-400">Belum ada daftar FAQ.</div>
            @endforelse
        </div>
    </div>
</section>


<!-- ========================================== -->
<!-- 10. CONTACT & CONSULTATION FORM SECTION -->
<!-- ========================================== -->
<section id="contact" class="py-20 bg-white border-t border-slate-200">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Info -->
            <div class="lg:col-span-5 space-y-6">
                <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Konsultasi Gratis</span>
                <h2 class="text-3xl font-extrabold text-slate-900 leading-tight">Jadwalkan Demo Langsung untuk Perusahaan Anda</h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Punya skema jam kerja shift yang kompleks, struktur holding banyak PT, atau butuh kustomisasi integrasi ERP? Tim konsultan implementasi RiHRIS siap berdiskusi.
                </p>

                <div class="space-y-4 pt-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center shrink-0">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Email Korespondensi</div>
                            <a href="mailto:{{ $settings['contact_email'] ?? 'hallo@phoebehub.com' }}" class="text-sm font-semibold text-slate-800 hover:text-red-600 transition">
                                {{ $settings['contact_email'] ?? 'hallo@phoebehub.com' }}
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center shrink-0">
                            <i data-lucide="phone" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Telepon Kantor</div>
                            <div class="text-sm font-semibold text-slate-800">{{ $settings['contact_phone'] ?? '+62 21 8062 8900' }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i data-lucide="message-square" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">WhatsApp Bisnis</div>
                            <a href="https://wa.me/6282218866002?text=Halo%20RiHRIS%2C%20saya%20ingin%20konsultasi%20layanan%20HRIS" target="_blank" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 hover:underline transition flex items-center gap-1.5">
                                <span>{{ $settings['contact_whatsapp'] ?? '082218866002' }}</span>
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form -->
            <div class="lg:col-span-7 bg-slate-50 border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs">
                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                            <input type="text" name="name" required class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Contoh: Rian Hidayat">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Perusahaan *</label>
                            <input type="email" name="email" required class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="nama@perusahaan.co.id">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                            <input type="text" name="phone" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="+628123456789">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Perusahaan</label>
                            <input type="text" name="company" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="PT Sukses Bersama">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pesan / Kebutuhan Konsultasi *</label>
                        <textarea name="message" rows="4" required class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Jelaskan jumlah karyawan dan fitur utama yang ingin Anda pelajari..."></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-sm font-semibold shadow-md shadow-red-600/20 transition flex items-center justify-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Kirim Pesan Permohonan Demo
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection
