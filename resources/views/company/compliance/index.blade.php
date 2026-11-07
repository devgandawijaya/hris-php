@extends('layouts.app')

@section('title', 'Kepatuhan Regulasi Ketenagakerjaan (UMK & BPJS)')

@section('content')
<div class="space-y-6" x-data="{ showUmkModal: false, showBpjsModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700">
                    Phase 10 • Statutory Compliance
                </span>
                <span class="text-xs text-slate-400">Section 10.G & 11</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-1 tracking-tight">Kepatuhan Regulasi Ketenagakerjaan Indonesia</h1>
            <p class="text-xs text-slate-500 mt-1">Audit kesesuaian Upah Minimum Regional (UMK/UMR PP 51/2023) dan penyusunan pelaporan BPJS SIPP Online & e-Dabu.</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="showUmkModal = true" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-sm flex items-center gap-2">
                <i data-lucide="scale" class="w-4 h-4"></i>
                Kelola Standar UMK
            </button>
            <button type="button" @click="showBpjsModal = true" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm flex items-center gap-2">
                <i data-lucide="file-check-2" class="w-4 h-4"></i>
                Buat Laporan BPJS Statutori
            </button>
        </div>
    </div>

    <!-- SECTION 1: UMK REGIONAL MINIMUM WAGE AUDIT -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-slate-100 gap-4">
            <div>
                <span class="text-xs font-bold text-red-600 uppercase tracking-wider">Audit Kepatuhan Pengupahan</span>
                <h2 class="text-lg font-black text-slate-900 mt-0.5">Uji Kesesuaian Upah Minimum Kota/Kabupaten (UMK {{ $umkAudit['benchmark_year'] }})</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Benchmark: <strong>{{ $umkAudit['benchmark_city'] }}</strong> — Standar Wajib: <strong class="font-mono text-slate-900">Rp {{ number_format($umkAudit['benchmark_amount'], 2, ',', '.') }}</strong>
                    <span class="text-[10px] text-slate-400">({{ $umkAudit['legal_reference'] }})</span>
                </p>
            </div>

            <!-- City Filter -->
            <form method="GET" action="{{ route('company.compliance.index') }}" class="flex items-center gap-2">
                <select name="city" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs text-slate-800">
                    <option value="">-- Pilih Wilayah Benchmark --</option>
                    @foreach($umkStandards as $std)
                        <option value="{{ $std->city_or_regency }}" {{ $selectedCity == $std->city_or_regency ? 'selected' : '' }}>
                            {{ $std->city_or_regency }} (Rp {{ number_format($std->minimum_wage_amount, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Compliance Rate Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Tingkat Kepatuhan UMK</div>
                <div class="text-3xl font-black {{ $umkAudit['compliance_rate_percentage'] >= 100 ? 'text-emerald-600' : 'text-amber-600' }} mt-1">
                    {{ $umkAudit['compliance_rate_percentage'] }}%
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">{{ $umkAudit['compliant_count'] }} dari {{ $umkAudit['total_employees'] }} Karyawan Memenuhi Syarat</div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Karyawan Sesuai / Di Atas UMK</div>
                <div class="text-3xl font-black text-emerald-600 mt-1">
                    {{ $umkAudit['compliant_count'] }}
                </div>
                <div class="text-[11px] text-emerald-700 mt-0.5">Aman dari risiko sanksi Disnaker</div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Karyawan Di Bawah Standar UMK</div>
                <div class="text-3xl font-black {{ $umkAudit['non_compliant_count'] > 0 ? 'text-red-600' : 'text-slate-400' }} mt-1">
                    {{ $umkAudit['non_compliant_count'] }}
                </div>
                <div class="text-[11px] {{ $umkAudit['non_compliant_count'] > 0 ? 'text-red-600 font-bold' : 'text-slate-500' }} mt-0.5">
                    {{ $umkAudit['non_compliant_count'] > 0 ? 'Perlu penyesuaian gaji pokok' : 'Tidak ada defisit gaji' }}
                </div>
            </div>
        </div>

        <!-- Deficit Employees Table (If Any) -->
        @if($umkAudit['non_compliant_count'] > 0)
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-red-700 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600"></i>
                    Daftar Karyawan Dengan Upah Di Bawah Standar Minimum:
                </h3>

                <div class="border border-red-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-red-50 text-red-900 uppercase text-[10px] font-bold">
                            <tr>
                                <th class="px-4 py-2.5">Karyawan</th>
                                <th class="px-4 py-2.5">Departemen</th>
                                <th class="px-4 py-2.5">Gaji Aktual</th>
                                <th class="px-4 py-2.5">Standar UMK</th>
                                <th class="px-4 py-2.5">Defisit Kekurangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-100 bg-white">
                            @foreach($umkAudit['non_compliant_employees'] as $emp)
                                <tr>
                                    <td class="px-4 py-2.5">
                                        <div class="font-bold text-slate-900">{{ $emp['name'] }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $emp['employee_number'] }}</div>
                                    </td>
                                    <td class="px-4 py-2.5">{{ $emp['department'] }}</td>
                                    <td class="px-4 py-2.5 font-mono font-bold text-slate-800">Rp {{ number_format($emp['salary'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 font-mono text-slate-600">Rp {{ number_format($umkAudit['benchmark_amount'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 font-mono font-bold text-red-600">- Rp {{ number_format($emp['deficit'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- SECTION 2: STATUTORY BPJS SIPP & E-DABU REPORTS -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                    Berkas Laporan Statutori BPJS Ketenagakerjaan (SIPP Online) & BPJS Kesehatan (e-Dabu)
                </h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Sesuai format CSV portal resmi Kementerian Ketenagakerjaan & BPJS RI.</p>
            </div>
        </div>

        <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                <tr>
                    <th class="px-5 py-3">Jenis Laporan BPJS</th>
                    <th class="px-5 py-3">Masa Laporan</th>
                    <th class="px-5 py-3">Periode Payroll</th>
                    <th class="px-5 py-3">Peserta Terdaftar</th>
                    <th class="px-5 py-3">Iuran Pekerja</th>
                    <th class="px-5 py-3">Iuran Perusahaan</th>
                    <th class="px-5 py-3">Total Tagihan</th>
                    <th class="px-5 py-3 text-right">Ekspor Statutori</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($bpjsReports as $rpt)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-3.5">
                            @if($rpt->report_type === 'bpjs_tk_sipp')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                    BPJS TK (SIPP Online)
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    BPJS Kesehatan (e-Dabu)
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-800">
                            {{ $rpt->report_month }}
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-800">
                            {{ $rpt->payrollPeriod->name }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-800">
                            {{ $rpt->total_employees }} Jiwa
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-700">
                            Rp {{ number_format($rpt->total_employee_share, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-700">
                            Rp {{ number_format($rpt->total_company_share, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                            Rp {{ number_format($rpt->total_contribution, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('company.compliance.bpjs.download', $rpt->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-2xs transition">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Unduh File CSV
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                            Belum ada laporan statutori BPJS yang dikompilasi. Klik tombol di atas untuk membuat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($bpjsReports->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $bpjsReports->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: ADD / UPDATE REGIONAL MINIMUM WAGE (UMK) -->
    <div x-show="showUmkModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="showUmkModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="scale" class="w-4 h-4 text-red-600"></i>
                    Input Standar Upah Minimum Regional (UMK)
                </h3>
                <button type="button" @click="showUmkModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('company.compliance.umk.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Provinsi *</label>
                        <input type="text" name="province" required placeholder="Jawa Barat" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kota / Kabupaten *</label>
                        <input type="text" name="city_or_regency" required placeholder="Kota Bekasi" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Berlaku *</label>
                        <input type="number" name="year" required value="{{ date('Y') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Besaran UMK (Rp) *</label>
                        <input type="number" name="minimum_wage_amount" required step="1000" placeholder="5343430" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dasar Hukum / Nomor SK Gubernur</label>
                    <input type="text" name="legal_reference" placeholder="Kepgub No. 561/Kep.804-Kesra/2025" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showUmkModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Simpan Standar UMK</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: GENERATE BPJS REPORT -->
    <div x-show="showBpjsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="showBpjsModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="file-check-2" class="w-4 h-4 text-emerald-600"></i>
                    Kompilasi Laporan BPJS Statutori
                </h3>
                <button type="button" @click="showBpjsModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('company.compliance.bpjs.generate') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Periode Payroll *</label>
                    <select name="payroll_period_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="">-- Pilih Periode Selesai / Disetujui --</option>
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Laporan Statutori *</label>
                    <select name="report_type" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="bpjs_tk_sipp">BPJS Ketenagakerjaan — SIPP Online (5 Program: JHT, JKK, JKM, JP, JKP)</option>
                        <option value="bpjs_kes_edabu">BPJS Kesehatan — e-Dabu (4% Pemberi Kerja + 1% Pekerja)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showBpjsModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Kompilasi Laporan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
