@extends('layouts.app')

@section('title', 'Cafeteria Plan & Flexible Benefits')

@section('content')
<div class="space-y-6" x-data="{ allocModal: false, claimModal: false, catModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Sistem Tunjangan Fleksibel (Cafeteria Plan)
                </span>
                <span class="text-xs text-slate-400">• UU No. 13/2003 jo. UU Cipta Kerja Pasal 100</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Manajemen Benefit Fleksibel & Klaim Poin Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Pemberian plafon benefit tahunan yang dapat disesuaikan kebutuhan pekerja (kesehatan kacamata/gigi, wellness/gym, pendidikan anak, dan transportasi hijau).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="catModal = true" class="px-3 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="tag" class="w-4 h-4"></i>
                Kategori Baru
            </button>
            <button @click="allocModal = true" class="px-3 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-white hover:bg-slate-900 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="award" class="w-4 h-4"></i>
                Alokasi Poin Plafon
            </button>
            <button @click="claimModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Klaim Reimbursement
            </button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Plafon Alokasi (Tahun {{ $selectedYear }})</span>
            <div class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($stats['total_allocated_points'], 0, ',', '.') }}</div>
            <span class="text-[11px] text-emerald-600 font-medium">1 Poin = Rp 1 (Plafon Kesejahteraan)</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Poin Terpakai</span>
            <div class="text-xl font-black text-rose-600 mt-1">Rp {{ number_format($stats['total_points_used'], 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 font-medium">Realisasi klaim terverifikasi</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Pengajuan Klaim</span>
            <div class="text-xl font-black text-indigo-600 mt-1">{{ $stats['total_claims_count'] }} Berkas</div>
            <span class="text-[11px] text-indigo-500 font-medium">Reimbursement kuitansi valid</span>
        </div>
    </div>

    <!-- Category Breakdown Badges -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="layers" class="w-4 h-4 text-slate-500"></i>
            Kategori Benefit & Realisasi Penyerapan Dana
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            @forelse($categoryBreakdown as $cat)
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                    <span class="text-[11px] font-bold text-slate-700 block truncate">{{ $cat['name'] }}</span>
                    <span class="text-xs font-black text-slate-900 block">Rp {{ number_format($cat['total_claimed'], 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $cat['code'] }}</span>
                </div>
            @empty
                <div class="text-xs text-slate-400 col-span-5">Belum ada kategori benefit terdaftar.</div>
            @endforelse
        </div>
    </div>

    <!-- Recent Claims Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-3 p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="file-check" class="w-4 h-4 text-emerald-600"></i>
                Daftar Klaim Penggantian Biaya (Reimbursement)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">No. Klaim</th>
                        <th class="py-3 px-3">Karyawan</th>
                        <th class="py-3 px-3">Kategori</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Nominal / Poin</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentClaims as $claim)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">{{ $claim->claim_number }}</td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $claim->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $claim->employee->nik }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $claim->category->name }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-500">{{ \Carbon\Carbon::parse($claim->claim_date)->format('d M Y') }}</td>
                            <td class="py-3 px-3 font-bold text-slate-900">Rp {{ number_format($claim->claim_amount, 0, ',', '.') }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $claim->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($claim->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $claim->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($claim->status === 'submitted')
                                    <div class="inline-flex items-center gap-1.5">
                                        <form action="{{ route('flex-benefits.claims.approve', $claim) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                                Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('flex-benefits.claims.reject', $claim) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-100 text-rose-700 hover:bg-rose-200 transition">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">Belum ada klaim reimbursement yang diajukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $recentClaims->links() }}
        </div>
    </div>

    <!-- Allocations List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-3">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="wallet-cards" class="w-4 h-4 text-indigo-600"></i>
            Plafon Poin Flexi-Benefit Karyawan (Tahun {{ $selectedYear }})
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Karyawan</th>
                        <th class="py-3 px-3">Departemen & Jabatan</th>
                        <th class="py-3 px-3">Total Plafon</th>
                        <th class="py-3 px-3">Poin Terpakai</th>
                        <th class="py-3 px-3">Sisa Plafon Poin</th>
                        <th class="py-3 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($allocations as $alloc)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $alloc->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $alloc->employee->nik }}</span>
                            </td>
                            <td class="py-3 px-3 text-slate-500">
                                <span>{{ $alloc->employee->department->name ?? '-' }}</span> • <span>{{ $alloc->employee->position->name ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3 font-bold text-slate-900">Rp {{ number_format($alloc->total_points_allocated, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-rose-600 font-semibold">Rp {{ number_format($alloc->points_used, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-emerald-600 font-bold">Rp {{ number_format($alloc->points_remaining, 0, ',', '.') }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $alloc->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $alloc->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Belum ada alokasi poin untuk tahun ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $allocations->links() }}
        </div>
    </div>

    <!-- Modal: Alokasi Poin -->
    <div x-show="allocModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Alokasikan Plafon Poin Fleksibel</h3>
                <button @click="allocModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('flex-benefits.allocations.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Karyawan</label>
                    <select name="employee_id" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tahun Periode</label>
                    <input type="number" name="period_year" value="{{ date('Y') }}" required class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Total Poin Plafon (1 Poin = Rp 1)</label>
                    <input type="number" step="100000" name="total_points_allocated" value="5000000" required class="w-full rounded-xl border-slate-200 text-xs">
                    <span class="text-[10px] text-slate-400">Standar default: Rp 5.000.000 per tahun per karyawan.</span>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="allocModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Simpan Alokasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Pengajuan Klaim -->
    <div x-show="claimModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Ajukan Klaim Reimbursement Benefit</h3>
                <button @click="claimModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('flex-benefits.claims.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Alokasi Karyawan</label>
                    <select name="flex_benefit_allocation_id" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Alokasi Karyawan --</option>
                        @foreach($allocations as $al)
                            <option value="{{ $al->id }}">{{ $al->employee->full_name }} (Sisa: Rp {{ number_format($al->points_remaining, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori Benefit</label>
                    <select name="flex_benefit_category_id" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Kuitansi / Kwitansi</label>
                    <input type="date" name="claim_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nominal Klaim (Rp)</label>
                    <input type="number" step="1000" name="claim_amount" required placeholder="Contoh: 750000" class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan / Deskripsi Tagihan</label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border-slate-200 text-xs" placeholder="Kuitansi pemeriksaan gigi, kacamata, atau kursus..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="claimModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Kirim Klaim</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Tambah Kategori -->
    <div x-show="catModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Tambah Kategori Benefit Fleksibel</h3>
                <button @click="catModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('flex-benefits.categories.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Kategori</label>
                    <input type="text" name="name" required placeholder="Contoh: Kacamata & Lensa Kontak" class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Identifikasi</label>
                    <input type="text" name="code" required placeholder="optical_dental, wellness_gym..." class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Keterangan / Batasan</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl border-slate-200 text-xs" placeholder="Penjelasan ruang lingkup reimbursement..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="catModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-semibold hover:bg-black">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
