@extends('layouts.app')

@section('title', 'Onboarding Buddy & Program Mentorship')

@section('content')
<div class="space-y-6" x-data="{ createPairModal: false, checkinModal: false, activeCheckin: null }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Employee Retention & Onboarding
                </span>
                <span class="text-xs text-slate-400">• Masa Probation 90 Hari</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Onboarding Buddy & Mentorship Program</h1>
            <p class="text-xs text-slate-500 mt-1">Pendampingan karyawan baru oleh Senior Buddy, evaluasi milestone 30-60-90 hari, dan pemantauan adaptasi budaya kerja.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createPairModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Pasangkan Buddy Baru
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Pasangan Buddy</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $summary['total_pairs'] }}</div>
            <span class="text-[11px] text-slate-500">{{ $summary['active_pairs'] }} Aktif Berjalan</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Kepuasan Mentee</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">★ {{ number_format($summary['avg_mentee_satisfaction'], 1) }} <span class="text-xs text-slate-400 font-normal">/ 5.0</span></div>
            <span class="text-[11px] text-slate-500">Umpan Balik Karyawan Baru</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Adaptasi Mentor</span>
            <div class="text-2xl font-black text-blue-600 mt-1">★ {{ number_format($summary['avg_mentor_adaptation'], 1) }} <span class="text-xs text-slate-400 font-normal">/ 5.0</span></div>
            <span class="text-[11px] text-slate-500">Skor Cultural Fit Senior</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Kelulusan 90 Hari</span>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $summary['completion_rate'] }}%</div>
            <span class="text-[11px] text-slate-500">{{ $summary['completed_pairs'] }} Pasangan Selesai</span>
        </div>
    </div>

    <!-- Mentorship Pairs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Penugasan Buddy & Progres Milestone</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Karyawan Baru (Mentee)</th>
                        <th class="px-4 py-3 font-bold">Senior Buddy (Mentor)</th>
                        <th class="px-4 py-3 font-bold">Periode 90 Hari</th>
                        <th class="px-4 py-3 font-bold">Milestone Check-in</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pairs as $p)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $p->mentee->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $p->mentee->department->name ?? 'Dept' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-blue-800">{{ $p->mentor->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $p->mentor->department->name ?? 'Dept' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>{{ $p->start_date->format('d M') }} - {{ $p->end_date->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">Durasi: 90 Hari</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @foreach($p->checkins as $chk)
                                        <div class="flex flex-col items-center">
                                            @if($chk->status === 'completed')
                                                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px]" title="{{ strtoupper($chk->milestone) }}: Selesai (Skor Mentee: {{ $chk->mentee_satisfaction_score }}/5)">
                                                    ✓
                                                </span>
                                            @else
                                                <button @click="activeCheckin = {{ $chk->toJson() }}; checkinModal = true" class="w-6 h-6 rounded-full bg-amber-100 text-amber-800 hover:bg-amber-200 flex items-center justify-center font-bold text-[10px]" title="Klik untuk submit evaluasi {{ strtoupper($chk->milestone) }}">
                                                    !
                                                </button>
                                            @endif
                                            <span class="text-[9px] text-slate-400 mt-0.5">{{ str_replace('_', ' ', $chk->milestone) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $p->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Belum ada program mentoring yang aktif. Klik tombol di atas untuk memasangkan karyawan baru dengan senior buddy.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Pasangkan Buddy Baru -->
    <div x-show="createPairModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="createPairModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Inisiasi Program Onboarding Buddy</h3>
                <button @click="createPairModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.mentorship.pairs.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Karyawan Baru (Mentee)</label>
                    <select name="mentee_employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }}) - Join: {{ $e->join_date ? $e->join_date->format('d/m/Y') : '-' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Senior Buddy / Mentor</label>
                    <select name="mentor_employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }} - {{ $e->position->title ?? 'Staff' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Mentoring</label>
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sasaran & Ekspektasi Bimbingan</label>
                    <textarea name="program_objectives" rows="2" placeholder="Fokus adaptasi: pengenalan stack aplikasi, budaya kerja tim, dan kelulusan probation." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createPairModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Mulai Program Buddy</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form Submit Check-in Milestone -->
    <div x-show="checkinModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="checkinModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Evaluasi Check-in Milestone</h3>
                <button @click="checkinModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('/company/mentorship/checkins') }}/' + (activeCheckin ? activeCheckin.id : '') + '/complete'" method="POST" class="space-y-3">
                @csrf
                <div class="bg-slate-50 p-3 rounded-xl text-xs text-slate-600">
                    <strong>Agenda Pembahasan:</strong>
                    <p x-text="activeCheckin ? activeCheckin.agenda_topics : ''" class="mt-0.5 text-slate-500"></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Kepuasan Mentee (1-5)</label>
                        <select name="mentee_satisfaction_score" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                            <option value="5">5 - Sangat Terbantu & Nyaman</option>
                            <option value="4" selected>4 - Terbantu Baik</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Kurang Interaksi</option>
                            <option value="1">1 - Tidak Efektif</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Adaptasi dari Mentor (1-5)</label>
                        <select name="mentor_adaptation_score" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                            <option value="5">5 - Sangat Cepat Beradaptasi</option>
                            <option value="4" selected>4 - Adaptasi Sesuai Ekspektasi</option>
                            <option value="3">3 - Membutuhkan Waktu Tambahan</option>
                            <option value="2">2 - Mengalami Kendala Komunikasi</option>
                            <option value="1">1 - Sulit Beradaptasi</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Umpan Balik Karyawan Baru (Mentee)</label>
                    <textarea name="mentee_feedback" rows="2" placeholder="Tanggapan mentee terhadap ketersediaan waktu dan bimbingan mentor..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Evaluasi Senior Buddy (Mentor)</label>
                    <textarea name="mentor_notes" rows="2" placeholder="Observasi mentor terkait etos kerja, penguasaan materi, dan kemandirian..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Aksi Lanjutan</label>
                    <input type="text" name="action_plan" placeholder="Contoh: Fokus pendalaman arsitektur database pada 30 hari ke depan" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="checkinModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Selesaikan Check-in</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
