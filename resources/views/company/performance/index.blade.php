@extends('layouts.app')

@section('title', 'Manajemen Kinerja (KPI & OKR)')

@section('content')
<div class="space-y-6" x-data="{ openCycleModal: false, openGoalModal: false, openAppraisalModal: false, selectedEmpId: '', selectedEmpName: '' }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Kinerja & Penilaian (KPI / OKR)</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau ketercapaian target kuantitatif karyawan, evaluasi mandiri (self-assessment), dan ulasan atasan.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openCycleModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-sm">
                <i data-lucide="calendar" class="w-4 h-4 text-slate-500"></i>
                Buat Siklus Evaluasi
            </button>
            <button @click="openGoalModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Tetapkan Sasaran Kerja
            </button>
        </div>
    </div>

    <!-- Cycle Selector Banner -->
    @if($cycles->isNotEmpty())
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-[#DC2626] flex items-center justify-center font-bold">
                    <i data-lucide="target" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Periode Evaluasi Aktif</span>
                    <div class="text-base font-black text-slate-900">{{ $activeCycle?->name ?? 'Pilih Periode' }}</div>
                </div>
            </div>

            <form action="{{ route('company.performance.index') }}" method="GET" class="flex items-center gap-2">
                <select name="cycle_id" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    @foreach($cycles as $c)
                        <option value="{{ $c->id }}" {{ $activeCycle?->id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->start_date->format('d/m/Y') }} - {{ $c->end_date->format('d/m/Y') }})</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Sasaran Kerja</div>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total_goals']) }} Sasaran</div>
            <div class="text-[10px] text-slate-400 mt-0.5">KPI & OKR terdaftar</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tercapai (Achieved)</div>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ number_format($stats['achieved_goals']) }} Sasaran</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">&ge; 100% dari target</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Review Selesai</div>
            <div class="text-xl font-black text-blue-600 mt-1">{{ number_format($stats['finalized_reviews']) }} Karyawan</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Dinilai oleh atasan</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rata-Rata Skor Kinerja</div>
            <div class="text-xl font-black text-[#DC2626] mt-1">{{ $stats['average_score'] }}/100</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Indeks kinerja organisasi</div>
        </div>
    </div>

    <!-- Goals Tracking Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Pencapaian Sasaran Kerja Karyawan (KPI & OKR)</h2>
            <span class="text-xs text-slate-500">Pantau progres target per kuartal/semester</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Tipe & Sasaran Kerja</th>
                        <th class="px-5 py-3.5">Target</th>
                        <th class="px-5 py-3.5">Realisasi Saat Ini</th>
                        <th class="px-5 py-3.5">Progres & Bobot</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($goals as $g)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $g->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $g->employee->position?->title ?? '-' }} • {{ $g->employee->department?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase {{ $g->type === 'okr' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $g->type }}
                                    </span>
                                    <span class="font-bold text-slate-900">{{ $g->title }}</span>
                                </div>
                                @if($g->deadline)
                                    <div class="text-[10px] text-slate-400 mt-1">Batas Waktu: {{ $g->deadline->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-mono font-semibold text-slate-800">
                                {{ number_format($g->target_value, 0, ',', '.') }} {{ $g->unit }}
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                {{ number_format($g->current_value, 0, ',', '.') }} {{ $g->unit }}
                            </td>
                            <td class="px-5 py-4 w-48">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="font-bold text-slate-800">{{ $g->progress_percentage }}%</span>
                                    <span class="text-slate-400 text-[10px]">Bobot: {{ $g->weight }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-red-600 h-2 rounded-full transition-all" style="width: {{ $g->progress_percentage }}%"></div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($g->status === 'achieved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Tercapai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Berjalan
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('company.performance.goals.progress', $g->id) }}" method="POST" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="number" step="1" name="current_value" value="{{ $g->current_value }}" class="w-16 text-center text-xs rounded-lg border border-slate-200 py-1">
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-slate-900 text-white font-bold text-[10px] hover:bg-slate-800 transition">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="target" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Belum ada sasaran kerja (KPI / OKR) ditetapkan.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol <strong>"Tetapkan Sasaran Kerja"</strong> di atas untuk menambahkan target kinerja.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($goals->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $goals->links() }}
            </div>
        @endif
    </div>

    <!-- Manager Reviews Table -->
    @if($activeCycle)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Ulasan & Penilaian Kinerja Karyawan (Appraisal)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Penilaian mandiri dan evaluasi atasan untuk siklus: {{ $activeCycle->name }}</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($employees as $emp)
                    @php
                        $rev = $reviews->firstWhere('employee_id', $emp->id);
                    @endphp
                    <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ $emp->full_name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $emp->employee_number }} • {{ $emp->position?->title ?? '-' }} ({{ $emp->department?->name ?? '-' }})</div>

                            @if($rev && $rev->self_rating)
                                <div class="text-[11px] text-blue-700 mt-1">
                                    Self-Rating: <strong>{{ $rev->self_rating }}/100</strong> — "{{ $rev->self_feedback }}"
                                </div>
                            @endif

                            @if($rev && $rev->manager_feedback)
                                <div class="text-[11px] text-slate-600 mt-1 italic">
                                    Catatan Manajer: "{{ $rev->manager_feedback }}"
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-4">
                            @if($rev && $rev->final_score)
                                <div class="text-right">
                                    <div class="text-[10px] uppercase font-bold text-slate-400">Skor Akhir</div>
                                    <div class="text-xl font-black text-[#DC2626]">{{ $rev->final_score }}/100</div>
                                </div>
                            @endif

                            <button @click="selectedEmpId = '{{ $emp->id }}'; selectedEmpName = '{{ $emp->full_name }}'; openAppraisalModal = true"
                                    class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition shadow-sm">
                                {{ $rev && $rev->status === 'finalized' ? 'Edit Penilaian' : 'Beri Penilaian Atasan' }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">Belum ada karyawan terdaftar.</div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- Modal Buat Siklus Evaluasi -->
    <div x-show="openCycleModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openCycleModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-2">Buat Periode Evaluasi Kinerja Baru</h3>

            <form action="{{ route('company.performance.cycles.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Periode / Siklus <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Evaluasi Kinerja Semester 1 {{ date('Y') }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required value="{{ now()->startOfYear()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required value="{{ now()->endOfYear()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openCycleModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Periode
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tetapkan Sasaran Kerja (Goal) -->
    <div x-show="openGoalModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openGoalModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-2">Tetapkan Sasaran Kerja (KPI / OKR)</h3>

            <form action="{{ route('company.performance.goals.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                @if($activeCycle)
                    <input type="hidden" name="performance_cycle_id" value="{{ $activeCycle->id }}">
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }}) - {{ $emp->position?->title ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Metodologi Sasaran <span class="text-red-500">*</span></label>
                        <select name="type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="kpi">Key Performance Indicator (KPI)</option>
                            <option value="okr">Objective & Key Result (OKR)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bobot Penilaian (%)</label>
                        <input type="number" name="weight" value="25" min="1" max="100" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul / Sasaran Kinerja <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Mengurangi response time API menjadi <200ms"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Angka <span class="text-red-500">*</span></label>
                        <input type="number" step="0.1" name="target_value" required placeholder="100"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Capaian Saat Ini</label>
                        <input type="number" step="0.1" name="current_value" value="0"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Ukur <span class="text-red-500">*</span></label>
                        <input type="text" name="unit" required value="%" placeholder="%, ms, Tiket, Rp"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Target Tenggat Waktu (Deadline)</label>
                    <input type="date" name="deadline" value="{{ now()->addMonths(3)->toDateString() }}"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openGoalModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Tetapkan Sasaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Penilaian Atasan (Manager Appraisal) -->
    <div x-show="openAppraisalModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openAppraisalModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-1">Penilaian Kinerja oleh Atasan (Manager Review)</h3>
            <p class="text-xs text-slate-500 mb-4">Karyawan: <strong x-text="selectedEmpName"></strong></p>

            <form action="{{ route('company.performance.appraisal.submit') }}" method="POST" class="space-y-4">
                @csrf
                @if($activeCycle)
                    <input type="hidden" name="performance_cycle_id" value="{{ $activeCycle->id }}">
                @endif
                <input type="hidden" name="employee_id" :value="selectedEmpId">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Skor Penilaian Manajer (0 s.d. 100) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.5" min="0" max="100" name="manager_rating" required placeholder="Contoh: 88.5"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Evaluasi & Ulasan Kualitatif <span class="text-red-500">*</span></label>
                    <textarea name="manager_feedback" rows="4" required placeholder="Kelebihan kerja, pencapaian target, dan area peningkatan kompetensi..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openAppraisalModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm">
                        Finalisasi Penilaian
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

