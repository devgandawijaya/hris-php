@extends('layouts.app')

@section('title', 'Evaluasi 360° & Performance Improvement Plan (PIP)')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'feedbacks',
    showReqModal: false,
    showPipModal: false,
    showNoteModal: false,
    showResolveModal: false,
    selectedPipId: null,
    selectedPipTitle: ''
}">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700">
                    Phase 10 • Talent Appraisals
                </span>
                <span class="text-xs text-slate-400">Section 10.M</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-1 tracking-tight">Evaluasi 360° & Performance Improvement Plan (PIP)</h1>
            <p class="text-xs text-slate-500 mt-1">Umpan balik multi-penilai (atasan, rekan kerja, bawahan, mandiri) dan program perbaikan kinerja terstruktur.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="showReqModal = true" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-sm flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                Request Review 360°
            </button>
            <button type="button" @click="showPipModal = true" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm flex items-center gap-2">
                <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                Terbitkan PIP Baru
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Penilaian 360°</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $feedbacks->total() }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Seluruh siklus evaluasi</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">360° Selesai Dievaluasi</div>
            <div class="text-2xl font-black text-emerald-600 mt-2">{{ $completedFeedbacksCount }}</div>
            <div class="text-[11px] text-emerald-700 mt-1">Telah diisi responden</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Menunggu Tanggapan</div>
            <div class="text-2xl font-black text-amber-600 mt-2">{{ $pendingFeedbacksCount }}</div>
            <div class="text-[11px] text-amber-700 mt-1">Pending di portal ESS</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Karyawan Dalam Masa PIP</div>
            <div class="text-2xl font-black text-red-600 mt-2">{{ $activePipCount }}</div>
            <div class="text-[11px] text-red-700 mt-1">Aktif dalam bimbingan</div>
        </div>
    </div>

    <!-- Tab Bar -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px">
        <button type="button" @click="activeTab = 'feedbacks'"
                :class="activeTab === 'feedbacks' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="compass" class="w-4 h-4"></i>
            Daftar Umpan Balik 360° ({{ $feedbacks->total() }})
        </button>

        <button type="button" @click="activeTab = 'pips'"
                :class="activeTab === 'pips' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="target" class="w-4 h-4"></i>
            Program Peningkatan Kinerja / PIP ({{ $pips->count() }})
        </button>
    </div>

    <!-- TAB 1: 360 FEEDBACK LIST -->
    <div x-show="activeTab === 'feedbacks'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Karyawan Yang Dinilai</th>
                        <th class="px-5 py-3">Hubungan Penilai</th>
                        <th class="px-5 py-3">Penilai (Rater)</th>
                        <th class="px-5 py-3">Status Evaluasi</th>
                        <th class="px-5 py-3">Skor Rata-Rata</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($feedbacks as $fb)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $fb->subjectEmployee->full_name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $fb->subjectEmployee->employee_number }} • {{ $fb->subjectEmployee->department?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $fb->rater_relationship === 'supervisor' ? 'bg-purple-100 text-purple-800' :
                                       ($fb->rater_relationship === 'peer' ? 'bg-blue-100 text-blue-800' :
                                       ($fb->rater_relationship === 'subordinate' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ $fb->rater_relationship }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($fb->is_anonymous)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-500 italic">
                                        <i data-lucide="eye-off" class="w-3.5 h-3.5 text-slate-400"></i> Anonim
                                    </span>
                                @else
                                    <span class="font-medium text-slate-800">{{ $fb->reviewerEmployee?->full_name ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($fb->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check" class="w-3 h-3"></i> Selesai ({{ $fb->completed_at?->format('d M Y') }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Menunggu Respon
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($fb->status === 'completed')
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 font-mono">{{ $fb->overall_score }} / 5.0</span>
                                        <span class="text-[10px] text-slate-400">
                                            (Nilai: {{ $fb->core_values_score }}, Lead: {{ $fb->leadership_score }}, Tim: {{ $fb->collaboration_score }}, Exec: {{ $fb->execution_score }})
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('company.appraisals360.summary', $fb->subject_employee_id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Ringkasan Radar 360°
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada permohonan umpan balik 360 derajat. Klik tombol di atas untuk membuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($feedbacks->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $feedbacks->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: PERFORMANCE IMPROVEMENT PLANS (PIP) -->
    <div x-show="activeTab === 'pips'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3">Karyawan & Judul PIP</th>
                        <th class="px-5 py-3">Supervisor / Mentor</th>
                        <th class="px-5 py-3">Periode Bimbingan</th>
                        <th class="px-5 py-3">Status PIP</th>
                        <th class="px-5 py-3">Sasaran Target</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pips as $pip)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $pip->employee->full_name }}</div>
                                <div class="text-[11px] font-semibold text-red-600 mt-0.5">{{ $pip->title }}</div>
                                <div class="text-[10px] text-slate-400">{{ Str::limit($pip->reason, 50) }}</div>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">
                                {{ $pip->supervisor?->full_name ?? 'HR Management' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-[11px] font-medium text-slate-800">{{ $pip->start_date->format('d M Y') }} s/d {{ $pip->end_date->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Durasi {{ $pip->start_date->diffInDays($pip->end_date) }} Hari</div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($pip->status === 'in_progress')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Sedang Berjalan
                                    </span>
                                @elseif($pip->status === 'passed')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Lulus (Passed)
                                    </span>
                                @elseif($pip->status === 'failed')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                        Tidak Lulus (Failed)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Diperpanjang
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 max-w-xs">
                                <p class="text-[11px] text-slate-700 line-clamp-2 leading-relaxed">{{ $pip->target_objectives }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="
                                        selectedPipId = {{ $pip->id }};
                                        selectedPipTitle = '{{ addslashes($pip->title) }}';
                                        showNoteModal = true;
                                    " class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                                        + Catatan Progres
                                    </button>

                                    @if($pip->status === 'in_progress')
                                        <button type="button" @click="
                                            selectedPipId = {{ $pip->id }};
                                            selectedPipTitle = '{{ addslashes($pip->title) }}';
                                            showResolveModal = true;
                                        " class="px-2.5 py-1 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold">
                                            Evaluasi Akhir
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada program PIP yang aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: REQUEST 360 FEEDBACK -->
    <div x-show="showReqModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4" @click.outside="showReqModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4 text-red-600"></i>
                    Request Evaluasi 360 Derajat
                </h3>
                <button type="button" @click="showReqModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.appraisals360.requests.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Karyawan Yang Dinilai *</label>
                    <select name="subject_employee_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->position?->title ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Karyawan Penilai (Rater) *</label>
                    <select name="reviewer_employee_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="">-- Pilih Penilai --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->position?->title ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hubungan Penilai *</label>
                        <select name="rater_relationship" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                            <option value="peer">Rekan Kerja (Peer)</option>
                            <option value="supervisor">Atasan Langsung (Supervisor)</option>
                            <option value="subordinate">Bawahan (Subordinate)</option>
                            <option value="self">Diri Sendiri (Self)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Siklus Penilaian</label>
                        <select name="review_cycle_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                            <option value="">-- Tanpa Siklus / Ad-hoc --</option>
                            @foreach($cycles as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1" checked class="rounded text-red-600 focus:ring-red-500">
                    <label for="is_anonymous" class="text-xs text-slate-700">Jaga kerahasiaan identitas penilai (100% Anonymous ke Karyawan)</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showReqModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Kirim Permintaan Review</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: CREATE PIP -->
    <div x-show="showPipModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl space-y-4" @click.outside="showPipModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="alert-octagon" class="w-4 h-4 text-red-600"></i>
                    Terbitkan Program Peningkatan Kinerja (PIP)
                </h3>
                <button type="button" @click="showPipModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.appraisals360.pips.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Karyawan Terbimbing *</label>
                        <select name="employee_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Supervisor Pembina</label>
                        <select name="supervisor_employee_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                            <option value="">-- HR / Default --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul / Program PIP *</label>
                    <input type="text" name="title" required placeholder="Contoh: PIP Peningkatan Kualitas Eksekusi & Target Sales Q1" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Latar Belakang / Alasan Diterbitkan *</label>
                    <textarea name="reason" rows="2" required placeholder="Jelaskan kesenjangan performa kerja atau KPI yang belum terpenuhi..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Berakhir *</label>
                        <input type="date" name="end_date" required value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sasaran & Target Yang Wajib Dicapai *</label>
                    <textarea name="target_objectives" rows="2" required placeholder="Poin target terukur yang harus diselesaikan dalam kurun waktu PIP..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Tindakan (Action Plan) *</label>
                    <textarea name="action_plan" rows="2" required placeholder="Bimbingan berkala, mentoring mingguan, review deliverables..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showPipModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Terbitkan PIP Resmi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD PIP NOTES -->
    <div x-show="showNoteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="showNoteModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Catat Progres Bimbingan PIP</h3>
                <button type="button" @click="showNoteModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="'{{ url('company/appraisals360/pips') }}/' + selectedPipId + '/notes'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <div class="text-xs text-slate-500 mb-1 font-semibold" x-text="selectedPipTitle"></div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Perkembangan Mingguan *</label>
                    <textarea name="notes" rows="4" required placeholder="Tuliskan catatan hasil 1-on-1 meeting, pencapaian milestone, atau area perbaikan..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showNoteModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: RESOLVE PIP -->
    <div x-show="showResolveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="showResolveModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Keputusan Akhir Evaluasi PIP</h3>
                <button type="button" @click="showResolveModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="'{{ url('company/appraisals360/pips') }}/' + selectedPipId + '/resolve'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <div class="text-xs text-slate-500 mb-2 font-semibold" x-text="selectedPipTitle"></div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hasil Akhir *</label>
                    <select name="status" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="passed">LULUS (Passed) — Performa memenuhi standar minimum</option>
                        <option value="failed">TIDAK LULUS (Failed) — Dilanjutkan ke peninjauan kontrak / PHK</option>
                        <option value="extended">DIPERPANJANG (Extended) — Diberikan tambahan masa bimbingan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Evaluasi Akhir</label>
                    <textarea name="final_evaluation" rows="3" placeholder="Ulasan ringkas dari Komite HR / Atasan..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showResolveModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Kirim Keputusan Akhir</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
