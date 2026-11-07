@extends('layouts.app')

@section('title', 'Pajak Natura & Kenikmatan (PMK 66/2023)')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-100 text-indigo-800 border border-indigo-200">
                    Fringe Benefits Tax
                </span>
                <span class="text-xs text-slate-400">• PMK No. 66 Tahun 2023</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Pajak Imbalan Natura & Kenikmatan</h1>
            <p class="text-xs text-slate-500 mt-1">Penilaian nilai pasar wajar fasilitas karyawan, batasan pengecualian fiskal, dan penambahan Dasar Pengenaan Pajak (DPP) PPh 21.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Catat Fasilitas Natura
            </button>
        </div>
    </div>

    <!-- Fiscal Totals Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Nilai Pasar Wajar (Gross)</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                    <i data-lucide="gift" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">Rp {{ number_format($totals['total_gross_market'], 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Total fasilitas natura diberikan bulan ini</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Batas Bebas Pajak (PMK 66)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-700">Rp {{ number_format($totals['total_non_taxable'], 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Fasilitas yang dikecualikan dari PPh 21</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Natura Objek PPh 21 (DPP)</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-rose-700">Rp {{ number_format($totals['total_taxable_natura'], 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Ditambahkan ke penghasilan bruto slip gaji</div>
        </div>
    </div>

    <!-- Table of Fringe Benefits -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Rekor Natura Karyawan (Periode {{ $selectedMonth }}/{{ $selectedYear }})</h2>
            <form action="{{ route('company.fringe-benefits.index') }}" method="GET" class="flex items-center gap-2">
                <select name="month" onchange="this.form.submit()" class="px-2.5 py-1 border border-slate-300 rounded-lg text-xs font-semibold">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>Bulan {{ $m }}</option>
                    @endfor
                </select>
                <select name="year" onchange="this.form.submit()" class="px-2.5 py-1 border border-slate-300 rounded-lg text-xs font-semibold">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Karyawan Penerima</th>
                        <th class="px-4 py-3 font-bold">Jenis Fasilitas Natura</th>
                        <th class="px-4 py-3 font-bold text-right">Nilai Pasar Wajar</th>
                        <th class="px-4 py-3 font-bold text-right">Batas Pengecualian</th>
                        <th class="px-4 py-3 font-bold text-right">Nilai Kena PPh 21</th>
                        <th class="px-4 py-3 font-bold">Status Fiskal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($benefits as $fb)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $fb->employee->full_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $fb->employee->department?->name ?? 'General' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $fb->description }}</div>
                                <div class="text-[10px] text-slate-400 capitalize">{{ str_replace('_', ' ', $fb->benefit_type) }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-slate-800">
                                Rp {{ number_format($fb->gross_market_value, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-emerald-600 font-semibold">
                                Rp {{ number_format($fb->non_taxable_threshold, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-black {{ $fb->is_taxable_object ? 'text-rose-600' : 'text-slate-400' }}">
                                Rp {{ number_format($fb->taxable_natura_value, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($fb->is_taxable_object)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Objek PPh 21</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Bebas Pajak</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Belum ada fasilitas natura yang dicatat pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $benefits->links() }}
        </div>
    </div>

    <!-- Create Natura Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Catat Fasilitas Natura / Kenikmatan (PMK 66/2023)</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('company.fringe-benefits.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Karyawan Penerima Fasilitas</label>
                    <select name="employee_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->position?->title ?? 'Staff' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Pajak</label>
                        <input type="number" name="tax_year" value="{{ date('Y') }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Masa Pajak (Bulan 1-12)</label>
                        <input type="number" name="period_month" value="{{ date('n') }}" min="1" max="12" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jenis Fasilitas PMK 66/2023</label>
                    <select name="benefit_type" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                        <option value="groceries_vouchers">Voucher Makanan / Sembako (Bebas s/d Rp 3.000.000/bln)</option>
                        <option value="housing_apartment_rental">Sewa Tempat Tinggal / Apartemen Korporat</option>
                        <option value="company_car_facility">Fasilitas Mobil Dinas Eksklusif</option>
                        <option value="club_membership">Keanggotaan Klub Olahraga / Golf</option>
                        <option value="travel_vacation_facility">Fasilitas Cuti Rekreasi / Wisata</option>
                        <option value="other">Fasilitas Kenikmatan Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Fasilitas</label>
                    <input type="text" name="description" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="e.g. Sewa Apartemen Sudirman Park Bulan November">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nilai Pasar Wajar (Rp)</label>
                        <input type="number" name="gross_market_value" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="10000000">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Batas Bebas Pajak (Rp)</label>
                        <input type="number" name="non_taxable_threshold" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="0">
                        <span class="text-[10px] text-slate-400">Kosongkan untuk memakai acuan PMK 66</span>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Justifikasi Fiskal / Catatan Rujukan</label>
                    <textarea name="fiscal_notes" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Sesuai ketentuan Pasal 3 ayat 2 PMK No. 66/2023..."></textarea>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-lg font-bold">Simpan Fasilitas</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
