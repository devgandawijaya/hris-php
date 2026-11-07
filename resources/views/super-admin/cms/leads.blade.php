@extends('layouts.app')

@section('title', 'Prospek Lead Landing Page (CRM)')

@section('content')
<div class="space-y-6" x-data="{ editModal: false, activeLead: {} }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                    Inbound CRM
                </span>
                <span class="text-xs text-slate-400">• Phase 13 Enterprise</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Manajemen Prospek Lead (CRM)</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar pertanyaan dan permintaan demo dari calon pelanggan publik.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('super.admin.cms.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="globe" class="w-4 h-4 text-red-600"></i>
                Editor CMS Publik
            </a>
        </div>
    </div>

    <!-- Lead Status Counter Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <a href="{{ route('super.admin.cms.leads.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-slate-300 transition">
            <div class="text-[11px] font-bold text-slate-500 uppercase">Semua Lead</div>
            <div class="text-xl font-black text-slate-900 mt-1">{{ $metrics['total'] }}</div>
        </a>
        <a href="{{ route('super.admin.cms.leads.index', ['status' => 'new']) }}" class="bg-white p-4 rounded-xl border border-amber-200 shadow-sm hover:border-amber-300 transition">
            <div class="text-[11px] font-bold text-amber-700 uppercase">Baru (New)</div>
            <div class="text-xl font-black text-amber-600 mt-1">{{ $metrics['new'] }}</div>
        </a>
        <a href="{{ route('super.admin.cms.leads.index', ['status' => 'contacted']) }}" class="bg-white p-4 rounded-xl border border-blue-200 shadow-sm hover:border-blue-300 transition">
            <div class="text-[11px] font-bold text-blue-700 uppercase">Dihubungi</div>
            <div class="text-xl font-black text-blue-600 mt-1">{{ $metrics['contacted'] }}</div>
        </a>
        <a href="{{ route('super.admin.cms.leads.index', ['status' => 'qualified']) }}" class="bg-white p-4 rounded-xl border border-indigo-200 shadow-sm hover:border-indigo-300 transition">
            <div class="text-[11px] font-bold text-indigo-700 uppercase">Qualified</div>
            <div class="text-xl font-black text-indigo-600 mt-1">{{ $metrics['qualified'] }}</div>
        </a>
        <a href="{{ route('super.admin.cms.leads.index', ['status' => 'converted']) }}" class="bg-white p-4 rounded-xl border border-emerald-200 shadow-sm hover:border-emerald-300 transition">
            <div class="text-[11px] font-bold text-emerald-700 uppercase">Konversi Tenant</div>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ $metrics['converted'] }}</div>
        </a>
    </div>

    <!-- Leads Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Formulir Kontak Masuk</h2>
            <form action="{{ route('super.admin.cms.leads.index') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, perusahaan..." class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs w-64">
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Calon Klien / Perusahaan</th>
                        <th class="px-4 py-3 font-bold">Kontak</th>
                        <th class="px-4 py-3 font-bold">Pesan Kebutuhan</th>
                        <th class="px-4 py-3 font-bold">Status CRM</th>
                        <th class="px-4 py-3 font-bold">Tanggal Masuk</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <div class="font-bold">{{ $lead->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $lead->company ?? 'Individu' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>{{ $lead->email }}</div>
                                <div class="text-[11px] text-slate-400">{{ $lead->phone ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 max-w-xs text-slate-700 truncate" title="{{ $lead->message }}">
                                {{ $lead->message }}
                            </td>
                            <td class="px-4 py-3">
                                @if($lead->status === 'new')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Baru</span>
                                @elseif($lead->status === 'contacted')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Dihubungi</span>
                                @elseif($lead->status === 'qualified')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">Qualified</span>
                                @elseif($lead->status === 'converted')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Menjadi Tenant</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Ditutup</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                {{ $lead->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="activeLead = {{ json_encode($lead) }}; editModal = true" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded font-bold text-xs">
                                    Update CRM
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Belum ada pesan kontak dari landing page.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $leads->links() }}
        </div>
    </div>

    <!-- Update Lead Modal -->
    <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Perbarui Status Prospek</h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="'/super-admin/cms/leads/' + activeLead.id" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <div class="text-[11px] text-slate-500 font-bold uppercase mb-1">Nama Calon Klien</div>
                    <div class="font-bold text-slate-900" x-text="activeLead.name + ' (' + (activeLead.company || 'Individu') + ')'"></div>
                </div>
                <div>
                    <div class="text-[11px] text-slate-500 font-bold uppercase mb-1">Isi Pesan</div>
                    <div class="p-2.5 bg-slate-50 rounded-lg text-slate-700 italic text-[11px]" x-text="activeLead.message"></div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status Tindak Lanjut</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" :value="activeLead.status">
                        <option value="new">Baru (New)</option>
                        <option value="contacted">Sedang Dihubungi (Contacted)</option>
                        <option value="qualified">Peluang Matang (Qualified)</option>
                        <option value="converted">Konversi Jadi Tenant (Converted)</option>
                        <option value="closed">Ditutup / Tidak Tertarik (Closed)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Follow-up Internal</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs" placeholder="Tulis catatan respon meeting atau demo..." :value="activeLead.notes"></textarea>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Tutup</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-lg font-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
