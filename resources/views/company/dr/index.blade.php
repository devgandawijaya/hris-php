@extends('layouts.app')

@section('title', 'Disaster Recovery & Multi-Region Failover')

@section('content')
<div class="space-y-6" x-data="{ drillModal: false, failoverModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                    High Availability & Business Continuity (BCP / DR)
                </span>
                <span class="text-xs text-slate-400">• Standar RPO $\le 5$ Menit & RTO $\le 15$ Menit</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Disaster Recovery & Sinkronisasi Multi-Region</h1>
            <p class="text-xs text-slate-500 mt-1">Pemantauan replikasi sinkron Jakarta-Singapore, pengujian latihan pemulihan bencana (*DR Drill*), dan mekanisme *automated emergency failover*.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="drillModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-white hover:bg-slate-900 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="play-circle" class="w-4 h-4"></i>
                Latihan DR Drill Baru
            </button>
            <button @click="failoverModal = true" class="px-3 py-2 rounded-xl text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="zap" class="w-4 h-4"></i>
                Emergency Failover
            </button>
        </div>
    </div>

    <!-- Active Topology Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Arsitektur Replikasi Multi-Wilayah</span>
                <h3 class="text-base font-black text-slate-900">Topologi Aktif: Primary Jakarta & Standby Singapore</h3>
            </div>
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                    {{ $config->health_status === 'healthy' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    Status: {{ $config->health_status }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
            <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100 space-y-1">
                <span class="text-blue-700 font-medium block">Wilayah Utama (Primary):</span>
                <span class="font-bold text-slate-900 text-sm block">{{ $config->primary_region }}</span>
                <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span> Read / Write Master
                </span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                <span class="text-slate-400 font-medium block">Wilayah Siaga (Standby):</span>
                <span class="font-bold text-slate-900 text-sm block">{{ $config->standby_region }}</span>
                <span class="text-[10px] text-slate-500">Synchronous Hot Standby</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                <span class="text-slate-400 font-medium block">Replication Lag Real-time:</span>
                <span class="font-black text-emerald-600 text-sm block">{{ $config->current_replication_lag_ms }} ms</span>
                <span class="text-[10px] text-slate-500">Sangat Rendah (Batas Aman: 1000 ms)</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                <span class="text-slate-400 font-medium block">Target RPO / RTO:</span>
                <span class="font-bold text-slate-900 text-sm block">RPO: {{ $config->rpo_target_minutes }}m | RTO: {{ $config->rto_target_minutes }}m</span>
                <span class="text-[10px] text-emerald-600 font-medium">99.99% Ketersediaan Data</span>
            </div>
        </div>
    </div>

    <!-- Drills Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="history" class="w-4 h-4 text-blue-600"></i>
            Riwayat Audit Latihan Pemulihan Bencana (Disaster Recovery Drills)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Tanggal Latihan</th>
                        <th class="py-3 px-3">Skenario Bencana</th>
                        <th class="py-3 px-3">Durasi Failover (RTO)</th>
                        <th class="py-3 px-3">Kehilangan Data (RPO)</th>
                        <th class="py-3 px-3">Lead Auditor</th>
                        <th class="py-3 px-3">Status Uji</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($drills as $drill)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3 font-semibold text-slate-900">{{ \Carbon\Carbon::parse($drill->drill_date)->format('d M Y') }}</td>
                            <td class="py-3 px-3 font-semibold text-slate-800">{{ str_replace('_', ' ', $drill->scenario_type) }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-emerald-700">
                                {{ $drill->actual_failover_duration_seconds }} Detik
                                <span class="text-[10px] text-slate-400 block font-normal">(Target: {{ $config->rto_target_minutes * 60 }} Detik)</span>
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-emerald-700">
                                {{ $drill->data_loss_records_count }} Record (Nihil)
                            </td>
                            <td class="py-3 px-3 text-slate-700">{{ $drill->lead_auditor_name }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $drill->status === 'passed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $drill->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Belum ada catatan latihan pemulihan bencana.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $drills->links() }}
        </div>
    </div>

    <!-- Modal Catat Drill Run -->
    <div x-show="drillModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Catat Hasil Latihan DR Failover</h3>
                <button @click="drillModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('disaster-recovery.drills.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Skenario Bencana Diuji</label>
                    <select name="scenario_type" required class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="primary_datacenter_outage">Pemadaman Listrik / Datacenter Utama Padam Total</option>
                        <option value="network_partition">Partisi Jaringan & Pemutusan Kabel Bawah Laut</option>
                        <option value="storage_corruption">Kegagalan Storage NVMe & Replikasi Crash</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Durasi Failover (Detik)</label>
                        <input type="number" name="actual_failover_duration_seconds" value="180" min="1" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kehilangan Data (Record)</label>
                        <input type="number" name="data_loss_records_count" value="0" min="0" required class="w-full rounded-xl border-slate-200 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Lead Auditor Infrastruktur</label>
                    <input type="text" name="lead_auditor_name" value="Tim SecOps & Cloud Infrastructure" required class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="drillModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white font-semibold hover:bg-black">Simpan Hasil Latihan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Emergency Failover -->
    <div x-show="failoverModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-rose-900">Konfirmasi Emergency Failover Switch</h3>
                <button @click="failoverModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs space-y-1">
                <span class="font-bold block">Peringatan Kritis:</span>
                <p>Tindakan ini akan mengalihkan peran Primary Master ke wilayah <strong>{{ $config->standby_region }}</strong>. Seluruh traffic database dan aplikasi SaaS akan diarahkan ke standby cluster.</p>
            </div>
            <form action="{{ route('disaster-recovery.failover') }}" method="POST">
                @csrf
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="failoverModal = false" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-600 text-white font-semibold hover:bg-rose-700">Eksekusi Failover Sekarang</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
