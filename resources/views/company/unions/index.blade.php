@extends('layouts.app')

@section('title', 'Serikat Pekerja & Perjanjian Kerja Bersama (PKB)')

@section('content')
<div class="space-y-6" x-data="{ unionModal: false, pkbModal: false, checkoffModal: false, selectedUnionId: null }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-100 text-indigo-800 border border-indigo-200">
                    Kepatuhan UU No. 21/2000 & Permenaker No. 28/2014
                </span>
                <span class="text-xs text-slate-400">• Tata Kelola Hubungan Industrial Kolektif</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Serikat Pekerja / Buruh & Perundingan PKB</h1>
            <p class="text-xs text-slate-500 mt-1">Verifikasi kuota keterwakilan perundingan (>50% Pasal 119 UU 13/2003), draft PKB batas 30 hari kerja, dan sistem iuran check-off.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="unionModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Catat Serikat Pekerja Baru
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 text-xs text-indigo-900 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Perundingan PKB (Pasal 119 UU 13/2003 jo. Permenaker No. 28/2014):</span>
            <ul class="list-disc list-inside space-y-0.5 text-indigo-800">
                <li>Serikat pekerja berhak mewakili pekerja dalam perundingan PKB apabila memiliki <strong>lebih dari 50% anggota</strong> dari total seluruh pekerja di perusahaan.</li>
                <li>Jika anggota kurang dari 50%, serikat pekerja dapat berkoalisi dengan serikat lain hingga mencapai lebih dari 50% atau melalui mekanisme pemungutan suara (voting).</li>
                <li>Masa perundingan pembuatan PKB diselesaikan paling lama <strong>30 (tiga puluh) hari kerja</strong> terhitung sejak dimulainya perundingan.</li>
                <li>Masa berlaku Perjanjian Kerja Bersama adalah paling lama <strong>2 (dua) tahun</strong> dan dapat diperpanjang maksimal 1 tahun.</li>
            </ul>
        </div>
    </div>

    <!-- Unions List -->
    <div class="space-y-4">
        @forelse($unions as $union)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">{{ $union->union_name }}</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $union->has_bargaining_rights ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $union->has_bargaining_rights ? 'Hak Runding PKB Sah (>= 50%)' : 'Belum Memenuhi Kuota Runding (< 50%)' }}
                            </span>
                        </div>
                        <span class="text-xs text-slate-500 font-mono">No. Pencatatan Disnaker: {{ $union->registration_number }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="selectedUnionId = {{ $union->id }}; pkbModal = true" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 transition flex items-center gap-1.5">
                            <i data-lucide="file-signature" class="w-3.5 h-3.5"></i>
                            Mulai Perundingan PKB
                        </button>
                        <button @click="selectedUnionId = {{ $union->id }}; checkoffModal = true" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition flex items-center gap-1.5">
                            <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                            Daftar Iuran Check-off
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">Ketua Pengurus:</span>
                        <span class="font-semibold text-slate-800">{{ $union->chairperson_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Anggota Terverifikasi:</span>
                        <span class="font-bold text-slate-800">{{ $union->total_verified_members }} Orang</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Persentase Keterwakilan:</span>
                        <span class="font-black text-rose-600">{{ number_format($union->workforce_percentage, 1) }}% dari {{ $totalWorkforce }} Karyawan</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Peserta Potong Gaji (Check-Off):</span>
                        <span class="font-semibold text-slate-800">{{ $union->duesDeductions->where('is_active', true)->count() }} Karyawan</span>
                    </div>
                </div>

                <!-- PKB Negotiations History -->
                @if($union->pkbNegotiations->isNotEmpty())
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2 block">Riwayat Perundingan PKB:</span>
                        <div class="space-y-2">
                            @foreach($union->pkbNegotiations as $pkb)
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                    <div>
                                        <div class="font-bold text-slate-900">PKB Periode {{ $pkb->pkb_period_years }} (Draft: {{ $pkb->draft_code }})</div>
                                        <div class="text-[11px] text-slate-500">
                                            Mulai: {{ $pkb->negotiation_start_date->format('d M Y') }} • Target Batas: {{ $pkb->negotiation_target_end_date->format('d M Y') }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $pkb->status === 'registered_at_disnaker' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ str_replace('_', ' ', $pkb->status) }}
                                        </span>
                                        @if($pkb->disnaker_decree_number)
                                            <span class="font-mono text-[10px] text-slate-600">SK: {{ $pkb->disnaker_decree_number }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                Belum ada serikat pekerja/buruh yang tercatat di perusahaan ini.
            </div>
        @endforelse
    </div>

    <!-- Union Register Modal -->
    <div x-show="unionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="unionModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Catat Serikat Pekerja / Buruh Baru</h3>
                <button @click="unionModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('unions.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Nama Serikat Pekerja *</label>
                    <input type="text" name="union_name" required placeholder="Serikat Pekerja Mandiri Teknologi (SPMT)" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Nomor Pencatatan Disnaker *</label>
                    <input type="text" name="registration_number" required placeholder="568/DISNAKER-PENCATATAN/2026/041" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nama Ketua Pengurus *</label>
                        <input type="text" name="chairperson_name" required placeholder="Hendra Gunawan" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Jumlah Anggota Sah Terverifikasi *</label>
                        <input type="number" name="total_verified_members" required min="1" value="15" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="unionModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-xl hover:bg-[#991B1B] font-semibold">Simpan Serikat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- PKB Modal -->
    <div x-show="pkbModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="pkbModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Mulai Perundingan Perjanjian Kerja Bersama (PKB)</h3>
                <button @click="pkbModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="'{{ url('unions') }}/' + selectedUnionId + '/pkb'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Periode Masa Berlaku PKB *</label>
                    <input type="text" name="pkb_period_years" required placeholder="2026-2028 (Maks 2 Tahun)" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Mulai Perundingan *</label>
                        <input type="date" name="negotiation_start_date" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Target Selesai (Maks 30 Hari Kerja) *</label>
                        <input type="date" name="negotiation_target_end_date" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Ketua Delegasi Manajemen *</label>
                        <input type="text" name="employer_delegation_lead" required placeholder="Direktur HR" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Ketua Delegasi Serikat *</label>
                        <input type="text" name="union_delegation_lead" required placeholder="Ketua SP/SB" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Ringkasan Materi Perundingan</label>
                    <textarea name="summary_clauses" rows="3" placeholder="Penyempurnaan jaminan kesehatan, fleksibilitas kerja, bonus kinerja..." class="w-full px-3 py-2 border rounded-xl border-slate-300"></textarea>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="pkbModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 font-semibold">Buka Perundingan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Check-off Modal -->
    <div x-show="checkoffModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="checkoffModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Daftarkan Pemotongan Iuran Anggota (Check-off System)</h3>
                <button @click="checkoffModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="'{{ url('unions') }}/' + selectedUnionId + '/checkoff'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Karyawan Anggota *</label>
                    <select name="employee_id" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_number }} - {{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nominal Iuran Bulanan (Rp) *</label>
                        <input type="number" name="monthly_dues_amount" required value="25000" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">No. Surat Kuasa Tertulis *</label>
                        <input type="text" name="consent_letter_number" required placeholder="SK-CHECKOFF/2026/012" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="checkoffModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl hover:bg-slate-800 font-semibold">Aktifkan Check-off</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
