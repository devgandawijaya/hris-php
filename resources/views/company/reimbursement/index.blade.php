@extends('layouts.app')

@section('title', 'Klaim Reimbursement & Beban Biaya')

@section('content')
<div class="space-y-6" x-data="{ openClaimModal: false, rejectModal: false, rejectActionUrl: '' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Klaim Reimbursement & Biaya</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola pengajuan klaim biaya operasional, medis, transportasi, dan perjalanan dinas karyawan.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openClaimModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Ajukan Klaim Reimbursement
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Menunggu Persetujuan</div>
                <div class="text-2xl font-black text-amber-600 mt-1">Rp {{ number_format($stats['pending_total'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Tahap Manager / Finance</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Siap Dicairkan / Masuk Payroll</div>
                <div class="text-2xl font-black text-blue-600 mt-1">Rp {{ number_format($stats['approved_finance_total'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Disetujui Finance</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Klaim Terbayar</div>
                <div class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($stats['paid_total'], 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Telah ditransfer / lunas</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-check" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <h2 class="text-base font-bold text-slate-900">Daftar Pengajuan Reimbursement</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('company.reimbursements.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
                <a href="{{ route('company.reimbursements.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'pending' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Pending</a>
                <a href="{{ route('company.reimbursements.index', ['status' => 'approved_manager']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'approved_manager' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Review Finance</a>
                <a href="{{ route('company.reimbursements.index', ['status' => 'approved_finance']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'approved_finance' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Siap Cair</a>
                <a href="{{ route('company.reimbursements.index', ['status' => 'paid']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'paid' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Lunas</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Kategori & Judul Klaim</th>
                        <th class="px-5 py-3.5 text-right font-black text-slate-900">Nominal Klaim</th>
                        <th class="px-5 py-3.5 text-center">Bukti Nota</th>
                        <th class="px-5 py-3.5">Status Persetujuan</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reimbursements as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 text-slate-600 font-medium">
                                {{ $item->expense_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $item->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $item->employee->department?->name ?? 'Staf' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase
                                        @if($item->category === 'medical') bg-blue-50 text-blue-700
                                        @elseif($item->category === 'transport') bg-emerald-50 text-emerald-700
                                        @elseif($item->category === 'business_trip') bg-purple-50 text-purple-700
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $item->category }}
                                    </span>
                                    <span class="font-bold text-slate-900">{{ $item->title }}</span>
                                </div>
                                @if($item->description)
                                    <div class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right font-black text-slate-900">
                                Rp {{ number_format($item->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($item->receipt_path)
                                    <a href="{{ asset('storage/' . $item->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-red-600 hover:text-red-800 font-bold">
                                        <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                                        <span>Lihat Nota</span>
                                    </a>
                                @else
                                    <span class="text-slate-300 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($item->status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Lunas (Paid)
                                    </span>
                                @elseif($item->status === 'approved_finance')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Siap Dicairkan (Finance Approved)
                                    </span>
                                @elseif($item->status === 'approved_manager')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Disetujui Manajer (Review Finance)
                                    </span>
                                @elseif($item->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Ditolak
                                    </span>
                                    @if($item->rejection_reason)
                                        <div class="text-[10px] text-rose-600 mt-0.5">{{ $item->rejection_reason }}</div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        Menunggu Persetujuan
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($item->status === 'pending')
                                        <!-- Step 1: Manager Approve -->
                                        <form action="{{ route('company.reimbursements.approve-manager', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-600 text-white font-bold text-[10px] hover:bg-amber-700 transition" title="Persetujuan Manajer">
                                                Setujui Manajer
                                            </button>
                                        </form>
                                    @elseif($item->status === 'approved_manager')
                                        <!-- Step 2: Finance Approve -->
                                        <form action="{{ route('company.reimbursements.approve-finance', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-blue-600 text-white font-bold text-[10px] hover:bg-blue-700 transition" title="Persetujuan Finance">
                                                Setujui Finance
                                            </button>
                                        </form>
                                    @elseif($item->status === 'approved_finance')
                                        <!-- Step 3: Direct Pay -->
                                        <form action="{{ route('company.reimbursements.pay', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Tandai reimbursement ini telah dibayarkan tunai/transfer langsung?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-bold text-[10px] hover:bg-emerald-700 transition" title="Cairkan Langsung">
                                                Cairkan
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($item->status, ['pending', 'approved_manager']))
                                        <!-- Reject Trigger -->
                                        <button @click="rejectActionUrl = '{{ route('company.reimbursements.reject', $item->id) }}'; rejectModal = true" class="px-2.5 py-1 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-[10px] transition">
                                            Tolak
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="receipt" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Belum ada data pengajuan reimbursement.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol <strong>"Ajukan Klaim Reimbursement"</strong> di atas untuk membuat pengajuan baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reimbursements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $reimbursements->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Buat Klaim Baru -->
    <div x-show="openClaimModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openClaimModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Ajukan Reimbursement Baru</h3>
                <button @click="openClaimModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.reimbursements.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan Pemohon --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Biaya <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="medical">Biaya Medis & Obat</option>
                            <option value="transport">Transportasi</option>
                            <option value="meal">Uang Makan Lembur</option>
                            <option value="business_trip">Perjalanan Dinas</option>
                            <option value="operational">Operasional Kantor</option>
                            <option value="other">Lain-lain</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengeluaran <span class="text-red-500">*</span></label>
                        <input type="date" name="expense_date" required value="{{ now()->toDateString() }}"
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul / Keperluan Klaim <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Tiket Kereta Dinas Bandung, Pembelian Obat Flu"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Biaya (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" min="1000" step="500" required placeholder="Contoh: 350000"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lampiran Foto Nota / Kuitansi (PDF/JPG/PNG)</label>
                    <input type="file" name="receipt" accept="image/*,application/pdf"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Tambahan</label>
                    <textarea name="description" rows="2" placeholder="Catatan opsional..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openClaimModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Ajukan Klaim
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tolak Klaim -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="rejectModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-bold text-slate-900 mb-2">Tolak Pengajuan Reimbursement</h3>
            <p class="text-xs text-slate-500 mb-4">Berikan alasan penolakan agar karyawan dapat memahami atau merevisi bukti pengeluaran.</p>

            <form :action="rejectActionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Nota tidak jelas / tidak terlampir kuitansi resmi..."
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition shadow-sm">
                        Tolak Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

