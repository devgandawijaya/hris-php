@extends('layouts.app')

@section('title', 'Manajemen Alih Daya (Outsourcing) & Kepatuhan TUPE')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                    Kepatuhan PP No. 35/2021 BAB IV
                </span>
                <span class="text-xs text-slate-400">• Tata Kelola Rekanan Alih Daya & Prinsip TUPE</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Manajemen Alih Daya (Outsourcing) & Rekanan</h1>
            <p class="text-xs text-slate-500 mt-1">Registrasi vendor berizin OSS Kemnaker, kontrak SPK pemborongan/jasa, verifikasi kepatuhan upah UMK, BPJS, dan perlindungan TUPE.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Daftarkan Vendor Baru
            </button>
        </div>
    </div>

    <!-- Statutory Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-900 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Alih Daya Sesuai PP No. 35 Tahun 2021 & Putusan MK No. 27/PUU-IX/2011:</span>
            <ul class="list-disc list-inside space-y-0.5 text-blue-800">
                <li>Perusahaan Alih Daya wajib berbadan hukum PT dan memiliki perizinan berusaha berbasis risiko dari sistem OSS Kemnaker (Pasal 18).</li>
                <li><strong>Prinsip TUPE (Transfer of Undertakings Protection of Employment)</strong>: Jaminan kelangsungan bekerja bagi pekerja bila terjadi pergantian perusahaan penyedia jasa sepanjang objek pekerjaan tetap ada (Pasal 19).</li>
                <li>Pekerja alih daya wajib menerima upah minimal setara UMK/UMP wilayah penempatan dan diikutsertakan dalam program BPJS Ketenagakerjaan & BPJS Kesehatan.</li>
            </ul>
        </div>
    </div>

    <!-- Vendors Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Rekanan Alih Daya Terdaftar (Vendors)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="p-3">Kode & Nama Rekanan</th>
                        <th class="p-3">Badan Hukum & NIB OSS</th>
                        <th class="p-3">PIC & Kontak</th>
                        <th class="p-3">Manajemen Fee</th>
                        <th class="p-3">Jumlah Kontrak & Personil</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vendors as $vendor)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3 font-semibold text-slate-900">
                                <div>{{ $vendor->name }}</div>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $vendor->vendor_code }}</span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">{{ $vendor->legal_entity_type }}</span>
                                <div class="text-[10px] text-slate-500 mt-0.5">NIB: {{ $vendor->nib_oss_license }}</div>
                            </td>
                            <td class="p-3">
                                <div class="text-slate-800">{{ $vendor->pic_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $vendor->pic_contact }}</div>
                            </td>
                            <td class="p-3">
                                <span class="font-bold text-slate-800">{{ number_format($vendor->management_fee_percentage, 1) }}%</span>
                            </td>
                            <td class="p-3">
                                <div class="text-slate-700">{{ $vendor->contracts->count() }} Kontrak Aktif</div>
                                <div class="text-[10px] text-slate-400">{{ $vendor->contracts->sum(fn($c) => $c->workerAssignments->count()) }} Personil Bertugas</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $vendor->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $vendor->status }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('outsourcing.show', $vendor->id) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition inline-flex items-center gap-1.5">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Detail & Kontrak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 text-xs">Belum ada rekanan alih daya yang didaftarkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vendors->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>

    <!-- Create Vendor Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="createModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Daftarkan Rekanan Alih Daya (Vendor) Baru</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('outsourcing.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Nama Perusahaan Rekanan *</label>
                    <input type="text" name="name" required placeholder="PT Sumber Daya Sejahtera" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Bentuk Badan Hukum *</label>
                        <select name="legal_entity_type" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                            <option value="PT">PT (Perseroan Terbatas)</option>
                            <option value="Koperasi">Koperasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">NIB Berizin OSS Kemnaker *</label>
                        <input type="text" name="nib_oss_license" required placeholder="0220109182312" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nama PIC *</label>
                        <input type="text" name="pic_name" required placeholder="Bambang Wijaya" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Kontak / Telepon PIC *</label>
                        <input type="text" name="pic_contact" required placeholder="081234567890" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">No. Pendaftaran Disnaker</label>
                        <input type="text" name="disnaker_registration_number" placeholder="560/DISNAKER/2026/089" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Management Fee (%) *</label>
                        <input type="number" step="0.01" name="management_fee_percentage" value="10.00" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-xl hover:bg-[#991B1B] font-semibold">Simpan Rekanan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
