@extends('layouts.app')

@section('title', 'Analitik Eksekutif SDM & Turnover')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-red-100 text-red-800 border border-red-200">
                    C-Level Intelligence
                </span>
                <span class="text-xs text-slate-400">• Phase 13 Enterprise</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Laporan Eksekutif Kinerja & Analitik SDM</h1>
            <p class="text-xs text-slate-500 mt-1">Perhitungan baku tingkat perputaran karyawan (Turnover Rate), indeks Bradford ketidakhadiran, dan demografi organisasi.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('company.analytics.advanced.index') }}" method="GET" class="flex items-center gap-1.5">
                <select name="year" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 shadow-sm">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>
            <a href="{{ route('company.analytics.advanced.export-csv', ['year' => $selectedYear]) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i>
                Ekspor Laporan (CSV)
            </a>
        </div>
    </div>

    <!-- Turnover KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Turnover Rate Tahunan</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="user-minus" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $turnover['turnover_rate'] }}%</div>
            <div class="text-[11px] text-slate-500 mt-1">
                Rumus: Separations / Avg Headcount
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Tingkat Retensi</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $turnover['retention_rate'] }}%</div>
            <div class="text-[11px] text-slate-500 mt-1">Persentase retensi karyawan bertahan</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Karyawan Keluar (Tahun Ini)</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $turnover['total_separations'] }} <span class="text-xs font-normal text-slate-500">orang</span></div>
            <div class="text-[11px] text-slate-500 mt-1">Resign: {{ $turnover['by_reason']['resigned'] }} | PHK: {{ $turnover['by_reason']['terminated'] }}</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Rata-Rata Headcount</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $turnover['average_headcount'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Awal: {{ $turnover['headcount_start'] }} → Akhir: {{ $turnover['headcount_end'] }}</div>
        </div>
    </div>

    <!-- Demographics & Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Gender Pyramid -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-red-600"></i>
                Komposisi Gender
            </h2>
            <div class="space-y-3 pt-2">
                <div>
                    <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                        <span>Laki-Laki ({{ $demographics['gender']['male'] }} orang)</span>
                        <span class="font-bold">{{ $demographics['gender']['male_pct'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $demographics['gender']['male_pct'] }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                        <span>Perempuan ({{ $demographics['gender']['female'] }} orang)</span>
                        <span class="font-bold">{{ $demographics['gender']['female_pct'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5">
                        <div class="bg-rose-500 h-2.5 rounded-full" style="width: {{ $demographics['gender']['female_pct'] }}%"></div>
                    </div>
                </div>
            </div>
            <div class="text-[11px] text-slate-400 pt-2 border-t border-slate-100 text-center">
                Total Pegawai Aktif: {{ $demographics['total_active_headcount'] }} orang
            </div>
        </div>

        <!-- Age Groups -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-4 h-4 text-red-600"></i>
                Distribusi Kelompok Usia
            </h2>
            <div class="grid grid-cols-2 gap-2.5 pt-2 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] font-bold uppercase">&lt; 25 Tahun</div>
                    <div class="text-lg font-black text-slate-900 mt-0.5">{{ $demographics['age_brackets']['under_25'] }} orang</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] font-bold uppercase">25 - 34 Tahun</div>
                    <div class="text-lg font-black text-slate-900 mt-0.5">{{ $demographics['age_brackets']['age_25_34'] }} orang</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] font-bold uppercase">35 - 44 Tahun</div>
                    <div class="text-lg font-black text-slate-900 mt-0.5">{{ $demographics['age_brackets']['age_35_44'] }} orang</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] font-bold uppercase">45+ Tahun</div>
                    <div class="text-lg font-black text-slate-900 mt-0.5">{{ $demographics['age_brackets']['age_45_plus'] }} orang</div>
                </div>
            </div>
        </div>

        <!-- Tenure Distribution -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i data-lucide="award" class="w-4 h-4 text-red-600"></i>
                Masa Kerja (Tenure)
            </h2>
            <div class="grid grid-cols-2 gap-2.5 pt-2 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] font-bold uppercase">&lt; 1 Tahun</div>
                    <div class="text-lg font-black text-slate-900 mt-0.5">{{ $demographics['tenure_brackets']['under_1yr'] }} orang</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] font-bold uppercase">1 - 3 Tahun</div>
                    <div class="text-lg font-black text-slate-900 mt-0.5">{{ $demographics['tenure_brackets']['yr_1_3'] }} orang</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] font-bold uppercase">3 - 5 Tahun</div>
                    <div class="text-lg font-black text-slate-900 mt-0.5">{{ $demographics['tenure_brackets']['yr_3_5'] }} orang</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] font-bold uppercase">&gt; 5 Tahun</div>
                    <div class="text-lg font-black text-slate-900 mt-0.5">{{ $demographics['tenure_brackets']['over_5yr'] }} orang</div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bradford Factor Absenteeism Index Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="activity" class="w-4 h-4 text-amber-500"></i>
                    Indeks Bradford Factor (Analisis Ketidakhadiran Sporadis)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Formula standar: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700">B = S² × D</code> (S = Jumlah Kejadian/Spells, D = Total Hari Absen). Ketidakhadiran singkat berulang memiliki bobot gangguan lebih tinggi.</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">Rendah: {{ $bradford['summary_counts']['low'] }}</span>
                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">Sedang: {{ $bradford['summary_counts']['medium'] }}</span>
                <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200">Tinggi: {{ $bradford['summary_counts']['high'] }}</span>
                <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200">Kritis: {{ $bradford['summary_counts']['critical'] }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Nama Karyawan</th>
                        <th class="px-4 py-3 font-bold">Departemen</th>
                        <th class="px-4 py-3 font-bold text-center">Kejadian (Spells S)</th>
                        <th class="px-4 py-3 font-bold text-center">Total Hari (D)</th>
                        <th class="px-4 py-3 font-bold text-right">Skor Bradford (B)</th>
                        <th class="px-4 py-3 font-bold text-right">Kategori Risiko</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bradford['flagged_employees'] as $emp)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $emp['name'] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $emp['department'] }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $emp['spells'] }} kali</td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $emp['days'] }} hari</td>
                            <td class="px-4 py-3 text-right font-black text-slate-900">{{ number_format($emp['bradford_score']) }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($emp['risk_level'] === 'critical')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Kritis (≥ 500)</span>
                                @elseif($emp['risk_level'] === 'high')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Tinggi (200-499)</span>
                                @elseif($emp['risk_level'] === 'medium')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Sedang (50-199)</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Normal (&lt; 50)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400 italic">Tidak ada catatan absensi tanpa izin yang mengkhawatirkan pada tahun ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Labor Cost Summary -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
            <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
            Ikhtisar Biaya Tenaga Kerja (Labor Cost Compliance) — {{ $labor['year'] }}
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl">
                <div class="text-slate-400 text-[10px] font-bold uppercase">Upah Bruto (Gross)</div>
                <div class="font-black text-slate-900 mt-1">Rp {{ number_format($labor['total_gross'], 0, ',', '.') }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl">
                <div class="text-slate-400 text-[10px] font-bold uppercase">Iuran BPJS Perusahaan</div>
                <div class="font-black text-slate-900 mt-1">Rp {{ number_format($labor['total_bpjs_employer'], 0, ',', '.') }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl">
                <div class="text-slate-400 text-[10px] font-bold uppercase">Upah Lembur</div>
                <div class="font-black text-slate-900 mt-1">Rp {{ number_format($labor['total_overtime'], 0, ',', '.') }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl">
                <div class="text-slate-400 text-[10px] font-bold uppercase">Pajak PPh 21 Ditampung</div>
                <div class="font-black text-slate-900 mt-1">Rp {{ number_format($labor['total_tax_pph21'], 0, ',', '.') }}</div>
            </div>
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                <div class="text-emerald-700 text-[10px] font-bold uppercase">Total Kompensasi</div>
                <div class="font-black text-emerald-900 mt-1">Rp {{ number_format($labor['total_compensation_cost'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

</div>
@endsection
