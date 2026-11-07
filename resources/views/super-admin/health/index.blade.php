@extends('layouts.app')

@section('title', 'Kesehatan Sistem & Telemetri SaaS')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    SaaS Telemetry
                </span>
                <span class="text-xs text-slate-400">• Phase 13 Enterprise</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Kesehatan Sistem & Finansial Platform</h1>
            <p class="text-xs text-slate-500 mt-1">Metrik pendapatan berulang bulanan (MRR), kapasitas database, dan diagnostik server.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('super.admin.plans.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4 text-red-600"></i>
                Kelola Paket Langganan
            </a>
            <a href="{{ route('super.admin.companies.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                Kelola Perusahaan
            </a>
        </div>
    </div>

    <!-- Financial KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">MRR (Monthly Recurring)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">Rp {{ number_format($financials['mrr'], 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Estimasi pendapatan bulanan berulang</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">ARR (Annual Run-Rate)</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">Rp {{ number_format($financials['arr'], 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Proyeksi tahunan (MRR × 12)</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">ARPU (Avg Revenue/User)</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">Rp {{ number_format($financials['arpu'], 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Rata-rata per penyewa aktif</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Tagihan Terbayar</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">Rp {{ number_format($financials['paid_invoices_sum'], 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Total akumulasi faktur lunas</div>
        </div>
    </div>

    <!-- Distribution & System Diagnostics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Plan Distribution -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="pie-chart" class="w-4 h-4 text-red-600"></i>
                Distribusi Paket Langganan Aktif
            </h2>
            <div class="space-y-3">
                @forelse($financials['plan_breakdown'] as $planName => $count)
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span>{{ $planName }}</span>
                            <span class="font-bold text-slate-900">{{ $count }} tenant</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-[#DC2626] h-2 rounded-full" style="width: {{ min(100, $count * 20) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">Belum ada data paket langganan aktif.</p>
                @endforelse
            </div>
        </div>

        <!-- System & Environment Health -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="cpu" class="w-4 h-4 text-emerald-600"></i>
                Diagnostik Server & PostgreSQL
            </h2>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] uppercase font-bold">PHP Version</div>
                    <div class="font-bold text-slate-800 mt-0.5">{{ $system['php_version'] }}</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] uppercase font-bold">Laravel Engine</div>
                    <div class="font-bold text-slate-800 mt-0.5">{{ $system['laravel_version'] }}</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] uppercase font-bold">Database Status</div>
                    <div class="font-bold text-emerald-600 mt-0.5 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Connected ({{ $system['database_driver'] }})
                    </div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl">
                    <div class="text-slate-400 text-[10px] uppercase font-bold">Memory Usage</div>
                    <div class="font-bold text-slate-800 mt-0.5">{{ $system['memory_usage_mb'] }} MB</div>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <div class="text-[11px] font-bold text-slate-500 uppercase mb-2">Volume Rekor Tabel Utama</div>
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="p-2 border border-slate-100 rounded-lg">
                        <div class="text-slate-400 text-[10px]">Tenants</div>
                        <div class="font-black text-slate-800">{{ $system['table_counts']['tenants'] }}</div>
                    </div>
                    <div class="p-2 border border-slate-100 rounded-lg">
                        <div class="text-slate-400 text-[10px]">Employees</div>
                        <div class="font-black text-slate-800">{{ $system['table_counts']['employees'] }}</div>
                    </div>
                    <div class="p-2 border border-slate-100 rounded-lg">
                        <div class="text-slate-400 text-[10px]">Audit Logs</div>
                        <div class="font-black text-slate-800">{{ $system['table_counts']['audit_logs'] }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
