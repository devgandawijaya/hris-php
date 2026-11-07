@extends('layouts.app')

@section('title', 'Panitia Pembina Keselamatan & Kesehatan Kerja (P2K3)')

@section('content')
<div class="space-y-6" x-data="{ committeeModal: false, reportModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                    Kepatuhan UU No. 1/1970 & Permenaker No. 04/1987
                </span>
                <span class="text-xs text-slate-400">• Lembaga K3 & Pelaporan Triwulan Disnaker</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Panitia Pembina K3 (P2K3) & Pelaporan Triwulan Disnaker</h1>
            <p class="text-xs text-slate-500 mt-1">Verifikasi mandatori P2K3 tenaga kerja $\ge 100$ orang, SKP Ahli K3 Umum Kemnaker RI, dan kepatuhan penyampaian laporan triwulan Disnaker (Pasal 12).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="committeeModal = true" class="px-3 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="edit" class="w-4 h-4"></i>
                {{ $committee ? 'Perbarui Susunan P2K3' : 'Bentuk Komite P2K3' }}
            </button>
            @if($committee)
                <button @click="reportModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                    <i data-lucide="file-plus" class="w-4 h-4"></i>
                    Buat Laporan Triwulan
                </button>
            @endif
        </div>
    </div>

    <!-- Mandatory Audit Alert -->
    <div class="rounded-2xl p-4 text-xs flex items-start gap-3 border {{ $complianceAssessment['is_p2k3_mandatory'] ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-emerald-50 border-emerald-200 text-emerald-900' }}">
        <i data-lucide="{{ $complianceAssessment['is_p2k3_mandatory'] ? 'alert-octagon' : 'check-circle' }}" class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $complianceAssessment['is_p2k3_mandatory'] ? 'text-amber-600' : 'text-emerald-600' }}"></i>
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="font-bold">Status Mandatori P2K3:</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $complianceAssessment['is_p2k3_mandatory'] ? 'bg-amber-200 text-amber-900' : 'bg-emerald-200 text-emerald-900' }}">
                    {{ $complianceAssessment['is_p2k3_mandatory'] ? 'Wajib Dibentuk (Mandatory)' : 'Anjuran Sukarela' }}
                </span>
            </div>
            <p>{{ $complianceAssessment['recommendation'] }} Total Tenaga Kerja Aktif: <strong>{{ $activeWorkforce }} orang</strong> (Batas minimal mandatori: 100 pekerja sesuai Pasal 2 Permenaker No. 04/1987).</p>
        </div>
    </div>

    <!-- Committee Profile Card -->
    @if($committee)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Struktur Lembaga K3 Perusahaan</span>
                    <h2 class="text-base font-black text-slate-900">Panitia Pembina Keselamatan & Kesehatan Kerja (P2K3)</h2>
                    <span class="text-xs text-slate-500 font-mono">SK Internal: {{ $committee->decree_number }} • Pengesahan Disnaker: {{ $committee->disnaker_ratification_number ?? 'Dalam Proses' }}</span>
                </div>
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 uppercase">
                        {{ $committee->status }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-slate-400 block font-medium">Ketua P2K3 (Pimpinan):</span>
                    <span class="font-bold text-slate-800 text-sm block">{{ $committee->chairman_name }}</span>
                    <span class="text-[10px] text-slate-500">Unsur Manajemen Tertinggi</span>
                </div>
                <div class="p-3 bg-rose-50/60 rounded-xl border border-rose-100 space-y-1">
                    <span class="text-rose-700 block font-medium">Sekretaris (Ahli K3 Umum):</span>
                    <span class="font-bold text-rose-900 text-sm block">{{ $committee->secretary_ahli_k3_name }}</span>
                    <span class="text-[10px] text-rose-600 font-mono font-bold">SKP: {{ $committee->ahli_k3_skp_number }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-slate-400 block font-medium">Jumlah Anggota Komite:</span>
                    <span class="font-bold text-slate-800 text-sm block">{{ $committee->total_committee_members }} Personil</span>
                    <span class="text-[10px] text-slate-500">Kombinasi Pengusaha & Pekerja</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-slate-400 block font-medium">Tanggal Pembentukan:</span>
                    <span class="font-bold text-slate-800 text-sm block">{{ \Carbon\Carbon::parse($committee->formation_date)->format('d M Y') }}</span>
                    <span class="text-[10px] text-emerald-600 font-medium">Aktif Terdaftar</span>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center space-y-3">
            <i data-lucide="hard-hat" class="w-12 h-12 text-slate-400 mx-auto"></i>
            <h3 class="text-sm font-bold text-slate-800">Komite P2K3 Belum Didaftarkan</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">Perusahaan diwajibkan mendaftarkan pengurus P2K3 dengan Sekretaris yang memiliki SKP Ahli K3 Umum resmi dari Kemnaker RI.</p>
            <button @click="committeeModal = true" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition">
                Bentuk Struktur P2K3 Sekarang
            </button>
        </div>
    @endif

    <!-- Quarterly Reports Table -->
    @if($committee)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-emerald-600"></i>
                        Laporan Triwulan P2K3 ke Disnaker Setempat (Pasal 12)
                    </h3>
                    <p class="text-[11px] text-slate-500">Wajib disampaikan sekurang-kurangnya 3 (tiga) bulan sekali kepada Kantor Disnaker.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                        <tr>
                            <th class="py-3 px-3">Periode Triwulan</th>
                            <th class="py-3 px-3">Inspeksi K3</th>
                            <th class="py-3 px-3">Pelatihan Safety</th>
                            <th class="py-3 px-3">Resume Kecelakaan</th>
                            <th class="py-3 px-3">No. Bukti Lapor Disnaker</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($quarterlyReports as $report)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3 font-bold text-slate-900">
                                    {{ $report->quarter }} - Tahun {{ $report->reporting_year }}
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-700">{{ $report->workplace_inspections_count }} Kali Inspeksi</td>
                                <td class="py-3 px-3 font-semibold text-slate-700">{{ $report->safety_trainings_count }} Sesi Pelatihan</td>
                                <td class="py-3 px-3 text-slate-600">{{ Str::limit($report->incident_summary, 40) }}</td>
                                <td class="py-3 px-3 font-mono font-medium text-slate-800">
                                    {{ $report->disnaker_receipt_number ?? 'Belum Ada (Draft)' }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $report->status === 'submitted_disnaker' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $report->status === 'submitted_disnaker' ? 'Sudah Lapor Disnaker' : 'Draft Internal' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('p2k3.reports.print', $report) }}" target="_blank" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition inline-flex items-center gap-1">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        Cetak Laporan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-400">Belum ada laporan triwulan yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Modal Registrasi Komite P2K3 -->
    <div x-show="committeeModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Registrasi Struktur Panitia Pembina K3 (P2K3)</h3>
                <button @click="committeeModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('p2k3.committee.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor SK Internal P2K3</label>
                        <input type="text" name="decree_number" value="{{ $committee->decree_number ?? 'SK-P2K3/'.date('Y').'/001' }}" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Pembentukan</label>
                        <input type="date" name="formation_date" value="{{ $committee ? $committee->formation_date->toDateString() : date('Y-m-d') }}" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Ketua P2K3 (Pimpinan Perusahaan)</label>
                    <input type="text" name="chairman_name" value="{{ $committee->chairman_name ?? 'Direktur Operasional / HR Director' }}" required class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Sekretaris (Ahli K3 Umum)</label>
                        <input type="text" name="secretary_ahli_k3_name" value="{{ $committee->secretary_ahli_k3_name ?? '' }}" required placeholder="Ir. Budi Santoso, ST, K3" class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor SKP Ahli K3 (Kemnaker)</label>
                        <input type="text" name="ahli_k3_skp_number" value="{{ $committee->ahli_k3_skp_number ?? '' }}" required placeholder="SKP.0123/NAKER-BINWASK3/2024" class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">No. Pengesahan Disnaker</label>
                        <input type="text" name="disnaker_ratification_number" value="{{ $committee->disnaker_ratification_number ?? '' }}" placeholder="DISNAKER/P2K3/2024/098" class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Total Anggota Komite</label>
                        <input type="number" name="total_committee_members" value="{{ $committee->total_committee_members ?? 12 }}" min="2" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="committeeModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Simpan Komite P2K3</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Laporan Triwulan Disnaker -->
    @if($committee)
        <div x-show="reportModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Buat Laporan Triwulan P2K3 Disnaker</h3>
                    <button @click="reportModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <form action="{{ route('p2k3.reports.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="p2k3_committee_id" value="{{ $committee->id }}">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tahun Laporan</label>
                            <input type="number" name="reporting_year" value="{{ date('Y') }}" required class="w-full rounded-xl border-slate-200 text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Triwulan (Quarter)</label>
                            <select name="quarter" required class="w-full rounded-xl border-slate-200 text-xs">
                                <option value="Q1">Triwulan I (Q1)</option>
                                <option value="Q2">Triwulan II (Q2)</option>
                                <option value="Q3">Triwulan III (Q3)</option>
                                <option value="Q4">Triwulan IV (Q4)</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah Inspeksi K3</label>
                            <input type="number" name="workplace_inspections_count" value="3" min="0" required class="w-full rounded-xl border-slate-200 text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah Pelatihan K3</label>
                            <input type="number" name="safety_trainings_count" value="2" min="0" required class="w-full rounded-xl border-slate-200 text-xs">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Resume Insiden & Bahaya Kerja</label>
                        <textarea name="incident_summary" rows="2" class="w-full rounded-xl border-slate-200 text-xs">Zero Accident. Seluruh temuan inspeksi bahaya kerja telah ditindaklanjuti.</textarea>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Tanda Terima Disnaker (Jika sudah lapor)</label>
                        <input type="text" name="disnaker_receipt_number" placeholder="Bukti tanda terima Disnaker / barcode surat" class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="reportModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white font-semibold hover:bg-[#991B1B]">Simpan Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
