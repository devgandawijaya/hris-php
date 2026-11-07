@extends('layouts.app')

@section('title', 'Detail SPPD ' . $order->sppd_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('company.business-trips.index') }}" class="text-slate-400 hover:text-slate-600">← Kembali</a>
                <h1 class="text-2xl font-bold text-slate-800">SPPD {{ $order->sppd_number }}</h1>
                @php
                    $statusMap = [
                        'draft' => ['Draft', 'bg-gray-100 text-gray-700'],
                        'submitted' => ['Menunggu Persetujuan', 'bg-amber-100 text-amber-700'],
                        'approved' => ['Disetujui', 'bg-blue-100 text-blue-700'],
                        'rejected' => ['Ditolak', 'bg-rose-100 text-rose-700'],
                        'ongoing' => ['Sedang Berlangsung', 'bg-indigo-100 text-indigo-700'],
                        'completed' => ['Selesai & Final', 'bg-emerald-100 text-emerald-700'],
                    ];
                    $badge = $statusMap[$order->status] ?? ['Lainnya', 'bg-slate-100 text-slate-700'];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge[1] }}">{{ $badge[0] }}</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Tujuan: <strong>{{ $order->destination_city }}</strong> ({{ $order->departure_date->format('d M Y') }} s.d. {{ $order->return_date->format('d M Y') }} - {{ $order->duration_days }} Hari)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('company.business-trips.print', $order) }}" target="_blank" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium hover:bg-slate-900 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Lembar SPPD Resmi
            </a>
        </div>
    </div>

    <!-- Overview Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info Pegawai & Penugasan -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-2">Informasi Pegawai & Tugas</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">Nama Pegawai:</span>
                    <span class="font-medium text-slate-800">{{ $order->employee->full_name }} ({{ $order->employee->employee_number }})</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Jabatan & Departemen:</span>
                    <span class="text-slate-700">{{ $order->employee->position ? $order->employee->position->title : '-' }} / {{ $order->employee->department ? $order->employee->department->name : '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Golongan Upah (Job Grade):</span>
                    <span class="text-slate-700 font-medium">{{ $order->employee->jobGrade ? $order->employee->jobGrade->grade_code . ' (' . $order->employee->jobGrade->name . ')' : 'Standar' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Maksud Kedinasan:</span>
                    <span class="text-slate-700 font-medium">{{ $order->purpose }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Moda Transportasi:</span>
                    <span class="text-slate-700 capitalize">{{ str_replace('_', ' ', $order->transport_type) }}</span>
                </div>
                @if($order->approved_at)
                    <div>
                        <span class="text-xs text-slate-400 block">Disetujui Oleh:</span>
                        <span class="text-slate-700 font-medium">{{ $order->approver ? $order->approver->name : 'Manager' }} pada {{ $order->approved_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>

            <!-- Approval Box if submitted -->
            @if($order->status === 'submitted')
                <div class="pt-4 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Persetujuan Manajerial</h4>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('company.business-trips.approve', $order) }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="approved" value="1">
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold">Setujui SPPD</button>
                        </form>
                        <form method="POST" action="{{ route('company.business-trips.approve', $order) }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="approved" value="0">
                            <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold">Tolak</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Uang Muka & Pencairan -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-2">Uang Muka Perjalanan Dinas</h3>
            @forelse($order->advances as $adv)
                <div class="p-4 bg-slate-50 rounded-xl space-y-2 border border-slate-100">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500">Nominal Dimohon:</span>
                        <span class="font-mono font-medium text-slate-800">Rp {{ number_format($adv->amount_requested, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500">Nominal Disetujui:</span>
                        <span class="font-mono font-bold text-emerald-600">Rp {{ number_format($adv->amount_approved, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500">Status Pencairan:</span>
                        <span class="font-semibold uppercase tracking-wider {{ $adv->status === 'disbursed' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $adv->status }}</span>
                    </div>
                    @if($adv->transfer_reference)
                        <div class="text-xs text-slate-500">Ref: <span class="font-mono font-bold">{{ $adv->transfer_reference }}</span> ({{ $adv->disbursed_at ? $adv->disbursed_at->format('d/m/Y H:i') : '' }})</div>
                    @endif

                    @if($adv->status === 'approved' && in_array($order->status, ['approved', 'ongoing']))
                        <form method="POST" action="{{ route('company.business-trips.advances.disburse', $adv) }}" class="pt-2">
                            @csrf
                            <input type="text" name="transfer_reference" placeholder="Nomor Ref Transfer Bank" class="w-full text-xs border-slate-300 rounded px-2 py-1 mb-2">
                            <button type="submit" class="w-full py-1.5 bg-blue-600 text-white rounded text-xs font-semibold hover:bg-blue-700">Tandai Dana Dicairkan (Disburse)</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-400 italic">Tidak ada permohonan uang muka untuk SPPD ini.</p>
            @endforelse
        </div>

        <!-- Rekonsiliasi Settlement -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-2">Rekonsiliasi & Settlement</h3>
            @php
                $totAdv = $order->advances->whereIn('status', ['approved', 'disbursed'])->sum('amount_approved');
                $totExp = $order->expenses->sum('amount');
                $balance = $totExp - $totAdv;
            @endphp
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Uang Muka:</span>
                    <span class="font-mono font-medium text-slate-800">Rp {{ number_format($totAdv, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Realisasi Kwitansi:</span>
                    <span class="font-mono font-medium text-slate-800">Rp {{ number_format($totExp, 0, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                    <span class="text-xs font-bold uppercase tracking-wider {{ $balance >= 0 ? 'text-blue-700' : 'text-amber-700' }}">
                        {{ $balance >= 0 ? 'Reimbursement ke Pegawai:' : 'Pengembalian Sisa ke Kantor:' }}
                    </span>
                    <span class="font-mono font-bold text-base {{ $balance >= 0 ? 'text-blue-700' : 'text-amber-700' }}">
                        Rp {{ number_format(abs($balance), 0, ',', '.') }}
                    </span>
                </div>
            </div>

            @if($order->status !== 'completed')
                <form method="POST" action="{{ route('company.business-trips.settle', $order) }}" class="pt-3 border-t border-slate-100">
                    @csrf
                    <textarea name="notes" rows="2" placeholder="Catatan audit settlement keuangan..." class="w-full text-xs border-slate-300 rounded px-2 py-1 mb-2"></textarea>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold">
                        Selesaikan & Finalisasi Settlement
                    </button>
                </form>
            @else
                <div class="p-3 bg-emerald-50 rounded-lg text-xs text-emerald-800 font-medium">
                    ✓ Rekonsiliasi telah difinalisasi pada {{ $order->settlement && $order->settlement->settled_at ? $order->settlement->settled_at->format('d/m/Y H:i') : '-' }}.
                </div>
            @endif
        </div>
    </div>

    <!-- Expenses Breakdown Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-800">Rincian Realisasi Pengeluaran & Bukti Kwitansi</h3>
            <button onclick="document.getElementById('modal-add-expense').classList.remove('hidden')" class="px-3 py-1.5 bg-slate-800 text-white rounded text-xs font-medium hover:bg-slate-900">
                + Tambah Kwitansi Pengeluaran
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Deskripsi Pengeluaran</th>
                        <th class="px-5 py-3 text-right">Nominal Biaya</th>
                        <th class="px-5 py-3 text-center">Status Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($order->expenses as $exp)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3 font-mono text-xs">{{ $exp->expense_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-medium capitalize">{{ str_replace('_', ' ', $exp->expense_category) }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-800">{{ $exp->description ?? '-' }}</td>
                            <td class="px-5 py-3 text-right font-mono font-semibold text-slate-800">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">Terverifikasi</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada rincian kwitansi pengeluaran yang dicatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add Expense -->
<div id="modal-add-expense" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800">Tambah Realisasi Pengeluaran SPPD</h3>
            <button onclick="document.getElementById('modal-add-expense').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form method="POST" action="{{ route('company.business-trips.expenses.store', $order) }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Kategori Pengeluaran *</label>
                <select name="expense_category" required class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                    <option value="per_diem">Uang Saku Harian (Per Diem)</option>
                    <option value="transport">Tiket Pesawat / Kereta / Bensin</option>
                    <option value="lodging">Hotel & Akomodasi</option>
                    <option value="meals">Makan & Konsumsi</option>
                    <option value="toll_parking">Tol & Parkir</option>
                    <option value="representation">Representasi Klien / Jamuan</option>
                    <option value="other">Lain-lain</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Nominal (Rp) *</label>
                <input type="number" name="amount" required min="1" placeholder="Contoh: 750000" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Transaksi *</label>
                <input type="date" name="expense_date" required value="{{ date('Y-m-d') }}" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Keterangan / Nomor Kwitansi</label>
                <input type="text" name="description" placeholder="Contoh: Invoice Hotel Santika Premiere 2 malam" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-expense').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Simpan Kwitansi</button>
            </div>
        </form>
    </div>
</div>
@endsection
