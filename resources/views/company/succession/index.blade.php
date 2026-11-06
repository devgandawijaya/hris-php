@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    showPlanModal: false,
    showCandidateModal: false,
    showSuggestModal: false,
    selectedPlanId: null,
    selectedPosition: null,
    suggestions: [],
    loadingSuggestions: false,
    openNominate(planId) {
        this.selectedPlanId = planId;
        this.showCandidateModal = true;
    },
    openSuggest(posId) {
        this.loadingSuggestions = true;
        this.showSuggestModal = true;
        fetch('{{ url('succession/suggest') }}/' + posId)
            .then(res => res.json())
            .then(data => {
                this.selectedPosition = data.position;
                this.suggestions = data.suggestions;
                this.loadingSuggestions = false;
            })
            .catch(() => {
                this.loadingSuggestions = false;
            });
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#DC2626] mb-1">
                <i data-lucide="git-pull-request" class="w-4 h-4"></i>
                Talent Pipeline & Continuity (Section 10.N)
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Rencana Suksesi & Talent Pool (Succession Planning)</h1>
            <p class="text-xs text-slate-500 mt-1">Pemetaan kesinambungan kepemimpinan dan suksesor posisi kunci (*Ready Now*, *Ready 1 Year*, *Ready 2+ Years*).</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <button @click="showPlanModal = true"
                    class="px-4 py-2.5 rounded-xl bg-[#DC2626] text-xs font-bold text-white hover:bg-red-700 transition shadow-sm shadow-red-500/20 flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                + Posisi Kunci Baru
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 4 KPI Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Posisi Kunci</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $pipeline['total_plans'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Jabatan kritis yang dipantau</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Posisi Terlindungi</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-shield" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-700 mt-2">{{ $pipeline['covered_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Memiliki suksesor 'Ready Now'</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Posisi Berisiko Tinggi</span>
                <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-red-600 mt-2">{{ $pipeline['uncovered_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Tanpa calon suksesor siap pakai</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kekuatan Bangku (Bench)</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-purple-700 mt-2">{{ $pipeline['bench_strength_percentage'] }}%</div>
            <div class="text-[11px] text-slate-500 mt-1">Dari total target kontinuitas</div>
        </div>
    </div>

    <!-- Succession Pipeline Grid -->
    <div class="space-y-6">
        @forelse($pipeline['plans'] as $plan)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
                <!-- Plan Header -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between pb-4 mb-4 border-b border-slate-100 gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center font-black text-base shrink-0 border border-slate-200">
                            {{ substr($plan->position?->title ?? 'P', 0, 2) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-black text-slate-900">{{ $plan->position?->title }}</h3>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $plan->criticality_level === 'critical' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-700' }} uppercase">
                                    {{ $plan->criticality_level }}
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $plan->ready_now_count > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $plan->risk_badge['label'] }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Departemen: <span class="font-bold text-slate-700">{{ $plan->position?->department?->name ?? 'Semua' }}</span>
                                • Pemangku Saat Ini: <span class="font-bold text-slate-800">{{ $plan->currentHolder?->full_name ?? 'Lowong / Belum Ditetapkan' }}</span>
                            </div>
                            @if($plan->emergency_replacement_plan)
                                <div class="text-[11px] text-slate-500 mt-1 italic">
                                    <span class="font-bold text-slate-700">Rencana Darurat:</span> {{ $plan->emergency_replacement_plan }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button @click="openSuggest({{ $plan->position_id }})"
                                class="px-3 py-1.5 rounded-xl border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-blue-600"></i>
                            Saran Berbasis AI
                        </button>
                        <button @click="openNominate({{ $plan->id }})"
                                class="px-3.5 py-1.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-red-700 transition flex items-center gap-1.5">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            + Nominasikan Suksesor
                        </button>
                    </div>
                </div>

                <!-- Candidates Bench Tiers -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tier 1: Ready Now -->
                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40">
                        <div class="flex items-center justify-between pb-2 mb-3 border-b border-emerald-200/60 text-xs">
                            <div class="font-bold text-emerald-900 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                Ready Now (Siap Sekarang)
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white text-emerald-700 shadow-2xs">
                                {{ $plan->candidates->where('readiness_level', 'ready_now')->where('status', 'active')->count() }} Calon
                            </span>
                        </div>
                        <div class="space-y-2">
                            @forelse($plan->candidates->where('readiness_level', 'ready_now')->where('status', 'active') as $c)
                                <div class="p-2.5 rounded-lg bg-white border border-emerald-100 text-xs shadow-2xs">
                                    <div class="font-bold text-slate-900">{{ $c->candidate?->full_name }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $c->candidate?->position?->title ?? 'Staf' }}</div>
                                    @if($c->strengths)
                                        <div class="text-[10px] text-emerald-700 mt-1 font-medium">Kekuatan: {{ $c->strengths }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-4 text-emerald-700/60 text-[11px] italic">Belum ada kandidat siap langsung menggantikan.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tier 2: Ready 1 Year -->
                    <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/40">
                        <div class="flex items-center justify-between pb-2 mb-3 border-b border-blue-200/60 text-xs">
                            <div class="font-bold text-blue-900 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                Ready in 1 Year (Siap 1 Tahun)
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white text-blue-700 shadow-2xs">
                                {{ $plan->candidates->where('readiness_level', 'ready_1_year')->where('status', 'active')->count() }} Calon
                            </span>
                        </div>
                        <div class="space-y-2">
                            @forelse($plan->candidates->where('readiness_level', 'ready_1_year')->where('status', 'active') as $c)
                                <div class="p-2.5 rounded-lg bg-white border border-blue-100 text-xs shadow-2xs">
                                    <div class="font-bold text-slate-900">{{ $c->candidate?->full_name }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $c->candidate?->position?->title ?? 'Staf' }}</div>
                                    @if($c->development_needs)
                                        <div class="text-[10px] text-blue-700 mt-1 font-medium">Kebutuhan: {{ $c->development_needs }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-4 text-blue-700/60 text-[11px] italic">Belum ada kandidat jangka menengah.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tier 3: Ready 2+ Years -->
                    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/40">
                        <div class="flex items-center justify-between pb-2 mb-3 border-b border-amber-200/60 text-xs">
                            <div class="font-bold text-amber-900 flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                Ready 2+ Years (Jangka Panjang)
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-white text-amber-700 shadow-2xs">
                                {{ $plan->candidates->where('readiness_level', 'ready_2_plus_years')->where('status', 'active')->count() }} Calon
                            </span>
                        </div>
                        <div class="space-y-2">
                            @forelse($plan->candidates->where('readiness_level', 'ready_2_plus_years')->where('status', 'active') as $c)
                                <div class="p-2.5 rounded-lg bg-white border border-amber-100 text-xs shadow-2xs">
                                    <div class="font-bold text-slate-900">{{ $c->candidate?->full_name }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $c->candidate?->position?->title ?? 'Staf' }}</div>
                                    @if($c->development_needs)
                                        <div class="text-[10px] text-amber-700 mt-1 font-medium">Kebutuhan: {{ $c->development_needs }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-4 text-amber-700/60 text-[11px] italic">Belum ada suksesor potensial jangka panjang.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 text-xs">
                <i data-lucide="git-pull-request" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                Belum ada rencana suksesi yang didaftarkan. Klik tombol <strong>+ Posisi Kunci Baru</strong> di atas untuk menetapkan posisi strategis perusahaan.
            </div>
        @endforelse
    </div>

    <!-- MODAL 1: POSISI KUNCI BARU -->
    <div x-show="showPlanModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showPlanModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-4">Tambah Posisi Kunci Rencana Suksesi</h3>
            <form method="POST" action="{{ route('company.succession.plans.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Jabatan Kunci <span class="text-red-500">*</span></label>
                    <select name="position_id" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                        <option value="">Pilih Posisi</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}">{{ $pos->title }} ({{ $pos->department?->name ?? 'Semua' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pemangku Jabatan Saat Ini (Incumbent)</label>
                    <select name="current_holder_employee_id" class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                        <option value="">(Lowong / Belum Ada)</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->position?->title }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tingkat Kritikalitas <span class="text-red-500">*</span></label>
                    <select name="criticality_level" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 font-semibold">
                        <option value="critical">Kritis (Posisi C-Level / Direktur / Head of Division Vital)</option>
                        <option value="high" selected>Tinggi (Manager Kunci / Lead Spesialis)</option>
                        <option value="medium">Menengah (Supervisor / Senior Staff)</option>
                        <option value="low">Rendah (Posisi Umum)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Rencana Darurat (Emergency Plan)</label>
                    <textarea name="emergency_replacement_plan" rows="2" placeholder="Tindakan jika pejabat saat ini tiba-tiba berhalangan tetap atau mengundurkan diri..."
                              class="w-full rounded-xl border-slate-200 text-xs py-2 px-3"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showPlanModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] font-bold text-white hover:bg-red-700">Simpan Rencana</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: NOMINASI SUKSESOR -->
    <div x-show="showCandidateModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCandidateModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-4">Nominasikan Kandidat Suksesor</h3>
            <form method="POST" action="{{ route('company.succession.candidates.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="succession_plan_id" :value="selectedPlanId">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Calon Karyawan <span class="text-red-500">*</span></label>
                    <select name="candidate_employee_id" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                        <option value="">Pilih Karyawan</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->position?->title ?? '-' }} - {{ $emp->department?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kesiapan Waktu (Readiness Tier) <span class="text-red-500">*</span></label>
                    <select name="readiness_level" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 font-semibold">
                        <option value="ready_now">Ready Now (Siap Menggantikan Segera)</option>
                        <option value="ready_1_year" selected>Ready 1 Year (Siap Dalam 1 Tahun Pendampingan)</option>
                        <option value="ready_2_plus_years">Ready 2+ Years (Jangka Panjang / Talenta Muda)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kekuatan Utama (Strengths)</label>
                    <textarea name="strengths" rows="2" placeholder="Kompetensi unggul, rekam jejak capaian KPI, integritas..."
                              class="w-full rounded-xl border-slate-200 text-xs py-2 px-3"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kebutuhan Pengembangan (Development Needs)</label>
                    <textarea name="development_needs" rows="2" placeholder="Pelatihan kepemimpinan, rotasi kerja, sertifikasi yang masih perlu diambil..."
                              class="w-full rounded-xl border-slate-200 text-xs py-2 px-3"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCandidateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] font-bold text-white hover:bg-red-700">Nominasikan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: REKOMENDASI SUKSESOR CERDAS (AI / KINERJA) -->
    <div x-show="showSuggestModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showSuggestModal = false" class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-slate-200">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-blue-600">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        Rekomendasi Suksesor Berbasis Algoritma Kinerja & Matriks Skill
                    </div>
                    <template x-if="selectedPosition">
                        <h3 class="text-base font-bold text-slate-900 mt-1" x-text="'Target Posisi: ' + selectedPosition.title + ' (' + selectedPosition.department + ')'"></h3>
                    </template>
                </div>
                <button @click="showSuggestModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <template x-if="loadingSuggestions">
                <div class="py-12 text-center text-slate-400 text-xs">Menganalisis penilaian KPI dan kecocokan profil kompetensi karyawan...</div>
            </template>

            <template x-if="!loadingSuggestions && suggestions.length > 0">
                <div class="space-y-3">
                    <div class="text-[11px] text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        * Skoring Gabungan dihitung dari: <strong>60% Nilai Kinerja KPI</strong> + <strong>40% Kecocokan Standar Kompetensi Posisi</strong>.
                    </div>
                    <template x-for="s in suggestions" :key="s.employee_id">
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
                            <div>
                                <div class="font-bold text-slate-900 text-xs" x-text="s.employee_name"></div>
                                <div class="text-[10px] text-slate-400" x-text="s.current_position + ' • ' + s.current_department"></div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold" x-text="'KPI: ' + s.performance_score"></span>
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold" x-text="'Skill Match: ' + s.skill_match_score + '%'"></span>
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-purple-50 text-purple-700 font-black" x-text="'Skor Gabungan: ' + s.combined_score"></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full uppercase"
                                      :class="s.suggested_readiness === 'ready_now' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'"
                                      x-text="s.suggested_readiness.replace('_', ' ')"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!loadingSuggestions && suggestions.length === 0">
                <div class="py-8 text-center text-slate-400 text-xs">Belum ada kandidat lain di perusahaan yang dapat direkomendasikan untuk posisi ini.</div>
            </template>
        </div>
    </div>
</div>
@endsection

