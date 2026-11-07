@extends('layouts.app')

@section('title', 'Peraturan Perusahaan & PKB Register')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-red-100 text-red-800 border border-red-200">
                    Industrial Statutory Compliance
                </span>
                <span class="text-xs text-slate-400">• UU No. 13/2003 Pasal 108-114</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Peraturan Perusahaan (PP) & PKB Register</h1>
            <p class="text-xs text-slate-500 mt-1">Pendaftaran naskah statutori ketenagakerjaan, masa berlaku 2 tahun, dan peringatan perpanjangan Disnaker (H-90).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Daftarkan Dokumen PP/PKB
            </button>
        </div>
    </div>

    <!-- H-90 Renewal Warning Alerts -->
    @foreach($alerts as $alert)
        @if($alert['needs_action'])
            <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 text-xs text-amber-900 flex items-start justify-between gap-3 shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold text-amber-950">PERINGATAN PERPANJANGAN PERATURAN PERUSAHAAN (H-{{ $alert['days_remaining'] }} HARI):</span>
                        <div class="text-amber-800 mt-0.5">
                            Dokumen <strong>{{ $alert['title'] }}</strong> akan habis masa berlakunya pada <u>{{ $alert['effective_end_date'] }}</u>.
                            Sesuai UU Ketenagakerjaan Pasal 111 ayat 3, pembaharuan draf dan permohonan pengesahan ke Dinas Tenaga Kerja (Disnaker) wajib diajukan sebelum masa berlaku berakhir.
                        </div>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-amber-200 text-amber-900 flex-shrink-0">
                    Perlu Pembaharuan
                </span>
            </div>
        @endif
    @endforeach

    <!-- Regulations Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($regulations as $reg)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $reg->document_type === 'perjanjian_kerja_bersama_pkb' ? 'bg-indigo-100 text-indigo-800' : 'bg-red-100 text-red-800' }}">
                            {{ $reg->document_type === 'perjanjian_kerja_bersama_pkb' ? 'PKB (Serikat Pekerja)' : 'Peraturan Perusahaan (PP)' }}
                        </span>
                        @if($reg->status === 'active')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Berlaku Aktif</span>
                        @elseif($reg->status === 'renewal_pending')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">H-90 Perpanjangan</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Kedaluwarsa</span>
                        @endif
                    </div>

                    <h2 class="text-base font-bold text-slate-900 mt-3">{{ $reg->title }}</h2>
                    <div class="text-xs text-slate-500 font-mono mt-0.5">SK Disnaker: {{ $reg->reg_number ?? 'Belum ada SK' }}</div>

                    <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1.5">
                        <div class="flex justify-between text-slate-600">
                            <span>Mulai Berlaku:</span>
                            <span class="font-bold text-slate-800">{{ $reg->effective_start_date->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Batas Akhir (Maks. 2 Thn):</span>
                            <span class="font-bold text-slate-800">{{ $reg->effective_end_date->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Disnaker Pengesah:</span>
                            <span class="font-bold text-slate-800">{{ $reg->disnaker_office ?? 'Disnakertrans Provinsi DKI Jakarta' }}</span>
                        </div>
                    </div>

                    @if($reg->summary_provisions)
                        <div class="mt-3 text-xs text-slate-600">
                            <span class="font-bold text-slate-700">Rangkuman Pokok:</span>
                            <p class="text-slate-500 mt-0.5 line-clamp-3 italic">{{ $reg->summary_provisions }}</p>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                    <span>Terdaftar di Sistem: {{ $reg->created_at->format('d M Y') }}</span>
                    <span class="text-emerald-700 font-semibold flex items-center gap-1">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Sah Ketenagakerjaan
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-2 bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 italic text-xs">
                Belum ada berkas Peraturan Perusahaan (PP) atau PKB yang terdaftar.
            </div>
        @endforelse
    </div>

    <!-- Create Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Daftarkan Dokumen PP / PKB Baru</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('company.regulations.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jenis Naskah Statutori</label>
                    <select name="document_type" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                        <option value="peraturan_perusahaan_pp">Peraturan Perusahaan (PP - UU 13/2003 Pasal 108)</option>
                        <option value="perjanjian_kerja_bersama_pkb">Perjanjian Kerja Bersama (PKB - Bersama Serikat)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Judul Dokumen Resmi</label>
                    <input type="text" name="title" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Peraturan Perusahaan PT Demo Solusindo 2026-2028">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor SK Pengesahan Disnaker</label>
                        <input type="text" name="reg_number" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="KEP.560/123/DISNAKER/2026">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Instansi Disnaker Pengesah</label>
                        <input type="text" name="disnaker_office" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Disnakertrans DKI Jakarta">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai Berlaku</label>
                        <input type="date" name="effective_start_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Berakhir (Maks. 2 Thn)</label>
                        <input type="date" name="effective_end_date" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ringkasan Hak, Kewajiban & Tata Tertib</label>
                    <textarea name="summary_provisions" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Uraian ringkas ketentuan jam kerja, tata tertib, hak cuti..."></textarea>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-lg font-bold">Simpan Dokumen</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
