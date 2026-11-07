@extends('layouts.app')

@section('title', 'Evaluasi Pelatihan Model Kirkpatrick (Level 1-4) & E-Sertifikat - ' . $company->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Evaluasi Pelatihan Kirkpatrick & E-Sertifikat</h1>
            <p class="text-sm text-slate-500 mt-1">Pengukuran efektivitas L&D 4-Level (Reaksi, Pembelajaran Pre/Post-Test, Perilaku Kerja, dan Dampak Bisnis) + Penerbitan Sertifikat Digital Terverifikasi.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('company.trainings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Manajemen Training
            </a>
        </div>
    </div>

    <!-- Kirkpatrick Explanation Banner -->
    <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-blue-50 border border-emerald-200 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm mb-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Kerangka Kerja Evaluasi Model Kirkpatrick (Level 1 s/d 4)
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs text-slate-600">
            <div class="bg-white/80 p-3 rounded-xl border border-emerald-100">
                <strong class="text-emerald-700 block mb-1">Level 1: Reaction</strong>
                Kepuasan dan keterlibatan peserta terhadap materi, instruktur, dan relevansi pelatihan (Skala 1.0 - 5.0).
            </div>
            <div class="bg-white/80 p-3 rounded-xl border border-emerald-100">
                <strong class="text-blue-700 block mb-1">Level 2: Learning</strong>
                Penguasaan materi & peningkatan kompetensi diukur via Pre-Test vs Post-Test Gain Formula.
            </div>
            <div class="bg-white/80 p-3 rounded-xl border border-emerald-100">
                <strong class="text-purple-700 block mb-1">Level 3: Behavior</strong>
                Penerapan keterampilan baru di lingkungan kerja nyata yang diobservasi oleh atasan langsung (Supervisor).
            </div>
            <div class="bg-white/80 p-3 rounded-xl border border-emerald-100">
                <strong class="text-amber-700 block mb-1">Level 4: Results</strong>
                Dampak nyata pelatihan terhadap produktivitas tim, penurunan error, dan ROI bisnis perusahaan.
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Peserta Terdaftar</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $summary['total_enrollments'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Karyawan terdaftar pelatihan</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Evaluasi Kirkpatrick Selesai</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $summary['evaluations_completed'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Memiliki rekam Level 1-3</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Knowledge Gain</p>
            <h3 class="text-2xl font-bold text-blue-600 mt-1">+{{ number_format($summary['avg_knowledge_gain'], 1) }}%</h3>
            <p class="text-xs text-slate-500 mt-2">Peningkatan Pre-Test ke Post-Test</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sertifikat Digital Diterbitkan</p>
            <h3 class="text-2xl font-bold text-purple-600 mt-1">{{ $summary['certificates_issued'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Disertai hash SHA256 & QR Verifikasi</p>
        </div>
    </div>

    <!-- Enrollments & Kirkpatrick Evaluation Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Daftar Evaluasi Karyawan & Pelatihan</h3>
            <span class="text-xs text-slate-500">{{ $enrollments->count() }} peserta</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Peserta & Program</th>
                        <th class="px-6 py-3">Level 1 (Reaction)</th>
                        <th class="px-6 py-3">Level 2 (Pre/Post Test Gain)</th>
                        <th class="px-6 py-3">Level 3 (Behavior)</th>
                        <th class="px-6 py-3">Sertifikat Digital</th>
                        <th class="px-6 py-3 text-right">Aksi Evaluasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($enrollments as $enrollment)
                    @php $eval = $enrollment->kirkpatrickEvaluation; @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900">{{ $enrollment->employee->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $enrollment->training->title ?? 'Pelatihan' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($eval && $eval->reaction_score)
                                <div class="flex items-center gap-1 font-bold text-emerald-700">
                                    ⭐ {{ number_format($eval->reaction_score, 2) }} <span class="text-xs text-slate-400 font-normal">/ 5.0</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum diisi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($eval && $eval->post_test_score !== null)
                                <div class="font-bold text-slate-900">
                                    {{ $eval->pre_test_score ?? 0 }} → {{ $eval->post_test_score }}
                                    <span class="text-xs text-blue-600 ml-1">(+{{ number_format($eval->learning_gain_percentage, 1) }}%)</span>
                                </div>
                                <div class="text-[11px] text-slate-400">Pre-Test vs Post-Test</div>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum ada skor</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($eval && $eval->behavior_score)
                                <div class="font-bold text-purple-700">
                                    {{ number_format($eval->behavior_score, 2) }} <span class="text-xs text-slate-400 font-normal">/ 5.0</span>
                                </div>
                                <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $eval->supervisor_observation ?? 'Observasi atasan' }}</div>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum dinilai atasan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($enrollment->digitalCertificate)
                                <a href="{{ route('company.kirkpatrick.certificate.print', $enrollment->digitalCertificate) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-mono font-semibold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-200 hover:bg-emerald-100 transition">
                                    📜 {{ $enrollment->digitalCertificate->certificate_number }}
                                </a>
                            @else
                                <form action="{{ route('company.kirkpatrick.certificate.issue', $enrollment) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 rounded text-xs font-medium transition">
                                        + Terbitkan E-Sertifikat
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-1">
                            <button onclick="openModalTest({{ $enrollment->id }}, '{{ $enrollment->employee->name ?? '' }}', {{ $eval->pre_test_score ?? 0 }}, {{ $eval->post_test_score ?? 0 }})" class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs font-medium text-slate-700 transition">
                                Skor Tes (L2)
                            </button>
                            <button onclick="openModalBehavior({{ $enrollment->id }}, '{{ $enrollment->employee->name ?? '' }}')" class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded text-xs font-medium text-slate-700 transition">
                                Perilaku (L3)
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            Belum ada data kepesertaan training. Daftarkan karyawan ke program pelatihan terlebih dahulu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Input Pre/Post Test (Level 2) -->
<div id="modal-test-scores" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-base text-slate-900">Input Skor Pre-Test & Post-Test</h3>
                <p id="test-modal-sub" class="text-xs text-slate-500"></p>
            </div>
            <button onclick="document.getElementById('modal-test-scores').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form id="form-test-scores" method="POST" class="mt-4 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Pre-Test (0-100) *</label>
                    <input type="number" id="input_pre_test" name="pre_test_score" min="0" max="100" required class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Post-Test (0-100) *</label>
                    <input type="number" id="input_post_test" name="post_test_score" min="0" max="100" required class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                </div>
            </div>
            <p class="text-xs text-slate-400">Gain persentase dihitung otomatis dengan formula: <code>(Post - Pre) / (100 - Pre) * 100</code>.</p>
            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-test-scores').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Simpan Skor</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Input Perilaku & Observasi Atasan (Level 3) -->
<div id="modal-behavior" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-base text-slate-900">Observasi Perilaku Kerja (Level 3)</h3>
                <p id="behavior-modal-sub" class="text-xs text-slate-500"></p>
            </div>
            <button onclick="document.getElementById('modal-behavior').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form id="form-behavior" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Penerapan Perilaku (1.0 - 5.0) *</label>
                <input type="number" name="behavior_score" min="1" max="5" step="0.1" value="4.5" required class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Observasi Supervisor *</label>
                <textarea name="supervisor_observation" rows="3" required placeholder="Jelaskan bagaimana karyawan mengaplikasikan keahlian baru di pekerjaannya sehari-hari..." class="w-full text-sm border-slate-300 rounded-lg p-2.5 border"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-behavior').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">Simpan Observasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModalTest(enrollmentId, empName, pre, post) {
        document.getElementById('test-modal-sub').innerText = 'Karyawan: ' + empName;
        document.getElementById('form-test-scores').action = '{{ url("kirkpatrick") }}/' + enrollmentId + '/test-scores';
        document.getElementById('input_pre_test').value = pre;
        document.getElementById('input_post_test').value = post;
        document.getElementById('modal-test-scores').classList.remove('hidden');
    }

    function openModalBehavior(enrollmentId, empName) {
        document.getElementById('behavior-modal-sub').innerText = 'Karyawan: ' + empName;
        document.getElementById('form-behavior').action = '{{ url("kirkpatrick") }}/' + enrollmentId + '/behavior';
        document.getElementById('modal-behavior').classList.remove('hidden');
    }
</script>
@endsection
