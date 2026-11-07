@extends('layouts.app')

@section('title', 'Koperasi Karyawan & Simpan Pinjam')

@section('content')
<div class="space-y-6" x-data="{ memberModal: false, transactionModal: false, activeTab: 'members' }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-100 text-indigo-800 border border-indigo-200">
                    Kesejahteraan Pekerja
                </span>
                <span class="text-xs text-slate-400">• UU No. 25/1992 & Pasal 100 UU 13/2003</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Koperasi Karyawan & Dana Sosial</h1>
            <p class="text-xs text-slate-500 mt-1">Perekaman keanggotaan koperasi karyawan, iuran simpanan otomatis potong gaji (payroll deduction), pinjaman mikro, dan santunan dana sosial.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('company.cooperative.payroll-deductions.run') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm inline-flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    Proses Potong Gaji Iuran
                </button>
            </form>
            <button @click="transactionModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Catat Transaksi / Pinjaman
            </button>
            <button @click="memberModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Daftar Anggota Baru
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Anggota Koperasi</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $members->total() }}</div>
            <span class="text-[11px] text-slate-500">Anggota Terdaftar</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Simpanan Terkumpul</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalSavings, 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500">Pokok + Wajib + Sukarela</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Saldo Pinjaman Beredar</span>
            <div class="text-2xl font-black text-amber-600 mt-1">Rp {{ number_format($totalLoans, 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500">Pinjaman Simpan Pinjam</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Mutasi Transaksi</span>
            <div class="text-2xl font-black text-indigo-600 mt-1">{{ $recentTransactions->count() }}</div>
            <span class="text-[11px] text-slate-500">Transaksi Terakhir</span>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex border-b border-slate-200 text-xs font-bold gap-6">
        <button @click="activeTab = 'members'" :class="activeTab === 'members' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Daftar Anggota Koperasi ({{ $members->total() }})
        </button>
        <button @click="activeTab = 'transactions'" :class="activeTab === 'transactions' ? 'border-[#DC2626] text-[#DC2626]' : 'border-transparent text-slate-500 hover:text-slate-800'" class="pb-3 border-b-2 transition">
            Riwayat Mutasi & Simpan Pinjam ({{ $recentTransactions->count() }})
        </button>
    </div>

    <!-- Tab 1: Members Table -->
    <div x-show="activeTab === 'members'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Buku Register Anggota Koperasi Karyawan</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">No. Anggota / Tanggal Gabung</th>
                        <th class="px-4 py-3 font-bold">Karyawan / Divisi</th>
                        <th class="px-4 py-3 font-bold">Simpanan Wajib (Payroll)</th>
                        <th class="px-4 py-3 font-bold">Total Saldo Simpanan</th>
                        <th class="px-4 py-3 font-bold">Sisa Pinjaman Aktif</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($members as $m)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-800 font-mono">{{ $m->member_number }}</span>
                                <div class="text-[10px] text-slate-400">Join: {{ $m->join_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $m->employee->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $m->employee->department->name ?? 'Dept' }} • NIK: {{ $m->employee->employee_number }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div>Rp {{ number_format($m->mandatory_savings, 0, ',', '.') }} / bln</div>
                                @if($m->voluntary_savings > 0)
                                    <div class="text-[10px] text-slate-400">+ Sukarela Rp {{ number_format($m->voluntary_savings, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-black text-emerald-700">
                                Rp {{ number_format($m->total_accumulated_savings, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 font-bold {{ $m->active_loan_balance > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                Rp {{ number_format($m->active_loan_balance, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $m->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $m->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                Belum ada karyawan yang terdaftar sebagai anggota koperasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 2: Transactions Table -->
    <div x-show="activeTab === 'transactions'" style="display: none;" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Mutasi Keuangan Koperasi & Santunan Sosial</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Tanggal</th>
                        <th class="px-4 py-3 font-bold">Anggota / Karyawan</th>
                        <th class="px-4 py-3 font-bold">Jenis Transaksi</th>
                        <th class="px-4 py-3 font-bold">Nominal</th>
                        <th class="px-4 py-3 font-bold">Metode / Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransactions as $tx)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3 font-mono text-slate-600">
                                {{ $tx->transaction_date->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $tx->member->employee->full_name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $tx->member->member_number }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ in_array($tx->transaction_type, ['mandatory_saving', 'loan_repayment']) ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $tx->transaction_type)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-900">
                                Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                @if($tx->is_payroll_deducted)
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-700 mr-1">Payroll</span>
                                @endif
                                {{ $tx->reference_notes }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Belum ada riwayat transaksi koperasi yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Daftar Anggota Baru -->
    <div x-show="memberModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl" @click.away="memberModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Pendaftaran Anggota Koperasi Baru</h3>
                <button @click="memberModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.cooperative.members.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan</label>
                    <select name="employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Bergabung</label>
                    <input type="date" name="join_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Simpanan Pokok Awal (Rp)</label>
                        <input type="number" step="1000" name="principal_savings_paid" value="100000" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Simpanan Wajib Bulanan (Rp)</label>
                        <input type="number" step="1000" name="mandatory_savings" value="50000" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Simpanan Sukarela Bulanan (Opsional)</label>
                    <input type="number" step="1000" name="voluntary_savings" value="0" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="memberModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Daftarkan Anggota</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Catat Transaksi / Pinjaman / Santunan -->
    <div x-show="transactionModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl" @click.away="transactionModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Catat Transaksi Pinjaman & Santunan Sosial</h3>
                <button @click="transactionModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.cooperative.transactions.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Anggota Koperasi</label>
                    <select name="employee_cooperative_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->employee->full_name }} ({{ $m->member_number }}) - Saldo: Rp {{ number_format($m->total_accumulated_savings, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Transaksi</label>
                    <select name="transaction_type" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        <option value="loan_disbursement">Pencairan Pinjaman Koperasi</option>
                        <option value="loan_repayment">Pembayaran Angsuran Pinjaman</option>
                        <option value="welfare_aid_disbursement">Penyaluran Santunan Dana Sosial (Duka Cita/Bencana)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Transaksi (Rp)</label>
                    <input type="number" step="1000" name="amount" placeholder="Contoh: 1500000" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Keperluan Transaksi</label>
                    <input type="text" name="notes" placeholder="Contoh: Pinjaman darurat biaya pendidikan atau Santunan rawat inap" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="transactionModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
