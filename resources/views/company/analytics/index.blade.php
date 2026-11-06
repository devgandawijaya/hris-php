@extends('layouts.app')

@section('title', 'Eksekutif Analitik & Laporan Lanjutan')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Eksekutif Analitik & Laporan Lanjutan</h1>
            <p class="text-sm text-slate-500 mt-1">Wawasan komprehensif metrik SDM, rasio turnover, kedisiplinan kerja, dan komposisi beban anggaran payroll perusahaan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('company.analytics.export.headcount') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-sm">
                <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i>
                Ekspor Headcount CSV
            </a>
            <a href="{{ route('company.analytics.export.payroll') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                Ekspor Payroll CSV
            </a>
        </div>
    </div>

    <!-- Executive KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Karyawan Aktif</span>
            <div class="text-2xl font-black text-slate-900">{{ number_format($metrics['active_headcount']) }} Orang</div>
            <div class="text-[11px] text-slate-500">Dari total {{ $metrics['total_headcount'] }} terdaftar</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tingkat Turnover Karyawan</span>
            <div class="text-2xl font-black text-blue-600">{{ number_format($metrics['turnover_rate'], 1) }}%</div>
            <div class="text-[11px] text-slate-500">{{ $metrics['resigned_headcount'] }} resign/terminasi</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tingkat Ketepatan Waktu</span>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($metrics['ontime_rate'], 1) }}%</div>
            <div class="text-[11px] text-emerald-600 font-semibold">Presensi bulan berjalan</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Beban Payroll Terakhir</span>
            <div class="text-2xl font-black text-purple-600 font-mono">Rp {{ number_format($metrics['latest_payroll_gross'] / 1000000, 1) }}M</div>
            <div class="text-[11px] text-slate-500">{{ $metrics['latest_period_name'] }}</div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart 1: Departemen Breakdown -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">Distribusi Karyawan per Departemen</h3>
                <span class="text-xs text-slate-400">Headcount</span>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="deptChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: PKWT vs PKWTT & Gender -->
        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900">Status Hubungan Kerja (PKWT vs PKWTT)</h3>
                <span class="text-xs text-slate-400">Legalitas</span>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="contractChart"></canvas>
            </div>
        </div>

        <!-- Chart 3: Komposisi Anggaran Payroll & Beban Perusahaan -->
        <div class="lg:col-span-12 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Komposisi Pengeluaran Finansial Payroll</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Rincian Take Home Pay, PPh 21 TER, Iuran BPJS Pemberi Kerja, dan Klaim Reimbursement.</p>
                </div>
                <span class="font-mono text-xs font-bold text-slate-700">{{ $metrics['latest_period_name'] }}</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Gaji Bersih (THP Karyawan)</span>
                    <strong class="font-mono text-slate-900 text-sm">Rp {{ number_format($metrics['latest_payroll_thp'], 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Setoran Pajak PPh 21 TER</span>
                    <strong class="font-mono text-blue-600 text-sm">Rp {{ number_format($metrics['latest_payroll_tax'], 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Iuran BPJS Perusahaan</span>
                    <strong class="font-mono text-emerald-600 text-sm">Rp {{ number_format($metrics['latest_payroll_bpjs_employer'], 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Klaim Reimbursement Bulan Ini</span>
                    <strong class="font-mono text-purple-600 text-sm">Rp {{ number_format($metrics['reimbursement_paid_month'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Center Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="pb-3 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-900">Pusat Ekspor Laporan Standar Ketenagakerjaan</h3>
            <p class="text-xs text-slate-500 mt-0.5">Unduh rekapan data operasional dalam format CSV terstruktur yang kompatibel dengan Excel, Google Sheets, dan auditor eksternal.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- 1. Headcount Export -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3 flex flex-col justify-between">
                <div>
                    <span class="font-bold text-slate-900 text-xs block">Laporan Master Data Karyawan</span>
                    <p class="text-[11px] text-slate-500 mt-1">Seluruh data NIK, departemen, jabatan, status PKWT/PKWTT, tanggal bergabung, dan gaji pokok.</p>
                </div>
                <a href="{{ route('company.analytics.export.headcount') }}" class="w-full text-center py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    Unduh Data Karyawan (.CSV)
                </a>
            </div>

            <!-- 2. Attendance Export -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3 flex flex-col justify-between">
                <div>
                    <span class="font-bold text-slate-900 text-xs block">Laporan Rekap Presensi & Jam Kerja</span>
                    <p class="text-[11px] text-slate-500 mt-1">Detail jam clock-in/out, menit keterlambatan, status kehadiran harian, dan verifikasi presensi.</p>
                </div>
                <form action="{{ route('company.analytics.export.attendance') }}" method="GET" class="space-y-2">
                    <input type="month" name="month" value="{{ date('Y-m') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-1.5 font-bold text-slate-700">
                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                        Unduh Presensi (.CSV)
                    </button>
                </form>
            </div>

            <!-- 3. Payroll Export -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3 flex flex-col justify-between">
                <div>
                    <span class="font-bold text-slate-900 text-xs block">Laporan Rekapitulasi Gaji (Payroll)</span>
                    <p class="text-[11px] text-slate-500 mt-1">Rincian gaji bruto, potongan BPJS, PPh 21 TER, lembur, dan THP per karyawan per periode.</p>
                </div>
                <form action="{{ route('company.analytics.export.payroll') }}" method="GET" class="space-y-2">
                    <select name="period_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-1.5 font-bold text-slate-700">
                        <option value="">Semua Periode</option>
                        @foreach($payrollPeriods as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                        Unduh Payroll (.CSV)
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Chart 1: Department Breakdown
        const deptLabels = {!! json_encode(array_keys($metrics['department_breakdown'])) !!};
        const deptValues = {!! json_encode(array_values($metrics['department_breakdown'])) !!};

        if (deptLabels.length > 0 && document.getElementById('deptChart')) {
            new Chart(document.getElementById('deptChart'), {
                type: 'doughnut',
                data: {
                    labels: deptLabels,
                    datasets: [{
                        data: deptValues,
                        backgroundColor: ['#DC2626', '#2563EB', '#10B981', '#F59E0B', '#8B5CF6', '#64748B'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Chart 2: PKWT vs PKWTT
        const statusKeys = {!! json_encode(array_keys($metrics['status_breakdown'])) !!};
        const statusValues = {!! json_encode(array_values($metrics['status_breakdown'])) !!};

        if (document.getElementById('contractChart')) {
            new Chart(document.getElementById('contractChart'), {
                type: 'bar',
                data: {
                    labels: statusKeys.map(k => k.toUpperCase()),
                    datasets: [{
                        label: 'Jumlah Karyawan',
                        data: statusValues,
                        backgroundColor: '#DC2626',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }
    });
</script>
@endsection
