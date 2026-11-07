@extends('layouts.app')

@section('title', 'Emisi Karbon Komuter Karyawan (Scope 3 GHG Protocol)')

@section('content')
<div class="space-y-6" x-data="{ profileModal: false, auditModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                    Protokol GRK Scope 3 (Kategori 7: Employee Commuting)
                </span>
                <span class="text-xs text-slate-400">• Dekarbonisasi Mobilitas & Penghematan FWA</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Jejak Karbon Komuter Karyawan & Audit WFH</h1>
            <p class="text-xs text-slate-500 mt-1">Pelacakan emisi gas rumah kaca harian perjalanan rumah-kantor berdasarkan moda transportasi dan reduksi emisi berkat skema kerja hybrid (WFH/FWA).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="auditModal = true" class="px-3 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                Jalankan Audit Emisi Tahunan
            </button>
            <button @click="profileModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Catat Profil Komuter Karyawan
            </button>
        </div>
    </div>

    <!-- Emission Factor Guide -->
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-4 text-xs text-teal-900 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Faktor Emisi Baku Perjalanan (DEFRA / IPCC Standard):</span>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 pt-1 text-[11px] font-mono">
                <div class="bg-white/80 p-2 rounded-lg border border-teal-200">Mobil Bensin: <strong>0.192</strong> kg/km</div>
                <div class="bg-white/80 p-2 rounded-lg border border-teal-200">Motor Bensin: <strong>0.103</strong> kg/km</div>
                <div class="bg-white/80 p-2 rounded-lg border border-teal-200">Mobil Listrik (EV): <strong>0.053</strong> kg/km</div>
                <div class="bg-white/80 p-2 rounded-lg border border-teal-200">KRL/MRT/Bus: <strong>0.041</strong> kg/km</div>
                <div class="bg-white/80 p-2 rounded-lg border border-teal-200">Sepeda / Jalan Kaki: <strong>0.000</strong> kg/km</div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Partisipasi Profil</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['total_participating'] }} Karyawan</div>
            <span class="text-[11px] text-slate-500 font-medium">Terpetakan moda transportasinya</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Emisi Mingguan Riil</span>
            <div class="text-xl font-black text-rose-600 mt-1">{{ number_format($stats['total_weekly_emissions_kg'], 1) }} kg CO2e</div>
            <span class="text-[11px] text-slate-500 font-medium">Dari hari operasional WFO</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Emisi Dihindari (WFH/FWA)</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ number_format($stats['total_weekly_avoided_kg'], 1) }} kg CO2e</div>
            <span class="text-[11px] text-emerald-600 font-medium font-bold">Hemat jejak karbon per minggu</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Net Emisi Audit {{ $selectedYear }}</span>
            <div class="text-xl font-black text-indigo-600 mt-1">
                {{ $latestAudit ? number_format($latestAudit->net_commute_emissions_ton_co2e, 2) . ' Ton' : '0.00 Ton' }}
            </div>
            <span class="text-[11px] text-indigo-500 font-medium">Scope 3 GHG Protocol</span>
        </div>
    </div>

    <!-- Annual Audits Table -->
    @if($audits->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-3">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="archive" class="w-4 h-4 text-teal-600"></i>
                Riwayat Audit Emisi Karbon Komuter Tahunan (Scope 3)
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                        <tr>
                            <th class="py-3 px-3">Tahun Audit</th>
                            <th class="py-3 px-3">Partisipan</th>
                            <th class="py-3 px-3">Total Jarak Komuter (Km)</th>
                            <th class="py-3 px-3">Emisi Bruto (Tanpa WFH)</th>
                            <th class="py-3 px-3">Emisi Dihindari (WFH)</th>
                            <th class="py-3 px-3">Net Emisi Riil</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($audits as $audit)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3 font-bold text-slate-900">Tahun {{ $audit->audit_year }}</td>
                                <td class="py-3 px-3">{{ $audit->participating_employees_count }} Karyawan</td>
                                <td class="py-3 px-3 font-mono">{{ number_format($audit->total_annual_commute_distance_km, 0, ',', '.') }} Km</td>
                                <td class="py-3 px-3 text-rose-600 font-semibold">{{ number_format($audit->gross_emissions_ton_co2e, 2) }} Ton CO2e</td>
                                <td class="py-3 px-3 text-emerald-600 font-bold">- {{ number_format($audit->avoided_emissions_wfh_ton_co2e, 2) }} Ton CO2e</td>
                                <td class="py-3 px-3 font-black text-slate-900">{{ number_format($audit->net_commute_emissions_ton_co2e, 2) }} Ton CO2e</td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                        {{ $audit->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Profiles Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="navigation" class="w-4 h-4 text-emerald-600"></i>
            Daftar Profil Komuter Karyawan
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Karyawan</th>
                        <th class="py-3 px-3">Jarak 1-Arah</th>
                        <th class="py-3 px-3">Moda Transportasi</th>
                        <th class="py-3 px-3">Jadwal WFO / WFH</th>
                        <th class="py-3 px-3">Emisi Mingguan</th>
                        <th class="py-3 px-3">Emisi Dihindari WFH</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($profiles as $prof)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $prof->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $prof->employee->department->name ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3 font-mono font-semibold">{{ $prof->home_to_office_distance_km }} Km</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $prof->primary_commute_mode === 'public_transit' || $prof->primary_commute_mode === 'electric_vehicle' || $prof->primary_commute_mode === 'bicycle_walking' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ str_replace('_', ' ', $prof->primary_commute_mode) }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800">{{ $prof->weekly_office_days }} Hari WFO</span> •
                                <span class="text-emerald-600 font-semibold">{{ $prof->weekly_remote_days }} Hari WFH</span>
                            </td>
                            <td class="py-3 px-3 font-bold text-rose-600">{{ number_format($prof->weekly_emissions_kg_co2, 2) }} kg CO2e</td>
                            <td class="py-3 px-3 font-bold text-emerald-600">{{ number_format($prof->weekly_avoided_emissions_kg_co2, 2) }} kg CO2e</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Belum ada data profil komuter karyawan yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $profiles->links() }}
        </div>
    </div>

    <!-- Modal Catat Profil Komuter -->
    <div x-show="profileModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Catat Profil Komuter Karyawan</h3>
                <button @click="profileModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('carbon-commute.profiles.store') }}" method="POST" class="space-y-4 text-xs">
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
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jarak 1-Arah Rumah-Kantor (Km)</label>
                        <input type="number" step="0.1" name="home_to_office_distance_km" value="15.0" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Moda Transportasi Utama</label>
                        <select name="primary_commute_mode" required class="w-full rounded-xl border-slate-200 text-xs">
                            <option value="public_transit">KRL / MRT / Bus TransJakarta</option>
                            <option value="motorcycle_gasoline">Sepeda Motor Bensin</option>
                            <option value="car_gasoline">Mobil Bensin Pribadi</option>
                            <option value="electric_vehicle">Kendaraan Listrik (EV)</option>
                            <option value="bicycle_walking">Sepeda Kayuh / Jalan Kaki</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Hari Kerja di Kantor (WFO/minggu)</label>
                        <input type="number" name="weekly_office_days" value="3" min="0" max="7" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Hari Kerja Remote (WFH/minggu)</label>
                        <input type="number" name="weekly_remote_days" value="2" min="0" max="7" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="profileModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Jalankan Audit Tahunan -->
    <div x-show="auditModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Jalankan Audit Emisi Scope 3 Tahunan</h3>
                <button @click="auditModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('carbon-commute.audit.run') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tahun Audit</label>
                    <input type="number" name="audit_year" value="{{ date('Y') }}" required class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <p class="text-[11px] text-slate-500">Sistem mengagregasi seluruh profil komuter aktif selama 50 minggu kerja setahun dan menghitung emisi yang berhasil dihemat oleh FWA/WFH.</p>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="auditModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-teal-600 text-white font-semibold hover:bg-teal-700">Kalkulasi Audit</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
