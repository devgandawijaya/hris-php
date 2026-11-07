@extends('layouts.app')

@section('title', 'Ringkasan Evaluasi 360° — ' . $employee->full_name)

@section('content')
<div class="space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <a href="{{ route('company.appraisals360.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Laporan Umpan Balik 360° Multi-Penilai</h1>
                <p class="text-xs text-slate-500 mt-0.5">Karyawan: <strong>{{ $employee->full_name }}</strong> ({{ $employee->employee_number }}) • {{ $employee->position?->title ?? '-' }}</p>
            </div>
        </div>

        <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
            {{ $summary['total_responses'] }} Responden Mengisi
        </span>
    </div>

    @if($summary['total_responses'] === 0)
        <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 shadow-sm">
            <i data-lucide="file-question" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
            <h3 class="text-sm font-bold text-slate-800">Belum Ada Hasil Evaluasi 360°</h3>
            <p class="text-xs text-slate-500 mt-1">Belum ada penilai yang menyelesaikan kuesioner evaluasi untuk karyawan ini.</p>
        </div>
    @else
        <!-- Scores Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Overall Score Card -->
            <div class="lg:col-span-4 bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-md flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-red-400">Indeks Skor Gabungan</span>
                    <div class="text-4xl font-black mt-2 font-mono">{{ $summary['overall_average'] }} <span class="text-base text-slate-400 font-normal">/ 5.0</span></div>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Rata-rata kumulatif dari seluruh sudut pandang (atasan, rekan kerja sejajar, bawahan langsung, dan swa-penilaian).
                    </p>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-700/60 space-y-2">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tingkat Penilaian Berdasarkan Relasi:</div>
                    @foreach($summary['by_relationship'] as $rel => $data)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-300 capitalize">{{ $rel }} ({{ $data['count'] }} org)</span>
                            <span class="font-mono font-bold text-red-400">{{ $data['average'] }} / 5.0</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 4 Competency Pillars -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-red-600"></i>
                    Pilar Kompetensi Inti
                </h3>

                <div class="space-y-4">
                    <!-- Core Values -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-800">1. Nilai Inti Perusahaan (Core Values & Integritas)</span>
                            <span class="font-mono font-bold text-slate-900">{{ $summary['core_values_avg'] }} / 5.0</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full" style="width: {{ ($summary['core_values_avg'] / 5.0) * 100 }}%"></div>
                        </div>
                    </div>

                    <!-- Leadership -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-800">2. Kepemimpinan & Inisiatif (Leadership)</span>
                            <span class="font-mono font-bold text-slate-900">{{ $summary['leadership_avg'] }} / 5.0</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-purple-600 rounded-full" style="width: {{ ($summary['leadership_avg'] / 5.0) * 100 }}%"></div>
                        </div>
                    </div>

                    <!-- Collaboration -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-800">3. Kerjasama & Kolaborasi Tim (Collaboration)</span>
                            <span class="font-mono font-bold text-slate-900">{{ $summary['collaboration_avg'] }} / 5.0</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-emerald-600 rounded-full" style="width: {{ ($summary['collaboration_avg'] / 5.0) * 100 }}%"></div>
                        </div>
                    </div>

                    <!-- Execution -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-800">4. Kualitas & Ketepatan Eksekusi (Execution & Delivery)</span>
                            <span class="font-mono font-bold text-slate-900">{{ $summary['execution_avg'] }} / 5.0</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-amber-500 rounded-full" style="width: {{ ($summary['execution_avg'] / 5.0) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Qualitative Feedback List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Strengths -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800 flex items-center gap-2">
                    <i data-lucide="thumbs-up" class="w-4 h-4 text-emerald-600"></i>
                    Kekuatan & Nilai Unggul (Strengths)
                </h3>
                <div class="space-y-3">
                    @forelse($summary['feedbacks'] as $fb)
                        @if($fb->strengths)
                            <div class="p-3.5 rounded-xl bg-emerald-50/50 border border-emerald-100 text-xs text-slate-700 leading-relaxed">
                                "{{ $fb->strengths }}"
                                <div class="text-[10px] text-emerald-700 font-semibold mt-1">
                                    — {{ $fb->is_anonymous ? 'Penilai Anonim (' . ucfirst($fb->rater_relationship) . ')' : $fb->reviewerEmployee?->full_name }}
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada catatan kekuatan.</p>
                    @endforelse
                </div>
            </div>

            <!-- Areas for Improvement -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-amber-800 flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-4 h-4 text-amber-600"></i>
                    Area Pengembangan (Areas for Growth)
                </h3>
                <div class="space-y-3">
                    @forelse($summary['feedbacks'] as $fb)
                        @if($fb->areas_for_improvement)
                            <div class="p-3.5 rounded-xl bg-amber-50/50 border border-amber-100 text-xs text-slate-700 leading-relaxed">
                                "{{ $fb->areas_for_improvement }}"
                                <div class="text-[10px] text-amber-700 font-semibold mt-1">
                                    — {{ $fb->is_anonymous ? 'Penilai Anonim (' . ucfirst($fb->rater_relationship) . ')' : $fb->reviewerEmployee?->full_name }}
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-xs text-slate-400 italic">Belum ada catatan pengembangan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
