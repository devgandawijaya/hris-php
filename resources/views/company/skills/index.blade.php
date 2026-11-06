@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    activeTab: 'gap_analysis',
    showSkillModal: false,
    showCategoryModal: false,
    showRequirementModal: false,
    showAssessModal: false,
    showDetailModal: false,
    selectedEmployee: null,
    selectedAnalysis: null,
    loadingDetail: false,
    openDetail(empId) {
        this.loadingDetail = true;
        this.showDetailModal = true;
        fetch('{{ url('skills/gap') }}/' + empId)
            .then(res => res.json())
            .then(data => {
                this.selectedEmployee = data.employee;
                this.selectedAnalysis = data.analysis;
                this.loadingDetail = false;
            })
            .catch(() => {
                this.loadingDetail = false;
            });
    }
}">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#DC2626] mb-1">
                <i data-lucide="award" class="w-4 h-4"></i>
                Talent & Career Development (Section 10.N)
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Matriks Kompetensi & Analisis Kesenjangan (Skill Gap)</h1>
            <p class="text-xs text-slate-500 mt-1">Pemetaan standar kecocokan keahlian jabatan, evaluasi sertifikasi, dan rekomendasi program upskilling.</p>
        </div>
        <div class="mt-4 md:mt-0 flex flex-wrap gap-2.5">
            <button @click="showCategoryModal = true"
                    class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-sm flex items-center gap-1.5">
                <i data-lucide="folder-plus" class="w-4 h-4 text-slate-500"></i>
                + Kategori
            </button>
            <button @click="showSkillModal = true"
                    class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-sm flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4 text-slate-500"></i>
                + Kamus Skill
            </button>
            <button @click="showRequirementModal = true"
                    class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-sm flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-4 h-4 text-slate-500"></i>
                + Standar Jabatan
            </button>
            <button @click="showAssessModal = true"
                    class="px-4 py-2 rounded-xl bg-[#DC2626] text-xs font-bold text-white hover:bg-red-700 transition shadow-sm shadow-red-500/20 flex items-center gap-1.5">
                <i data-lucide="user-check" class="w-4 h-4"></i>
                Nilai / Sertifikasi Karyawan
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

    <!-- 4 KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Karyawan Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $deptOverview['total_employees'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Dalam cakupan evaluasi kompetensi</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rata-rata Kecocokan (Match)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="percent" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $deptOverview['average_match_score'] }}%</div>
            <div class="text-[11px] text-slate-500 mt-1">Kesesuaian standar profil jabatan</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kompetensi Tinggi (≥80%)</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="award" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-purple-700 mt-2">{{ $deptOverview['high_readiness_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Karyawan siap promosi / suksesi</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Perlu Upskilling (Gap > 0)</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-600 mt-2">{{ $deptOverview['gap_count'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Target prioritas pelatihan internal</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 mb-6 space-x-6 text-xs font-bold">
        <button @click="activeTab = 'gap_analysis'"
                :class="activeTab === 'gap_analysis' ? 'border-[#DC2626] text-[#DC2626] border-b-2 pb-3' : 'text-slate-500 hover:text-slate-700 pb-3'">
            <div class="flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                1. Analisis Kesenjangan & Upskilling (Gap Matrix)
            </div>
        </button>
        <button @click="activeTab = 'position_standards'"
                :class="activeTab === 'position_standards' ? 'border-[#DC2626] text-[#DC2626] border-b-2 pb-3' : 'text-slate-500 hover:text-slate-700 pb-3'">
            <div class="flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                2. Standar Kompetensi Jabatan & Kamus Skill
            </div>
        </button>
        <button @click="activeTab = 'skill_records'"
                :class="activeTab === 'skill_records' ? 'border-[#DC2626] text-[#DC2626] border-b-2 pb-3' : 'text-slate-500 hover:text-slate-700 pb-3'">
            <div class="flex items-center gap-2">
                <i data-lucide="check-square" class="w-4 h-4"></i>
                3. Daftar Penilaian & Sertifikasi
            </div>
        </button>
    </div>

    <!-- TAB 1: GAP ANALYSIS -->
    <div x-show="activeTab === 'gap_analysis'" class="space-y-6">
        <!-- Filter Bar -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" action="{{ route('company.skills.index') }}" class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-600">Filter Departemen:</label>
                <select name="department_id" onchange="this.form.submit()"
                        class="text-xs rounded-xl border-slate-200 bg-slate-50 focus:border-red-500 focus:ring-red-500 py-1.5 px-3">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $selectedDeptId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
                @if($selectedDeptId)
                    <a href="{{ route('company.skills.index') }}" class="text-xs font-semibold text-red-600 hover:underline">Reset</a>
                @endif
            </form>
            <div class="text-xs text-slate-500 font-medium">
                Menampilkan {{ $employees->count() }} karyawan aktif
            </div>
        </div>

        <!-- Employee Gap Scorecard Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="p-4">Karyawan</th>
                            <th class="p-4">Jabatan & Departemen</th>
                            <th class="p-4">Kecocokan Kompetensi</th>
                            <th class="p-4 text-center">Standar Terpenuhi</th>
                            <th class="p-4 text-center">Status Kesiapan</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($employees as $emp)
                            @php
                                $scorecard = $employeeScorecards[$emp->id] ?? null;
                                $matchPct = $scorecard ? $scorecard['overall_match_percentage'] : 0;
                                $totalReq = $scorecard ? $scorecard['total_requirements'] : 0;
                                $metReq = $scorecard ? $scorecard['met_requirements'] : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-4">
                                    <div class="font-bold text-slate-900">{{ $emp->full_name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $emp->employee_code }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-semibold text-slate-800">{{ $emp->position?->title ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $emp->department?->name ?? '-' }}</div>
                                </td>
                                <td class="p-4 min-w-[200px]">
                                    <div class="flex items-center justify-between text-xs font-bold mb-1">
                                        <span class="{{ $matchPct >= 80 ? 'text-emerald-700' : ($matchPct >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $matchPct }}%</span>
                                        <span class="text-[10px] text-slate-400">{{ $totalReq }} Standar Skill</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300 {{ $matchPct >= 80 ? 'bg-emerald-500' : ($matchPct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                             style="width: {{ $matchPct }}%"></div>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $metReq == $totalReq && $totalReq > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $metReq }} / {{ $totalReq }} Sesuai
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    @if($totalReq === 0)
                                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-500 font-bold">Belum Ada Standar</span>
                                    @elseif($matchPct >= 80)
                                        <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-black">Siap Suksesi</span>
                                    @elseif($matchPct >= 50)
                                        <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-black">Tahap Perkembangan</span>
                                    @else
                                        <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-red-50 text-red-700 font-black">Prioritas Upskilling</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <button @click="openDetail({{ $emp->id }})"
                                            class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold transition flex items-center gap-1.5 ml-auto">
                                        <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                        Rincian & Pelatihan
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Belum ada data karyawan aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: POSITION STANDARDS & SKILL DICTIONARY -->
    <div x-show="activeTab === 'position_standards'" class="space-y-8">
        <!-- Standar Kompetensi Jabatan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Standar Kompetensi Per Jabatan</h2>
                    <p class="text-xs text-slate-500">Target tingkat kemahiran (Level 1-5) yang wajib dimiliki untuk memangku jabatan ini.</p>
                </div>
                <button @click="showRequirementModal = true"
                        class="px-3.5 py-1.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-red-700 transition">
                    + Tambah Standar
                </button>
            </div>

            <div class="space-y-6">
                @forelse($positions as $pos)
                    @php $reqs = $positionRequirements->get($pos->id) ?? collect(); @endphp
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 mb-3 border-b border-slate-200">
                            <div>
                                <span class="text-xs font-bold text-slate-900">{{ $pos->title }}</span>
                                <span class="text-[11px] text-slate-500 ml-2">({{ $pos->department?->name ?? 'Semua Departemen' }})</span>
                            </div>
                            <span class="text-[11px] font-bold text-slate-500 mt-1 sm:mt-0">{{ $reqs->count() }} Kompetensi Ditetapkan</span>
                        </div>
                        @if($reqs->isEmpty())
                            <p class="text-xs text-slate-400 italic">Belum ada standar kompetensi yang ditetapkan untuk jabatan ini.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($reqs as $r)
                                    <div class="p-3 bg-white rounded-xl border border-slate-200 flex items-center justify-between shadow-2xs">
                                        <div>
                                            <div class="font-bold text-slate-800 text-xs">{{ $r->skill?->name ?? 'Skill' }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $r->skill?->category?->name ?? 'General' }}</div>
                                            <div class="mt-1 flex items-center gap-1.5">
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700">
                                                    Target: Level {{ $r->required_level }}/5
                                                </span>
                                                @if($r->is_mandatory)
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-red-50 text-red-600">Wajib</span>
                                                @endif
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('company.skills.requirements.delete', $r->id) }}" onsubmit="return confirm('Hapus standar ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-400 hover:text-red-600 transition" title="Hapus Standar">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Belum ada jabatan yang dibuat.</p>
                @endforelse
            </div>
        </div>

        <!-- Kamus Skill -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Kamus Kompetensi (Competency Dictionary)</h2>
                    <p class="text-xs text-slate-500">Daftar keahlian teknis (hard skill), soft skill, kepemimpinan, dan kepatuhan regulasi.</p>
                </div>
                <button @click="showSkillModal = true"
                        class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                    + Tambah Ke Kamus
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($skills as $s)
                    <div class="p-4 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                {{ $s->category?->name ?? 'General' }}
                            </span>
                            @if($s->code)
                                <span class="text-[10px] font-mono text-slate-400">{{ $s->code }}</span>
                            @endif
                        </div>
                        <div class="font-bold text-slate-900 text-xs mt-2">{{ $s->name }}</div>
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $s->description ?? 'Tidak ada deskripsi rinci.' }}</p>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-8 text-slate-400 text-xs">Belum ada kompetensi terdaftar. Silakan buat kamus keahlian baru.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 3: SKILL RECORDS -->
    <div x-show="activeTab === 'skill_records'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Keahlian & Sertifikasi Karyawan</h2>
                    <p class="text-xs text-slate-500">Log penilaian proficiency level dan data sertifikasi profesional terverifikasi.</p>
                </div>
                <button @click="showAssessModal = true"
                        class="px-3.5 py-1.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-red-700 transition">
                    + Catat Nilai Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="p-3.5">Karyawan</th>
                            <th class="p-3.5">Kompetensi Keahlian</th>
                            <th class="p-3.5">Tingkat Kemahiran</th>
                            <th class="p-3.5">Sertifikasi & Masa Berlaku</th>
                            <th class="p-3.5">Verifikator</th>
                            <th class="p-3.5">Tanggal Dinilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @php
                            $allEmployeeSkills = \App\Models\EmployeeSkill::where('tenant_id', app(\App\Tenancy\TenantManager::class)->getCurrentTenantId())
                                ->with(['employee', 'skill.category', 'verifiedBy'])
                                ->latest()
                                ->take(30)
                                ->get();
                        @endphp
                        @forelse($allEmployeeSkills as $es)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900">{{ $es->employee?->full_name ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $es->employee?->employee_code ?? '' }}</div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-800">{{ $es->skill?->name ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $es->skill?->category?->name ?? 'General' }}</div>
                                </td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $es->current_level >= 4 ? 'bg-emerald-50 text-emerald-700' : ($es->current_level >= 3 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                        ★ {{ $es->proficiency_label }}
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    @if($es->certification_name)
                                        <div class="font-bold text-slate-800">{{ $es->certification_name }}</div>
                                        <div class="text-[10px] text-slate-500">
                                            Exp: {{ $es->valid_until ? $es->valid_until->format('d/m/Y') : 'Seumur Hidup' }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Penilaian Internal</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-slate-600">
                                    {{ $es->verifiedBy?->name ?? 'Sistem HR' }}
                                </td>
                                <td class="p-3.5 text-slate-500">
                                    {{ $es->updated_at->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Belum ada riwayat penilaian kompetensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL 1: RINCIAN GAP & REKOMENDASI PELATIHAN -->
    <div x-show="showDetailModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showDetailModal = false"
             class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-slate-200">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Rincian Analisis Kesenjangan (Skill Gap)</h3>
                    <template x-if="selectedEmployee">
                        <p class="text-xs text-slate-500 mt-0.5" x-text="selectedEmployee.name + ' (' + selectedEmployee.position + ' - ' + selectedEmployee.department + ')'"></p>
                    </template>
                </div>
                <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <template x-if="loadingDetail">
                <div class="py-12 text-center text-slate-400 text-xs">Memuat data kesenjangan kompetensi...</div>
            </template>

            <template x-if="!loadingDetail && selectedAnalysis">
                <div class="space-y-6">
                    <!-- Overall Match Card -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tingkat Kecocokan Standar</div>
                            <div class="text-xl font-black text-slate-900 mt-1" x-text="selectedAnalysis.overall_match_percentage + '%'"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] text-slate-500">Standar Terpenuhi</div>
                            <div class="text-sm font-bold text-slate-800" x-text="selectedAnalysis.met_requirements + ' dari ' + selectedAnalysis.total_requirements + ' keahlian'"></div>
                        </div>
                    </div>

                    <!-- Skills List -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">Evaluasi Standar Per Keahlian</h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            <template x-for="s in selectedAnalysis.skills" :key="s.skill_id">
                                <div class="p-3 rounded-xl border border-slate-200 bg-white flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900" x-text="s.skill_name"></div>
                                        <div class="text-[10px] text-slate-400" x-text="s.category_name"></div>
                                    </div>
                                    <div class="text-right flex items-center gap-3">
                                        <div>
                                            <div class="font-bold text-slate-800" x-text="'Lv ' + s.current_level + ' / Target ' + s.required_level"></div>
                                            <div class="text-[10px]" :class="s.is_met ? 'text-emerald-600 font-bold' : 'text-amber-600 font-bold'"
                                                 x-text="s.is_met ? 'Standar Terpenuhi' : 'Kesenjangan: -' + s.gap + ' Level'"></div>
                                        </div>
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="s.is_met ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                              x-text="s.is_met ? '✓' : '!'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Recommended Trainings (Phase 4 Integration) -->
                    <template x-if="selectedAnalysis.recommended_trainings && selectedAnalysis.recommended_trainings.length > 0">
                        <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200">
                            <div class="flex items-center gap-2 text-xs font-bold text-blue-900 mb-2">
                                <i data-lucide="graduation-cap" class="w-4 h-4 text-blue-600"></i>
                                Rekomendasi Pelatihan Terkait (Learning Management System):
                            </div>
                            <div class="space-y-1.5">
                                <template x-for="t in selectedAnalysis.recommended_trainings" :key="t.id">
                                    <div class="p-2.5 rounded-lg bg-white border border-blue-100 flex items-center justify-between text-xs">
                                        <div>
                                            <div class="font-bold text-slate-800" x-text="t.title"></div>
                                            <div class="text-[10px] text-slate-500" x-text="'Trainer: ' + (t.trainer_name || 'Internal') + ' • ' + t.location"></div>
                                        </div>
                                        <a href="{{ route('company.training.index') }}" class="px-2.5 py-1 rounded bg-blue-600 text-white font-bold text-[10px] hover:bg-blue-700 transition">
                                            Daftarkan
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL 2: TAMBAH KATEGORI SKILL -->
    <div x-show="showCategoryModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCategoryModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-4">Tambah Kategori Kompetensi Baru</h3>
            <form method="POST" action="{{ route('company.skills.categories.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Technical Architecture, Soft Skill, Compliance"
                           class="w-full rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500 text-xs py-2 px-3">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Ringkas</label>
                    <textarea name="description" rows="2" placeholder="Penjelasan ruang lingkup kategori..."
                              class="w-full rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500 text-xs py-2 px-3"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCategoryModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] font-bold text-white hover:bg-red-700">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: TAMBAH SKILL KE KAMUS -->
    <div x-show="showSkillModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showSkillModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-4">Daftarkan Kompetensi Baru ke Kamus</h3>
            <form method="POST" action="{{ route('company.skills.skills.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori Kompetensi <span class="text-red-500">*</span></label>
                    <select name="skill_category_id" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Kompetensi / Keahlian <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Laravel Backend Framework"
                           class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kode Keahlian (Opsional)</label>
                    <input type="text" name="code" placeholder="Contoh: TECH-LARAVEL"
                           class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Kompetensi</label>
                    <textarea name="description" rows="2" placeholder="Uraian kompetensi dan standar penguasaan..."
                              class="w-full rounded-xl border-slate-200 text-xs py-2 px-3"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showSkillModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] font-bold text-white hover:bg-red-700">Simpan ke Kamus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: STANDAR KOMPETENSI JABATAN -->
    <div x-show="showRequirementModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showRequirementModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-4">Tetapkan Standar Kompetensi Jabatan</h3>
            <form method="POST" action="{{ route('company.skills.requirements.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jabatan Sasaran <span class="text-red-500">*</span></label>
                    <select name="position_id" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                        <option value="">Pilih Jabatan</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}">{{ $pos->title }} ({{ $pos->department?->name ?? 'Semua' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kompetensi Keahlian <span class="text-red-500">*</span></label>
                    <select name="skill_id" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                        <option value="">Pilih Kompetensi</option>
                        @foreach($skills as $sk)
                            <option value="{{ $sk->id }}">{{ $sk->name }} ({{ $sk->category?->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tingkat Kemahiran Wajib (Target Level) <span class="text-red-500">*</span></label>
                    <select name="required_level" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 font-semibold">
                        <option value="1">Level 1 - Dasar (Novice / Mengetahui Konsep)</option>
                        <option value="2">Level 2 - Menengah (Intermediate / Bekerja Terbimbing)</option>
                        <option value="3" selected>Level 3 - Mahir (Competent / Mandiri)</option>
                        <option value="4">Level 4 - Ahli (Advanced / Mampu Membimbing Orang Lain)</option>
                        <option value="5">Level 5 - Pakar (Master / Ahli Strategis Perusahaan)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_mandatory" value="1" id="is_mandatory" checked
                           class="rounded text-red-600 focus:ring-red-500">
                    <label for="is_mandatory" class="font-bold text-slate-700">Kompetensi Wajib (Mandatory)</label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showRequirementModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] font-bold text-white hover:bg-red-700">Simpan Standar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 5: NILAI / SERTIFIKASI KARYAWAN -->
    <div x-show="showAssessModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showAssessModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-4">Penilaian Kompetensi & Sertifikasi Karyawan</h3>
            <form method="POST" action="{{ route('company.skills.assess.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Karyawan <span class="text-red-500">*</span></label>
                        <select name="employee_id" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                            <option value="">Pilih Karyawan</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kompetensi Keahlian <span class="text-red-500">*</span></label>
                        <select name="skill_id" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3">
                            <option value="">Pilih Kompetensi</option>
                            @foreach($skills as $sk)
                                <option value="{{ $sk->id }}">{{ $sk->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tingkat Kemahiran Aktual (Level 1-5) <span class="text-red-500">*</span></label>
                    <select name="current_level" required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 font-semibold">
                        <option value="1">Level 1 - Dasar (Novice)</option>
                        <option value="2">Level 2 - Menengah (Intermediate)</option>
                        <option value="3" selected>Level 3 - Mahir (Competent)</option>
                        <option value="4">Level 4 - Ahli (Advanced)</option>
                        <option value="5">Level 5 - Pakar (Master)</option>
                    </select>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                    <div class="font-bold text-slate-800 text-[11px] uppercase tracking-wider">Informasi Sertifikasi Resmi (Opsional)</div>
                    <div>
                        <label class="block font-medium text-slate-600 mb-1">Nama Sertifikasi / Lembaga Penerbit</label>
                        <input type="text" name="certification_name" placeholder="Contoh: Certified Scrum Master (Scrum.org) / BNSP HR"
                               class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 bg-white">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-600 mb-1">Tanggal Terbit</label>
                            <input type="date" name="certification_date" class="w-full rounded-xl border-slate-200 text-xs py-1.5 px-3 bg-white">
                        </div>
                        <div>
                            <label class="block font-medium text-slate-600 mb-1">Masa Berlaku (Valid Until)</label>
                            <input type="date" name="valid_until" class="w-full rounded-xl border-slate-200 text-xs py-1.5 px-3 bg-white">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Evaluasi / Penguji</label>
                    <textarea name="assessment_notes" rows="2" placeholder="Catatan hasil verifikasi portofolio atau tes kemampuan..."
                              class="w-full rounded-xl border-slate-200 text-xs py-2 px-3"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showAssessModal = false" class="px-4 py-2 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] font-bold text-white hover:bg-red-700">Simpan Penilaian</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

