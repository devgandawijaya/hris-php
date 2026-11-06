@extends('layouts.app')

@section('title', 'Rekrutmen & Pelamar Kerja')

@section('content')
<div class="space-y-6" x-data="{ openCreateModal: false }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Rekrutmen & Akuisisi Talenta</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola lowongan pekerjaan, basis data kandidat pelamar, jadwal wawancara, dan penawaran kerja.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Pasang Lowongan Baru
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Lowongan</div>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total_postings']) }} Posisi</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Semua lowongan dibuka</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lowongan Aktif</div>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ number_format($stats['active_postings']) }} Posisi</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Menerima lamaran</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pelamar Masuk</div>
            <div class="text-xl font-black text-blue-600 mt-1">{{ number_format($stats['total_applications']) }} Berkas</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Basis data kandidat</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kandidat Diterima (Hired)</div>
            <div class="text-xl font-black text-[#DC2626] mt-1">{{ number_format($stats['hired_count']) }} Orang</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Lolos ke tahap kerja</div>
        </div>
    </div>

    <!-- Job Postings List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <h2 class="text-base font-bold text-slate-900">Daftar Lowongan Pekerjaan</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('company.recruitment.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
                <a href="{{ route('company.recruitment.index', ['status' => 'published']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'published' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Aktif</a>
                <a href="{{ route('company.recruitment.index', ['status' => 'closed']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'closed' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Ditutup</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Judul Posisi</th>
                        <th class="px-5 py-3.5">Departemen</th>
                        <th class="px-5 py-3.5">Tipe Hubungan Kerja</th>
                        <th class="px-5 py-3.5">Kisaran Gaji</th>
                        <th class="px-5 py-3.5 text-center">Jumlah Pelamar</th>
                        <th class="px-5 py-3.5">Batas Lamaran</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($postings as $job)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4">
                                <a href="{{ route('company.recruitment.show', $job->id) }}" class="font-bold text-slate-900 hover:text-[#DC2626] transition flex items-center gap-2">
                                    <i data-lucide="briefcase" class="w-4 h-4 text-slate-400"></i>
                                    {{ $job->title }}
                                </a>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $job->location }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-700 font-medium">
                                {{ $job->department?->name ?? 'Semua Bagian' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ str_replace('_', ' ', $job->employment_type) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-800 font-mono font-semibold">
                                @if($job->salary_min && $job->salary_max)
                                    Rp {{ number_format($job->salary_min, 0, ',', '.') }} - {{ number_format($job->salary_max, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400">Kompetitif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-700">
                                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                    {{ $job->applications_count }} Pelamar
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $job->deadline ? $job->deadline->format('d M Y') : 'Terbuka' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($job->status === 'published')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @elseif($job->status === 'closed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                        Ditutup
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('company.recruitment.show', $job->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition">
                                    <span>Pipeline</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="briefcase" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Belum ada lowongan pekerjaan aktif.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol <strong>"Pasang Lowongan Baru"</strong> di atas untuk mempublikasikan lowongan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($postings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $postings->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Pasang Lowongan Baru -->
    <div x-show="openCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openCreateModal = false" class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Pasang Lowongan Pekerjaan Baru</h3>
                <button @click="openCreateModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.recruitment.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul Posisi Lowongan <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Senior Fullstack Laravel Developer"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Departemen</label>
                        <select name="department_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Pekerjaan <span class="text-red-500">*</span></label>
                        <select name="employment_type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="full_time">Penuh Waktu (Full Time)</option>
                            <option value="contract">Kontrak (PKWT)</option>
                            <option value="part_time">Paruh Waktu (Part Time)</option>
                            <option value="internship">Magang (Internship)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Kerja <span class="text-red-500">*</span></label>
                        <input type="text" name="location" required value="Jakarta Selatan / Hybrid"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Batas Akhir Pendaftaran</label>
                        <input type="date" name="deadline" value="{{ now()->addMonth()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gaji Minimal (Rp)</label>
                        <input type="number" name="salary_min" step="500000" placeholder="8000000"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gaji Maksimal (Rp)</label>
                        <input type="number" name="salary_max" step="500000" placeholder="15000000"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Uraian Pekerjaan (Job Description) <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="3" required placeholder="Tanggung jawab utama dan cakupan peran..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kualifikasi & Persyaratan</label>
                    <textarea name="requirements" rows="2" placeholder="Pengalaman, keahlian teknis, dan sertifikasi yang dibutuhkan..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Publikasikan Lowongan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

