@extends('layouts.app')

@section('title', 'Layanan HR & Tiket Helpdesk Internal')

@section('content')
<div class="space-y-6" x-data="{ 
    openResolveModal: false, 
    openDetailModal: false,
    selectedTicketId: '', 
    selectedTicketNumber: '', 
    selectedSubject: '', 
    selectedRequester: '',
    selectedCategory: '',
    selectedDesc: '',
    selectedResolution: ''
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">HR Helpdesk & Permohonan Layanan Karyawan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola tiket permohonan Surat Keterangan Kerja (Paklaring), referensi bank/visa, koreksi data, dan konsultasi BPJS.</p>
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Tiket Masuk</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Semua permohonan layanan</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu Diproses (Open)</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($stats['open']) }}</div>
            <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Memerlukan tindak lanjut HR</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terselesaikan (Resolved)</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['resolved']) }}</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Dokumen & layanan diserahkan</div>
        </div>
    </div>

    <!-- Tickets Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Antrean Tiket Layanan HR</h2>
                <p class="text-xs text-slate-400 mt-0.5">Tindak lanjuti penerbitan dokumen dan pertanyaan administratif karyawan.</p>
            </div>

            <!-- Status Filter -->
            <form action="{{ route('company.helpdesk.index') }}" method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200 px-3 py-2 bg-slate-50 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">Semua Status Tiket</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Menunggu (Open)</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Diproses (In Progress)</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Selesai (Resolved)</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Ditutup (Closed)</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Tiket & Kategori</th>
                        <th class="py-3 px-4">Pemohon</th>
                        <th class="py-3 px-4">Subjek & Rincian Kebutuhan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tgl Pengajuan</th>
                        <th class="py-3 px-4">Penyelesaian HR</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $ticket->ticket_number }}</div>
                                <div class="mt-1">
                                    @if($ticket->category === 'work_certificate')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                            Surat Keterangan Kerja
                                        </span>
                                    @elseif($ticket->category === 'bank_reference')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                                            Referensi Bank/KPR
                                        </span>
                                    @elseif($ticket->category === 'bpjs_inquiry')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            BPJS TK / Kesehatan
                                        </span>
                                    @elseif($ticket->category === 'data_correction')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Koreksi Data
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            Umum
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $ticket->employee?->full_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $ticket->employee?->employee_code }} &bull; {{ $ticket->employee?->department?->name ?? 'Dept' }}</div>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <div class="font-bold text-slate-900 truncate" title="{{ $ticket->subject }}">{{ $ticket->subject }}</div>
                                <div class="text-slate-500 text-[11px] truncate mt-0.5" title="{{ $ticket->description }}">{{ $ticket->description }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($ticket->status === 'open')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu (Open)
                                    </span>
                                @elseif($ticket->status === 'in_progress')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Diproses
                                    </span>
                                @elseif($ticket->status === 'resolved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check" class="w-3 h-3"></i> Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Ditutup
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div>{{ $ticket->created_at->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $ticket->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                @if($ticket->resolution_notes)
                                    <div class="text-slate-800 text-[11px] truncate font-medium" title="{{ $ticket->resolution_notes }}">
                                        {{ $ticket->resolution_notes }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Petugas: {{ $ticket->assignedTo?->name ?? 'HR Officer' }}</div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum diproses</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button 
                                        @click="openDetailModal = true;
                                                selectedTicketId = '{{ $ticket->id }}';
                                                selectedTicketNumber = '{{ $ticket->ticket_number }}';
                                                selectedRequester = '{{ addslashes($ticket->employee?->full_name) }}';
                                                selectedCategory = '{{ $ticket->category }}';
                                                selectedSubject = '{{ addslashes($ticket->subject) }}';
                                                selectedDesc = '{{ addslashes($ticket->description) }}';
                                                selectedResolution = '{{ addslashes($ticket->resolution_notes ?? '') }}';"
                                        class="px-2.5 py-1 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-bold transition text-xs border border-slate-200">
                                        Detail
                                    </button>

                                    @if($ticket->status !== 'resolved' && $ticket->status !== 'closed')
                                        <button 
                                            @click="openResolveModal = true; 
                                                    selectedTicketId = '{{ $ticket->id }}'; 
                                                    selectedTicketNumber = '{{ $ticket->ticket_number }}';
                                                    selectedSubject = '{{ addslashes($ticket->subject) }}';"
                                            class="px-2.5 py-1 rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 font-bold transition text-xs border border-emerald-200">
                                            Selesaikan
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i data-lucide="check-check" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Tidak ada tiket helpdesk yang menunggu penanganan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: View Detail -->
    <div x-show="openDetailModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openDetailModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="life-buoy" class="w-5 h-5 text-blue-600"></i>
                    Detail Tiket Layanan HR
                </div>
                <button @click="openDetailModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <span class="font-bold text-slate-400 uppercase text-[10px] block">No. Tiket & Pemohon</span>
                    <div class="text-sm font-bold text-slate-900 mt-0.5"><span x-text="selectedTicketNumber"></span> &bull; <span x-text="selectedRequester"></span></div>
                </div>
                <div>
                    <span class="font-bold text-slate-400 uppercase text-[10px] block">Subjek Permohonan</span>
                    <div class="text-sm font-bold text-slate-800 mt-0.5" x-text="selectedSubject"></div>
                </div>
                <div>
                    <span class="font-bold text-slate-400 uppercase text-[10px] block">Rincian Permohonan</span>
                    <div class="mt-1 p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 leading-relaxed whitespace-pre-line" x-text="selectedDesc"></div>
                </div>
                <template x-if="selectedResolution">
                    <div>
                        <span class="font-bold text-emerald-600 uppercase text-[10px] block">Catatan Penyelesaian HR</span>
                        <div class="mt-1 p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-900 leading-relaxed whitespace-pre-line" x-text="selectedResolution"></div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" @click="openDetailModal = false" class="px-5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Resolve Ticket -->
    <div x-show="openResolveModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="openResolveModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-base">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                    Selesaikan Tiket Layanan
                </div>
                <button @click="openResolveModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('helpdesk') }}/' + selectedTicketId + '/resolve'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="text-xs text-slate-600">
                    Konfirmasi penyelesaian tiket: <strong class="text-slate-900" x-text="selectedTicketNumber"></strong> - <span class="text-slate-700" x-text="selectedSubject"></span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Penyelesaian & Nomor Dokumen <span class="text-red-500">*</span></label>
                    <textarea name="resolution_notes" rows="4" required placeholder="Contoh: Dokumen Surat Keterangan Kerja No. SK/HR/2026/042 telah diterbitkan dan diemailkan ke karyawan..." class="w-full text-xs rounded-xl border border-slate-200 p-3 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openResolveModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">
                        Selesaikan Tiket
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

