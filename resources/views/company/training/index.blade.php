@extends('layouts.app')

@section('title', 'Pelatihan & Pengembangan (L&D)')

@section('content')
<div class="space-y-6" x-data="{ 
    openCourseModal: false, 
    openEnrollModal: false, 
    openCompleteModal: false,
    selectedCourseId: '', 
    selectedCourseTitle: '',
    selectedEnrollmentId: '',
    selectedEnrollmentEmpName: ''
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pelatihan & Pengembangan Kompetensi (L&D)</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola program pelatihan karyawan, pendaftaran peserta, penilaian kelulusan, dan penerbitan sertifikat digital.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openCourseModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Jadwalkan Pelatihan Baru
            </button>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-medium space-y-1">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                Terjadi kesalahan input:
            </div>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Program</div>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total_courses']) }} Program</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Pelatihan terdaftar</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Peserta Terdaftar</div>
            <div class="text-xl font-black text-blue-600 mt-1">{{ number_format($stats['total_enrollments']) }} Peserta</div>
            <div class="text-[10px] text-blue-600 font-semibold mt-0.5">Partisipasi karyawan</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Telah Lulus</div>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ number_format($stats['completed_enrollments']) }} Kelulusan</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Lulus evaluasi program</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sertifikat Diterbitkan</div>
            <div class="text-xl font-black text-purple-600 mt-1">{{ number_format($stats['certificates_issued']) }} Sertifikat</div>
            <div class="text-[10px] text-purple-600 font-semibold mt-0.5">Nomor registrasi unik</div>
        </div>
    </div>

    <!-- Category Filter Bar -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            @php $currentCat = request('category', ''); @endphp
            <a href="{{ route('company.training.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ empty($currentCat) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua Kategori</a>
            <a href="{{ route('company.training.index', ['category' => 'technical']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $currentCat === 'technical' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">Technical</a>
            <a href="{{ route('company.training.index', ['category' => 'leadership']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $currentCat === 'leadership' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">Leadership</a>
            <a href="{{ route('company.training.index', ['category' => 'compliance']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $currentCat === 'compliance' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">Compliance & Regulasi</a>
            <a href="{{ route('company.training.index', ['category' => 'soft_skills']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $currentCat === 'soft_skills' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">Soft Skills</a>
        </div>
        <div class="text-xs text-slate-500 font-medium">
            Menampilkan <span class="font-bold text-slate-900">{{ $courses->total() }}</span> program pelatihan
        </div>
    </div>

    <!-- Courses List -->
    <div class="space-y-4">
        @forelse($courses as $course)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ expanded: false }">
                <div class="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="space-y-2 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @php
                                $badgeClasses = match($course->category) {
                                    'technical' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'leadership' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'compliance' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'soft_skills' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    default => 'bg-slate-50 text-slate-700 border-slate-200',
                                };
                                $statusClasses = match($course->status) {
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'in_progress' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-slate-50 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $badgeClasses }}">
                                {{ ucfirst(str_replace('_', ' ', $course->category)) }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold capitalize border {{ $statusClasses }}">
                                {{ $course->status }}
                            </span>
                        </div>

                        <div>
                            <h3 class="text-lg font-black text-slate-900">{{ $course->title }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $course->description ?? 'Tidak ada deskripsi detail.' }}</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-500 font-medium">
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                                Trainer: <strong class="text-slate-700 font-bold">{{ $course->trainer_name ?? 'Internal HR' }}</strong>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                Lokasi: <span class="text-slate-700">{{ $course->location }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                Jadwal: <span class="text-slate-700 font-bold">{{ $course->start_date->format('d M Y') }} - {{ $course->end_date->format('d M Y') }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400"></i>
                                Peserta: <strong class="text-slate-900 font-black">{{ $course->enrollments_count }}</strong>
                                @if($course->capacity)
                                    / {{ $course->capacity }} Kuota
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Course Actions -->
                    <div class="flex items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100 justify-end">
                        <button 
                            @click="selectedCourseId = '{{ $course->id }}'; selectedCourseTitle = '{{ addslashes($course->title) }}'; openEnrollModal = true"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-sm"
                        >
                            <i data-lucide="user-plus" class="w-3.5 h-3.5 text-slate-500"></i>
                            Daftarkan Karyawan
                        </button>

                        <button 
                            @click="expanded = !expanded"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition"
                        >
                            <i data-lucide="list" class="w-3.5 h-3.5"></i>
                            <span x-text="expanded ? 'Tutup Peserta' : 'Daftar Peserta (' + '{{ $course->enrollments_count }}' + ')'"></span>
                        </button>
                    </div>
                </div>

                <!-- Enrolled Participants Accordion Panel -->
                <div x-show="expanded" class="border-t border-slate-100 bg-slate-50/70 p-5 space-y-3" style="display: none;">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-500">Daftar Karyawan Terdaftar ({{ $course->enrollments->count() }})</h4>
                    </div>

                    @if($course->enrollments->isEmpty())
                        <div class="text-center py-6 text-xs text-slate-400">
                            Belum ada karyawan yang didaftarkan ke program pelatihan ini.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200/80 text-[10px] font-black uppercase text-slate-400">
                                        <th class="pb-2">Karyawan</th>
                                        <th class="pb-2">Departemen</th>
                                        <th class="pb-2">Status</th>
                                        <th class="pb-2">Nilai Akhir</th>
                                        <th class="pb-2">No. Sertifikat</th>
                                        <th class="pb-2 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($course->enrollments as $enr)
                                        <tr class="hover:bg-white/60 transition">
                                            <td class="py-2.5 font-bold text-slate-900">
                                                {{ $enr->employee?->full_name }}
                                                <div class="text-[10px] text-slate-400 font-normal">{{ $enr->employee?->nik }}</div>
                                            </td>
                                            <td class="py-2.5 text-slate-600 font-medium">
                                                {{ $enr->employee?->department?->name ?? 'Semua Departemen' }}
                                            </td>
                                            <td class="py-2.5">
                                                @if($enr->status === 'completed')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        Lulus
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                        Terdaftar
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 font-black text-slate-900">
                                                {{ $enr->score !== null ? number_format($enr->score, 1) : '-' }}
                                            </td>
                                            <td class="py-2.5 font-mono text-[11px] font-bold text-purple-700">
                                                {{ $enr->certificate_number ?? '-' }}
                                            </td>
                                            <td class="py-2.5 text-right">
                                                @if($enr->status !== 'completed')
                                                    <button 
                                                        @click="selectedEnrollmentId = '{{ $enr->id }}'; selectedEnrollmentEmpName = '{{ addslashes($enr->employee?->full_name) }}'; openCompleteModal = true"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700 transition"
                                                    >
                                                        <i data-lucide="award" class="w-3 h-3"></i>
                                                        Catat Kelulusan
                                                    </button>
                                                @else
                                                    <span class="text-[11px] text-emerald-600 font-bold inline-flex items-center gap-1">
                                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                        Selesai
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 shadow-sm">
                <i data-lucide="graduation-cap" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <div class="text-sm font-bold text-slate-700">Belum ada program pelatihan yang dijadwalkan</div>
                <p class="text-xs text-slate-400 mt-1">Klik tombol 'Jadwalkan Pelatihan Baru' untuk menambahkan agenda pelatihan kompetensi karyawan.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div>
        {{ $courses->links() }}
    </div>

    <!-- Modal 1: Jadwalkan Pelatihan Baru -->
    <div x-show="openCourseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openCourseModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900">Jadwalkan Program Pelatihan Baru</h3>
                <button @click="openCourseModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.training.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul Pelatihan <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Modern Laravel Clean Architecture & DDD" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Trainer / Lembaga</label>
                        <input type="text" name="trainer_name" placeholder="Contoh: John Doe / Internal L&D" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-slate-700 focus:ring-2 focus:ring-red-500 focus:outline-none">
                            <option value="technical">Technical</option>
                            <option value="leadership">Leadership</option>
                            <option value="compliance">Compliance</option>
                            <option value="soft_skills">Soft Skills</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi / Media <span class="text-red-500">*</span></label>
                        <input type="text" name="location" required placeholder="Ruang Workshop / Google Meet" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kapasitas Maksimal (Orang)</label>
                        <input type="number" name="capacity" min="1" placeholder="Kosongkan jika tanpa batas" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Silabus Program</label>
                    <textarea name="description" rows="3" placeholder="Uraian kompetensi yang diharapkan dicapai peserta..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="openCourseModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] shadow-sm shadow-red-500/20">
                        Simpan Program
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Daftarkan Karyawan -->
    <div x-show="openEnrollModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openEnrollModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-black text-slate-900">Daftarkan Karyawan</h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="selectedCourseTitle"></p>
                </div>
                <button @click="openEnrollModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/company/training/' + selectedCourseId + '/enroll'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                        <option value="">-- Pilih Karyawan Aktif --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->nik }} - {{ $emp->department?->name ?? 'Umum' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="openEnrollModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] shadow-sm shadow-red-500/20">
                        Daftarkan Peserta
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Catat Kelulusan & Terbitkan Sertifikat -->
    <div x-show="openCompleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openCompleteModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-black text-slate-900">Catat Kelulusan & Sertifikat</h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="'Peserta: ' + selectedEnrollmentEmpName"></p>
                </div>
                <button @click="openCompleteModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/company/training/enrollments/' + selectedEnrollmentId + '/complete'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Evaluasi Akhir (0 - 100) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.1" min="0" max="100" name="score" required placeholder="Contoh: 88.5" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-black text-slate-900 focus:ring-2 focus:ring-red-500 focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Nomor sertifikat registrasi unik akan dibuat otomatis oleh sistem jika nilai dinyatakan lulus.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Evaluasi / Feedback Trainer</label>
                    <textarea name="feedback" rows="3" placeholder="Uraian performa atau feedback untuk karyawan..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="openCompleteModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-sm shadow-emerald-500/20">
                        Terbitkan Sertifikat
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
