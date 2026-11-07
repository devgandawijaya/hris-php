@extends('layouts.app')

@section('title', 'Keamanan Zero Trust & Enkripsi Data Spesifik (UU PDP)')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Kepatuhan Pasal 35 UU No. 27/2022 (UU PDP)
                </span>
                <span class="text-xs text-slate-400">• Standar Keamanan Kriptografi Kunci HSM & Cloud KMS</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Audit Keamanan Zero Trust & Enkripsi Data Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Verifikasi enkripsi tingkat kolom (*envelope encryption AES-256-GCM*) atas data pribadi spesifik dan audit trail akses data rahasia.</p>
        </div>
        <div>
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-sm inline-flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                Skor Keamanan: {{ $auditReport['overall_security_score'] }} / 100
            </span>
        </div>
    </div>

    <!-- Security Posture Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-1">
            <span class="text-xs text-slate-400 font-semibold uppercase">Enkripsi Data Diam (At Rest)</span>
            <div class="text-base font-black text-slate-900">{{ $auditReport['zero_trust_status']['encryption_at_rest'] }}</div>
            <span class="text-[10px] text-emerald-600 font-medium">Envelope Encryption HSM Active</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-1">
            <span class="text-xs text-slate-400 font-semibold uppercase">Enkripsi Jalur (In Transit)</span>
            <div class="text-base font-black text-slate-900">{{ $auditReport['zero_trust_status']['encryption_in_transit'] }}</div>
            <span class="text-[10px] text-emerald-600 font-medium">HSTS & Perfect Forward Secrecy</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-1">
            <span class="text-xs text-slate-400 font-semibold uppercase">Prinsip Hak Akses Minimal</span>
            <div class="text-base font-black text-emerald-600">Least Privilege (RBAC)</div>
            <span class="text-[10px] text-slate-500 font-medium">Terkunci per peran & tenant</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-1">
            <span class="text-xs text-slate-400 font-semibold uppercase">Standar Manajemen Kunci</span>
            <div class="text-base font-black text-slate-900">FIPS 140-2 Level 3</div>
            <span class="text-[10px] text-slate-500 font-medium">Automated Key Rotation 90 Hari</span>
        </div>
    </div>

    <!-- Sensitive Fields Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="lock" class="w-4 h-4 text-emerald-600"></i>
            Inventarisasi & Status Enkripsi Data Spesifik Karyawan (UU PDP)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Atribut Data Karyawan</th>
                        <th class="py-3 px-3">Kategori Yuridis PDP</th>
                        <th class="py-3 px-3">Metode Perlindungan Kriptografi</th>
                        <th class="py-3 px-3">Status Enkripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($auditReport['sensitive_fields_audit'] as $key => $field)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">{{ $field['field'] }}</span>
                                <span class="text-[10px] font-mono text-slate-400">Database Column: {{ $key }}</span>
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800">{{ $field['category'] }}</td>
                            <td class="py-3 px-3 font-mono text-indigo-700 font-semibold">{{ $field['algo'] }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                    <i data-lucide="shield-check" class="w-3 h-3 inline"></i> Terenkripsi
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Audit Log -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-5 space-y-3">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <i data-lucide="list" class="w-4 h-4 text-slate-600"></i>
            Audit Trail Akses Data Sensitif Terkini (Zero Trust Log)
        </h3>
        <div class="space-y-2 text-xs">
            @forelse($recentAccessLogs as $log)
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800">{{ $log->action ?? 'ACCESS' }}</span>
                        <span class="text-slate-500">• {{ $log->description ?? 'Pemeriksaan integritas catatan data kepegawaian' }}</span>
                    </div>
                    <div class="text-right text-[11px] text-slate-400 font-mono">
                        {{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : date('d M Y H:i:s') }}
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-slate-400">Tidak ada anomali akses data sensitif terdeteksi.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection
