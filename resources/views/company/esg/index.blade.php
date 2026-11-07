@extends('layouts.app')

@section('title', 'ESG & Sustainability Reporting Ketenagakerjaan')

@section('content')
<div class="space-y-6" x-data="{ generateModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Kepatuhan POJK No. 51/POJK.03/2017 & Standar GRI 401/404/405
                </span>
                <span class="text-xs text-slate-400">• Tata Kelola Lingkungan, Sosial & Ketenagakerjaan</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Laporan Keberlanjutan Ketenagakerjaan (ESG)</h1>
            <p class="text-xs text-slate-500 mt-1">Pengukuran kesetaraan upah gender, diversitas kepemimpinan, jam pengembangan talenta, dan metrik keselamatan kerja berstandar global.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="generateModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Hitung Ulang Snapshot ESG
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-xs text-emerald-900 flex items-start gap-3">
        <i data-lucide="leaf" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Pedoman Laporan Keberlanjutan OJK POJK 51/2017 & Global Reporting Initiative (GRI):</span>
            <ul class="list-disc list-inside space-y-0.5 text-emerald-800">
                <li><strong>GRI 405-2: Rasio Upah Setara Gender:</strong> Rasio perbandingan rata-rata remunerasi perempuan terhadap laki-laki pada jabatan dan grade yang setara (target ideal: $1.0000$).</li>
                <li><strong>GRI 405-1: Keberagaman Dewan & Manajemen:</strong> Persentase keterwakilan perempuan di jajaran Dewan Direksi, Komisaris, serta level manajerial.</li>
                <li><strong>GRI 404-1: Rata-rata Jam Pelatihan Karyawan:</strong> Akumulasi jam pelatihan per karyawan per tahun sebagai indikator pengembangan sumber daya manusia.</li>
                <li><strong>GRI 403-9: Rasio Insiden Cedera (LTIFR):</strong> Frekuensi kecelakaan kerja yang menyebabkan hilang hari kerja per satu juta jam kerja.</li>
            </ul>
        </div>
    </div>

    @if($currentSnapshot)
        <!-- Stat Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">Gender Pay Ratio</span>
                <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($currentSnapshot->gender_pay_gap_ratio, 4) }}</div>
                <span class="text-[10px] {{ $currentSnapshot->gender_pay_gap_ratio >= 0.95 && $currentSnapshot->gender_pay_gap_ratio <= 1.05 ? 'text-emerald-600' : 'text-amber-600' }} font-bold">
                    {{ $currentSnapshot->gender_pay_gap_ratio >= 0.95 && $currentSnapshot->gender_pay_gap_ratio <= 1.05 ? 'Upah Setara (Ideal)' : 'Terdapat Selisih Upah' }}
                </span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">% Perempuan Kerja</span>
                <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($currentSnapshot->female_workforce_pct, 1) }}%</div>
                <span class="text-[10px] text-slate-500 font-medium">Dari total angkatan kerja</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">% Kepemimpinan Wanita</span>
                <div class="text-xl font-black text-indigo-600 mt-1">{{ number_format($currentSnapshot->female_leadership_pct, 1) }}%</div>
                <span class="text-[10px] text-indigo-500 font-medium">Manajerial & Lead</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">% Diversitas Direksi</span>
                <div class="text-xl font-black text-purple-600 mt-1">{{ number_format($currentSnapshot->board_diversity_pct, 1) }}%</div>
                <span class="text-[10px] text-purple-500 font-medium">Board of Directors</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">Jam Pelatihan / Org</span>
                <div class="text-xl font-black text-teal-600 mt-1">{{ number_format($currentSnapshot->avg_training_hours_per_employee, 1) }} Jam</div>
                <span class="text-[10px] text-slate-500 font-medium">GRI 404-1 Tahunan</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <span class="text-[10px] text-slate-400 font-semibold uppercase block">Turnover Karyawan</span>
                <div class="text-xl font-black text-rose-600 mt-1">{{ number_format($currentSnapshot->annual_turnover_rate, 1) }}%</div>
                <span class="text-[10px] text-slate-500 font-medium">GRI 401-1 Tahunan</span>
            </div>
        </div>
    @endif

    <!-- Snapshots History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-4 h-4 text-emerald-600"></i>
                Rekapitulasi Snapshot Keberlanjutan Tahunan (POJK 51/2017)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Tahun Laporan</th>
                        <th class="py-3 px-3">Gender Pay Ratio</th>
                        <th class="py-3 px-3">% Pekerja Wanita</th>
                        <th class="py-3 px-3">% Lead Wanita</th>
                        <th class="py-3 px-3">% Board Diversitas</th>
                        <th class="py-3 px-3">Jam Training/Org</th>
                        <th class="py-3 px-3">Turnover</th>
                        <th class="py-3 px-3">LTIFR Safety</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($snapshots as $snap)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3 font-bold text-slate-900">Tahun {{ $snap->reporting_year }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-800">{{ number_format($snap->gender_pay_gap_ratio, 4) }}</td>
                            <td class="py-3 px-3 font-semibold text-slate-700">{{ number_format($snap->female_workforce_pct, 1) }}%</td>
                            <td class="py-3 px-3 font-semibold text-indigo-600">{{ number_format($snap->female_leadership_pct, 1) }}%</td>
                            <td class="py-3 px-3 font-semibold text-purple-600">{{ number_format($snap->board_diversity_pct, 1) }}%</td>
                            <td class="py-3 px-3 text-slate-800">{{ number_format($snap->avg_training_hours_per_employee, 1) }} Jam</td>
                            <td class="py-3 px-3 text-rose-600 font-semibold">{{ number_format($snap->annual_turnover_rate, 1) }}%</td>
                            <td class="py-3 px-3 text-slate-700 font-mono">{{ number_format($snap->lost_time_injury_frequency_rate, 2) }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $snap->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $snap->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('esg.print', $snap) }}" target="_blank" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition inline-flex items-center gap-1">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    Cetak POJK 51
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-slate-400">Belum ada snapshot metrik ESG yang dihasilkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Generate Snapshot -->
    <div x-show="generateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Hitung Ulang Snapshot ESG</h3>
                <button @click="generateModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('esg.generate') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tahun Laporan Keberlanjutan</label>
                    <input type="number" name="reporting_year" value="{{ date('Y') }}" required class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <p class="text-[11px] text-slate-500">Sistem akan mengagregasi data payroll, gender, jenjang jabatan, jam pelatihan L&D, dan insiden K3 secara otomatis.</p>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="generateModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Jalankan Snapshot</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
