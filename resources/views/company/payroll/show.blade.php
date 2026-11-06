@extends('layouts.app')

@section('title', 'Detail Periode Payroll: ' . $period->name)

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('company.payroll.index') }}" class="p-2 rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $period->name }}</h1>
                @if($period->status === 'paid')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        Lunas (Paid)
                    </span>
                @elseif($period->status === 'approved')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        Disetujui (Approved)
                    </span>
                @elseif($period->status === 'processed')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        Terkalkulasi (Processed)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        Draft
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-4">
                <span><strong class="text-slate-700">Cut-Off:</strong> {{ $period->start_date->format('d M Y') }} s.d. {{ $period->end_date->format('d M Y') }}</span>
                <span>•</span>
                <span><strong class="text-slate-700">Tanggal Bayar:</strong> {{ $period->payment_date->format('d M Y') }}</span>
            </p>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- 1. Process / Recalculate -->
            <form action="{{ route('company.payroll.process', $period->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm">
                    <i data-lucide="calculator" class="w-4 h-4 text-amber-400"></i>
                    {{ $payrolls->isEmpty() ? 'Jalankan Kalkulasi Payroll' : 'Kalkulasi Ulang' }}
                </button>
            </form>

            @if($period->status === 'processed')
                <!-- 2. Approve Period -->
                <form action="{{ route('company.payroll.approve', $period->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui seluruh slip gaji periode ini?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm">
                        <i data-lucide="check-check" class="w-4 h-4"></i>
                        Setujui Payroll
                    </button>
                </form>
            @endif

            @if($period->status === 'approved')
                <!-- 3. Mark As Paid / Disburse -->
                <form action="{{ route('company.payroll.pay', $period->id) }}" method="POST" class="inline" onsubmit="return confirm('Tandai payroll ini telah dicairkan/dibayarkan ke rekening seluruh karyawan?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition shadow-sm">
                        <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                        Cairkan Gaji (Paid)
                    </button>
                </form>
            @endif

            <!-- 4. Export CSV -->
            @if($payrolls->isNotEmpty())
                <a href="{{ route('company.payroll.export', $period->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-sm">
                    <i data-lucide="download" class="w-4 h-4 text-slate-500"></i>
                    Ekspor CSV
                </a>
            @endif
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Karyawan</div>
            <div class="text-lg font-black text-slate-900 mt-1">{{ number_format($metrics['total_employees']) }} Org</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Tercakup dalam periode</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Gaji Kotor (Gross)</div>
            <div class="text-base font-black text-slate-900 mt-1 truncate">Rp {{ number_format($metrics['total_gross'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Pokok + Tunj. + Lembur</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Iuran BPJS Karyawan</div>
            <div class="text-base font-black text-rose-600 mt-1 truncate">Rp {{ number_format($metrics['total_bpjs_tk_employee'] + $metrics['total_bpjs_kes_employee'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Potongan JHT, JP, Kes</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Beban BPJS PT</div>
            <div class="text-base font-black text-slate-700 mt-1 truncate">Rp {{ number_format($metrics['total_bpjs_tk_employer'] + $metrics['total_bpjs_kes_employer'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Iuran Perusahaan</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PPh 21 TER</div>
            <div class="text-base font-black text-amber-600 mt-1 truncate">Rp {{ number_format($metrics['total_tax'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">PP 58/2023 & PMK 168</div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-red-200 bg-red-50/30 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#DC2626]">Take Home Pay</div>
            <div class="text-base font-black text-[#DC2626] mt-1 truncate">Rp {{ number_format($metrics['total_thp'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-red-500 font-medium mt-0.5">Total Gaji Bersih</div>
        </div>
    </div>

    <!-- Payroll Line Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                Rincian Penggajian Karyawan
            </h2>
            <span class="text-xs text-slate-500">Semua nominal dalam Rupiah (IDR)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">PTKP & Pajak</th>
                        <th class="px-4 py-3 text-right">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right">Tunjangan</th>
                        <th class="px-4 py-3 text-right">Lembur</th>
                        <th class="px-4 py-3 text-right">Reimbursement</th>
                        <th class="px-4 py-3 text-right">Gaji Bruto</th>
                        <th class="px-4 py-3 text-right">BPJS Karyawan</th>
                        <th class="px-4 py-3 text-right">PPh 21 TER</th>
                        <th class="px-4 py-3 text-right font-black text-[#DC2626]">Take Home Pay</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payrolls as $row)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $row->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $row->employee->employee_number }} • {{ $row->employee->position?->title ?? 'Staf' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $row->employee->tax_ptkp_status ?? 'TK/0' }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $row->employee->npwp ? 'NPWP Valid' : 'Non-NPWP' }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-slate-800">
                                {{ number_format($row->basic_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-slate-700">
                                {{ number_format($row->allowances_total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-slate-700">
                                {{ number_format($row->overtime_pay, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-emerald-600 font-medium">
                                {{ number_format($row->reimbursement_pay, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-slate-900">
                                {{ number_format($row->gross_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-rose-600 font-medium">
                                -{{ number_format($row->bpjs_tk_employee + $row->bpjs_kes_employee, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-amber-700 font-semibold">
                                -{{ number_format($row->pph21_tax, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-[#DC2626]">
                                Rp {{ number_format($row->take_home_pay, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <a href="{{ route('company.payroll.payslip.show', $row->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-[11px] transition shadow-xs" title="Lihat & Cetak Slip Gaji">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                                    <span>Slip Gaji</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-12 text-center text-slate-400">
                                <i data-lucide="calculator" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Payroll belum dikalkulasi untuk periode ini.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol <strong>"Jalankan Kalkulasi Payroll"</strong> di atas untuk memproses gaji seluruh karyawan secara otomatis.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

