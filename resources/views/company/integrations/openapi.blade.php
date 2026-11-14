@extends('layouts.app')

@section('title', 'Open API 3.1 Developer Portal & Integrasi Pihak Ketiga — RiHRIS')

@section('content')
<div class="space-y-6" x-data="{
    selectedEndpoint: 'get_employees',
    apiKey: '{{ $apiKeys->first()?->key_prefix ?? 'ri_live_' }}sample_key_here',
    requestMethod: 'GET',
    requestPath: '/employees',
    requestBody: '',
    responseStatus: null,
    responseBody: null,
    responseTime: null,
    isLoading: false,
    activeSnippetTab: 'curl',

    endpoints: {
        'get_employees': {
            name: 'GET /employees',
            method: 'GET',
            path: '/employees',
            tag: 'Employees',
            desc: 'Mengambil daftar seluruh karyawan aktif/non-aktif pada tenant.',
            body: ''
        },
        'post_employees': {
            name: 'POST /employees',
            method: 'POST',
            path: '/employees',
            tag: 'Employees',
            desc: 'Mendaftarkan karyawan baru ke master data RiHRIS dari ATS/ERP eksternal.',
            body: JSON.stringify({
                employee_number: 'EMP-2026-' + Math.floor(100 + Math.random() * 900),
                first_name: 'Aditya',
                last_name: 'Pratama',
                email: 'aditya.pratama@perusahaan.com',
                phone: '081234567890',
                gender: 'male',
                employment_status: 'pkwt',
                join_date: '2026-11-01',
                basic_salary: 8500000
            }, null, 2)
        },
        'get_employee_detail': {
            name: 'GET /employees/{id}',
            method: 'GET',
            path: '/employees/1',
            tag: 'Employees',
            desc: 'Mengambil profil detail satu karyawan berdasarkan ID atau NIK.',
            body: ''
        },
        'get_attendance': {
            name: 'GET /attendance',
            method: 'GET',
            path: '/attendance?date={{ now()->toDateString() }}',
            tag: 'Attendance',
            desc: 'Mengambil log presensi harian seluruh karyawan.',
            body: ''
        },
        'post_attendance': {
            name: 'POST /attendance',
            method: 'POST',
            path: '/attendance',
            tag: 'Attendance',
            desc: 'Mengirim log clock-in atau clock-out tunggal dari mesin fingerprint / biometrik.',
            body: JSON.stringify({
                employee_number: 'EMP-001',
                type: 'clock_in',
                timestamp: '{{ now()->format('Y-m-d H:i:s') }}',
                device_id: 'ZKTECO-GATE-01'
            }, null, 2)
        },
        'post_attendance_bulk': {
            name: 'POST /attendance/bulk',
            method: 'POST',
            path: '/attendance/bulk',
            tag: 'Attendance',
            desc: 'Sinkronisasi batch log presensi dari mesin absensi biometrik offline.',
            body: JSON.stringify({
                device_id: 'ZKTECO-FINGERPRINT-GATE-01',
                logs: [
                    {
                        employee_number: 'EMP-001',
                        type: 'clock_in',
                        timestamp: '{{ now()->format('Y-m-d 08:02:15') }}'
                    },
                    {
                        employee_number: 'EMP-002',
                        type: 'clock_in',
                        timestamp: '{{ now()->format('Y-m-d 08:05:40') }}'
                    }
                ]
            }, null, 2)
        },
        'get_departments': {
            name: 'GET /departments',
            method: 'GET',
            path: '/departments',
            tag: 'Departments',
            desc: 'Mengambil daftar struktur departemen & jumlah karyawan.',
            body: ''
        },
        'post_departments': {
            name: 'POST /departments',
            method: 'POST',
            path: '/departments',
            tag: 'Departments',
            desc: 'Menambahkan departemen baru dari sinkronisasi master ERP.',
            body: JSON.stringify({
                name: 'Logistik & Distribusi',
                description: 'Divisi rantai pasok dan pergudangan pusat.'
            }, null, 2)
        },
        'get_leave_types': {
            name: 'GET /leave/types',
            method: 'GET',
            path: '/leave/types',
            tag: 'Leave',
            desc: 'Mengambil daftar tipe cuti resmi perusahaan.',
            body: ''
        },
        'get_leave_requests': {
            name: 'GET /leave/requests',
            method: 'GET',
            path: '/leave/requests',
            tag: 'Leave',
            desc: 'Mengambil daftar permohonan cuti karyawan.',
            body: ''
        },
        'post_leave_requests': {
            name: 'POST /leave/requests',
            method: 'POST',
            path: '/leave/requests',
            tag: 'Leave',
            desc: 'Mengajukan permohonan cuti dari aplikasi eksternal.',
            body: JSON.stringify({
                employee_number: 'EMP-001',
                leave_type_id: 1,
                start_date: '{{ now()->addDays(5)->toDateString() }}',
                end_date: '{{ now()->addDays(7)->toDateString() }}',
                reason: 'Keperluan keluarga di luar kota.'
            }, null, 2)
        },
        'get_payroll_summary': {
            name: 'GET /payroll/summary',
            method: 'GET',
            path: '/payroll/summary',
            tag: 'Payroll',
            desc: 'Mengambil rekap total payroll & alokasi beban per departemen untuk jurnal akuntansi.',
            body: ''
        },
        'get_webhook_events': {
            name: 'GET /webhooks/events',
            method: 'GET',
            path: '/webhooks/events',
            tag: 'Webhooks',
            desc: 'Katalog event-driven webhook outbound yang dapat dilanggan.',
            body: ''
        }
    },

    selectEndpoint(key) {
        this.selectedEndpoint = key;
        const ep = this.endpoints[key];
        this.requestMethod = ep.method;
        this.requestPath = ep.path;
        this.requestBody = ep.body;
        this.responseStatus = null;
        this.responseBody = null;
        this.responseTime = null;
    },

    async executeRequest() {
        this.isLoading = true;
        this.responseStatus = null;
        this.responseBody = null;
        const startTime = performance.now();

        const url = '{{ url('/api/v1') }}' + this.requestPath;
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-API-KEY': this.apiKey
        };

        const options = {
            method: this.requestMethod,
            headers: headers
        };

        if (this.requestMethod !== 'GET' && this.requestBody) {
            options.body = this.requestBody;
        }

        try {
            const res = await fetch(url, options);
            const endTime = performance.now();
            this.responseTime = Math.round(endTime - startTime) + ' ms';
            this.responseStatus = res.status;

            const text = await res.text();
            try {
                this.responseBody = JSON.stringify(JSON.parse(text), null, 2);
            } catch (e) {
                this.responseBody = text;
            }
        } catch (err) {
            const endTime = performance.now();
            this.responseTime = Math.round(endTime - startTime) + ' ms';
            this.responseStatus = 'Network Error';
            this.responseBody = JSON.stringify({ error: err.message }, null, 2);
        } finally {
            this.isLoading = false;
        }
    }
}">

    {{-- 1. Top Header Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-red-950 rounded-2xl p-6 sm:p-7 text-white shadow-xl shadow-slate-950/20 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-red-600/10 pointer-events-none blur-2xl"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 relative z-10">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-500/20 text-red-200 border border-red-500/30 text-[11px] font-bold">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        OpenAPI 3.1.0 Specification
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        REST API v1 Gateway Online
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black mt-2 tracking-tight text-white flex items-center gap-2.5">
                    <i data-lucide="terminal" class="w-7 h-7 text-red-500"></i>
                    RiHRIS Open API Developer Portal
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Spesifikasi OpenAPI resmi dan konsol pengujian interaktif untuk integrasi dua arah antara sistem pihak ketiga (ERP SAP/Odoo, Mesin Fingerprint Biometrik, Core Banking, HR Internal) dengan sistem RiHRIS.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('api.v1.openapi.json') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold backdrop-blur-sm border border-white/20 transition inline-flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="file-json" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>Unduh openapi.json</span>
                </a>
                <a href="{{ route('company.integrations.index') }}" class="px-3.5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                    <i data-lucide="key" class="w-3.5 h-3.5"></i>
                    <span>Kelola API Keys</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 2. Architecture & Quick Protocol Reference Strip --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Otentikasi Mesin</div>
                <div class="text-xs font-bold text-slate-900 truncate">Header X-API-KEY / Bearer</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="server" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Base Gateway URL</div>
                <div class="text-xs font-mono font-bold text-slate-900 truncate">{{ url('/api/v1') }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="fingerprint" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Biometric Device Ready</div>
                <div class="text-xs font-bold text-slate-900 truncate">Single & Batch Push Support</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="webhook" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Outbound Event Engine</div>
                <div class="text-xs font-bold text-slate-900 truncate">HMAC-SHA256 Webhooks</div>
            </div>
        </div>
    </div>

    {{-- 3. Live Interactive API Tester Console (Try It Out) --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3">
            <div>
                <h2 class="text-sm font-black text-slate-900 flex items-center gap-2 uppercase tracking-wider">
                    <i data-lucide="play-circle" class="w-4 h-4 text-red-600"></i>
                    Konsol Pengujian API Interaktif (Live API Tester)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Uji coba pemanggilan endpoint secara langsung dengan kunci API aktif Anda.</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold text-slate-500">Pilih Endpoint:</span>
                <select @change="selectEndpoint($event.target.value)" class="text-xs font-bold rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 focus:ring-2 focus:ring-red-500 focus:outline-none text-slate-800">
                    <optgroup label="1. Master Karyawan (Employees)">
                        <option value="get_employees">GET /employees (List Karyawan)</option>
                        <option value="post_employees">POST /employees (Daftarkan Karyawan Baru)</option>
                        <option value="get_employee_detail">GET /employees/{id} (Detail Karyawan)</option>
                    </optgroup>
                    <optgroup label="2. Presensi & Mesin Biometrik (Attendance)">
                        <option value="get_attendance">GET /attendance (Log Presensi Harian)</option>
                        <option value="post_attendance">POST /attendance (Log Presensi Tunggal)</option>
                        <option value="post_attendance_bulk">POST /attendance/bulk (Batch Push Mesin Biometrik)</option>
                    </optgroup>
                    <optgroup label="3. Struktur Organisasi (Departments)">
                        <option value="get_departments">GET /departments (Daftar Departemen)</option>
                        <option value="post_departments">POST /departments (Tambah Departemen ERP)</option>
                    </optgroup>
                    <optgroup label="4. Manajemen Cuti (Leave)">
                        <option value="get_leave_types">GET /leave/types (Daftar Tipe Cuti)</option>
                        <option value="get_leave_requests">GET /leave/requests (Daftar Pengajuan Cuti)</option>
                        <option value="post_leave_requests">POST /leave/requests (Ajukan Permohonan Cuti)</option>
                    </optgroup>
                    <optgroup label="5. Penggajian & Jurnal ERP (Payroll)">
                        <option value="get_payroll_summary">GET /payroll/summary (Rekap Payroll Akuntansi)</option>
                    </optgroup>
                    <optgroup label="6. Webhooks Outbound">
                        <option value="get_webhook_events">GET /webhooks/events (Katalog Event Webhook)</option>
                    </optgroup>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            {{-- Left: Request Builder --}}
            <div class="lg:col-span-6 space-y-4">
                {{-- URL Input Bar --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Target Request URL</label>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-2 rounded-xl text-xs font-black font-mono"
                              :class="requestMethod === 'GET' ? 'bg-blue-100 text-blue-800' : (requestMethod === 'POST' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800')"
                              x-text="requestMethod"></span>
                        <div class="flex-1 flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-medium text-slate-800 overflow-x-auto">
                            <span class="text-slate-400">{{ url('/api/v1') }}</span>
                            <span class="font-bold text-slate-900" x-text="requestPath"></span>
                        </div>
                    </div>
                </div>

                {{-- API Key Input Bar --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase">Kunci API (X-API-KEY)</label>
                        @if($apiKeys->isNotEmpty())
                            <span class="text-[10px] text-slate-400">Menggunakan kunci aktif tenant</span>
                        @else
                            <a href="{{ route('company.integrations.index') }}" class="text-[10px] font-bold text-red-600 hover:underline">+ Generate Kunci API</a>
                        @endif
                    </div>
                    <input type="text" x-model="apiKey" placeholder="Masukkan plain API key (ri_live_...)" class="w-full text-xs font-mono font-bold bg-white rounded-xl border border-slate-200 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                {{-- JSON Request Body (if POST/PUT) --}}
                <template x-if="requestMethod !== 'GET'">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase">JSON Request Body</label>
                            <span class="text-[10px] text-slate-400 font-mono">application/json</span>
                        </div>
                        <textarea x-model="requestBody" rows="8" class="w-full text-xs font-mono bg-slate-950 text-emerald-400 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-red-500 custom-scrollbar"></textarea>
                    </div>
                </template>

                {{-- Execute Button --}}
                <div class="pt-2">
                    <button type="button" @click="executeRequest()" :disabled="isLoading" class="w-full py-2.5 rounded-xl bg-[#DC2626] hover:bg-[#991B1B] text-white text-xs font-bold transition flex items-center justify-center gap-2 shadow-md shadow-red-500/20 disabled:opacity-50">
                        <template x-if="isLoading">
                            <span class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Mengirim Request...</span>
                            </span>
                        </template>
                        <template x-if="!isLoading">
                            <span class="inline-flex items-center gap-2">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>Kirim Request API Sekarang</span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>

            {{-- Right: Live Response Viewer & Code Snippets --}}
            <div class="lg:col-span-6 space-y-4">
                {{-- Live Response Box --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase">Live Response Inspector</label>
                        <div class="flex items-center gap-2" x-show="responseStatus !== null">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                  :class="responseStatus >= 200 && responseStatus < 300 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                  x-text="'Status: ' + responseStatus"></span>
                            <span class="text-[10px] font-mono text-slate-400" x-text="responseTime"></span>
                        </div>
                    </div>

                    <div class="w-full h-56 bg-slate-950 text-slate-200 rounded-xl p-3 text-xs font-mono overflow-auto border border-slate-800 custom-scrollbar">
                        <template x-if="responseBody">
                            <pre class="whitespace-pre-wrap leading-relaxed" x-text="responseBody"></pre>
                        </template>
                        <template x-if="!responseBody && !isLoading">
                            <div class="h-full flex flex-col items-center justify-center text-slate-500 text-center">
                                <i data-lucide="code" class="w-8 h-8 mb-2 opacity-50"></i>
                                <span>Tekan tombol "Kirim Request API" untuk melihat balasan server live.</span>
                            </div>
                        </template>
                        <template x-if="isLoading">
                            <div class="h-full flex items-center justify-center text-slate-400 text-center">
                                <span class="animate-pulse">Menghubungi endpoint gateway...</span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Multi-Language Code Snippet Generator --}}
                <div class="bg-slate-900 rounded-xl p-4 text-xs font-mono text-slate-200">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800 mb-2.5">
                        <span class="text-[10px] font-bold uppercase text-slate-400">Contoh Kode Integrasi</span>
                        <div class="flex items-center gap-1.5 text-[10px]">
                            <button type="button" @click="activeSnippetTab = 'curl'" :class="activeSnippetTab === 'curl' ? 'bg-red-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded transition">cURL</button>
                            <button type="button" @click="activeSnippetTab = 'php'" :class="activeSnippetTab === 'php' ? 'bg-red-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded transition">PHP</button>
                            <button type="button" @click="activeSnippetTab = 'python'" :class="activeSnippetTab === 'python' ? 'bg-red-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded transition">Python</button>
                            <button type="button" @click="activeSnippetTab = 'node'" :class="activeSnippetTab === 'node' ? 'bg-red-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2 py-0.5 rounded transition">Node.js</button>
                        </div>
                    </div>

                    {{-- cURL Snippet --}}
                    <div x-show="activeSnippetTab === 'curl'" class="overflow-x-auto text-[11px] leading-relaxed text-slate-300">
                        <pre>curl -X <span class="text-amber-400" x-text="requestMethod"></span> \
  '{{ url('/api/v1') }}<span x-text="requestPath"></span>' \
  -H 'Accept: application/json' \
  -H 'X-API-KEY: <span class="text-emerald-400" x-text="apiKey"></span>'<span x-show="requestMethod !== 'GET'"> \
  -H 'Content-Type: application/json' \
  -d '<span class="text-blue-300" x-text="requestBody.replace(/\n/g, '')"></span>'</span></pre>
                    </div>

                    {{-- PHP Snippet --}}
                    <div x-show="activeSnippetTab === 'php'" class="overflow-x-auto text-[11px] leading-relaxed text-slate-300">
                        <pre>&lt;?php
$ch = curl_init('{{ url('/api/v1') }}' . '<span x-text="requestPath"></span>');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => '<span x-text="requestMethod"></span>',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'X-API-KEY: ' . '<span x-text="apiKey"></span>',
        'Content-Type: application/json'
    ],<span x-show="requestMethod !== 'GET'">
    CURLOPT_POSTFIELDS => '<span x-text="requestBody.replace(/\n/g, '')"></span>',</span>
]);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);</pre>
                    </div>

                    {{-- Python Snippet --}}
                    <div x-show="activeSnippetTab === 'python'" class="overflow-x-auto text-[11px] leading-relaxed text-slate-300">
                        <pre>import requests

url = "{{ url('/api/v1') }}<span x-text="requestPath"></span>"
headers = {
    "Accept": "application/json",
    "X-API-KEY": "<span x-text="apiKey"></span>"
}
<span x-show="requestMethod === 'GET'">response = requests.get(url, headers=headers)</span><span x-show="requestMethod !== 'GET'">payload = <span x-text="requestBody"></span>
response = requests.<span x-text="requestMethod.toLowerCase()"></span>(url, json=payload, headers=headers)</span>
print(response.json())</pre>
                    </div>

                    {{-- Node.js Snippet --}}
                    <div x-show="activeSnippetTab === 'node'" class="overflow-x-auto text-[11px] leading-relaxed text-slate-300">
                        <pre>const res = await fetch('{{ url('/api/v1') }}' + '<span x-text="requestPath"></span>', {
  method: '<span x-text="requestMethod"></span>',
  headers: {
    'Accept': 'application/json',
    'X-API-KEY': '<span x-text="apiKey"></span>',
    'Content-Type': 'application/json'
  }<span x-show="requestMethod !== 'GET'">,
  body: JSON.stringify(<span x-text="requestBody"></span>)</span>
});
const data = await res.json();
console.log(data);</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Enterprise Integration Blueprints & Architecture Guides --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4 text-emerald-600"></i>
                Panduan Integrasi Pihak Ketiga (Implementation Blueprints)
            </h2>
            <span class="text-xs text-slate-400">Best Practices & Use Cases</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            {{-- Guide 1: Biometric Devices --}}
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-2">
                <div class="flex items-center gap-2 font-bold text-slate-900">
                    <span class="p-1 rounded-lg bg-emerald-100 text-emerald-700"><i data-lucide="fingerprint" class="w-4 h-4"></i></span>
                    <span>1. Mesin Absensi Biometrik</span>
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Sinkronkan mesin fingerprint / face recognition (ZKTeco, Solution, Hikvision) secara real-time via <code class="bg-white px-1 py-0.5 rounded border border-slate-200">POST /attendance</code> atau batch sync offline logs via <code class="bg-white px-1 py-0.5 rounded border border-slate-200">POST /attendance/bulk</code>.
                </p>
                <div class="pt-1 text-[10px] text-emerald-700 font-bold">Scope: attendance:write</div>
            </div>

            {{-- Guide 2: ERP Integration --}}
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-2">
                <div class="flex items-center gap-2 font-bold text-slate-900">
                    <span class="p-1 rounded-lg bg-blue-100 text-blue-700"><i data-lucide="database" class="w-4 h-4"></i></span>
                    <span>2. ERP SAP / Odoo Sync</span>
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Tarik data master karyawan secara berkala melalui <code class="bg-white px-1 py-0.5 rounded border border-slate-200">GET /employees</code> atau daftarkan karyawan baru yang di-hire dari sistem eksternal menggunakan <code class="bg-white px-1 py-0.5 rounded border border-slate-200">POST /employees</code>.
                </p>
                <div class="pt-1 text-[10px] text-blue-700 font-bold">Scope: employees:read, employees:write</div>
            </div>

            {{-- Guide 3: Accounting & General Ledger --}}
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-2">
                <div class="flex items-center gap-2 font-bold text-slate-900">
                    <span class="p-1 rounded-lg bg-purple-100 text-purple-700"><i data-lucide="banknote" class="w-4 h-4"></i></span>
                    <span>3. Jurnal Gaji ke Akuntansi</span>
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Setiap akhir bulan setelah payroll di-finalize, sistem akuntansi (Jurnal.id, Accurate, Xero) dapat memanggil <code class="bg-white px-1 py-0.5 rounded border border-slate-200">GET /payroll/summary</code> untuk meng-generate posting jurnal beban gaji per departemen.
                </p>
                <div class="pt-1 text-[10px] text-purple-700 font-bold">Scope: payroll:read</div>
            </div>

            {{-- Guide 4: Outbound Webhooks --}}
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-2">
                <div class="flex items-center gap-2 font-bold text-slate-900">
                    <span class="p-1 rounded-lg bg-amber-100 text-amber-700"><i data-lucide="bell" class="w-4 h-4"></i></span>
                    <span>4. Event Webhook Real-time</span>
                </div>
                <p class="text-slate-600 text-[11px] leading-relaxed">
                    Daftarkan endpoint URL pihak ketiga untuk menerima event instan seperti <code class="bg-white px-1 py-0.5 rounded border border-slate-200">employee.created</code>, <code class="bg-white px-1 py-0.5 rounded border border-slate-200">leave.approved</code>, dan <code class="bg-white px-1 py-0.5 rounded border border-slate-200">payroll.finalized</code> dengan verifikasi signature HMAC-SHA256.
                </p>
                <div class="pt-1 text-[10px] text-amber-700 font-bold">Signature: X-RiHRIS-Signature</div>
            </div>
        </div>
    </div>

</div>
@endsection
