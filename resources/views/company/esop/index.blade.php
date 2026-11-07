@extends('layouts.app')

@section('title', 'Employee Stock Option Plan (ESOP / RSU)')

@section('content')
<div class="space-y-6" x-data="{ grantModal: false, exerciseModal: false, selectedGrant: null, exercisableCount: 0 }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-violet-100 text-violet-800 border border-violet-200">
                    Insentif Jangka Panjang & Retensi Kunci
                </span>
                <span class="text-xs text-slate-400">• Tata Kelola UU No. 40/2007 (Perseroan Terbatas)</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Employee Stock Option Plan (ESOP / RSU)</h1>
            <p class="text-xs text-slate-500 mt-1">Penerbitan hak opsi saham karyawan, jadwal vesting 4 tahun dengan cliff 12 bulan, tracking strike price dan valuasi fair market value.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="grantModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Terbitkan ESOP Grant Baru
            </button>
        </div>
    </div>

    <!-- Legal & Mechanism Banner -->
    <div class="bg-violet-50 border border-violet-200 rounded-2xl p-4 text-xs text-violet-900 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-violet-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Mekanisme Standar Opsi Saham Korporat (ESOP / RSU):</span>
            <ul class="list-disc list-inside space-y-0.5 text-violet-800">
                <li><strong>Jadwal Vesting 4 Tahun (48 Bulan):</strong> Hak kepemilikan opsi bertambah secara proporsional selama masa kerja karyawan.</li>
                <li><strong>Masa Cliff 12 Bulan (1 Tahun):</strong> Tidak ada opsi yang vest sebelum mencapai 12 bulan masa kerja sejak tanggal grant; pada bulan ke-12 tepat, 25% dari total opsi langsung vest sekaligus.</li>
                <li><strong>Harga Pelaksanaan (Strike Price):</strong> Harga tetap yang dibayarkan karyawan saat melakukan eksekusi opsi untuk menjadi saham fisik.</li>
            </ul>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Pool Saham Diberikan</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($stats['total_shares_pool']) }} Lembar</div>
            <span class="text-[11px] text-slate-500 font-medium">Alokasi program ESOP</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Saham Telah Vested</span>
            <div class="text-xl font-black text-indigo-600 mt-1">{{ number_format($stats['total_vested_shares']) }} Lembar</div>
            <span class="text-[11px] text-indigo-500 font-medium">Memenuhi syarat cliff & tenure</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Saham Dieksekusi</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ number_format($stats['total_exercised_shares']) }} Lembar</div>
            <span class="text-[11px] text-emerald-500 font-medium">Telah ditebus oleh karyawan</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Hibah Aktif</span>
            <div class="text-xl font-black text-violet-600 mt-1">{{ $stats['total_active_grants'] }} Kontrak</div>
            <span class="text-[11px] text-slate-500 font-medium">Talenta kunci perusahaan</span>
        </div>
    </div>

    <!-- Table of Grants -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="layers" class="w-4 h-4 text-violet-600"></i>
            Daftar Kontrak Opsi Saham (ESOP Grants)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">No. Kontrak / Grant</th>
                        <th class="py-3 px-3">Karyawan Penerima</th>
                        <th class="py-3 px-3">Tgl Grant</th>
                        <th class="py-3 px-3">Total Saham</th>
                        <th class="py-3 px-3">Strike Price</th>
                        <th class="py-3 px-3">Valuasi FMV</th>
                        <th class="py-3 px-3">Vested (Saham)</th>
                        <th class="py-3 px-3">Exercised</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($grants as $grant)
                        @php
                            $available = $grant->vested_shares_count - $grant->exercised_shares_count;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">{{ $grant->grant_number }}</td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $grant->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $grant->employee->department->name ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3 text-slate-500">{{ \Carbon\Carbon::parse($grant->grant_date)->format('d M Y') }}</td>
                            <td class="py-3 px-3 font-bold text-slate-900">{{ number_format($grant->total_shares_granted) }}</td>
                            <td class="py-3 px-3 font-mono">Rp {{ number_format($grant->strike_price_per_share, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 font-mono text-slate-500">Rp {{ number_format($grant->fair_market_value, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 font-bold text-indigo-600">
                                {{ number_format($grant->vested_shares_count) }}
                                <span class="text-[10px] text-slate-400 block font-normal">({{ number_format(($grant->vested_shares_count / max(1, $grant->total_shares_granted)) * 100, 1) }}%)</span>
                            </td>
                            <td class="py-3 px-3 text-emerald-600 font-bold">{{ number_format($grant->exercised_shares_count) }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $grant->status === 'fully_vested' ? 'bg-indigo-100 text-indigo-800' : ($grant->status === 'exercised' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $grant->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <form action="{{ route('esop.refresh-vesting', $grant) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="Hitung ulang vesting sesuai tanggal hari ini" class="p-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                    @if($available > 0)
                                        <button @click="selectedGrant = {{ $grant->id }}; exercisableCount = {{ $available }}; exerciseModal = true"
                                                class="px-2 py-1 rounded-lg text-[11px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                            Exercise ({{ $available }})
                                        </button>
                                    @else
                                        <span class="text-[10px] text-slate-400 font-mono">0 Ready</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-slate-400">Belum ada kontrak opsi saham (ESOP) yang diterbitkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $grants->links() }}
        </div>
    </div>

    <!-- Modal Terbitkan ESOP -->
    <div x-show="grantModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Penerbitan Kontrak ESOP / RSU</h3>
                <button @click="grantModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('esop.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Karyawan Penerima</label>
                    <select name="employee_id" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nik }} - {{ $emp->full_name }} ({{ $emp->position->name ?? 'Staff' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Grant</label>
                        <input type="date" name="grant_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jumlah Opsi Saham</label>
                        <input type="number" name="total_shares_granted" value="10000" min="1" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Strike Price (Rp/lembar)</label>
                        <input type="number" step="100" name="strike_price_per_share" value="1000" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Fair Market Value (Rp)</label>
                        <input type="number" step="100" name="fair_market_value" value="3500" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jadwal Vesting (Tahun)</label>
                        <input type="number" name="vesting_schedule_years" value="4" min="1" max="10" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Masa Cliff (Bulan)</label>
                        <input type="number" name="cliff_months" value="12" min="0" max="36" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="grantModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Terbitkan Grant</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Exercise Opsi -->
    <div x-show="exerciseModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Eksekusi / Exercise Saham Vested</h3>
                <button @click="exerciseModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="'/esop/' + selectedGrant + '/exercise'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900">
                    <span class="block font-bold">Saham Vested yang Siap Dieksekusi:</span>
                    <span class="text-base font-black" x-text="exercisableCount + ' Lembar Saham'"></span>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Jumlah Saham yang Ditebus (Exercise)</label>
                    <input type="number" name="shares_to_exercise" :max="exercisableCount" min="1" required class="w-full rounded-xl border-slate-200 text-xs">
                    <span class="text-[10px] text-slate-400">Karyawan menyetorkan harga pelaksanaan (strike price).</span>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="exerciseModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700">Konfirmasi Eksekusi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
