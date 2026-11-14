@extends('layouts.app')

@section('title', 'Integrasi, API & WhatsApp')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ $initialTab ?? 'api' }}', 
    openApiKeyModal: false, 
    openWebhookModal: false 
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <i data-lucide="network" class="w-7 h-7 text-red-600"></i>
                Integrasi Eksternal, OpenAPI & Webhooks
            </h1>
            <p class="text-sm text-slate-500 mt-1">Kelola token REST API, spesifikasi OpenAPI 3.1 untuk pihak ketiga, langganan webhook event-driven, dan notifikasi WhatsApp.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('company.integrations.openapi') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm">
                <i data-lucide="terminal" class="w-3.5 h-3.5 text-red-500"></i>
                <span>OpenAPI 3.1 Portal</span>
            </a>
            <button @click="openApiKeyModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="key" class="w-3.5 h-3.5"></i>
                Generate API Key Baru
            </button>
            <button @click="openWebhookModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-sm">
                <i data-lucide="webhook" class="w-3.5 h-3.5 text-slate-500"></i>
                Tambah Webhook
            </button>
        </div>
    </div>

    <!-- Alert Success / Plain Key Display -->
    @if(session('api_key_created'))
        <div class="p-5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 space-y-2">
            <div class="flex items-center gap-2 font-black text-sm">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
                Kunci Rahasia API Berhasil Dibuat (Simpan Sekarang!)
            </div>
            <p class="text-xs text-amber-800">
                Kunci rahasia berikut hanya ditampilkan <strong>satu kali saja</strong> demi keamanan. Harap salin dan simpan di tempat yang aman untuk aplikasi/mesin pihak ketiga Anda.
            </p>
            <div class="flex items-center gap-2 pt-1">
                <input type="text" readonly value="{{ session('api_key_created')['plain_key'] }}" class="w-full text-xs font-mono font-bold bg-white rounded-xl border border-amber-300 px-3 py-2 text-slate-900 select-all">
            </div>
        </div>
    @endif

    @if(session('success') && !session('api_key_created'))
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

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px overflow-x-auto">
        <button type="button" @click="activeTab = 'api'"
                :class="activeTab === 'api' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2 shrink-0">
            <i data-lucide="key" class="w-4 h-4"></i>
            Enterprise REST API Keys ({{ $apiKeys->count() }})
        </button>

        <a href="{{ route('company.integrations.openapi') }}"
           class="px-4 py-2.5 border-b-2 border-transparent text-slate-600 hover:text-red-600 font-semibold text-xs transition flex items-center gap-2 shrink-0">
            <i data-lucide="layers" class="w-4 h-4 text-red-500"></i>
            OpenAPI 3.1 & Developer Console
            <span class="px-1.5 py-0.2 rounded-full bg-red-100 text-red-700 text-[9px] font-bold">New</span>
        </a>

        <button type="button" @click="activeTab = 'webhooks'"
                :class="activeTab === 'webhooks' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2 shrink-0">
            <i data-lucide="webhook" class="w-4 h-4"></i>
            Webhook Endpoints ({{ $webhooks->count() }})
        </button>

        <button type="button" @click="activeTab = 'whatsapp'"
                :class="activeTab === 'whatsapp' ? 'border-[#DC2626] text-[#DC2626] font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-4 py-2.5 border-b-2 text-xs transition flex items-center gap-2 shrink-0">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            WhatsApp Dispatcher & Log ({{ $waLogs->count() }})
        </button>
    </div>

    <!-- TAB 1: API KEYS -->
    <div x-show="activeTab === 'api'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Kunci Akses API (X-API-KEY / Bearer Token)</h2>
                <span class="text-xs text-slate-500">Otentikasi aman mesin-ke-mesin untuk integrasi pihak ketiga</span>
            </div>
            <a href="{{ route('company.integrations.openapi') }}" class="text-xs font-bold text-red-600 hover:underline flex items-center gap-1">
                <span>Buka OpenAPI Console</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Nama Token / Klien</th>
                        <th class="px-5 py-3.5">Prefix Token</th>
                        <th class="px-5 py-3.5">Hak Akses (Scopes)</th>
                        <th class="px-5 py-3.5">Terakhir Digunakan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($apiKeys as $key)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $key->name }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ $key->key_prefix }}••••••••••••••••
                            </td>
                            <td class="px-5 py-4">
                                @if(is_array($key->permissions) && in_array('*', $key->permissions))
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Akses Penuh (*)</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach((array) $key->permissions as $p)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-700 border border-slate-200">{{ $p }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-500 font-mono text-[11px]">
                                {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Belum pernah' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($key->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Dinonaktifkan
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($key->is_active)
                                    <form action="{{ route('company.integrations.api-keys.revoke', $key) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan API key ini? Sistem eksternal yang menggunakannya tidak akan dapat mengakses lagi.')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-[11px] font-bold transition">
                                            Cabut Kunci
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada Kunci API yang dibuat. Klik tombol "Generate API Key Baru" untuk mengintegrasikan mesin fingerprint atau sistem pihak ketiga.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: WEBHOOKS -->
    <div x-show="activeTab === 'webhooks'" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Langganan Webhook Outbound</h2>
            <span class="text-xs text-slate-500">Notifikasi event instan ke sistem Anda (HMAC-SHA256)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Nama Integrasi</th>
                        <th class="px-5 py-3.5">URL Target</th>
                        <th class="px-5 py-3.5">Event yang Dilanggan</th>
                        <th class="px-5 py-3.5">Total Terkirim</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Uji Coba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($webhooks as $wh)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ $wh->name }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600 max-w-xs truncate">
                                {{ $wh->target_url }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach((array) $wh->events as $ev)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700">
                                            {{ $ev }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600 font-bold">
                                {{ $wh->deliveries_count }}x
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Aktif
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('company.integrations.webhooks.test', $wh) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-[11px] font-bold transition flex items-center gap-1.5 ml-auto">
                                        <i data-lucide="send" class="w-3 h-3 text-emerald-600"></i>
                                        Test Ping
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada endpoint Webhook yang terdaftar. Daftarkan URL server Anda untuk menerima notifikasi real-time saat ada event presensi, cuti, atau payroll.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: WHATSAPP NOTIFICATION -->
    <div x-show="activeTab === 'whatsapp'" x-cloak class="space-y-6">
        <!-- Test Dispatch Form -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4 max-w-xl">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4 text-emerald-600"></i>
                    Simulasi Kirim Notifikasi WhatsApp
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Uji coba pengiriman pesan instan ke nomor WhatsApp karyawan.</p>
            </div>

            <form action="{{ route('company.integrations.whatsapp.test') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Tujuan <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" required placeholder="Contoh: 082218866002" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Isi Pesan Notifikasi <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="3" required placeholder="Pesan pengingat presensi atau informasi pengumuman kantor..." class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition shadow-sm shadow-emerald-500/20">
                    Kirim Pesan Simulasi
                </button>
            </form>
        </div>

        <!-- Dispatch Logs Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900">Riwayat Pengiriman Notifikasi WhatsApp</h2>
                <span class="text-xs text-slate-500">20 Pengiriman Terakhir</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <th class="px-5 py-3.5">Nomor Tujuan</th>
                            <th class="px-5 py-3.5">Template</th>
                            <th class="px-5 py-3.5">Pratinjau Pesan</th>
                            <th class="px-5 py-3.5">Waktu Kirim</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($waLogs as $log)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                    {{ $log->phone_number }}
                                </td>
                                <td class="px-5 py-4 font-bold text-slate-700 uppercase text-[10px]">
                                    {{ $log->template_name }}
                                </td>
                                <td class="px-5 py-4 text-slate-600 max-w-sm truncate">
                                    {{ $log->message_content }}
                                </td>
                                <td class="px-5 py-4 text-slate-500 font-mono text-[11px]">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Terkirim
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada log pesan WhatsApp yang terkirim.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal 1: Generate API Key -->
    <div x-show="openApiKeyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openApiKeyModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-black text-slate-900">Generate Enterprise API Key</h3>
            <p class="text-xs text-slate-500 mt-1">Kunci ini akan digunakan oleh sistem pihak ketiga (ERP, Mesin Biometrik Fingerprint, SuperApp HR) untuk mengakses data secara terprogram.</p>

            <form action="{{ route('company.integrations.api-keys.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Aplikasi / Klien <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Mesin Absensi Fingerprint ZKTeco Gate 1" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Pilih Hak Akses / Scope:</label>
                    <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1 custom-scrollbar text-xs">
                        @foreach($availableScopes as $scopeKey => $scopeLabel)
                            <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $scopeKey }}" {{ $scopeKey === '*' ? 'checked' : '' }} class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                <span class="text-slate-800 text-[11px]">{{ $scopeLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="openApiKeyModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B]">Generate Key</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Buat Webhook -->
    <div x-show="openWebhookModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openWebhookModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-black text-slate-900">Daftarkan Webhook Endpoint</h3>
            <p class="text-xs text-slate-500 mt-1">Sistem eksternal Anda akan menerima HTTP POST dengan signature SHA-256 setiap kali event terjadi.</p>

            <form action="{{ route('company.integrations.webhooks.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Integrasi <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Slack HR Alerts" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">URL Target Webhook <span class="text-red-500">*</span></label>
                    <input type="url" name="target_url" required placeholder="https://api.perusahaan.com/webhooks/rihris" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-mono text-slate-800">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Pilih Event Langganan:</label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="events[]" value="*" checked class="text-red-600 focus:ring-red-500 rounded">
                        Semua Event (*)
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="events[]" value="employee.created" class="text-red-600 focus:ring-red-500 rounded">
                        Karyawan Baru Terdaftar (employee.created)
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="events[]" value="attendance.recorded" class="text-red-600 focus:ring-red-500 rounded">
                        Presensi Masuk/Pulang (attendance.recorded)
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="events[]" value="leave.approved" class="text-red-600 focus:ring-red-500 rounded">
                        Cuti Disetujui (leave.approved)
                    </label>
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="events[]" value="payroll.finalized" class="text-red-600 focus:ring-red-500 rounded">
                        Gaji Diproses (payroll.finalized)
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="openWebhookModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800">Simpan Webhook</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
