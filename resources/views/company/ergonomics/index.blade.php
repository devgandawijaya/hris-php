@extends('layouts.app')

@section('title', 'Ergonomi Tempat Kerja & Beban Kerja Digital')

@section('content')
<div class="space-y-6" x-data="{ ergoModal: false, surveyModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-sky-100 text-sky-800 border border-sky-200">
                    Kepatuhan Permenaker No. 5/2018 & K3 Ergonomi Digital
                </span>
                <span class="text-xs text-slate-400">• Standar Lingkungan Kerja & Pencegahan Burnout</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Audit Ergonomi & Beban Kerja Mental Digital</h1>
            <p class="text-xs text-slate-500 mt-1">Audit pencahayaan minimum 300 Lux, postur stasiun kerja, dan pencegahan computer vision syndrome serta kelelahan kognitif rapat virtual.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="ergoModal = true" class="px-3 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="monitor" class="w-4 h-4"></i>
                Audit Ergonomi Stasiun Kerja
            </button>
            <button @click="surveyModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                Survei Beban Digital Karyawan
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 text-xs text-sky-900 flex items-start gap-3">
        <i data-lucide="activity" class="w-5 h-5 text-sky-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Standar Faktor Fisika & Ergonomi (Permenaker No. 5 Tahun 2018):</span>
            <ul class="list-disc list-inside space-y-0.5 text-sky-800">
                <li><strong>Standar Intensitas Pencahayaan (Tabel Lampiran):</strong> Pekerjaan kantor dan komputer membutuhkan penerangan minimal <strong>300 Lux</strong> tanpa silau (*glare*).</li>
                <li><strong>Postur Kerja Statis:</strong> Kursi kerja wajib dilengkapi penopang tulang belakang (*lumbar support*), jarak monitor sejajar pandangan mata, dan sandaran pergelangan tangan (*wrist rest*).</li>
                <li><strong>Istirahat Kognitif:</strong> Mengadopsi prinsip 20-20-20 serta pembatasan durasi rapat daring beruntun guna mencegah kelelahan mental dan *digital eye strain*.</li>
            </ul>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Stasiun Kerja Diaudit</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['total_assessments'] }} Area</div>
            <span class="text-[11px] text-slate-500 font-medium">Audit fisik lingkungan kerja</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Rata-rata Skor Ergonomi</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ $stats['avg_compliance_score'] }} / 100</div>
            <span class="text-[11px] text-emerald-600 font-medium">Skor kepatuhan stasiun kerja</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Temuan Risiko Tinggi</span>
            <div class="text-xl font-black {{ $stats['high_posture_risks'] > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">
                {{ $stats['high_posture_risks'] }} Stasiun
            </div>
            <span class="text-[11px] {{ $stats['high_posture_risks'] > 0 ? 'text-rose-500 font-bold' : 'text-slate-400' }}">
                {{ $stats['high_posture_risks'] > 0 ? 'Membutuhkan tindakan korektif' : 'Nihil risiko bahaya berat' }}
            </span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Rata-rata Indeks Burnout</span>
            <div class="text-xl font-black text-indigo-600 mt-1">{{ $stats['avg_burnout_index'] }} / 100</div>
            <span class="text-[11px] text-slate-500 font-medium">Survei beban kerja mental</span>
        </div>
    </div>

    <!-- Section 1: Ergonomic Assessments Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-sky-600"></i>
            Audit Ergonomi Stasiun Kerja (Permenaker 5/2018)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Tgl Audit</th>
                        <th class="py-3 px-3">Departemen / Karyawan</th>
                        <th class="py-3 px-3">Tipe Stasiun</th>
                        <th class="py-3 px-3">Pencahayaan (Lux)</th>
                        <th class="py-3 px-3">Kelengkapan Ergonomis</th>
                        <th class="py-3 px-3">Skor & Risiko</th>
                        <th class="py-3 px-3">Tindakan Korektif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assessments as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3 text-slate-500">{{ \Carbon\Carbon::parse($item->assessment_date)->format('d M Y') }}</td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $item->department->name ?? 'Seluruh Divisi' }}</span>
                                @if($item->employee)
                                    <span class="text-[10px] text-slate-400">{{ $item->employee->full_name }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">{{ $item->workstation_type }}</td>
                            <td class="py-3 px-3">
                                <span class="font-mono font-bold {{ $item->lighting_lux_level >= 300 ? 'text-emerald-700' : 'text-rose-600' }}">
                                    {{ $item->lighting_lux_level }} Lux
                                </span>
                                <span class="text-[10px] text-slate-400 block">{{ $item->lighting_lux_level >= 300 ? 'Memenuhi Min 300' : 'Di Bawah Standar' }}</span>
                            </td>
                            <td class="py-3 px-3 text-[11px] space-y-0.5">
                                <div class="flex items-center gap-1 {{ $item->chair_lumbar_support_compliant ? 'text-emerald-700' : 'text-slate-400' }}">
                                    <i data-lucide="{{ $item->chair_lumbar_support_compliant ? 'check' : 'x' }}" class="w-3.5 h-3.5"></i> Kursi Lumbar
                                </div>
                                <div class="flex items-center gap-1 {{ $item->monitor_eye_level_compliant ? 'text-emerald-700' : 'text-slate-400' }}">
                                    <i data-lucide="{{ $item->monitor_eye_level_compliant ? 'check' : 'x' }}" class="w-3.5 h-3.5"></i> Monitor Sejajar Mata
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">{{ number_format($item->ergonomic_compliance_score, 0) }} / 100</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $item->posture_risk_level === 'low' ? 'bg-emerald-100 text-emerald-800' : ($item->posture_risk_level === 'high' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    Risiko: {{ $item->posture_risk_level }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-600 max-w-xs">{{ $item->corrective_actions ?? 'Kondisi ergonomi optimal.' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">Belum ada catatan audit ergonomi stasiun kerja.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $assessments->links() }}
        </div>
    </div>

    <!-- Section 2: Digital Stress Surveys Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="brain" class="w-4 h-4 text-indigo-600"></i>
            Survei Beban Kerja Digital & Stres Kognitif (Screen Fatigue)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Karyawan</th>
                        <th class="py-3 px-3">Jam Layar/Hari</th>
                        <th class="py-3 px-3">Meeting Fatigue (1-5)</th>
                        <th class="py-3 px-3">Chat Luar Jam (1-5)</th>
                        <th class="py-3 px-3">Kelelahan Mata (1-5)</th>
                        <th class="py-3 px-3">Indeks Burnout</th>
                        <th class="py-3 px-3">Rekomendasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stressSurveys as $survey)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $survey->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $survey->employee->nik }}</span>
                            </td>
                            <td class="py-3 px-3 font-bold text-slate-800">{{ $survey->avg_daily_screen_hours }} Jam</td>
                            <td class="py-3 px-3 font-mono font-bold">{{ $survey->meeting_fatigue_score }} / 5</td>
                            <td class="py-3 px-3 font-mono font-bold">{{ $survey->after_hours_communication_score }} / 5</td>
                            <td class="py-3 px-3 font-mono font-bold">{{ $survey->eyestrain_headache_frequency }} / 5</td>
                            <td class="py-3 px-3">
                                <span class="font-black text-slate-900 block text-sm">{{ number_format($survey->digital_burnout_risk_index, 1) }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $survey->risk_classification === 'low' ? 'bg-emerald-100 text-emerald-800' : ($survey->risk_classification === 'severe' || $survey->risk_classification === 'high' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $survey->risk_classification }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-600 max-w-xs">{{ $survey->coping_recommendation }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">Belum ada survei beban digital yang diinputkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $stressSurveys->links() }}
        </div>
    </div>

    <!-- Modal Audit Ergonomi -->
    <div x-show="ergoModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Audit Ergonomi Stasiun Kerja (Permenaker 5/2018)</h3>
                <button @click="ergoModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('ergonomics.assessments.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Departemen</label>
                    <select name="department_id" class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Karyawan (Opsional)</label>
                    <select name="employee_id" class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Spesifik Karyawan (Opsional) --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tipe Stasiun Kerja</label>
                    <input type="text" name="workstation_type" value="Office Desk & Monitor Workstation" required class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Intensitas Pencahayaan (Lux Meter)</label>
                    <input type="number" name="lighting_lux_level" value="350" min="50" max="2000" required class="w-full rounded-xl border-slate-200 text-xs">
                    <span class="text-[10px] text-slate-400">Standar Permenaker 5/2018: Minimal 300 Lux.</span>
                </div>
                <div class="space-y-2 pt-1">
                    <label class="block font-semibold text-slate-700">Verifikasi Perlengkapan Fisik:</label>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="chair_lumbar_support_compliant" value="1" checked id="lumbar" class="rounded text-[#DC2626]">
                        <label for="lumbar" class="text-slate-700">Kursi dilengkapi penopang pinggang (lumbar support)</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="monitor_eye_level_compliant" value="1" checked id="eye" class="rounded text-[#DC2626]">
                        <label for="eye" class="text-slate-700">Ketinggian monitor sejajar dengan pandangan mata</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="keyboard_wrist_rest_compliant" value="1" checked id="wrist" class="rounded text-[#DC2626]">
                        <label for="wrist" class="text-slate-700">Tersedia bantalan pergelangan tangan (wrist rest)</label>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Rekomendasi Tindakan Korektif</label>
                    <textarea name="corrective_actions" rows="2" class="w-full rounded-xl border-slate-200 text-xs" placeholder="Misal: Penyesuaian ketinggian meja atau penggantian lampu LED 15W..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="ergoModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Simpan Audit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Survei Beban Digital -->
    <div x-show="surveyModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Input Survei Beban Kerja Digital Karyawan</h3>
                <button @click="surveyModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('ergonomics.surveys.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Karyawan</label>
                    <select name="employee_id" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Rata-rata Jam Paparan Layar Komputer / Hari</label>
                    <input type="number" step="0.5" name="avg_daily_screen_hours" value="8.0" min="1" max="20" required class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Meeting Fatigue</label>
                        <select name="meeting_fatigue_score" required class="w-full rounded-xl border-slate-200 text-xs">
                            <option value="1">1 - Sangat Rendah</option>
                            <option value="2">2 - Rendah</option>
                            <option value="3" selected>3 - Sedang</option>
                            <option value="4">4 - Tinggi</option>
                            <option value="5">5 - Berat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pesan Luar Jam</label>
                        <select name="after_hours_communication_score" required class="w-full rounded-xl border-slate-200 text-xs">
                            <option value="1">1 - Tidak Ada</option>
                            <option value="2" selected>2 - Jarang</option>
                            <option value="3">3 - Kadang</option>
                            <option value="4">4 - Sering</option>
                            <option value="5">5 - Sangat Sering</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kelelahan Mata</label>
                        <select name="eyestrain_headache_frequency" required class="w-full rounded-xl border-slate-200 text-xs">
                            <option value="1">1 - Tidak Pernah</option>
                            <option value="2" selected>2 - Ringan</option>
                            <option value="3">3 - Sedang</option>
                            <option value="4">4 - Sering</option>
                            <option value="5">5 - Konstan</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="surveyModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">Hitung Indeks Burnout</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
