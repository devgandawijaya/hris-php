@extends('layouts.app')

@section('title', 'Logbook Pemagangan - ' . $internship->intern_candidate_name)

@section('content')
<div class="space-y-6" x-data="{ logModal: false, verifyModal: false, selectedLogId: null }">

    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('internships.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <span class="text-xs text-slate-400 font-mono">{{ $internship->agreement_letter_number }}</span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $internship->intern_candidate_name }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('internships.print-certificate', $internship->id) }}" target="_blank" class="px-3 py-2 bg-teal-600 text-white text-xs font-semibold rounded-xl hover:bg-teal-700 transition flex items-center gap-1.5">
                <i data-lucide="award" class="w-4 h-4"></i>
                Cetak Sertifikat Magang
            </a>
            <button @click="logModal = true" class="px-3 py-2 bg-[#DC2626] text-white text-xs font-semibold rounded-xl hover:bg-[#991B1B] transition flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Catat Logbook Harian
            </button>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-slate-400 block font-bold uppercase text-[10px]">Institusi Asal:</span>
            <div class="font-bold text-slate-800 mt-1">{{ $internship->institution_origin }}</div>
            <span class="text-[10px] text-slate-400 font-mono">NIK: {{ $internship->intern_nik_ktp }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-slate-400 block font-bold uppercase text-[10px]">Kurikulum / Posisi:</span>
            <div class="font-bold text-slate-800 mt-1">{{ $internship->program_title }}</div>
            <span class="text-[10px] text-slate-400">{{ $internship->department->name ?? 'Lintas Divisi' }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-slate-400 block font-bold uppercase text-[10px]">Pembimbing (Mentor):</span>
            <div class="font-bold text-slate-800 mt-1">{{ $internship->mentor->full_name ?? 'Belum Ditunjuk' }}</div>
            <span class="text-[10px] text-slate-400">{{ $internship->mentor->position->title ?? '-' }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-slate-400 block font-bold uppercase text-[10px]">Uang Saku & BPJS:</span>
            <div class="font-bold text-emerald-700 mt-1">Rp {{ number_format($internship->pocket_money_allowance_daily, 0, ',', '.') }}/hari</div>
            <span class="text-[10px] px-1.5 py-0.2 rounded bg-blue-100 text-blue-800">BPJS BPU Aktif</span>
        </div>
    </div>

    <!-- Logbook Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Logbook Catatan Aktivitas & Evaluasi Kompetensi Instruktur</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Ringkasan Aktivitas / Pekerjaan</th>
                        <th class="p-3">Kompetensi yang Dipelajari</th>
                        <th class="p-3">Ulasan & Rating Mentor</th>
                        <th class="p-3">Status Verifikasi</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($internship->logbooks as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3 font-medium text-slate-900 whitespace-nowrap">{{ $log->log_date->format('d M Y') }}</td>
                            <td class="p-3 text-slate-700 max-w-sm">{{ $log->activity_summary }}</td>
                            <td class="p-3 font-semibold text-slate-800">{{ $log->competency_mastered }}</td>
                            <td class="p-3">
                                @if($log->is_verified_by_mentor)
                                    <div class="font-bold text-amber-600">★ {{ number_format($log->mentor_rating, 1) }} / 5.0</div>
                                    <div class="text-[11px] text-slate-500 italic">{{ $log->mentor_feedback }}</div>
                                @else
                                    <span class="text-[11px] text-slate-400">Menunggu Review</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $log->is_verified_by_mentor ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $log->is_verified_by_mentor ? 'Terverifikasi' : 'Pending' }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                @if(!$log->is_verified_by_mentor)
                                    <button @click="selectedLogId = {{ $log->id }}; verifyModal = true" class="px-2.5 py-1 rounded bg-teal-600 text-white font-medium hover:bg-teal-700 transition text-[11px]">
                                        Nilai & Verifikasi
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 text-xs">Belum ada catatan aktivitas harian pada logbook ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Log Modal -->
    <div x-show="logModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="logModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Tambah Catatan Logbook Harian</h3>
                <button @click="logModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('internships.logbooks.store', $internship->id) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Tanggal *</label>
                    <input type="date" name="log_date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Kompetensi yang Dipelajari *</label>
                    <input type="text" name="competency_mastered" required placeholder="Pengujian API, Database Indexing, UI Component..." class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Ringkasan Aktivitas Pekerjaan *</label>
                    <textarea name="activity_summary" rows="4" required placeholder="Hari ini mempelajari arsitektur microservices dan menyelesaikan sprint ticket..." class="w-full px-3 py-2 border rounded-xl border-slate-300"></textarea>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="logModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-xl hover:bg-[#991B1B] font-semibold">Simpan Aktivitas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Verify Log Modal -->
    <div x-show="verifyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="verifyModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Verifikasi & Penilaian Instruktur / Mentor</h3>
                <button @click="verifyModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="'{{ url('internships/logbooks') }}/' + selectedLogId + '/verify'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Rating Evaluasi Kinerja (Skala 1.0 - 5.0) *</label>
                    <input type="number" step="0.1" min="1.0" max="5.0" name="mentor_rating" required value="4.5" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Ulasan & Masukan Pembimbing *</label>
                    <textarea name="mentor_feedback" rows="3" required placeholder="Pemagang menunjukkan inisiatif tinggi dan memahami materi dengan cepat..." class="w-full px-3 py-2 border rounded-xl border-slate-300"></textarea>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="verifyModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-xl hover:bg-teal-700 font-semibold">Verifikasi Logbook</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
