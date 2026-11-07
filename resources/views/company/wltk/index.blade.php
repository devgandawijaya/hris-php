@extends('layouts.app')

@section('title', 'Wajib Lapor Ketenagakerjaan Perusahaan (WLTK)')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                    Kepatuhan Regulasi Kemnaker
                </span>
                <span class="text-xs text-slate-400">• UU No. 7/1981 jo. Permenaker No. 18/2017</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Wajib Lapor Ketenagakerjaan Perusahaan (WLTK / WLKP)</h1>
            <p class="text-xs text-slate-500 mt-1">Penyusunan berkas pelaporan ketenagakerjaan tahunan resmi, pemantauan masa berlaku pelaporan, dan ekspor format daring Kemnaker.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Generate Draft Laporan WLTK
            </button>
        </div>
    </div>

    <!-- Statutory Alert & Renewal Notification -->
    @if($renewalAlerts->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-900 flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
            <div class="space-y-1">
                <span class="font-bold">Peringatan Jatuh Tempo Wajib Lapor Ketenagakerjaan:</span>
                <p>Terdapat {{ $renewalAlerts->count() }} laporan ketenagakerjaan yang mendekati batas waktu perpanjangan tahunan (30-60 hari ke depan). Lakukan pelaporan ulang tahunan untuk menghindari sanksi administratif Disnaker.</p>
            </div>
        </div>
    @endif

    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-900 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan UU No. 7 Tahun 1981:</span>
            <ul class="list-disc list-inside space-y-0.5 text-blue-800">
                <li>Setiap pengusaha wajib melaporkan keadaan ketenagakerjaan di perusahaannya secara berkala <strong>setiap 1 (satu) tahun sekali</strong> (Pasal 6 ayat 1).</li>
                <li>Laporan memuat data komposisi tenaga kerja (WNI, WNA, gender, disabilitas), status kontrak kerja, fasilitas K3, dan kepesertaan jaminan sosial BPJS.</li>
                <li>Pelaporan dapat diunggah secara daring pada portal resmi <strong>wlkp.kemnaker.go.id</strong> menggunakan berkas ekspor sistem ini.</li>
            </ul>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Histori & Status Laporan WLTK Perusahaan</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Nomor Laporan / Tahun</th>
                        <th class="px-4 py-3 font-bold">Total Karyawan</th>
                        <th class="px-4 py-3 font-bold">WNI vs WNA</th>
                        <th class="px-4 py-3 font-bold">PKWTT vs PKWT</th>
                        <th class="px-4 py-3 font-bold">Disabilitas</th>
                        <th class="px-4 py-3 font-bold">Status / Masa Berlaku</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $r)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <a href="{{ route('company.wltk.show', $r->id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $r->report_number }}
                                </a>
                                <div class="text-[10px] text-slate-400">Tahun {{ $r->reporting_year }} ({{ ucfirst($r->report_period) }})</div>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-800">
                                {{ number_format($r->total_workers) }} Orang
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>WNI: {{ $r->total_wni_male + $r->total_wni_female }} (♂{{ $r->total_wni_male }} ♀{{ $r->total_wni_female }})</div>
                                <div class="text-[10px] text-slate-400">TKA: {{ $r->total_wna_male + $r->total_wna_female }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>Tetap: {{ $r->total_pkwtt }}</div>
                                <div class="text-[10px] text-slate-400">Kontrak: {{ $r->total_pkwt }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $r->total_disability_workers > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $r->total_disability_workers }} Orang
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($r->status === 'submitted_kemnaker')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Sudah Lapor Disnaker
                                    </span>
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        Berlaku s/d: {{ $r->valid_until ? $r->valid_until->format('d/m/Y') : '-' }}
                                    </div>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Draft Sistem
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Belum disubmit</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <a href="{{ route('company.wltk.show', $r->id) }}" class="px-2 py-1 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition inline-block text-[11px] font-semibold">
                                    Detail
                                </a>
                                <a href="{{ route('company.wltk.export-json', $r->id) }}" class="px-2 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 transition inline-block text-[11px] font-semibold" title="Download Format JSON WLKP">
                                    JSON
                                </a>
                                <a href="{{ route('company.wltk.export-csv', $r->id) }}" class="px-2 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 transition inline-block text-[11px] font-semibold" title="Download Format CSV">
                                    CSV
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                Belum ada berkas laporan WLTK yang dibuat. Klik tombol di atas untuk generate otomatis dari database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Generate Draft WLTK -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl" @click.away="createModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Generate Laporan WLTK Baru</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <p class="text-xs text-slate-500 mb-4">
                Sistem akan mengagregasi data real-time seluruh karyawan aktif perusahaan (demografi, jenis kontrak kerja, rentang upah, dan kepesertaan BPJS) sesuai format Permenaker No. 18/2017.
            </p>

            <form action="{{ route('company.wltk.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Pelaporan</label>
                    <input type="number" name="reporting_year" value="{{ date('Y') }}" min="2020" max="2035" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Periode / Jenis Laporan</label>
                    <select name="report_period" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2" required>
                        <option value="annual">Laporan Berkala Tahunan (Wajib 1 Tahun Sekali)</option>
                        <option value="establishment">Pendirian / Perusahaan Baru</option>
                        <option value="relocation">Pemindahan Alamat / Domisili Usaha</option>
                        <option value="closure">Penutupan Perusahaan</option>
                    </select>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Generate Draft Sekarang</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
