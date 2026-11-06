@extends('layouts.app')

@section('title', 'Papan Pengumuman Perusahaan')

@section('content')
<div class="space-y-6" x-data="{ openCreateModal: false }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Papan Pengumuman & Edaran Internal</h1>
            <p class="text-sm text-slate-500 mt-1">Publikasikan informasi kebijakan, hari libur, pengumuman darurat, dan kegiatan perusahaan ke seluruh atau divisi tertentu.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="megaphone" class="w-4 h-4"></i>
                Buat Pengumuman Baru
            </button>
        </div>
    </div>

    <!-- Announcements Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($announcements as $announcement)
            <div class="bg-white rounded-2xl border {{ $announcement->is_pinned ? 'border-amber-300 ring-2 ring-amber-400/20 shadow-md' : 'border-slate-200 shadow-sm' }} p-5 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <!-- Badge row -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            @if($announcement->category === 'urgent')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-red-100 text-red-700 tracking-wider">
                                    Urgent / Mendesak
                                </span>
                            @elseif($announcement->category === 'policy')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-700 tracking-wider">
                                    Kebijakan Baru
                                </span>
                            @elseif($announcement->category === 'holiday')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-700 tracking-wider">
                                    Hari Libur / Cuti
                                </span>
                            @elseif($announcement->category === 'event')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-100 text-purple-700 tracking-wider">
                                    Event / Agenda
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-700 tracking-wider">
                                    Umum
                                </span>
                            @endif

                            @if($announcement->is_pinned)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                                    <i data-lucide="pin" class="w-2.5 h-2.5 fill-amber-700"></i> Disematkan
                                </span>
                            @endif
                        </div>

                        <!-- Target Department -->
                        <span class="text-[10px] font-bold text-slate-400">
                            {{ $announcement->targetDepartment?->name ?? 'Seluruh Divisi' }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="text-base font-bold text-slate-900 leading-snug line-clamp-2">
                        {{ $announcement->title }}
                    </h3>

                    <!-- Content -->
                    <p class="text-xs text-slate-600 mt-2.5 line-clamp-4 leading-relaxed whitespace-pre-line">
                        {{ $announcement->content }}
                    </p>
                </div>

                <!-- Footer metadata & action -->
                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <div>
                        <div>Oleh: <strong class="text-slate-700">{{ $announcement->createdBy?->name ?? 'Admin HR' }}</strong></div>
                        <div class="text-[10px]">{{ $announcement->published_at->format('d M Y') }}</div>
                    </div>

                    <form action="{{ route('company.announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Hapus Pengumuman">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200">
                <i data-lucide="bell-off" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                <h4 class="text-sm font-bold text-slate-700">Belum Ada Pengumuman</h4>
                <p class="text-xs text-slate-400 mt-1">Buat pengumuman pertama Anda untuk memberikan kabar terbaru ke seluruh tim.</p>
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            {{ $announcements->links() }}
        </div>
    @endif

    <!-- Modal: Create Announcement -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openCreateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="megaphone" class="w-5 h-5 text-red-600"></i>
                    Buat Pengumuman Baru
                </div>
                <button @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.announcements.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul Pengumuman <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Pemberitahuan Libur Bersama Idul Fitri 1447 H" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="general">Umum</option>
                            <option value="policy">Kebijakan Baru (Policy)</option>
                            <option value="holiday">Hari Libur / Cuti Bersama</option>
                            <option value="event">Kegiatan & Acara</option>
                            <option value="urgent">Mendesak (Urgent)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Departemen</label>
                        <select name="target_department_id" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Seluruh Karyawan (Semua Divisi)</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Rilis <span class="text-red-500">*</span></label>
                        <input type="date" name="published_at" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Berakhir Pada (Opsional)</label>
                        <input type="date" name="expires_at" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 bg-slate-50 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                </div>

                <div class="flex items-center gap-2 p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <input type="checkbox" name="is_pinned" id="is_pinned" value="1" class="w-4 h-4 rounded text-red-600 focus:ring-red-500">
                    <label for="is_pinned" class="text-xs font-bold text-slate-700 cursor-pointer">
                        Sematkan Pengumuman (Pin to Top) di dashboard ESS karyawan
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Isi Lengkap Pengumuman <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="5" required placeholder="Tuliskan isi informasi edaran atau kebijakan secara jelas..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500 leading-relaxed"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-[#DC2626] hover:bg-[#991B1B] rounded-xl shadow-sm transition">
                        Publikasikan Pengumuman
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

