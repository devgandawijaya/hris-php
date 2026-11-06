@extends('layouts.app')

@section('title', 'Aduan Karyawan & Saluran Whistleblowing')

@section('content')
<div class="space-y-6" x-data="{ 
    openStatusModal: false, 
    openResolveModal: false, 
    openDetailModal: false,
    selectedGrievanceId: '', 
    selectedSubject: '', 
    selectedReporter: '',
    selectedCategory: '',
    selectedDesc: '',
    selectedStatus: '',
    selectedInvestigationNotes: '',
    selectedResolution: ''
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Aduan Karyawan & Saluran Whistleblowing</h1>
            <p class="text-sm text-slate-500 mt-1">Kanal penyampaian keluhan kerja, mediasi perselisihan industrial, serta perlindungan pelapor (whistleblower).</p>
        </div>
    </div>

    <!-- Confidentiality Guarantee Banner -->
    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-950 flex items-start gap-3 text-xs leading-relaxed">
        <i data-lucide="shield-check" class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5"></i>
        <div>
            <span class="font-bold">Perlindungan Kerahasiaan & Non-Retaliasi:</span>
            Karyawan dapat memilih untuk mengirim aduan secara anonim. Identitas pelapor dilindungi sepenuhnya guna mencegah tindakan intimidasi atau sanksi balasan di tempat kerja, menjamin transparansi serta kepatuhan norma hubungan kerja.
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Keluhan Masuk</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Semua laporan terdaftar</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dalam Investigasi / Review</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($stats['pending']) }}</div>
            <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Membutuhkan tindakan HR</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terselesaikan (Resolved)</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['resolved']) }}</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Mediasi & solusi tuntas</div>
        </div>
    </div>

    <!-- Grievances Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Daftar Pengaduan & Keluhan Karyawan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pantau alur investigasi, wawancara para pihak, dan catatan mediasi.</p>
            </div>

            <!-- Status Filter -->
            <form action="{{ route('company.grievances.index') }}" method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Semua Status Aduan</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Baru Masuk (Submitted)</option>
                    <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>Dalam Review</option>
                    <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Dalam Investigasi</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Selesai (Resolved)</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Ditutup (Closed)</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Kategori & Tanggal</th>
                        <th class="py-3 px-4">Pelapor</th>
                        <th class="py-3 px-4">Subjek & Deskripsi</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Hasil Solusi / Mediasi</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($grievances as $g)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 capitalize">
                                    {{ str_replace('_', ' ', $g->category) }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $g->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @if($g->is_anonymous)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-900 text-slate-100">
                                        <i data-lucide="eye-off" class="w-3 h-3 text-amber-400"></i>
                                        Anonim (Rahasia)
                                    </span>
                                @else
                                    <div class="font-bold text-slate-900">{{ $g->employee?->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $g->employee?->department?->name ?? 'Dept' }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <div class="font-bold text-slate-900 truncate" title="{{ $g->subject }}">{{ $g->subject }}</div>
                                <div class="text-slate-500 text-[11px] truncate mt-0.5" title="{{ $g->description }}">{{ $g->description }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($g->status === 'submitted')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Baru Masuk
                                    </span>
                                @elseif($g->status === 'in_review')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800">
                                        Review HR
                                    </span>
                                @elseif($g->status === 'investigating')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-orange-100 text-orange-800">
                                        Investigasi
                                    </span>
                                @elseif($g->status === 'resolved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check" class="w-3 h-3"></i> Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Ditutup
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                @if($g->resolution_notes)
                                    <div class="text-slate-700 text-[11px] truncate font-medium" title="{{ $g->resolution_notes }}">
                                        {{ $g->resolution_notes }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Oleh: {{ $g->resolvedBy?->name ?? 'HR' }} &bull; {{ $g->resolved_at?->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum ada resolusi</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button 
                                        @click="openDetailModal = true; 
                                                selectedGrievanceId = '{{ $g->id }}'; 
                                                selectedSubject = '{{ addslashes($g->subject) }}'; 
                                                selectedReporter = '{{ $g->is_anonymous ? 'Anonim (Rahasia)' : addslashes($g->employee?->full_name) }}';
                                                selectedCategory = '{{ $g->category }}';
                                                selectedDesc = '{{ addslashes($g->description) }}';
                                                selectedStatus = '{{ $g->status }}';
                                                selectedInvestigationNotes = '{{ addslashes($g->investigation_notes ?? '') }}';
                                                selectedResolution = '{{ addslashes($g->resolution_notes ?? '') }}';"
                                        class="px-2.5 py-1 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-bold transition text-xs border border-slate-200">
                                        Detail
                                    </button>

                                    @if($g->status !== 'resolved' && $g->status !== 'closed')
                                        <button 
                                            @click="openStatusModal = true; selectedGrievanceId = '{{ $g->id }}';"
                                            class="px-2.5 py-1 rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 font-bold transition text-xs border border-amber-200">
                                            Proses
                                        </button>
                                        <button 
                                            @click="openResolveModal = true; selectedGrievanceId = '{{ $g->id }}'; selectedSubject = '{{ addslashes($g->subject) }}';"
                                            class="px-2.5 py-1 rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 font-bold transition text-xs border border-emerald-200">
                                            Selesaikan
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Tidak ada aduan karyawan dalam kriteria ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($grievances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $grievances->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: View Detail -->
    <div x-show="openDetailModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openDetailModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i>
                    Detail Pengaduan Karyawan
                </div>
                <button @click="openDetailModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <span class="font-bold text-slate-400 uppercase text-[10px] block">Pelapor</span>
                    <div class="text-sm font-bold text-slate-900 mt-0.5" x-text="selectedReporter"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-400 uppercase text-[10px] block">Subjek Aduan</span>
                    <div class="text-sm font-bold text-slate-800 mt-0.5" x-text="selectedSubject"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-400 uppercase text-[10px] block">Deskripsi Lengkap</span>
                    <div class="mt-1 p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 leading-relaxed whitespace-pre-line" x-text="selectedDesc"></div>
                </div>
                <template x-if="selectedInvestigationNotes">
                    <div>
                        <span class="font-bold text-amber-600 uppercase text-[10px] block">Catatan Investigasi / Temuan Lapangan</span>
                        <div class="mt-1 p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 leading-relaxed whitespace-pre-line" x-text="selectedInvestigationNotes"></div>
                    </div>
                </template>
                <template x-if="selectedResolution">
                    <div>
                        <span class="font-bold text-emerald-600 uppercase text-[10px] block">Solusi / Hasil Mediasi Final</span>
                        <div class="mt-1 p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-900 leading-relaxed whitespace-pre-line" x-text="selectedResolution"></div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" @click="openDetailModal = false" class="px-5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Update Status -->
    <div x-show="openStatusModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openStatusModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="refresh-cw" class="w-5 h-5 text-amber-600"></i>
                    Perbarui Tahap Penanganan
                </div>
                <button @click="openStatusModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('grievances') }}/' + selectedGrievanceId + '/status'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahap Penanganan Baru <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="in_review">Review HR (Penelaahan Awal)</option>
                        <option value="investigating">Dalam Investigasi (Wawancara & Bukti)</option>
                        <option value="closed">Tutup Aduan (Tidak Berdasar / Batal)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Investigasi / Perkembangan</label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Telah memanggil saksi dan mengumpulkan data rekaman CCTV..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openStatusModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition">
                        Simpan Perkembangan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Resolve Grievance -->
    <div x-show="openResolveModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openResolveModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                    Selesaikan Aduan & Mediasi
                </div>
                <button @click="openResolveModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('grievances') }}/' + selectedGrievanceId + '/resolve'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="text-xs text-slate-600">
                    Selesaikan aduan: <strong class="text-slate-900" x-text="selectedSubject"></strong>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Penyelesaian & Solusi Mediasi <span class="text-red-500">*</span></label>
                    <textarea name="resolution_notes" rows="4" required placeholder="Jelaskan kesepakatan damai, sanksi yang dijatuhkan (jika ada), atau tindakan koreksi operasional yang disepakati..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openResolveModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">
                        Tandai Selesai & Beri Solusi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
