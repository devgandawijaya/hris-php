@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 pb-6 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#DC2626] mb-1">
                <i data-lucide="clock" class="w-4 h-4"></i>
                Indonesian Compliance & Task Automation (Section 11 & 16)
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Automasi & Background Cron Scheduler</h1>
            <p class="text-xs text-slate-500 mt-1">Pemantauan tugas terjadwal otomatis (Worker Scheduler) kepatuhan regulasi ketenagakerjaan dan log eksekusi.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-2 text-xs font-bold text-slate-500 bg-slate-100 px-3.5 py-2 rounded-xl">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Laravel Scheduler: Active (Docker Service)
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 4 Core Automated Jobs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        @foreach($jobs as $job)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                                <i data-lucide="cpu" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $job['name'] }}</h3>
                                <div class="text-[10px] font-mono text-slate-400 font-semibold">{{ $job['command'] }}</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700">
                            {{ $job['schedule'] }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        {{ $job['description'] }}
                    </p>

                    @if($job['last_log'])
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 text-xs mb-4">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1">
                                <span>Eksekusi Terakhir:</span>
                                <span class="{{ $job['last_log']->status === 'success' ? 'text-emerald-600' : 'text-red-600' }}">
                                    ● {{ strtoupper($job['last_log']->status) }} ({{ $job['last_log']->executed_at->diffForHumans() }})
                                </span>
                            </div>
                            <div class="text-slate-700 font-medium text-[11px]">{{ $job['last_log']->summary }}</div>
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 text-[11px] text-slate-400 italic mb-4">
                            Belum ada riwayat eksekusi tercatat di database.
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">Otomatisasi Latar Belakang</span>
                    <form method="POST" action="{{ route('company.cron.run') }}">
                        @csrf
                        <input type="hidden" name="command" value="{{ $job['command'] }}">
                        <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-1.5">
                            <i data-lucide="play" class="w-3.5 h-3.5 text-slate-500"></i>
                            Jalankan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Execution Logs History Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Riwayat Log Eksekusi Tugas (Audit Trail Cron)</h2>
                <p class="text-xs text-slate-500">Catatan riwayat tugas otomatis, jumlah baris data yang diproses, dan status kelulusan sistem.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $logs->total() }} Log Tersimpan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="p-3.5">Nama Perintah</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-center">Data Diproses</th>
                        <th class="p-3.5">Ringkasan Hasil Eksekusi</th>
                        <th class="p-3.5 text-right">Waktu Eksekusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3.5">
                                <span class="font-mono font-bold text-slate-900">{{ $log->command_name }}</span>
                            </td>
                            <td class="p-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }} uppercase">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center font-bold text-slate-800">
                                {{ $log->items_processed }} Item
                            </td>
                            <td class="p-3.5 text-slate-600 max-w-md">
                                {{ $log->summary }}
                            </td>
                            <td class="p-3.5 text-right text-slate-400 font-mono text-[11px]">
                                {{ $log->executed_at->format('d M Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">Belum ada riwayat eksekusi cron log yang tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
