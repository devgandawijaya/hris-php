@extends('layouts.app')

@section('title', 'Manajemen Paket Langganan SaaS')

@section('content')
<div class="space-y-6" x-data="{ createModal: false, editModal: false, activePlan: {} }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-red-100 text-red-800 border border-red-200">
                    SaaS Pricing & Limits
                </span>
                <span class="text-xs text-slate-400">• Phase 13 Enterprise</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Paket Langganan & Batasan Fitur</h1>
            <p class="text-xs text-slate-500 mt-1">Konfigurasi kuota karyawan maksimal, kapasitas penyimpanan, dan flag akses fitur per paket.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Paket Baru
            </button>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white rounded-2xl border {{ $plan->is_active ? 'border-slate-200 shadow-sm' : 'border-slate-100 opacity-60 bg-slate-50' }} p-6 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $plan->is_active ? 'bg-red-50 text-red-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $plan->name }}
                        </span>
                        <span class="text-xs text-slate-400 font-semibold">{{ $plan->subscriptions_count }} Tenant</span>
                    </div>

                    <div class="mt-4">
                        <div class="text-3xl font-black text-slate-900">
                            Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-500">/bln</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Rp {{ number_format($plan->price_yearly, 0, ',', '.') }} /tahun
                        </div>
                    </div>

                    <!-- Quotas & Limits -->
                    <div class="mt-6 space-y-2.5 text-xs text-slate-700">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <span class="text-slate-500">Maksimal Karyawan:</span>
                            <span class="font-bold text-slate-900">{{ $plan->max_employees >= 99999 ? 'Unlimited' : number_format($plan->max_employees) }} orang</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <span class="text-slate-500">Kapasitas Penyimpanan:</span>
                            <span class="font-bold text-slate-900">{{ number_format($plan->storage_limit_mb / 1024, 1) }} GB</span>
                        </div>
                    </div>

                    <!-- Feature Badges -->
                    <div class="mt-4 space-y-1.5 text-xs">
                        <div class="flex items-center gap-2 {{ $plan->has_api_access ? 'text-emerald-700 font-semibold' : 'text-slate-400 line-through' }}">
                            <i data-lucide="{{ $plan->has_api_access ? 'check-circle' : 'x-circle' }}" class="w-3.5 h-3.5"></i>
                            REST API v1 Enterprise
                        </div>
                        <div class="flex items-center gap-2 {{ $plan->has_whatsapp ? 'text-emerald-700 font-semibold' : 'text-slate-400 line-through' }}">
                            <i data-lucide="{{ $plan->has_whatsapp ? 'check-circle' : 'x-circle' }}" class="w-3.5 h-3.5"></i>
                            Notifikasi WhatsApp
                        </div>
                        <div class="flex items-center gap-2 {{ $plan->has_custom_workflows ? 'text-emerald-700 font-semibold' : 'text-slate-400 line-through' }}">
                            <i data-lucide="{{ $plan->has_custom_workflows ? 'check-circle' : 'x-circle' }}" class="w-3.5 h-3.5"></i>
                            Workflow Persetujuan Kustom
                        </div>
                        <div class="flex items-center gap-2 {{ $plan->has_bank_disbursal ? 'text-emerald-700 font-semibold' : 'text-slate-400 line-through' }}">
                            <i data-lucide="{{ $plan->has_bank_disbursal ? 'check-circle' : 'x-circle' }}" class="w-3.5 h-3.5"></i>
                            Disbursal Bank Nasional (BCA/Mandiri/BNI)
                        </div>
                        <div class="flex items-center gap-2 {{ $plan->has_ebupot_export ? 'text-emerald-700 font-semibold' : 'text-slate-400 line-through' }}">
                            <i data-lucide="{{ $plan->has_ebupot_export ? 'check-circle' : 'x-circle' }}" class="w-3.5 h-3.5"></i>
                            Ekspor Pajak CSV e-Bupot 21/26
                        </div>
                        <div class="flex items-center gap-2 {{ $plan->has_digital_contracts ? 'text-emerald-700 font-semibold' : 'text-slate-400 line-through' }}">
                            <i data-lucide="{{ $plan->has_digital_contracts ? 'check-circle' : 'x-circle' }}" class="w-3.5 h-3.5"></i>
                            Kontrak Digital & TTE UU ITE
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <form action="{{ route('super.admin.plans.toggle', $plan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs font-semibold {{ $plan->is_active ? 'text-amber-600 hover:text-amber-700' : 'text-emerald-600 hover:text-emerald-700' }}">
                            {{ $plan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <button @click="activePlan = {{ json_encode($plan) }}; editModal = true" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold transition">
                        Edit Batasan
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Plan Modal -->
    <div x-show="createModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Buat Paket Langganan Baru</h3>
                <button @click="createModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('super.admin.plans.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Paket</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="e.g. Enterprise Plus">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Harga Bulanan (Rp)</label>
                        <input type="number" name="price_monthly" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="1500000">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Harga Tahunan (Rp)</label>
                        <input type="number" name="price_yearly" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="15000000">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Batas Maks. Karyawan</label>
                        <input type="number" name="max_employees" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="150">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kapasitas Storage (MB)</label>
                        <input type="number" name="storage_limit_mb" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="10240">
                    </div>
                </div>
                <div class="pt-2">
                    <label class="block font-bold text-slate-700 mb-2">Akses Fitur Premium</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2"><input type="checkbox" name="has_api_access" value="1"> REST API v1</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="has_whatsapp" value="1"> WhatsApp Alerts</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="has_custom_workflows" value="1"> Custom Workflows</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="has_bank_disbursal" value="1"> Bank Disbursal</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="has_ebupot_export" value="1"> e-Bupot CSV</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="has_digital_contracts" value="1"> E-Signature UU ITE</label>
                    </div>
                </div>
                <div class="pt-4 flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-lg font-bold">Simpan Paket</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
