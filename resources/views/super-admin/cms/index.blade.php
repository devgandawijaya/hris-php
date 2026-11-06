@extends('layouts.app')

@section('title', 'CMS Management — Super Admin')

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'sliders' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Content Management System (CMS)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola konten, banner hero, fitur, FAQ, dan informasi landing page secara langsung.</p>
        </div>
        <div>
            <a href="{{ route('landing') }}" target="_blank" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                Pratinjau Landing Page
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-px">
        <button type="button" @click="activeTab = 'sliders'"
                :class="activeTab === 'sliders' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="sliders" class="w-4 h-4"></i>
            Hero Sliders
        </button>

        <button type="button" @click="activeTab = 'features'"
                :class="activeTab === 'features' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
            Fitur Utama
        </button>

        <button type="button" @click="activeTab = 'faqs'"
                :class="activeTab === 'faqs' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="help-circle" class="w-4 h-4"></i>
            FAQ Accordion
        </button>

        <button type="button" @click="activeTab = 'settings'"
                :class="activeTab === 'settings' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="settings" class="w-4 h-4"></i>
            Pengaturan & Kontak
        </button>

        <button type="button" @click="activeTab = 'messages'"
                :class="activeTab === 'messages' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition whitespace-nowrap flex items-center gap-2">
            <i data-lucide="inbox" class="w-4 h-4"></i>
            Pesan Masuk ({{ $messages->count() }})
        </button>
    </div>

    <!-- TAB 1: SLIDERS -->
    <div x-show="activeTab === 'sliders'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-red-600"></i>
                Tambah Hero Slide Baru
            </h2>

            <form action="{{ route('super.admin.cms.sliders.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Slide (Heading) *</label>
                        <input type="text" name="title" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Contoh: Platform HRIS SaaS Terlengkap">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan Tampil (Index) *</label>
                        <input type="number" name="order_index" value="1" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Subjudul (Deskripsi)</label>
                    <textarea name="subtitle" rows="2" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Penjelasan singkat fitur utama slide..."></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Tombol CTA</label>
                        <input type="text" name="cta_text" value="Coba Gratis 14 Hari" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">URL Tombol CTA</label>
                        <input type="text" name="cta_url" value="/register" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    </div>
                </div>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                    Simpan Slide
                </button>
            </form>
        </div>

        <!-- Sliders List -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Daftar Slide Aktif</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Urutan</th>
                        <th class="px-5 py-3">Judul Slide</th>
                        <th class="px-5 py-3">Tombol CTA</th>
                        <th class="px-5 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sliders as $slider)
                        <tr>
                            <td class="px-5 py-3 font-bold text-slate-900">#{{ $slider->order_index }}</td>
                            <td class="px-5 py-3">
                                <div class="font-bold text-slate-900">{{ $slider->title }}</div>
                                <div class="text-[11px] text-slate-500 line-clamp-1">{{ $slider->subtitle }}</div>
                            </td>
                            <td class="px-5 py-3 font-mono text-[11px]">{{ $slider->cta_text }} ({{ $slider->cta_url }})</td>
                            <td class="px-5 py-3 text-right">
                                <form action="{{ route('super.admin.cms.sliders.delete', $slider->id) }}" method="POST" onsubmit="return confirm('Hapus slide ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada slide.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: FEATURES -->
    <div x-show="activeTab === 'features'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-red-600"></i>
                Tambah Fitur Utama Baru
            </h2>

            <form action="{{ route('super.admin.cms.features.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Fitur *</label>
                        <input type="text" name="title" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Absensi GPS Geofencing">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Ikon Lucide *</label>
                        <input type="text" name="icon" value="clock" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="clock, users, shield-check">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan *</label>
                        <input type="number" name="order_index" value="1" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Singkat *</label>
                    <textarea name="description" rows="2" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"></textarea>
                </div>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                    Simpan Fitur
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Daftar Fitur Utama</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Urutan</th>
                        <th class="px-5 py-3">Ikon</th>
                        <th class="px-5 py-3">Judul Fitur</th>
                        <th class="px-5 py-3">Deskripsi</th>
                        <th class="px-5 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($features as $feat)
                        <tr>
                            <td class="px-5 py-3 font-bold">#{{ $feat->order_index }}</td>
                            <td class="px-5 py-3 font-mono text-slate-500">{{ $feat->icon }}</td>
                            <td class="px-5 py-3 font-bold text-slate-900">{{ $feat->title }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $feat->description }}</td>
                            <td class="px-5 py-3 text-right">
                                <form action="{{ route('super.admin.cms.features.delete', $feat->id) }}" method="POST" onsubmit="return confirm('Hapus fitur ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada fitur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: FAQS -->
    <div x-show="activeTab === 'faqs'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4 text-red-600"></i>
                Tambah Pertanyaan FAQ Baru
            </h2>

            <form action="{{ route('super.admin.cms.faqs.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pertanyaan *</label>
                        <input type="text" name="question" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500" placeholder="Apakah mendukung perhitungan lembur PP 35/2021?">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan *</label>
                        <input type="number" name="order_index" value="1" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jawaban Lengkap *</label>
                    <textarea name="answer" rows="3" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"></textarea>
                </div>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                    Simpan FAQ
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Daftar Pertanyaan FAQ</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Urutan</th>
                        <th class="px-5 py-3">Pertanyaan</th>
                        <th class="px-5 py-3">Jawaban</th>
                        <th class="px-5 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($faqs as $f)
                        <tr>
                            <td class="px-5 py-3 font-bold">#{{ $f->order_index }}</td>
                            <td class="px-5 py-3 font-bold text-slate-900">{{ $f->question }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $f->answer }}</td>
                            <td class="px-5 py-3 text-right">
                                <form action="{{ route('super.admin.cms.faqs.delete', $f->id) }}" method="POST" onsubmit="return confirm('Hapus FAQ ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada FAQ.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: SETTINGS & CONTACT -->
    <div x-show="activeTab === 'settings'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="settings" class="w-4 h-4 text-red-600"></i>
                Konfigurasi Platform & Informasi Kontak Legal
            </h2>

            <form action="{{ route('super.admin.cms.settings.update') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Website / Brand</label>
                        <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'RiHRIS' }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tagline Singkat</label>
                        <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Korespondensi</label>
                        <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Telepon Kantor</label>
                        <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp Bisnis</label>
                        <input type="text" name="contact_whatsapp" value="{{ $settings['contact_whatsapp'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Kantor Pusat</label>
                    <textarea name="contact_address" rows="2" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">{{ $settings['contact_address'] ?? '' }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">SEO Meta Title</label>
                        <input type="text" name="seo_title" value="{{ $settings['seo_title'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">SEO Meta Description</label>
                        <input type="text" name="seo_description" value="{{ $settings['seo_description'] ?? '' }}" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800">
                    </div>
                </div>

                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm">
                    Perbarui Pengaturan
                </button>
            </form>
        </div>
    </div>

    <!-- TAB 5: CONTACT MESSAGES -->
    <div x-show="activeTab === 'messages'" x-cloak class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 font-bold text-xs text-slate-800">Pesan Masuk Permohonan Demo & Konsultasi</div>
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Pengirim</th>
                        <th class="px-5 py-3">Kontak & Perusahaan</th>
                        <th class="px-5 py-3">Isi Pesan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($messages as $msg)
                        <tr>
                            <td class="px-5 py-3 text-[11px] text-slate-400 whitespace-nowrap">{{ $msg->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3 font-bold text-slate-900">{{ $msg->name }}</td>
                            <td class="px-5 py-3">
                                <div>{{ $msg->email }}</div>
                                <div class="text-[11px] text-slate-500">{{ $msg->phone }} • {{ $msg->company }}</div>
                            </td>
                            <td class="px-5 py-3 text-slate-700 max-w-md">{{ $msg->message }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center text-slate-400">Belum ada pesan masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

