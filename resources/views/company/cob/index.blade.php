@extends('layouts.app')

@section('title', 'Asuransi Komersial & CoB BPJS Kesehatan')

@section('content')
<div class="space-y-6" x-data="{ policyModal: false, claimModal: false, selectedPolicyId: null, employeeName: '', remainingLimit: 0 }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                    Perpres No. 82/2018 jo. Permenkes No. 51/2018
                </span>
                <span class="text-xs text-slate-400">• Sinergi Penjaminan BPJS & Asuransi Swasta</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Koordinasi Manfaat (CoB) BPJS Kesehatan</h1>
            <p class="text-xs text-slate-500 mt-1">Integrasi penjaminan kesehatan komersial dengan BPJS Kesehatan sebagai First Payer, melindungi selisih biaya rawat inap dan naik kelas perawatan.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="policyModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Daftarkan Polis Asuransi CoB
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-4 text-xs text-teal-900 flex items-start gap-3">
        <i data-lucide="shield-check" class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Koordinasi Manfaat / Coordination of Benefit (Perpres 82/2018):</span>
            <ul class="list-disc list-inside space-y-0.5 text-teal-800">
                <li><strong>BPJS Kesehatan sebagai First Payer:</strong> Menjamin tarif paket INA-CBGs sesuai hak kelas rawat inap peserta BPJS Kesehatan.</li>
                <li><strong>Asuransi Komersial sebagai Top-Up / Secondary Payer:</strong> Menjamin selisih biaya tambahan akibat peningkatan kelas perawatan (misal: naik ke VIP atau Eksekutif) atau biaya non-paket lainnya.</li>
                <li><strong>Efisiensi Anggaran Kesehatan:</strong> Mencegah tagihan ganda (*double claim*) sekaligus menekan beban iuran pribadi (*out-of-pocket*) karyawan hingga 0 rupiah.</li>
            </ul>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Polis Terdaftar</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['total_policies'] }} Polis</div>
            <span class="text-[11px] text-slate-500 font-medium">Asuransi komersial top-up</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Plafon Proteksi</span>
            <div class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($stats['total_coverage_pool'], 0, ',', '.') }}</div>
            <span class="text-[11px] text-teal-600 font-medium">Pool proteksi tahunan</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Total Pemanfaatan Klaim</span>
            <div class="text-xl font-black text-rose-600 mt-1">Rp {{ number_format($stats['total_usage_amount'], 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 font-medium">Realisasi top-up RS</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs text-slate-400 font-semibold uppercase">Peserta Terproteksi Aktif</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ $stats['active_insured_count'] }} Karyawan</div>
            <span class="text-[11px] text-emerald-500 font-medium">Status polis aktif</span>
        </div>
    </div>

    <!-- Table of Policies -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="shield" class="w-4 h-4 text-teal-600"></i>
            Daftar Kepesertaan Asuransi Tambahan CoB BPJS
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Karyawan</th>
                        <th class="py-3 px-3">No. Polis & Asuransi</th>
                        <th class="py-3 px-3">No. Kartu BPJS</th>
                        <th class="py-3 px-3">Plan / Kelas</th>
                        <th class="py-3 px-3">Plafon Tahunan</th>
                        <th class="py-3 px-3">Pemakaian</th>
                        <th class="py-3 px-3">Sisa Plafon</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($policies as $p)
                        @php
                            $remaining = max(0, (float)$p->annual_limit_amount - (float)$p->current_usage_amount);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $p->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $p->employee->nik }} • {{ $p->employee->department->name ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">{{ $p->insurance_company_name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $p->policy_number }}</span>
                            </td>
                            <td class="py-3 px-3 font-mono font-semibold text-slate-700">{{ $p->bpjs_health_card_number }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-100">
                                    {{ $p->plan_type }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-bold text-slate-900">Rp {{ number_format($p->annual_limit_amount, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-rose-600 font-semibold">Rp {{ number_format($p->current_usage_amount, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-emerald-600 font-bold">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $p->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <button @click="selectedPolicyId = {{ $p->id }}; employeeName = '{{ addslashes($p->employee->full_name) }}'; remainingLimit = {{ $remaining }}; claimModal = true"
                                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-teal-600 text-white hover:bg-teal-700 transition inline-flex items-center gap-1">
                                    <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                                    Klaim CoB
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-slate-400">Belum ada data polis asuransi komersial CoB terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $policies->links() }}
        </div>
    </div>

    <!-- Modal Daftarkan Polis Baru -->
    <div x-show="policyModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Pendaftaran Polis Asuransi Tambahan CoB</h3>
                <button @click="policyModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('cob-insurance.store') }}" method="POST" class="space-y-4 text-xs">
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
                    <label class="block font-semibold text-slate-700 mb-1">Nama Perusahaan Asuransi Komersial</label>
                    <input type="text" name="insurance_company_name" required placeholder="Contoh: PT Asuransi Allianz Life Indonesia" class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Polis Asuransi</label>
                        <input type="text" name="policy_number" required placeholder="POL-9928172" class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">No. Kartu BPJS Kesehatan</label>
                        <input type="text" name="bpjs_health_card_number" required placeholder="0001234567890" class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Plan / Tipe Paket</label>
                        <input type="text" name="plan_type" value="Platinum Executive" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Plafon Tahunan (Rp)</label>
                        <input type="number" step="1000000" name="annual_limit_amount" value="100000000" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Mekanisme Klaim CoB</label>
                    <select name="cob_claim_mechanism" class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="bpjs_first_payer">BPJS First Payer + Asuransi Swasta Top-up</option>
                        <option value="direct_cashless">Direct Cashless Hospital Network</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="policyModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Simpan Polis CoB</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Koordinasi Klaim RS -->
    <div x-show="claimModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Koordinasi Manfaat Klaim RS (CoB)</h3>
                <button @click="claimModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('cob-insurance.coordinate-claim') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="cob_policy_id" :value="selectedPolicyId">
                <div class="p-3 bg-teal-50 border border-teal-200 rounded-xl text-teal-900 space-y-0.5">
                    <span class="block font-bold text-xs" x-text="'Pasien: ' + employeeName"></span>
                    <span class="block text-[11px] text-teal-700" x-text="'Sisa Plafon Asuransi Top-up: Rp ' + new Intl.NumberFormat('id-ID').format(remainingLimit)"></span>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Total Biaya Perawatan RS (Rp)</label>
                    <input type="number" step="1000" name="total_hospital_bill" required placeholder="Contoh: 15000000" class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Porsi Ditanggung BPJS (INA-CBGs) (Rp)</label>
                    <input type="number" step="1000" name="bpjs_covered_amount" required placeholder="Contoh: 9000000" class="w-full rounded-xl border-slate-200 text-xs">
                    <span class="text-[10px] text-slate-400">Selisih biaya akan otomatis ditalangi oleh polis asuransi top-up.</span>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="claimModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-teal-600 text-white font-semibold hover:bg-teal-700">Proses Koordinasi CoB</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
