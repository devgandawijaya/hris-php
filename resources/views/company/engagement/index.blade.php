@extends('layouts.app')

@section('title', 'Pelibatan Karyawan (Survei eNPS, Apresiasi & Agenda)')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'surveys',
    openSurveyModal: false,
    openRecognitionModal: false,
    openEventModal: false
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pelibatan Karyawan (Employee Engagement)</h1>
            <p class="text-sm text-slate-500 mt-1">Survei kepuasan kerja & eNPS, papan apresiasi karyawan teladan (Wall of Fame), serta kalender agenda perusahaan.</p>
        </div>
        <div class="flex items-center gap-2">
            <template x-if="activeTab === 'surveys'">
                <button @click="openSurveyModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    Buat Survei Baru
                </button>
            </template>
            <template x-if="activeTab === 'recognitions'">
                <button @click="openRecognitionModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                    <i data-lucide="award" class="w-4 h-4"></i>
                    Beri Apresiasi (Kudos)
                </button>
            </template>
            <template x-if="activeTab === 'events'">
                <button @click="openEventModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    Tambah Agenda
                </button>
            </template>
        </div>
    </div>

    <!-- Tab Buttons Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200">
        <button type="button" @click="activeTab = 'surveys'"
                :class="activeTab === 'surveys' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
            Survei Kepuasan & eNPS
        </button>

        <button type="button" @click="activeTab = 'recognitions'"
                :class="activeTab === 'recognitions' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="award" class="w-4 h-4"></i>
            Wall of Recognition (Kudos)
        </button>

        <button type="button" @click="activeTab = 'events'"
                :class="activeTab === 'events' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            Kalender Agenda & Acara
        </button>
    </div>

    <!-- TAB 1: SURVEYS & eNPS -->
    <div x-show="activeTab === 'surveys'" class="space-y-6">
        <!-- eNPS Dashboard Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Skor eNPS Perusahaan</span>
                    <div class="text-3xl font-black mt-1 {{ $aggregateEnps['enps_score'] >= 30 ? 'text-emerald-600' : ($aggregateEnps['enps_score'] >= 0 ? 'text-blue-600' : 'text-red-600') }}">
                        {{ $aggregateEnps['enps_score'] > 0 ? '+' : '' }}{{ $aggregateEnps['enps_score'] }}
                    </div>
                </div>
                <div class="text-[10px] text-slate-400 mt-2">Rentang: -100 s.d. +100</div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Promoters (Skor 9-10)</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($aggregateEnps['promoters'] ?? 0) }} Responden</div>
                <div class="text-[10px] text-emerald-700 font-semibold mt-1">Karyawan sangat loyal & antusias</div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Passives (Skor 7-8)</span>
                <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($aggregateEnps['passives'] ?? 0) }} Responden</div>
                <div class="text-[10px] text-slate-400 mt-1">Karyawan puas netral</div>
            </div>

            <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Detractors (Skor 0-6)</span>
                <div class="text-2xl font-black text-red-600 mt-1">{{ number_format($aggregateEnps['detractors'] ?? 0) }} Responden</div>
                <div class="text-[10px] text-red-600 font-semibold mt-1">Memerlukan perhatian HR</div>
            </div>
        </div>

        <!-- Surveys Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">Kampanye Survei Kepuasan Karyawan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar survei aktif dan tingkat partisipasi karyawan.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Judul Kampanye & Tipe</th>
                            <th class="py-3 px-4">Periode</th>
                            <th class="py-3 px-4">Privasi</th>
                            <th class="py-3 px-4">Jumlah Responden</th>
                            <th class="py-3 px-4">Skor eNPS</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($surveys as $s)
                            @php $enps = $s->calculateEnps(); @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $s->title }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase font-bold">{{ $s->survey_type }}</div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div>{{ $s->start_date->format('d/m/Y') }} - {{ $s->end_date->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($s->is_anonymous)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-slate-100">
                                            <i data-lucide="eye-off" class="w-3 h-3 text-amber-400"></i> Anonim Penuh
                                        </span>
                                    @else
                                        <span class="text-slate-500 text-[11px]">Terbuka</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <strong class="text-slate-900">{{ $enps['total_responses'] }}</strong> partisipan
                                </td>
                                <td class="py-3 px-4 font-black">
                                    <span class="{{ $enps['enps_score'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $enps['enps_score'] > 0 ? '+' : '' }}{{ $enps['enps_score'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    <i data-lucide="smile" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                    Belum ada survei kepuasan kerja yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: WALL OF RECOGNITION (KUDOS) -->
    <div x-show="activeTab === 'recognitions'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($recognitions as $rec)
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <!-- Badge Top -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                {{ $rec->badge_type === 'star_performer' ? 'bg-amber-100 text-amber-800' : ($rec->badge_type === 'innovation' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800') }}">
                                <i data-lucide="award" class="w-3 h-3"></i>
                                {{ str_replace('_', ' ', $rec->badge_type) }}
                            </span>

                            @if($rec->reward_amount > 0)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    +Rp {{ number_format($rec->reward_amount, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>

                        <!-- Employee Recipient -->
                        <h4 class="font-bold text-base text-slate-900 leading-snug">
                            {{ $rec->employee?->full_name }}
                        </h4>
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            {{ $rec->employee?->department?->name ?? 'Dept' }}
                        </div>

                        <div class="mt-3 text-xs font-bold text-slate-800">{{ $rec->title }}</div>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed italic bg-slate-50 p-3 rounded-xl border border-slate-100">
                            "{{ $rec->message }}"
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <div>Dari: <strong class="text-slate-700">{{ $rec->sender?->name ?? 'HR Team' }}</strong></div>
                        <div>{{ $rec->created_at->format('d M Y') }}</div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200">
                    <i data-lucide="trophy" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                    <h4 class="text-sm font-bold text-slate-700">Belum Ada Apresiasi</h4>
                    <p class="text-xs text-slate-400 mt-1">Berikan apresiasi pertama kepada rekan kerja atau tim teladan Anda.</p>
                </div>
            @endforelse
        </div>

        @if($recognitions->hasPages())
            <div class="p-4 bg-white rounded-2xl border border-slate-200">
                {{ $recognitions->links() }}
            </div>
        @endif
    </div>

    <!-- TAB 3: COMPANY EVENTS & CALENDAR -->
    <div x-show="activeTab === 'events'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($events as $event)
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                {{ $event->event_type === 'townhall' ? 'bg-red-100 text-red-800' : ($event->event_type === 'gathering' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                {{ $event->event_type }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">
                                {{ $event->targetDepartment?->name ?? 'Seluruh Karyawan' }}
                            </span>
                        </div>

                        <h4 class="font-bold text-base text-slate-900 leading-snug">
                            {{ $event->title }}
                        </h4>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            {{ $event->description ?? '-' }}
                        </p>

                        <div class="mt-4 space-y-1.5 text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <div class="flex items-center gap-2">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span>{{ $event->start_time->format('d M Y, H:i') }} - {{ $event->end_time->format('H:i') }} WIB</span>
                            </div>
                            @if($event->location)
                                <div class="flex items-center gap-2">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>{{ $event->location }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 text-[10px] text-slate-400">
                        Penyelenggara: <strong class="text-slate-700">{{ $event->createdBy?->name ?? 'HR Committee' }}</strong>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200">
                    <i data-lucide="calendar-x" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                    <h4 class="text-sm font-bold text-slate-700">Belum Ada Agenda Terjadwal</h4>
                    <p class="text-xs text-slate-400 mt-1">Tambahkan kegiatan seperti Town Hall atau Outing kantor.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal: Create Survey -->
    <div x-show="openSurveyModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openSurveyModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="clipboard-list" class="w-5 h-5 text-red-600"></i>
                    Buat Survei Kepuasan Kerja Baru
                </div>
                <button @click="openSurveyModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.engagement.surveys.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul Kampanye Survei <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Survei Budaya Kerja & Fasilitas Kantor Kuartal 1" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipe Metrik <span class="text-red-500">*</span></label>
                        <select name="survey_type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="enps">eNPS (Net Promoter Score 0-10)</option>
                            <option value="satisfaction">Kepuasan Kerja (Likert 1-5)</option>
                            <option value="culture">Budaya & Kepemimpinan</option>
                            <option value="pulse">Pulse Survey Singkat</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Privasi Jawaban</label>
                        <select name="is_anonymous" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="1">100% Anonim (Direkomendasikan)</option>
                            <option value="0">Terbuka (Identitas Terlihat)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Berakhir <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Instruksi & Pengantar Survei</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan tujuan survei dan jaminan kerahasiaan jawaban..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openSurveyModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#DC2626] hover:bg-[#991B1B] rounded-xl shadow-sm transition">
                        Publikasikan Survei
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Grant Recognition -->
    <div x-show="openRecognitionModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openRecognitionModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="award" class="w-5 h-5 text-amber-500"></i>
                    Beri Apresiasi & Penghargaan (Kudos)
                </div>
                <button @click="openRecognitionModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.engagement.recognitions.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Karyawan Penerima <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->department?->name ?? 'Dept' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori Lencana <span class="text-red-500">*</span></label>
                        <select name="badge_type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="star_performer">Star Performer of the Month</option>
                            <option value="team_player">Team Player & Kolaborasi</option>
                            <option value="innovation">Inovasi & Kreativitas</option>
                            <option value="customer_hero">Customer Service Champion</option>
                            <option value="core_values">Penerapan Nilai Budaya Perusahaan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Reward Finansial (Opsional)</label>
                        <input type="number" step="50000" name="reward_amount" placeholder="0" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul Apresiasi <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Dedikasi Luar Biasa Meluncurkan Fitur Tepat Waktu" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pesan Apresiasi <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="3" required placeholder="Tuliskan ucapan terima kasih dan alasan spesifik mengapa karyawan layak diapresiasi..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openRecognitionModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition">
                        Kirim Apresiasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Create Company Event -->
    <div x-show="openEventModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openEventModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="calendar-plus" class="w-5 h-5 text-blue-600"></i>
                    Tambah Agenda / Kegiatan Perusahaan
                </div>
                <button @click="openEventModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.engagement.events.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Acara / Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: All-Hands Townhall Meeting Q1 2026" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Kegiatan <span class="text-red-500">*</span></label>
                        <select name="event_type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="townhall">Town Hall / All-Hands</option>
                            <option value="gathering">Outing / Family Gathering</option>
                            <option value="celebration">Perayaan Ulang Tahun / Pencapaian</option>
                            <option value="meeting">Rapat Koordinasi Strategis</option>
                            <option value="training">Workshop & Training</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Peserta</label>
                        <select name="target_department_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Seluruh Karyawan (Semua Divisi)</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Waktu Mulai <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="start_time" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Waktu Selesai <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="end_time" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Lokasi / Tautan Google Meet / Zoom</label>
                    <input type="text" name="location" placeholder="Contoh: Auditorium Lantai 18 / https://meet.google.com/xyz" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openEventModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition">
                        Jadwalkan Acara
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
