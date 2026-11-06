@extends('layouts.app')

@section('title', 'Pinjaman Karyawan & Kasbon Darurat')

@section('content')
<div class="space-y-6" x-data="{ 
    openCreateModal: false, 
    openInstallmentModal: false,
    selectedLoanNumber: '',
    selectedEmployeeName: '',
    installments: []
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pinjaman Karyawan & Kasbon Gaji</h1>
            <p class="text-sm text-slate-500 mt-1">Fasilitas kesejahteraan finansial, kasbon darurat, serta otomatisasi angsuran pemotongan gaji (payroll deduction).</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Ajukan Pinjaman / Kasbon Baru
            </button>
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pinjaman</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_loans']) }} Berkas</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Semua pengajuan tercatat</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dana Telah Dicairkan</div>
            <div class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($stats['total_disbursed'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Disalurkan ke karyawan</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sisa Tagihan Berjalan</div>
            <div class="text-xl font-black text-blue-600 mt-1">Rp {{ number_format($stats['outstanding_balance'], 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Outstanding dari {{ $stats['active_loans'] }} pinjaman aktif</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu Persetujuan</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($stats['pending_approval']) }} Pengajuan</div>
            <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Perlu verifikasi HR/Finance</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Daftar Pinjaman & Kasbon Karyawan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pantau jadwal angsuran, sisa tagihan, dan status pelunasan.</p>
            </div>

            <!-- Filter Status -->
            <form action="{{ route('company.loans.index') }}" method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Semua Status Pinjaman</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif Berjalan</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Lunas (Completed)</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Pinjaman & Tipe</th>
                        <th class="py-3 px-4">Karyawan</th>
                        <th class="py-3 px-4">Pokok & Bunga</th>
                        <th class="py-3 px-4">Tenor & Cicilan/Bln</th>
                        <th class="py-3 px-4">Sisa Tagihan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($loans as $loan)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 font-mono">{{ $loan->loan_number }}</div>
                                <div class="mt-0.5">
                                    @if($loan->loan_type === 'salary_advance')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">
                                            Kasbon Gaji
                                        </span>
                                    @elseif($loan->loan_type === 'emergency')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-red-100 text-red-800">
                                            Dana Darurat
                                        </span>
                                    @elseif($loan->loan_type === 'education')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-800">
                                            Pendidikan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-700">
                                            Lainnya
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $loan->employee?->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $loan->employee?->employee_code }} &bull; {{ $loan->employee?->department?->name ?? 'Dept' }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">Rp {{ number_format($loan->amount, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-400">Bunga: {{ $loan->interest_rate }}% (Total: Rp {{ number_format($loan->total_payable, 0, ',', '.') }})</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $loan->term_months }} Bulan</div>
                                <div class="text-[11px] text-slate-500">Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }} /bln</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-black text-xs {{ $loan->remaining_amount > 0 ? 'text-blue-700' : 'text-emerald-600' }}">
                                    Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($loan->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu Persetujuan
                                    </span>
                                @elseif($loan->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Aktif Berjalan
                                    </span>
                                @elseif($loan->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check-check" class="w-3 h-3"></i> Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($loan->status === 'pending')
                                        <form action="{{ route('company.loans.approve', $loan->id) }}" method="POST" onsubmit="return confirm('Setujui dan cairkan pinjaman ini?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 font-bold transition text-xs border border-emerald-200">
                                                Setujui & Cairkan
                                            </button>
                                        </form>
                                    @endif

                                    <button 
                                        @click="openInstallmentModal = true; 
                                                selectedLoanNumber = '{{ $loan->loan_number }}'; 
                                                selectedEmployeeName = '{{ addslashes($loan->employee?->full_name) }}';
                                                installments = {{ Js::from($loan->installments) }};"
                                        class="px-2.5 py-1 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-bold transition text-xs border border-slate-200">
                                        Jadwal Cicilan ({{ $loan->installments->count() }})
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i data-lucide="wallet" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Belum ada pengajuan pinjaman atau kasbon.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($loans->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $loans->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Detail Installment Schedule -->
    <div x-show="openInstallmentModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openInstallmentModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i>
                        Jadwal Angsuran Pinjaman
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5"><span x-text="selectedLoanNumber"></span> &bull; <span x-text="selectedEmployeeName"></span></p>
                </div>
                <button @click="openInstallmentModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="mt-4 max-h-72 overflow-y-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">Ke-</th>
                            <th class="py-2.5 px-3">Jatuh Tempo</th>
                            <th class="py-2.5 px-3">Jumlah Angsuran</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="item in installments" :key="item.id">
                            <tr>
                                <td class="py-2 px-3 font-bold" x-text="'#' + item.installment_number"></td>
                                <td class="py-2 px-3" x-text="new Date(item.due_date).toLocaleDateString('id-ID')"></td>
                                <td class="py-2 px-3 font-bold text-slate-900" x-text="'Rp ' + Number(item.amount).toLocaleString('id-ID')"></td>
                                <td class="py-2 px-3">
                                    <template x-if="item.status === 'paid' || item.status === 'deducted_from_payroll'">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>
                                    </template>
                                    <template x-if="item.status === 'pending'">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Belum Bayar</span>
                                    </template>
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <template x-if="item.status === 'pending'">
                                        <form :action="'{{ url('loans/installments') }}/' + item.id + '/pay'" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 text-[10px] font-bold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                                                Bayar
                                            </button>
                                        </form>
                                    </template>
                                    <template x-if="item.status !== 'pending'">
                                        <span class="text-[10px] text-slate-400">&check;</span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button" @click="openInstallmentModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Create Loan -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openCreateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-red-600"></i>
                    Ajukan Pinjaman / Kasbon Baru
                </div>
                <button @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.loans.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan Pemohon --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->department?->name ?? 'Dept' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipe Fasilitas <span class="text-red-500">*</span></label>
                        <select name="loan_type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="salary_advance">Kasbon Gaji (Salary Advance)</option>
                            <option value="emergency">Dana Darurat Medis / Keluarga</option>
                            <option value="education">Bantuan Biaya Pendidikan</option>
                            <option value="housing">Renovasi / DP Perumahan</option>
                            <option value="other">Kebutuhan Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jumlah Pokok (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" step="50000" min="100000" name="amount" required placeholder="Contoh: 3000000" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tenor Angsuran (Bulan) <span class="text-red-500">*</span></label>
                        <input type="number" min="1" max="24" name="term_months" value="3" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Suku Bunga (%) / Thn</label>
                        <input type="number" step="0.1" min="0" max="50" name="interest_rate" value="0" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <span class="text-[10px] text-slate-400">0% untuk fasilitas kasbon internal</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Pengajuan Pinjaman <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="Tuliskan keperluan mendesak penggunaan dana..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#DC2626] hover:bg-[#991B1B] rounded-xl shadow-sm transition">
                        Ajukan Pinjaman
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
