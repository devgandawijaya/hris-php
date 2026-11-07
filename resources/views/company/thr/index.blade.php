@extends('layouts.app')

@section('title', 'Tunjangan Hari Raya (THR) Statutori')

@section('content')
<div class="space-y-6" x-data="{ calcModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                    Kepatuhan Permenaker No. 6/2016 jo. PP 58/2023
                </span>
                <span class="text-xs text-slate-400">• Kewajiban Statutori Hari Raya Keagamaan</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Perhitungan THR Statutori, Denda H-7 & Pajak TER</h1>
            <p class="text-xs text-slate-500 mt-1">Perhitungan otomatis hak THR pro-rata masa kerja, audit batas bayar H-7 (denda 5% Pasal 10), dan pemotongan PPh 21 TER.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="calcModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                Hitung THR Statutori
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-900 flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Hukum THR Keagamaan (Permenaker No. 6/2016):</span>
            <ul class="list-disc list-inside space-y-0.5 text-amber-800">
                <li><strong>Waktu Pembayaran (Pasal 5 ayat 4):</strong> Wajib dibayarkan paling lambat <strong>7 (tujuh) hari sebelum hari raya keagamaan (H-7)</strong>.</li>
                <li><strong>Sanksi Denda Keterlambatan (Pasal 10):</strong> Keterlambatan dikenai denda sebesar <strong>5% (lima persen) dari total THR</strong> yang wajib dibayar sejak berakhirnya batas waktu H-7 (tanpa menghilangkan kewajiban membayar pokok THR).</li>
                <li><strong>Masa Kerja (Pasal 2 & 3):</strong> Masa kerja $\ge 12$ bulan memperoleh 1 bulan upah; masa kerja $1 \le \text{tenure} < 12$ bulan memperoleh prorata $\frac{\text{masa kerja}}{12} \times 1\text{ bulan upah}$.</li>
                <li><strong>Pajak PPh 21 (PP 58/2023):</strong> Dikenakan Tarif Efektif Rata-Rata (TER) pada masa pajak pencairan THR.</li>
            </ul>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Penerima THR</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['total_recipients'] }} Karyawan</div>
            <span class="text-[11px] text-slate-500 font-medium">Tahun {{ $selectedYear }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Anggaran Bruto</span>
            <div class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($stats['total_gross_thr'], 0, ',', '.') }}</div>
            <span class="text-[11px] text-indigo-600 font-medium">Gaji pokok + tunjangan tetap</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Denda Telat H-7 (5%)</span>
            <div class="text-xl font-black {{ $stats['total_late_penalty'] > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                Rp {{ number_format($stats['total_late_penalty'], 0, ',', '.') }}
            </div>
            <span class="text-[11px] {{ $stats['total_late_penalty'] > 0 ? 'text-rose-500 font-bold' : 'text-emerald-500' }} font-medium">
                {{ $stats['total_late_penalty'] > 0 ? 'Pelanggaran batas bayar H-7' : 'Tepat waktu sesuai regulasi' }}
            </span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Net Dibayarkan</span>
            <div class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($stats['total_net_paid'], 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 font-medium">Setelah potongan TER & denda</span>
        </div>
    </div>

    <!-- Filters & List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-emerald-600"></i>
                Daftar Pencairan THR Keagamaan Statutori
            </h3>
            <form method="GET" action="{{ route('thr-disbursements.index') }}" class="flex items-center gap-2 text-xs">
                <select name="holiday_type" class="rounded-xl border-slate-200 text-xs" onchange="this.form.submit()">
                    <option value="idul_fitri" {{ $holidayType === 'idul_fitri' ? 'selected' : '' }}>Hari Raya Idul Fitri</option>
                    <option value="natal" {{ $holidayType === 'natal' ? 'selected' : '' }}>Hari Raya Natal</option>
                    <option value="nyepi" {{ $holidayType === 'nyepi' ? 'selected' : '' }}>Hari Raya Nyepi</option>
                    <option value="waisak" {{ $holidayType === 'waisak' ? 'selected' : '' }}>Hari Raya Waisak</option>
                    <option value="imlek" {{ $holidayType === 'imlek' ? 'selected' : '' }}>Tahun Baru Imlek</option>
                    <option value="all" {{ $holidayType === 'all' ? 'selected' : '' }}>Semua Hari Raya</option>
                </select>
                <input type="number" name="year" value="{{ $selectedYear }}" class="w-20 rounded-xl border-slate-200 text-xs" onchange="this.form.submit()">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Karyawan</th>
                        <th class="py-3 px-3">Hari Raya</th>
                        <th class="py-3 px-3">Masa Kerja</th>
                        <th class="py-3 px-3">Upah Dasar</th>
                        <th class="py-3 px-3">THR Bruto</th>
                        <th class="py-3 px-3">PPh 21 TER</th>
                        <th class="py-3 px-3">Denda H-7 (5%)</th>
                        <th class="py-3 px-3">Net THR</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($disbursements as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $item->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $item->employee->nik }} • {{ $item->employee->department->name ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3 uppercase font-semibold text-slate-700 font-mono text-[11px]">
                                {{ str_replace('_', ' ', $item->religious_holiday_type) }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800">{{ $item->tenure_months }} Bulan</span>
                                <span class="text-[10px] text-slate-400 block">Faktor: {{ number_format($item->prorata_multiplier, 2) }}x</span>
                            </td>
                            <td class="py-3 px-3 text-slate-700">Rp {{ number_format($item->regular_wage_basis, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 font-bold text-slate-900">Rp {{ number_format($item->calculated_thr_amount, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-rose-600 font-medium">- Rp {{ number_format($item->ter_tax_deduction, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 {{ $item->late_penalty_interest > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                @if($item->late_penalty_interest > 0)
                                    + Rp {{ number_format($item->late_penalty_interest, 0, ',', '.') }} (Denda 5%)
                                @else
                                    Rp 0 (Tepat Waktu)
                                @endif
                            </td>
                            <td class="py-3 px-3 font-black text-emerald-600 text-sm">Rp {{ number_format($item->net_thr_paid, 0, ',', '.') }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $item->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('thr-disbursements.print-slip', $item) }}" target="_blank" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition inline-flex items-center gap-1">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        Slip
                                    </a>
                                    @if($item->status !== 'paid')
                                        <form action="{{ route('thr-disbursements.mark-paid', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                                Lunas
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-slate-400">Belum ada data kalkulasi THR untuk tahun dan hari raya ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $disbursements->links() }}
        </div>
    </div>

    <!-- Modal Hitung THR -->
    <div x-show="calcModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-100" x-data="{ scope: 'all' }">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Kalkulasi THR Statutori Permenaker 6/2016</h3>
                <button @click="calcModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('thr-disbursements.calculate') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tahun Pajak</label>
                        <input type="number" name="tax_year" value="{{ date('Y') }}" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Hari Raya</label>
                        <select name="religious_holiday_type" required class="w-full rounded-xl border-slate-200 text-xs">
                            <option value="idul_fitri">Hari Raya Idul Fitri</option>
                            <option value="natal">Hari Raya Natal</option>
                            <option value="nyepi">Hari Raya Nyepi</option>
                            <option value="waisak">Hari Raya Waisak</option>
                            <option value="imlek">Tahun Baru Imlek</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Hari Raya</label>
                        <input type="date" name="holiday_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required class="w-full rounded-xl border-slate-200 text-xs">
                        <span class="text-[10px] text-slate-400">Batas bayar statutori otomatis H-7.</span>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Rencana Bayar</label>
                        <input type="date" name="actual_payment_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required class="w-full rounded-xl border-slate-200 text-xs">
                        <span class="text-[10px] text-rose-500">Jika lewat H-7, denda 5% diterapkan.</span>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Cakupan Karyawan</label>
                    <select name="employee_scope" x-model="scope" class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="all">Seluruh Karyawan Aktif Perusahaan</option>
                        <option value="individual">Karyawan Tertentu (Individu)</option>
                    </select>
                </div>
                <div x-show="scope === 'individual'">
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Karyawan</label>
                    <select name="employee_id" class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="calcModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Jalankan Kalkulasi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
