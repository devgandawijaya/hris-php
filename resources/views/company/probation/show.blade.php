@extends('layouts.app')

@section('title', 'Detail Evaluasi Probation - ' . ($evaluation->employee->name ?? 'Karyawan'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('company.probation.index') }}" class="p-2 bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-800">{{ $evaluation->employee->name ?? '-' }}</h1>
                    @if($evaluation->status === 'PASSED')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            ✓ LULUS (PKWTT)
                        </span>
                    @elseif($evaluation->status === 'FAILED')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                            ✕ TIDAK LULUS
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            DIPERPANJANG
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-1">Evaluasi Masa Percobaan Berdasarkan Pasal 60 UU Ketenagakerjaan No. 13/2003</p>
            </div>
        </div>

        @if($evaluation->status === 'PASSED' && $evaluation->sk_number)
        <div>
            <a href="{{ route('company.probation.print-sk', $evaluation) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak SK Pengangkatan Karyawan Tetap
            </a>
        </div>
        @endif
    </div>

    <!-- Score Breakdown Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Skor Akhir Tertimbang</p>
            <h3 class="text-3xl font-extrabold {{ $evaluation->final_score >= 75 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">
                {{ $evaluation->final_score }}
            </h3>
            <p class="text-xs text-slate-500 mt-2">Passing Grade: 75.00 poin</p>
        </div>

        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kompetensi Teknis (40%)</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $evaluation->technical_score }}</h3>
            <div class="w-full bg-slate-100 rounded-full h-2 mt-3">
                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, $evaluation->technical_score) }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kehadiran & Disiplin (30%)</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $evaluation->attendance_score }}</h3>
            <div class="w-full bg-slate-100 rounded-full h-2 mt-3">
                <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ min(100, $evaluation->attendance_score) }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Budaya & Attitude (30%)</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $evaluation->culture_score }}</h3>
            <div class="w-full bg-slate-100 rounded-full h-2 mt-3">
                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min(100, $evaluation->culture_score) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Evaluation Details Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Karyawan & Evaluasi</h3>
            <table class="w-full text-sm">
                <tr class="border-b border-slate-100">
                    <td class="py-2.5 text-slate-500">Nama Karyawan</td>
                    <td class="py-2.5 font-semibold text-slate-900">{{ $evaluation->employee->name ?? '-' }}</td>
                </tr>
                <tr class="border-b border-slate-100">
                    <td class="py-2.5 text-slate-500">Nomor Induk Pegawai</td>
                    <td class="py-2.5 font-mono text-slate-800">{{ $evaluation->employee->employee_code ?? '-' }}</td>
                </tr>
                <tr class="border-b border-slate-100">
                    <td class="py-2.5 text-slate-500">Tanggal Masuk (Join Date)</td>
                    <td class="py-2.5 text-slate-800">{{ \Carbon\Carbon::parse($evaluation->employee->join_date)->format('d F Y') }}</td>
                </tr>
                <tr class="border-b border-slate-100">
                    <td class="py-2.5 text-slate-500">Batas Waktu Probation</td>
                    <td class="py-2.5 text-slate-800">{{ \Carbon\Carbon::parse($evaluation->probation_end_date)->format('d F Y') }}</td>
                </tr>
                <tr class="border-b border-slate-100">
                    <td class="py-2.5 text-slate-500">Evaluator / Penilai</td>
                    <td class="py-2.5 text-slate-800">{{ $evaluation->evaluator_name }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 text-slate-500">Tanggal Evaluasi</td>
                    <td class="py-2.5 text-slate-800">{{ $evaluation->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 border-b border-slate-100 pb-3">Keputusan & Status SK Pengangkatan</h3>
            @if($evaluation->status === 'PASSED')
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-emerald-900 text-sm">
                    <div class="font-bold text-base mb-1">Ditetapkan Sebagai Karyawan Tetap (PKWTT)</div>
                    <p class="text-xs text-emerald-800">Karyawan telah memenuhi kualifikasi standar kelulusan probation dan status kepegawaian telah diperbarui otomatis.</p>
                </div>
                <table class="w-full text-sm">
                    <tr class="border-b border-slate-100">
                        <td class="py-2.5 text-slate-500">Nomor SK PKWTT</td>
                        <td class="py-2.5 font-mono font-bold text-slate-900">{{ $evaluation->sk_number ?? 'Auto-generated' }}</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-2.5 text-slate-500">Jabatan Baru</td>
                        <td class="py-2.5 text-slate-800">{{ $evaluation->new_job_title ?? $evaluation->employee->position ?? 'Tetap' }}</td>
                    </tr>
                    @if($evaluation->new_basic_salary)
                    <tr>
                        <td class="py-2.5 text-slate-500">Gaji Pokok Baru</td>
                        <td class="py-2.5 font-bold text-emerald-600">Rp {{ number_format($evaluation->new_basic_salary, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </table>
            @else
                <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-rose-900 text-sm">
                    <div class="font-bold text-base mb-1">Masa Hubungan Kerja Selesai</div>
                    <p class="text-xs text-rose-800">Karyawan tidak mencapai batas nilai kelulusan probation. Hubungan kerja dihentikan sesuai Pasal 60 UU Ketenagakerjaan.</p>
                </div>
            @endif

            <div class="pt-2">
                <span class="text-xs font-semibold text-slate-500 block mb-1">Catatan Evaluator:</span>
                <p class="text-sm text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 italic">
                    "{{ $evaluation->evaluator_notes ?? 'Tidak ada catatan tambahan.' }}"
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
