@extends('layouts.app')

@section('title', 'Jaminan Kehilangan Pekerjaan (JKP BPJS TK)')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Jaring Pengaman Sosial Ketenagakerjaan
                </span>
                <span class="text-xs text-slate-400">• PP No. 37 Tahun 2021</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Jaminan Kehilangan Pekerjaan (JKP BPJS TK)</h1>
            <p class="text-xs text-slate-500 mt-1">Fasilitasi penerbitan surat bukti PHK, sertifikasi hak klaim JKP (uang tunai, akses pasar kerja, dan pelatihan kerja SiapKerja Kemnaker).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Sertifikasi Klaim JKP Baru
            </button>
        </div>
    </div>

    <!-- Statutory Mandate & Benefit Formula Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-900 flex items-start gap-3">
        <i data-lucide="shield-check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Manfaat JKP (PP No. 37 Tahun 2021):</span>
            <ul class="list-disc list-inside space-y-0.5 text-blue-800">
                <li>Berlaku bagi pekerja yang mengalami PHK non-sukarela (efisiensi, penutupan, kepailitan); <strong>pekerja resign tidak berhak JKP</strong> (Pasal 19 ayat 3).</li>
                <li>Syarat masa iur kepesertaan BPJS Ketenagakerjaan: minimal <strong>12 bulan dalam 24 bulan</strong> dan membayar iuran minimal <strong>6 bulan berturut-turut</strong>.</li>
                <li>Manfaat Uang Tunai: <strong>45% upah</strong> untuk 3 bulan pertama dan <strong>25% upah</strong> untuk 3 bulan berikutnya, dengan batas atas upah patokan sebesar <strong>Rp 5.000.000,-</strong> (Pasal 21 ayat 3).</li>
            </ul>
        </div>
    </div>

    <!-- Certified Claims Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Sertifikasi Klaim JKP bagi Pekerja Ter-PHK</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Nomor Sertifikat / Tanggal PHK</th>
                        <th class="px-4 py-3 font-bold">Nama Karyawan / No. KPJ</th>
                        <th class="px-4 py-3 font-bold">Alasan PHK Yuridis</th>
                        <th class="px-4 py-3 font-bold">Masa Iur (Berturut-turut)</th>
                        <th class="px-4 py-3 font-bold">Estimasi Manfaat Tunai</th>
                        <th class="px-4 py-3 font-bold">Kelayakan JKP</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($certifications as $c)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <a href="{{ route('company.jkp.show', $c->id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $c->certificate_number }}
                                </a>
                                <div class="text-[10px] text-slate-400">PHK: {{ $c->termination_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $c->employee->full_name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">KPJ: {{ $c->bpjs_tk_card_number }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ ucfirst(str_replace('_', ' ', $c->termination_reason_code)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>{{ $c->consecutive_paid_months }} bln berturut-turut</div>
                                <div class="text-[10px] text-slate-400">Total: {{ $c->total_paid_months_24m }} bln (dlm 24 bln)</div>
                            </td>
                            <td class="px-4 py-3 font-bold text-emerald-700">
                                Rp {{ number_format($c->total_potential_cash_benefit, 0, ',', '.') }}
                                <div class="text-[10px] text-slate-400 font-normal">B1-3: Rp {{ number_format($c->month_1_to_3_benefit_monthly, 0, ',', '.') }}/bln</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($c->eligible_for_jkp)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Memenuhi Syarat
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Masa Iur Kurang
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <a href="{{ route('company.jkp.show', $c->id) }}" class="px-2 py-1 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition inline-block text-[11px] font-semibold">
                                    Detail
                                </a>
                                <a href="{{ route('company.jkp.print', $c->id) }}" target="_blank" class="px-2 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 transition inline-block text-[11px] font-semibold">
                                    Cetak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                Belum ada sertifikasi klaim JKP yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Sertifikasi Klaim JKP -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="createModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Sertifikasi Klaim Manfaat JKP</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.jkp.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan Ter-PHK</label>
                    <select name="employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }}) - Gaji: Rp {{ number_format($e->basic_salary, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Efektif PHK</label>
                        <input type="date" name="termination_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan PHK Statutori</label>
                        <select name="termination_reason_code" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                            <option value="efisiensi_merger">Efisiensi / Penggabungan Perusahaan</option>
                            <option value="perusahaan_tutup">Perusahaan Tutup (Kerugian Kontinu)</option>
                            <option value="pailit">Perusahaan Pailit</option>
                            <option value="force_majeure">Keadaan Memaksa (Force Majeure)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Masa Iur Berturut-turut (Bln)</label>
                        <input type="number" name="consecutive_paid_months" value="6" min="1" max="120" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Total Iur dalam 24 Bln</label>
                        <input type="number" name="total_paid_months_24m" value="12" min="1" max="120" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Kartu BPJS Ketenagakerjaan (KPJ)</label>
                    <input type="text" name="bpjs_tk_card_number" placeholder="Contoh: 01928471928" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Rekomendasi Perusahaan</label>
                    <textarea name="company_recommendation_letter" rows="2" placeholder="Pekerja berhak mendapatkan akses pencairan manfaat JKP tunai dan pelatihan kerja..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Terbitkan Sertifikasi Hak JKP</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
