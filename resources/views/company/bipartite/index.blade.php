@extends('layouts.app')

@section('title', 'Forum Lembaga Kerja Sama (LKS) Bipartit')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Industrial Dialogue
                </span>
                <span class="text-xs text-slate-400">• Permenaker No. 32/2008 & UU 13/2003 Pasal 106</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Forum Lembaga Kerja Sama (LKS) Bipartit</h1>
            <p class="text-xs text-slate-500 mt-1">Media komunikasi, musyawarah mufakat hubungan industrial, risalah sidang berkala, dan pelaporan semesteran Disnaker.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Catat Risalah Sidang Bipartit
            </button>
        </div>
    </div>

    <!-- Statutory Context Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-900 flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <span class="font-bold">Ketentuan Pembentukan LKS Bipartit:</span>
            <ul class="list-disc list-inside space-y-0.5 text-blue-800">
                <li>Wajib dibentuk pada setiap perusahaan yang mempekerjakan <strong>50 (lima puluh) orang pekerja atau lebih</strong> (Pasal 106 UU Ketenagakerjaan).</li>
                <li>Susunan kepengurusan beranggotakan unsur Pengusaha dan unsur Pekerja/Buruh dengan perbandingan <strong>1 : 1</strong> (Permenaker 32/2008 Pasal 6).</li>
                <li>Kegiatan LKS Bipartit wajib dilaporkan kepada Dinas Tenaga Kerja sekurang-kurangnya <strong>6 (enam) bulan sekali</strong>.</li>
            </ul>
        </div>
    </div>

    <!-- Meetings Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Risalah Sidang Musyawarah Bipartit</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">No. Risalah / Tanggal</th>
                        <th class="px-4 py-3 font-bold">Agenda & Topik Musyawarah</th>
                        <th class="px-4 py-3 font-bold">Komposisi Perwakilan (1:1)</th>
                        <th class="px-4 py-3 font-bold">Rekomendasi Bersama</th>
                        <th class="px-4 py-3 font-bold">Laporan Disnaker</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($meetings as $m)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 font-mono">{{ $m->meeting_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $m->meeting_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $m->meeting_title }}</div>
                                <div class="text-[11px] text-slate-500 line-clamp-1">{{ $m->agenda_topics }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                        Pengusaha: {{ count($m->employer_representatives) }}
                                    </span>
                                    <span class="text-slate-400 font-bold">:</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Pekerja: {{ count($m->worker_representatives) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 max-w-xs text-slate-700 truncate" title="{{ $m->action_items }}">
                                {{ $m->action_items ?? 'Tidak ada tindak lanjut khusus' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($m->disnaker_reported_at)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1 w-max">
                                        <i data-lucide="check" class="w-3 h-3"></i> Dilaporkan
                                    </span>
                                    <div class="text-[9px] text-slate-400 mt-0.5">{{ $m->disnaker_reported_at->format('d/m/Y') }}</div>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Belum Lapor
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(!$m->disnaker_reported_at)
                                    <form action="{{ route('company.bipartite.report-disnaker', $m->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-slate-900 hover:bg-black text-white rounded font-bold text-[11px]">
                                            Tandai Lapor Disnaker
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Belum ada risalah sidang LKS Bipartit yang dicatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $meetings->links() }}
        </div>
    </div>

    <!-- Create Bipartite Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Catat Risalah Sidang LKS Bipartit</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('company.bipartite.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Sidang Bipartit</label>
                        <input type="date" name="meeting_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Judul / Sesi Musyawarah</label>
                        <input type="text" name="meeting_title" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Sidang Rutin Bipartit Semester I">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Agenda & Topik Pembahasan</label>
                    <input type="text" name="agenda_topics" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Evaluasi keselamatan kerja, fasilitas kantin, dan jam lembur">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Perwakilan Pengusaha (Koma / Baris Baru)</label>
                        <textarea name="employer_representatives" rows="2" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Bambang Soedarmono (Direktur HR), Rian (Manajer Ops)"></textarea>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Perwakilan Pekerja (Koma / Baris Baru)</label>
                        <textarea name="worker_representatives" rows="2" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Ani Widyastuti (Ketua SP), Hendra (Sekretaris SP)"></textarea>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Risalah Musyawarah & Kesepakatan</label>
                    <textarea name="meeting_minutes" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Uraikan poin-poin mufakat yang dicapai dalam sidang..."></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Komitmen Tindak Lanjut & Target Waktu</label>
                    <textarea name="action_items" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Penyediaan APD tambahan paling lambat tanggal 15 bulan depan..."></textarea>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-lg font-bold">Simpan Risalah</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
