@extends('layouts.app')

@section('title', 'Manajemen Tenaga Kerja Asing (TKA) & RPTKA')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                    Kepatuhan PP No. 34/2021
                </span>
                <span class="text-xs text-slate-400">• Izin Kerja TKA & Kemnaker</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Manajemen Tenaga Kerja Asing (TKA) & RPTKA</h1>
            <p class="text-xs text-slate-500 mt-1">Perekaman pengesahan RPTKA Kemnaker, penunjukan TKI Pendamping, kewajiban DKPTKA, dan monitoring izin tinggal (ITAS).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Daftarkan Izin TKA Baru
            </button>
        </div>
    </div>

    <!-- Statutory Alert Banner -->
    @if($expiringPermits->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-900 flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
            <div class="space-y-1">
                <span class="font-bold">Peringatan Jatuh Tempo Masa Berlaku RPTKA:</span>
                <p>Terdapat {{ $expiringPermits->count() }} izin RPTKA yang akan berakhir dalam 60 hari ke depan. Lakukan proses perpanjangan atau penerbitan EPO (*Exit Permit Only*) untuk menghindari sanksi keimigrasian.</p>
            </div>
        </div>
    @endif

    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-900 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Penggunaan TKA (PP No. 34 Tahun 2021):</span>
            <ul class="list-disc list-inside space-y-0.5 text-blue-800">
                <li>TKA <strong>dilarang menduduki jabatan personalia/HR</strong> (Pasal 46 UU Ketenagakerjaan jo. Kepmenaker 349/2019).</li>
                <li>Pemberi kerja wajib menunjuk <strong>Tenaga Kerja Indonesia (TKI) Pendamping</strong> untuk alih teknologi dan keahlian (Pasal 11 PP 34/2021).</li>
                <li>Membayar Dana Kompensasi Penggunaan TKA (<strong>DKPTKA sebesar USD 100 per bulan</strong> per jabatan) sebagai PNBP kas negara.</li>
            </ul>
        </div>
    </div>

    <!-- Permits Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Izin Kerja & Pengesahan RPTKA Aktif</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Nama TKA / No. Paspor</th>
                        <th class="px-4 py-3 font-bold">No. RPTKA / Jabatan</th>
                        <th class="px-4 py-3 font-bold">Masa Berlaku RPTKA</th>
                        <th class="px-4 py-3 font-bold">TKI Pendamping (WNI)</th>
                        <th class="px-4 py-3 font-bold">DKPTKA / PNBP</th>
                        <th class="px-4 py-3 font-bold">Status Izin</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permits as $p)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <a href="{{ route('company.expatriates.show', $p->id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $p->employee->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-400">Paspor: <span class="font-mono">{{ $p->passport_number }}</span></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $p->job_position_title }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $p->rptka_number }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>s/d {{ $p->rptka_expiry_date->format('d M Y') }}</div>
                                @if($p->isExpiringSoon(60))
                                    <span class="text-[10px] text-amber-600 font-bold block mt-0.5">Segera Berakhir (&lt; 60 Hari)</span>
                                @else
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                @if($p->companionEmployee)
                                    <div class="font-semibold text-emerald-800">{{ $p->companionEmployee->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">Alih Keahlian Aktif</div>
                                @else
                                    <span class="text-rose-600 font-bold text-[10px]">Belum Ditunjuk</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-700">
                                USD {{ number_format($p->dkptka_monthly_amount_usd, 0) }}/bln
                                <div class="text-[10px] text-slate-400">Bukti: {{ $p->dkptka_payment_proof ?? 'Tercatat' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $p->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('company.expatriates.show', $p->id) }}" class="px-2 py-1 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition inline-block text-[11px] font-semibold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                Belum ada data perizinan TKA yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Pendaftaran RPTKA Baru -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl" @click.away="createModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Pendaftaran Izin Kerja TKA & RPTKA</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.expatriates.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Karyawan (TKA)</label>
                        <select name="employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->citizenship ?? 'WNA' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan TKA (Dilarang HR)</label>
                        <input type="text" name="job_position_title" placeholder="Contoh: Chief Technical Architect" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Pengesahan RPTKA Kemnaker</label>
                        <input type="text" name="rptka_number" placeholder="Contoh: KEP-RPTKA/2026/0891" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Berakhir RPTKA</label>
                        <input type="date" name="rptka_expiry_date" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Berlaku RPTKA</label>
                        <input type="date" name="rptka_approval_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Paspor</label>
                        <input type="text" name="passport_number" placeholder="Contoh: E4819283" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Masa Berlaku Paspor</label>
                        <input type="date" name="passport_expiry_date" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">TKI / WNI Pendamping (Pasal 11)</label>
                        <select name="companion_employee_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                            <option value="">-- Pilih Rekan WNI Pendamping --</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->department->name ?? 'Dept' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Iuran DKPTKA Bulanan (USD)</label>
                        <input type="number" step="0.01" name="dkptka_monthly_amount_usd" value="100.00" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor NTPN / Bukti Bayar PNBP</label>
                        <input type="text" name="dkptka_payment_proof" placeholder="Contoh: NTPN-PNBP-2026-081" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Alih Teknologi & Pengetahuan</label>
                    <textarea name="technology_transfer_plan" rows="2" placeholder="Program pelatihan dan transfer knowledge kepada pendamping lokal..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Daftarkan Izin RPTKA</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
