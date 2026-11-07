@extends('layouts.app')

@section('title', 'Hub Notifikasi Multi-Channel')

@section('content')
<div class="space-y-6" x-data="{ templateModal: false, testModal: false, selectedTemplate: {}, activeEmployeeId: '' }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Omnichannel Messaging
                </span>
                <span class="text-xs text-slate-400">• Phase 13 Enterprise</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Pusat Notifikasi Multi-Channel (Email & WhatsApp)</h1>
            <p class="text-xs text-slate-500 mt-1">Kustomisasi naskah pesan otomatis, simulasi pengujian pengiriman pesan, dan riwayat transmisi.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="testModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                Kirim Pengujian Notifikasi
            </button>
            <button @click="templateModal = true" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buat Template Naskah
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Total Notifikasi Terkirim</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                    <i data-lucide="inbox" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total_sent']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Akumulasi pengiriman sistem</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Kanal WhatsApp</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-700">{{ number_format($stats['whatsapp_count']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Pesan teks instan ke nomor seluler</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Kanal Surat Elektronik (Email)</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-blue-700">{{ number_format($stats['email_count']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Notifikasi surat resmi via SMTP</div>
        </div>
    </div>

    <!-- Template Catalog -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
            <i data-lucide="file-text" class="w-4 h-4 text-red-600"></i>
            Katalog Template Pesan & Variabel Dinamis
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($templates as $tmpl)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tmpl->channel === 'whatsapp' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ strtoupper($tmpl->channel) }}
                            </span>
                            <code class="text-[11px] font-bold text-slate-600 bg-white px-1.5 py-0.5 rounded border border-slate-200">{{ $tmpl->code }}</code>
                        </div>
                        <div class="font-bold text-slate-900 text-sm mt-2">{{ $tmpl->name }}</div>
                        @if($tmpl->subject)
                            <div class="text-[11px] text-slate-500 font-semibold mt-0.5 truncate">Subjek: {{ $tmpl->subject }}</div>
                        @endif
                        <div class="p-2.5 bg-white rounded-lg border border-slate-200 text-slate-700 text-xs mt-2 italic font-mono text-[11px] line-clamp-3">
                            {{ $tmpl->body_template }}
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-200/80 flex items-center justify-between text-[11px]">
                        <span class="text-slate-400 capitalize">Kategori: {{ $tmpl->category }}</span>
                        <button @click="selectedTemplate = {{ json_encode($tmpl) }}; testModal = true" class="text-emerald-600 hover:text-emerald-700 font-bold">
                            Uji Kirim &rarr;
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-6 text-slate-400 italic text-xs">Belum ada template terdaftar. Silakan buat template baru.</div>
            @endforelse
        </div>
    </div>

    <!-- Dispatch History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Log Pengiriman Transmisi Terakhir</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Kanal</th>
                        <th class="px-4 py-3 font-bold">Penerima (Pegawai)</th>
                        <th class="px-4 py-3 font-bold">Kontak Tujuan</th>
                        <th class="px-4 py-3 font-bold">Pratinjau Pesan</th>
                        <th class="px-4 py-3 font-bold">Waktu Kirim</th>
                        <th class="px-4 py-3 font-bold text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $log->channel === 'whatsapp' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ strtoupper($log->channel) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $log->recipient_name }}</td>
                            <td class="px-4 py-3 font-mono text-slate-600">{{ $log->recipient_target }}</td>
                            <td class="px-4 py-3 max-w-sm text-slate-700 truncate" title="{{ $log->message_preview }}">
                                {{ $log->message_preview }}
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                {{ $log->sent_at ? $log->sent_at->format('d M Y H:i:s') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ strtoupper($log->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Belum ada transmisi notifikasi yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>

    <!-- Create Template Modal -->
    <div x-show="templateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Buat Template Notifikasi Baru</h3>
                <button @click="templateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('company.notifications.hub.templates.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode Template (Unik)</label>
                        <input type="text" name="code" required class="w-full px-3 py-2 border border-slate-200 rounded-lg uppercase" placeholder="e.g. PKWT_EXPIRY">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kanal Transmisi</label>
                        <select name="channel" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                            <option value="whatsapp">WhatsApp Text</option>
                            <option value="email">Email Surat Resmi</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Deskriptif</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Pengingat Kontrak H-30">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori</label>
                        <select name="category" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                            <option value="contract">Perjanjian Kontrak</option>
                            <option value="payroll">Gaji & Upah</option>
                            <option value="attendance">Presensi & Cuti</option>
                            <option value="disciplinary">Disiplin Kerja</option>
                            <option value="general">Umum</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Subjek Pesan (Opsional untuk Email)</label>
                    <input type="text" name="subject" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Pemberitahuan: {employee_name} - {company_name}">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Naskah Template (Gunakan placeholder {nama_variabel})</label>
                    <textarea name="body_template" rows="4" required class="w-full px-3 py-2 border border-slate-200 rounded-lg font-mono text-xs" placeholder="Yth. {employee_name}, kami menginformasikan bahwa..."></textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Variabel tersedia: <code class="text-slate-600">{employee_name}</code>, <code class="text-slate-600">{company_name}</code>, <code class="text-slate-600">{department}</code>, <code class="text-slate-600">{date_today}</code></p>
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="templateModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-lg font-bold">Simpan Template</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Test Dispatch Simulator Modal -->
    <div x-show="testModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Uji Transmisi Notifikasi Langsung</h3>
                <button @click="testModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('company.notifications.hub.test.dispatch') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Template Naskah</label>
                    <select name="template_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}">[{{ strtoupper($t->channel) }}] {{ $t->name }} ({{ $t->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Karyawan Tujuan</label>
                    <select name="employee_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg" required>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->phone ?? '081234567890' }} | {{ $emp->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Parameter Tambahan (Judul/Alasan)</label>
                        <input type="text" name="extra_title" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="Pembaruan PKWT 2026">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Parameter Nominal (Rp)</label>
                        <input type="text" name="extra_amount" class="w-full px-3 py-2 border border-slate-200 rounded-lg" placeholder="12.500.000">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Parameter Tanggal</label>
                    <input type="date" name="extra_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg">
                </div>
                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" @click="testModal = false" class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold">Kirim Sekarang</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
