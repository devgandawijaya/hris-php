@extends('layouts.app')

@section('title', 'Manajemen Perusahaan')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Daftar Perusahaan Penyewa (Tenants)</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola seluruh organisasi terdaftar, status masa aktif langganan, dan pembatasan akses.</p>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('super.admin.companies.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-xl border border-slate-300 pl-10 pr-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                       placeholder="Cari berdasarkan nama perusahaan atau slug...">
            </div>

            <div class="sm:col-span-4">
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active (Aktif)</option>
                    <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial (Uji Coba)</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended (Ditangguhkan)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired (Kedaluwarsa)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('super.admin.companies.index') }}" class="py-2 px-3 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition flex items-center justify-center" title="Reset filter">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Companies Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3.5">Perusahaan</th>
                        <th class="px-5 py-3.5">Subdomain Slug</th>
                        <th class="px-5 py-3.5">Paket Langganan</th>
                        <th class="px-5 py-3.5">Pengguna Terhubung</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $tenant->name }}</div>
                                <div class="text-[11px] text-slate-400">UUID: {{ substr($tenant->uuid, 0, 8) }}...</div>
                            </td>
                            <td class="px-5 py-4 font-mono text-[11px] text-slate-600">
                                {{ $tenant->slug }}
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-semibold text-[11px]">
                                    <i data-lucide="sparkles" class="w-3 h-3 text-amber-500"></i>
                                    {{ $tenant->activeSubscription?->plan?->name ?? 'Free Trial' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-semibold text-slate-700">{{ $tenant->users_count }} Akun</span>
                            </td>
                            <td class="px-5 py-4">
                                @if($tenant->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                @elseif($tenant->status === 'trial')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Trial</span>
                                @elseif($tenant->status === 'suspended')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Suspended</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-300">{{ $tenant->status }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-2">
                                <a href="{{ route('super.admin.companies.show', $tenant->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition text-xs font-semibold">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Detail
                                </a>

                                <form action="{{ route('super.admin.companies.toggle-status', $tenant->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status operasional perusahaan ini?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $tenant->status === 'active' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                                        {{ $tenant->status === 'active' ? 'Suspend' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                Tidak ditemukan data perusahaan yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

