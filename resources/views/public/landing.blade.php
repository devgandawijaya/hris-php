@extends('layouts.landing')

@section('content')

<!-- A. HERO SLIDER SECTION (Alpine.js Powered Multi-Slide Slider) -->
<section class="relative bg-gradient-to-b from-red-50/60 via-white to-white overflow-hidden py-12 lg:py-20"
         x-data="{
             activeSlide: 0,
             totalSlides: {{ $sliders->count() > 0 ? $sliders->count() : 1 }},
             autoSlideTimer: null,
             startAutoSlide() {
                 this.autoSlideTimer = setInterval(() => {
                     this.nextSlide();
                 }, 6000);
             },
             stopAutoSlide() {
                 clearInterval(this.autoSlideTimer);
             },
             nextSlide() {
                 this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
             },
             prevSlide() {
                 this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
             }
         }"
         x-init="startAutoSlide()"
         @mouseenter="stopAutoSlide()"
         @mouseleave="startAutoSlide()">

    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 relative">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Slides Wrapper -->
        <div class="relative min-h-[460px] flex items-center">
            @forelse($sliders as $index => $slide)
                <div x-show="activeSlide === {{ $index }}"
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-300 absolute"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0 -translate-y-4"
                     class="w-full grid grid-cols-1 lg:grid-cols-12 gap-12 items-center"
                     x-cloak>
                    <!-- Left: Content -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100/80 text-[#DC2626] text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-[#DC2626]"></span>
                            HRIS SaaS Multi-Perusahaan No. 1 Indonesia
                        </div>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 leading-[1.15] tracking-tight">
                            {{ $slide->title }}
                        </h1>
                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl">
                            {{ $slide->subtitle }}
                        </p>
                        <div class="flex flex-wrap items-center gap-4 pt-2">
                            <a href="{{ $slide->cta_url ?: route('register') }}" class="inline-flex items-center gap-2.5 px-6 py-3.5 rounded-xl text-base font-semibold text-white bg-[#DC2626] hover:bg-[#991B1B] shadow-lg shadow-red-600/30 transition transform hover:-translate-y-0.5">
                                {{ $slide->cta_text ?: 'Coba Gratis 14 Hari' }}
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </a>
                            <a href="#why-us" class="inline-flex items-center gap-2 px-5 py-3.5 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-sm transition">
                                Pelajari Kepatuhan PP 35
                            </a>
                        </div>
                        <!-- Features checkmarks -->
                        <div class="pt-4 flex flex-wrap gap-y-2 gap-x-6 text-xs font-medium text-slate-500">
                            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> PPh 21 TER 2024</span>
                            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> 5 Program BPJS TK</span>
                            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> Lembur 1/173 Otomatis</span>
                        </div>
                    </div>

                    <!-- Right: Visual Preview Card -->
                    <div class="lg:col-span-5">
                        <div class="relative rounded-2xl bg-gradient-to-tr from-slate-900 via-slate-800 to-slate-900 p-6 shadow-2xl border border-slate-700 text-white">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                </div>
                                <span class="text-xs font-mono text-slate-400">RiHRIS Multi-Tenant Engine</span>
                            </div>

                            <div class="space-y-4">
                                <div class="p-4 rounded-xl bg-slate-800/80 border border-slate-700">
                                    <div class="text-xs text-slate-400">Tenant Aktif</div>
                                    <div class="text-base font-bold text-white flex items-center justify-between mt-1">
                                        <span>PT Solusi Digital Nusantara</span>
                                        <span class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded">Active</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="p-3.5 rounded-xl bg-slate-800/60 border border-slate-700/80">
                                        <div class="text-xs text-slate-400">PPh 21 TER Kategori</div>
                                        <div class="text-lg font-bold text-red-400 mt-1">Kategori B (K/1)</div>
                                    </div>
                                    <div class="p-3.5 rounded-xl bg-slate-800/60 border border-slate-700/80">
                                        <div class="text-xs text-slate-400">BPJS Kesehatan</div>
                                        <div class="text-lg font-bold text-emerald-400 mt-1">Cap Rp 12.000.000</div>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-red-950/40 border border-red-800/50 flex items-center gap-3">
                                    <i data-lucide="shield-check" class="w-6 h-6 text-red-400 shrink-0"></i>
                                    <p class="text-xs text-red-200">
                                        Data setiap entitas perusahaan terisolasi total via <span class="font-mono text-white">tenant_id</span> Global Scope.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center w-full py-12">
                    <h1 class="text-4xl font-extrabold text-slate-900">RiHRIS SaaS Indonesia</h1>
                    <p class="mt-4 text-slate-600">Platform Human Resource Information System Modern.</p>
                </div>
            @endforelse
        </div>

        <!-- Slider Navigation Controls -->
        @if($sliders->count() > 1)
            <div class="flex items-center justify-between pt-8 border-t border-slate-100">
                <!-- Dots Indicator -->
                <div class="flex items-center gap-2">
                    <template x-for="i in totalSlides" :key="i">
                        <button type="button"
                                @click="activeSlide = i - 1"
                                :class="activeSlide === (i - 1) ? 'w-8 bg-[#DC2626]' : 'w-2 bg-slate-300'"
                                class="h-2 rounded-full transition-all duration-300"
                                :aria-label="'Go to slide ' + i">
                        </button>
                    </template>
                </div>

                <!-- Next & Prev Buttons -->
                <div class="flex items-center gap-2">
                    <button type="button" @click="prevSlide()" class="p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition" aria-label="Previous slide">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </button>
                    <button type="button" @click="nextSlide()" class="p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition" aria-label="Next slide">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>
</section>


<!-- B. FEATURES SECTION (Centered Container max-w-1200) -->
<section id="features" class="py-20 bg-slate-50/80 border-y border-slate-100">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Keunggulan Utama</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Didesain Khusus untuk Standar Bisnis Indonesia</h2>
            <p class="text-sm text-slate-600 mt-3">
                Kombinasi keamanan tingkat korporat, fleksibilitas multi-anak perusahaan, dan otomatisasi regulasi ketenagakerjaan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($features as $feat)
                <div class="bg-white rounded-xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center mb-5 group-hover:bg-[#DC2626] group-hover:text-white transition duration-200">
                        <i data-lucide="{{ $feat->icon ?: 'check-circle-2' }}" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $feat->title }}</h3>
                    <p class="text-xs leading-relaxed text-slate-600">{{ $feat->description }}</p>
                </div>
            @empty
                <div class="col-span-4 text-center py-8 text-slate-400">Belum ada fitur terdaftar.</div>
            @endforelse
        </div>
    </div>
</section>


<!-- C. MODULES SECTION -->
<section id="modules" class="py-20 bg-white">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Modul Komprehensif</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Seluruh Siklus HR Dalam Satu Aplikasi</h2>
            <p class="text-sm text-slate-600 mt-3">
                Mulai dari rekrutmen hingga terminasi, semua terhubung secara terpadu tanpa perlu integrasi pihak ketiga yang rumit.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($modules as $mod)
                <div class="rounded-xl border border-slate-200 p-6 hover:border-red-300 hover:shadow-sm transition bg-white flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-lg bg-slate-100 text-[#DC2626] flex items-center justify-center mb-4">
                            <i data-lucide="{{ $mod->icon ?: 'briefcase' }}" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">{{ $mod->name }}</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $mod->description }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-[#DC2626]">
                        <span>Siap Digunakan</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center py-8 text-slate-400">Belum ada modul terkonfigurasi.</div>
            @endforelse
        </div>
    </div>
</section>


<!-- D. WHY CHOOSE US / INDONESIAN COMPLIANCE -->
<section id="why-us" class="py-20 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-red-400">Legal & Regulasi Ketenagakerjaan</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold mt-2 leading-tight">
                    Bebas Kekhawatiran Sanksi Hukum Ketenagakerjaan
                </h2>
                <p class="mt-4 text-sm text-slate-300 leading-relaxed">
                    RiHRIS terus memperbarui logika mesin penggajian dan kehadiran sesuai undang-undang dan peraturan menteri terbaru di Republik Indonesia.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Tarif Efektif Rata-Rata (TER) PPh 21 - PP 58/2023</h4>
                            <p class="text-xs text-slate-400 mt-1">Perhitungan otomatis masa bulanan (Kategori A, B, C) serta rekonsiliasi masa pajak terakhir sesuai UU HPP.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Uang Kompensasi PKWT PP 35/2021</h4>
                            <p class="text-xs text-slate-400 mt-1">Estimasi otomatis kewajiban kompensasi berakhirnya masa kontrak kerja karyawan PKWT.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">5 Program BPJS Ketenagakerjaan & BPJS Kesehatan</h4>
                            <p class="text-xs text-slate-400 mt-1">JHT, JKK sesuai level risiko perusahaan, JKM, JP dengan batas upah resmi, serta BPJS Kesehatan dengan cap Rp 12 juta.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 sm:p-8 space-y-6">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="calculator" class="w-5 h-5 text-red-400"></i>
                    Simulasi Otomatisasi Payroll Indonesia
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-2 border-b border-slate-700 text-slate-300">
                        <span>Gaji Pokok + Tunjangan Tetap</span>
                        <span class="font-mono text-white font-semibold">Rp 12.000.000</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-700 text-slate-300">
                        <span>BPJS TK JHT (Pekerja 2.0%)</span>
                        <span class="font-mono text-red-400">- Rp 240.000</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-700 text-slate-300">
                        <span>BPJS TK JP (Pekerja 1.0% cap)</span>
                        <span class="font-mono text-red-400">- Rp 100.423</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-700 text-slate-300">
                        <span>BPJS Kesehatan (Pekerja 1.0% cap)</span>
                        <span class="font-mono text-red-400">- Rp 120.000</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-700 text-slate-300">
                        <span>PPh 21 TER (Kategori B / Tarif 4%)</span>
                        <span class="font-mono text-red-400">- Rp 480.000</span>
                    </div>
                    <div class="flex justify-between pt-3 text-sm font-bold text-white bg-slate-900/60 p-3 rounded-xl border border-slate-700">
                        <span>Take Home Pay (THP) Karyawan</span>
                        <span class="font-mono text-emerald-400">Rp 11.059.577</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- E. PRICING PLANS SECTION -->
<section id="pricing" class="py-20 bg-slate-50">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8" x-data="{ billingCycle: 'monthly' }">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Paket Berlangganan</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Investasi Transparan Sesuai Skala Perusahaan</h2>
            <p class="text-sm text-slate-600 mt-3">Semua paket dilengkapi pembaruan otomatis regulasi hukum Indonesia.</p>

            <!-- Billing Toggle -->
            <div class="mt-6 inline-flex items-center p-1 bg-white rounded-xl border border-slate-200">
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
                <div class="bg-white rounded-2xl border {{ $plan->slug === 'professional' ? 'border-[#DC2626] ring-2 ring-red-500/20 shadow-lg' : 'border-slate-200 shadow-sm' }} p-6 flex flex-col justify-between relative">
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
                            <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-[#16A34A]"></i> Presensi GPS & Selfie</li>
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


<!-- F. TESTIMONIALS SECTION -->
<section class="py-20 bg-white">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Testimoni Klien</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Dipercaya Berbagai Perusahaan di Seluruh Indonesia</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @forelse($testimonials as $testi)
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-sm relative">
                    <div class="flex items-center gap-1 text-amber-400 mb-4">
                        @for($i = 0; $i < $testi->rating; $i++)
                            <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        @endfor
                    </div>
                    <p class="text-sm text-slate-700 italic leading-relaxed mb-6">
                        "{{ $testi->quote }}"
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 text-[#DC2626] font-bold flex items-center justify-center text-sm">
                            {{ substr($testi->author_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $testi->author_name }}</div>
                            <div class="text-xs text-slate-500">{{ $testi->author_title }} — {{ $testi->company_name }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center text-slate-400">Belum ada testimoni.</div>
            @endforelse
        </div>
    </div>
</section>


<!-- G. FAQ ACCORDION SECTION (Alpine.js Powered) -->
<section id="faq" class="py-20 bg-slate-50/80 border-t border-slate-100">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Pusat Bantuan</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Pertanyaan yang Sering Diajukan</h2>
        </div>

        <div class="max-w-3xl mx-auto space-y-4" x-data="{ activeFaq: null }">
            @forelse($faqs as $index => $faq)
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm transition">
                    <button type="button"
                            @click="activeFaq = activeFaq === {{ $index }} ? null : {{ $index }}"
                            class="w-full px-6 py-4 text-left flex items-center justify-between text-sm font-bold text-slate-900 hover:text-[#DC2626] transition">
                        <span>{{ $faq->question }}</span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="activeFaq === {{ $index }} ? 'rotate-180 text-[#DC2626]' : ''"></i>
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


<!-- H. CONTACT & CONSULTATION FORM SECTION -->
<section id="contact" class="py-20 bg-white">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Info -->
            <div class="lg:col-span-5 space-y-6">
                <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Hubungi Kami</span>
                <h2 class="text-3xl font-extrabold text-slate-900 leading-tight">Konsultasikan Kebutuhan HR Perusahaan Anda</h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Ingin demo langsung atau memiliki skema jam kerja dan kebijakan tunjangan khusus? Tim ahli kami siap membantu Anda.
                </p>

                <div class="space-y-4 pt-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center shrink-0">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">Email Korespondensi</div>
                            <div class="text-sm font-semibold text-slate-800">{{ $settings['contact_email'] ?? 'halo@rihris.id' }}</div>
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
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center shrink-0">
                            <i data-lucide="message-square" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">WhatsApp Bisnis</div>
                            <div class="text-sm font-semibold text-slate-800">{{ $settings['contact_whatsapp'] ?? '+62 812 8899 7700' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form -->
            <div class="lg:col-span-7 bg-slate-50 border border-slate-200 rounded-2xl p-6 sm:p-8">
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
                        Kirim Pesan Konsultasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection

