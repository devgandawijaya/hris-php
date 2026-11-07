@extends('layouts.app')

@section('title', 'Evaluasi Masa Percobaan (Pasal 60 UU Ketenagakerjaan) - ' . $company->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Evaluasi Masa Percobaan (Probation)</h1>
            <p class="text-sm text-slate-500 mt-1">Penilaian komprehensif 3 bulan karyawan baru, konversi otomatis ke PKWTT & penerbitan SK Pengangkatan (Pasal 60 UU No. 13/2003).</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('modal-new-evaluation').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Lakukan Penilaian Probation
            </button>
        </div>
    </div>

    <!-- Legal Compliance Alert -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-amber-900 text-sm flex items-start gap-3 shadow-sm">
        <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <span class="font-bold">Ketentuan Pasal 60 UU No. 13/2003 (Masa Percobaan Kerja):</span>
            Masa percobaan kerja hanya dapat dipersyaratkan untuk <strong>Perjanjian Kerja Waktu Tidak Tertentu (PKWTT)</strong> dan maksimal berlangsung <strong>3 (tiga) bulan</strong>. Karyawan yang dinyatakan lulus berhak mendapatkan <em>Surat Keputusan (SK) Pengangkatan Karyawan Tetap</em>. Jika dinyatakan tidak lulus sebelum 3 bulan, hubungan kerja berakhir tanpa kewajiban kompensasi pesangon.
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Penilaian Dilakukan</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $summary['total_evaluations'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Evaluasi tercatat dalam sistem</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Lulus (Diangkat Tetap)</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $summary['passed_count'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">SK PKWTT telah terbit</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tidak Lulus (Terminated)</p>
            <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ $summary['failed_count'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Berakhir tanpa pesangon</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Karyawan Masa Probation</p>
            <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ $summary['pending_probation'] }}</h3>
            <p class="text-xs text-slate-500 mt-2">Sedang menjalani masa uji coba</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Riwayat Evaluasi Probation & SK Pengangkatan</h3>
            <span class="text-xs text-slate-500">{{ $evaluations->count() }} data evaluasi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Karyawan</th>
                        <th class="px-6 py-3">Skor Teknis (40%)</th>
                        <th class="px-6 py-3">Skor Kehadiran (30%)</th>
                        <th class="px-6 py-3">Skor Budaya (30%)</th>
                        <th class="px-6 py-3">Skor Akhir</th>
                        <th class="px-6 py-3">Keputusan</th>
                        <th class="px-6 py-3">No. SK PKWTT</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($evaluations as $eval)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900">{{ $eval->employee->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $eval->employee->employee_code ?? '-' }} | {{ $eval->employee->position ?? 'Staff' }}</div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800">{{ $eval->technical_score }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-800">{{ $eval->attendance_score }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-800">{{ $eval->culture_score }}</td>
                        <td class="px-6 py-4">
                            <span class="text-base font-bold {{ $eval->final_score >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $eval->final_score }}
                            </span>
                            <span class="text-xs text-slate-400">/ 100</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($eval->status === 'PASSED')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                                    LULUS (TETAP)
                                </span>
                            @elseif($eval->status === 'FAILED')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800">
                                    TIDAK LULUS
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">
                                    DIPERPANJANG
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">
                            {{ $eval->sk_number ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('company.probation.show', $eval) }}" class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded text-xs font-medium transition">
                                Detail
                            </a>
                            @if($eval->status === 'PASSED' && $eval->sk_number)
                            <a href="{{ route('company.probation.print-sk', $eval) }}" target="_blank" class="inline-flex items-center px-2.5 py-1.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded text-xs font-medium transition">
                                Cetak SK
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            Belum ada penilaian masa percobaan yang dicatat. Klik tombol "Lakukan Penilaian Probation" untuk menilai karyawan baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Penilaian Probation -->
<div id="modal-new-evaluation" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center pb-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-lg text-slate-900">Form Evaluasi Masa Percobaan (3 Bulan)</h3>
                <p class="text-xs text-slate-500">Standar bobot: Teknis (40%), Kehadiran & Disiplin (30%), Budaya & Attitude (30%)</p>
            </div>
            <button onclick="document.getElementById('modal-new-evaluation').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form action="{{ route('company.probation.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan Probation *</label>
                <select name="employee_id" required class="w-full text-sm border-slate-300 rounded-lg p-2.5 border focus:ring-red-500">
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($eligibleEmployees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_code }}) - Masuk: {{ \Carbon\Carbon::parse($emp->join_date)->format('d/m/Y') }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Penilai / Reviewer *</label>
                    <input type="text" name="evaluator_name" required value="{{ auth()->user()->name ?? 'Direct Supervisor' }}" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Akhir Probation *</label>
                    <input type="date" name="probation_end_date" required value="{{ date('Y-m-d') }}" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Teknis (0-100) *</label>
                    <input type="number" name="technical_score" min="0" max="100" required value="80" class="w-full text-sm border-slate-300 rounded-lg p-2 border text-center font-bold">
                    <span class="text-[10px] text-slate-500 mt-1 block">Bobot 40%</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Kehadiran (0-100) *</label>
                    <input type="number" name="attendance_score" min="0" max="100" required value="85" class="w-full text-sm border-slate-300 rounded-lg p-2 border text-center font-bold">
                    <span class="text-[10px] text-slate-500 mt-1 block">Bobot 30%</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Skor Budaya (0-100) *</label>
                    <input type="number" name="culture_score" min="0" max="100" required value="80" class="w-full text-sm border-slate-300 rounded-lg p-2 border text-center font-bold">
                    <span class="text-[10px] text-slate-500 mt-1 block">Bobot 30%</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rekomendasi Keputusan *</label>
                    <select name="recommendation" required class="w-full text-sm border-slate-300 rounded-lg p-2.5 border focus:ring-red-500">
                        <option value="PASS">LULUS (Diangkat Karyawan Tetap PKWTT)</option>
                        <option value="FAIL">TIDAK LULUS (Pemutusan Hubungan Kerja)</option>
                        <option value="EXTEND">PERPANJANG (Maks 1 bulan jika diizinkan PP)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Penyesuaian Gaji Baru (Rp) - Opsional</label>
                    <input type="number" name="new_basic_salary" min="0" step="1000" placeholder="Biarkan kosong jika tidak berubah" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Penyesuaian Jabatan Baru (Opsional)</label>
                <input type="text" name="new_job_title" placeholder="Contoh: Junior Engineer → Full Software Engineer" class="w-full text-sm border-slate-300 rounded-lg p-2.5 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Evaluator & Justifikasi</label>
                <textarea name="evaluator_notes" rows="3" placeholder="Deskripsikan pencapaian kunci, integritas kerja, dan alasan kelulusan..." class="w-full text-sm border-slate-300 rounded-lg p-2.5 border"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-new-evaluation').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Simpan Evaluasi & Terbitkan Keputusan</button>
            </div>
        </form>
    </div>
</div>
@endsection
