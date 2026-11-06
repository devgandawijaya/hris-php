@extends('layouts.app')

@section('title', 'Workflow Engine & Persetujuan Berjenjang')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'queue', 
    openWorkflowModal: false, 
    openApproveModal: false, 
    openRejectModal: false,
    selectedRequestId: '',
    selectedRequestTitle: ''
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Workflow Engine & Persetujuan Berjenjang</h1>
            <p class="text-sm text-slate-500 mt-1">Konfigurasikan rantai persetujuan multi-level untuk pengajuan cuti, lembur, reimbursement, dan payroll tanpa perubahan kode.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openWorkflowModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Buat Alur Kerja Baru
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Antrean Persetujuan</div>
            <div class="text-xl font-black text-amber-600 mt-1">{{ number_format($pendingApprovals->total()) }} Pengajuan</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Menunggu respon verifikator</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Skema Workflow</div>
            <div class="text-xl font-black text-slate-900 mt-1">{{ number_format($workflows->count()) }} Skema</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Aktif lintas modul operasional</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Audit Trail Persetujuan</div>
            <div class="text-xl font-black text-emerald-600 mt-1">100% Tercatat</div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">Lengkap dengan timestamp & IP</div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px">
        <button type="button" @click="activeTab = 'queue'"
                :class="activeTab === 'queue' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="inbox" class="w-4 h-4"></i>
            Antrean Persetujuan Berjalan ({{ $pendingApprovals->total() }})
        </button>

        <button type="button" @click="activeTab = 'definitions'"
                :class="activeTab === 'definitions' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2">
            <i data-lucide="git-branch" class="w-4 h-4"></i>
            Konfigurasi Skema Alur Kerja ({{ $workflows->count() }})
        </button>
    </div>

    <!-- TAB 1: ANTREAN PERSETUJUAN -->
    <div x-show="activeTab === 'queue'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Daftar Pengajuan Menunggu Persetujuan</h2>
            <span class="text-xs text-slate-500">Tindakan approver berwenang</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Jenis Alur Kerja</th>
                        <th class="px-5 py-3.5">Objek Pengajuan</th>
                        <th class="px-5 py-3.5">Langkah Berjalan</th>
                        <th class="px-5 py-3.5">Waktu Diajukan</th>
                        <th class="px-5 py-3.5 text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingApprovals as $req)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4">
                                <span class="font-bold text-slate-900 block">{{ $req->workflow?->name }}</span>
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $req->workflow?->entity_type }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-700 font-medium">
                                <div class="font-bold text-slate-900">
                                    {{ class_basename($req->approvable_type) }} #{{ $req->approvable_id }}
                                </div>
                                <div class="text-[11px] text-slate-500">ID Referensi: {{ $req->approvable_id }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                    Tahap {{ $req->current_step }} dari {{ $req->workflow?->steps->count() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500 font-mono text-[11px]">
                                {{ $req->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-4 text-right space-x-2">
                                <button 
                                    @click="selectedRequestId = '{{ $req->id }}'; selectedRequestTitle = '{{ class_basename($req->approvable_type) }} #{{ $req->approvable_id }}'; openApproveModal = true"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700 transition"
                                >
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    Setujui
                                </button>
                                <button 
                                    @click="selectedRequestId = '{{ $req->id }}'; selectedRequestTitle = '{{ class_basename($req->approvable_type) }} #{{ $req->approvable_id }}'; openRejectModal = true"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-red-600 text-white text-[11px] font-bold hover:bg-red-700 transition"
                                >
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    Tolak
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                Tidak ada pengajuan yang membutuhkan persetujuan Anda saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $pendingApprovals->links() }}
        </div>
    </div>

    <!-- TAB 2: KONFIGURASI SKEMA WORKFLOW -->
    <div x-show="activeTab === 'definitions'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($workflows as $wf)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">{{ $wf->name }}</h3>
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Modul: {{ $wf->entity_type }}</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $wf->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                        {{ $wf->is_active ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-700 block">Rantai Persetujuan Berjenjang:</span>
                    <div class="space-y-2">
                        @foreach($wf->steps as $s)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/60 text-xs">
                                <div class="w-6 h-6 rounded-lg bg-red-100 text-red-700 font-black flex items-center justify-center text-[11px]">
                                    {{ $s->step_order }}
                                </div>
                                <div class="flex-1">
                                    <span class="font-bold text-slate-900">Level {{ $s->step_order }}:</span>
                                    <span class="text-slate-600 font-mono text-[11px] ml-1">{{ $s->approver_role_slug }}</span>
                                </div>
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 p-12 text-center bg-white rounded-2xl border border-slate-200 shadow-sm text-slate-400">
                Belum ada skema alur kerja kustom yang didefinisikan.
            </div>
        @endforelse
    </div>

    <!-- Modal 1: Buat Alur Kerja Baru -->
    <div x-show="openWorkflowModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openWorkflowModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900">Definisikan Skema Alur Kerja</h3>
                <button @click="openWorkflowModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.workflows.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Alur Kerja <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Leave Approval 3-Level" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Modul Transaksi <span class="text-red-500">*</span></label>
                    <select name="entity_type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-slate-700 focus:ring-2 focus:ring-red-500 focus:outline-none">
                        <option value="leave">Pengajuan Cuti (Leave)</option>
                        <option value="reimbursement">Klaim Biaya (Reimbursement)</option>
                        <option value="overtime">Surat Perintah Lembur (Overtime)</option>
                        <option value="payroll">Pengesahan Gaji Bulanan (Payroll)</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Urutan Peran Penyetuju (Pilih minimal 1):</label>
                    <div class="space-y-2">
                        <div>
                            <span class="text-[11px] font-bold text-slate-500 block mb-1">Level 1 (Penyetuju Pertama):</span>
                            <select name="approver_role_slugs[]" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium">
                                @foreach($roles as $r)
                                    <option value="{{ $r->slug }}" {{ $r->slug === 'hr-manager' ? 'selected' : '' }}>{{ $r->name }} ({{ $r->slug }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-500 block mb-1">Level 2 (Penyetuju Kedua):</span>
                            <select name="approver_role_slugs[]" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium">
                                <option value="">-- Tanpa Level 2 --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->slug }}" {{ $r->slug === 'company-owner' ? 'selected' : '' }}>{{ $r->name }} ({{ $r->slug }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="openWorkflowModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] shadow-sm shadow-red-500/20">Simpan Alur Kerja</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Setujui Permohonan -->
    <div x-show="openApproveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openApproveModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-black text-slate-900">Konfirmasi Persetujuan Langkah</h3>
            <p class="text-xs text-slate-500 mt-1" x-text="'Pengajuan: ' + selectedRequestTitle"></p>

            <form :action="'/company/workflows/' + selectedRequestId + '/approve'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Persetujuan (Opsional)</label>
                    <textarea name="comment" rows="3" placeholder="Disetujui sesuai regulasi dan plafon anggaran..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="openApproveModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">Setujui Permohonan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Tolak Permohonan -->
    <div x-show="openRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openRejectModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-black text-slate-900 text-red-600">Konfirmasi Penolakan</h3>
            <p class="text-xs text-slate-500 mt-1" x-text="'Pengajuan: ' + selectedRequestTitle"></p>

            <form :action="'/company/workflows/' + selectedRequestId + '/reject'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="reason" required rows="3" placeholder="Sebutkan alasan penolakan secara jelas..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="openRejectModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-700">Tolak Permohonan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

