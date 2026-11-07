@extends('layouts.app')

@section('title', 'Satgas PPKS & Fasilitas Kesejahteraan Ramah Perempuan')

@section('content')
<div class="space-y-6" x-data="{ complaintModal: false, auditModal: false, statusModal: false, selectedCaseId: null }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                    Kepatuhan Kepmenaker No. 88/2023 & UU TPKS No. 12/2022
                </span>
                <span class="text-xs text-slate-400">• Ruang Aman & Fasilitas Perlindungan Perempuan</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Satgas PPKS & Fasilitas Ramah Perempuan</h1>
            <p class="text-xs text-slate-500 mt-1">Kanal pelaporan rahasia tanpa retaliasi, audit ruang laktasi higienis (Permenkes 15/2013), dan angkutan shift malam (Pasal 76 UU 13/2003).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="complaintModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                Catat Laporan Pengaduan Rahasia
            </button>
            <button @click="auditModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-purple-600 text-white hover:bg-purple-700 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="check-square" class="w-4 h-4"></i>
                Audit Fasilitas Ramah Perempuan
            </button>
        </div>
    </div>

    <!-- Statutory Summary Card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold uppercase text-slate-400 block">Status Satgas PPKS:</span>
            <div class="text-sm font-black text-slate-800 mt-1 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Aktif & Beroperasi
            </div>
            <span class="text-[10px] text-slate-400">Kepmenaker 88/2023</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold uppercase text-slate-400 block">Ruang Laktasi (Permenkes 15/2013):</span>
            <div class="text-sm font-bold text-slate-800 mt-1">
                {{ $complianceReport['fasilitas_ruang_laktasi'] }}
            </div>
            <span class="text-[10px] text-slate-400">Higienis & Privasi Terjaga</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold uppercase text-slate-400 block">Angkutan Shift Malam (23:00 - 05:00):</span>
            <div class="text-sm font-bold text-slate-800 mt-1">
                {{ $complianceReport['angkutan_antar_jemput_malam'] }}
            </div>
            <span class="text-[10px] text-slate-400">Pasal 76 ayat 3-4 UU 13/2003</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold uppercase text-slate-400 block">Status Kepatuhan Gender:</span>
            <div class="text-sm font-black text-rose-600 mt-1">
                {{ $complianceReport['status_kepatuhan_keseluruhan'] }}
            </div>
            <span class="text-[10px] text-slate-400">Standar Audit Disnaker</span>
        </div>
    </div>

    <!-- Complaint Cases Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Penanganan Pengaduan Kasus Satgas PPKS (Confidential)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="p-3">Tiket & Tgl Kejadian</th>
                        <th class="p-3">Identitas Pelapor</th>
                        <th class="p-3">Kategori Dugaan</th>
                        <th class="p-3">Investigator Satgas</th>
                        <th class="p-3">Langkah Perlindungan</th>
                        <th class="p-3">Status Kasus</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cases as $case)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3">
                                <span class="font-mono font-bold text-slate-900">{{ $case->ticket_number }}</span>
                                <div class="text-[10px] text-slate-400">{{ $case->incident_date->format('d M Y') }}</div>
                            </td>
                            <td class="p-3">
                                <span class="font-semibold text-slate-800">{{ $case->complainant_pseudonym }}</span>
                                @if($case->is_anonymous)
                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-500 ml-1">Anonim</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $case->allegation_type === 'physical' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ str_replace('_', ' ', $case->allegation_type) }}
                                </span>
                            </td>
                            <td class="p-3 text-slate-700">{{ $case->investigator_satgas_lead }}</td>
                            <td class="p-3 text-slate-600 max-w-xs truncate">{{ $case->victim_support_measures }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $case->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $case->status }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <button @click="selectedCaseId = {{ $case->id }}; statusModal = true" class="px-2.5 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                    Update Kasus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 text-xs">Belum ada pengaduan kasus PPKS yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Complaint Modal -->
    <div x-show="complaintModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="complaintModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Catat Laporan Pengaduan PPKS Baru</h3>
                <button @click="complaintModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('ppks.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Kejadian *</label>
                        <input type="date" name="incident_date" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Kategori Dugaan *</label>
                        <select name="allegation_type" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                            <option value="verbal">Kekerasan / Pelecehan Verbal</option>
                            <option value="non_physical">Kekerasan Non-Fisik</option>
                            <option value="physical">Kekerasan Fisik</option>
                            <option value="digital_online">Pelecehan Digital / Online</option>
                        </select>
                    </div>
                </div>

                <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_anonymous" value="1" checked class="rounded border-purple-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-purple-900 font-semibold">Samarkan Identitas Pelapor (Anonim Terlindungi)</span>
                    </div>
                    <div>
                        <input type="text" name="complainant_pseudonym" placeholder="Pseudonim (e.g. Saksi Terlindungi-B)" class="w-full px-2.5 py-1.5 border rounded-lg border-purple-200 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Ketua Tim Investigator Satgas *</label>
                    <input type="text" name="investigator_satgas_lead" required placeholder="Dra. Nurul Hidayati (Satgas PPKS)" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Rencana Tindakan Perlindungan & Dukungan Korban</label>
                    <textarea name="victim_support_measures" rows="3" placeholder="Pendampingan konseling psikologis, jaminan mutasi sementara, pengamanan..." class="w-full px-3 py-2 border rounded-xl border-slate-300"></textarea>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="complaintModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-xl hover:bg-[#991B1B] font-semibold">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Facility Audit Modal -->
    <div x-show="auditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="auditModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Audit Fasilitas Kesejahteraan Ramah Perempuan</h3>
                <button @click="auditModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('ppks.audit-facility') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Tanggal Pemeriksaan / Audit *</label>
                    <input type="date" name="inspection_date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>

                <div class="space-y-2 pt-1">
                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-slate-800">Ruang Laktasi & Menyusui (Permenkes 15/2013)</div>
                            <div class="text-[10px] text-slate-500">Tersedia wastafel, kursi ergonomis, lemari pendingin ASI</div>
                        </div>
                        <input type="checkbox" name="lactation_room_standard_met" value="1" checked class="rounded text-purple-600 focus:ring-purple-500">
                    </div>

                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-slate-800">Angkutan Jemputan Shift Malam (Pasal 76 UU 13/2003)</div>
                            <div class="text-[10px] text-slate-500">Khusus pekerja perempuan yang bekerja pukul 23.00 - 05.00</div>
                        </div>
                        <input type="checkbox" name="night_shift_transport_provided" value="1" checked class="rounded text-purple-600 focus:ring-purple-500">
                    </div>

                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-slate-800">Makanan & Minuman Bergizi Shift Malam</div>
                            <div class="text-[10px] text-slate-500">Pemberian asupan gizi memadai selama bertugas malam hari</div>
                        </div>
                        <input type="checkbox" name="night_shift_nutritious_food_provided" value="1" checked class="rounded text-purple-600 focus:ring-purple-500">
                    </div>

                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-slate-800">Fasilitas Penitipan Anak / Subsidi Daycare</div>
                            <div class="text-[10px] text-slate-500">Dukungan korporat bagi pekerja dengan balita</div>
                        </div>
                        <input type="checkbox" name="daycare_subsidy_available" value="1" class="rounded text-purple-600 focus:ring-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Catatan Evaluasi Audit</label>
                    <textarea name="compliance_notes" rows="2" placeholder="Hasil inspeksi fisik di gedung kantor..." class="w-full px-3 py-2 border rounded-xl border-slate-300"></textarea>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="auditModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 font-semibold">Simpan Hasil Audit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
