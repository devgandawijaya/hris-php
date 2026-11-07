@extends('layouts.app')

@section('title', 'Kios Presensi QR Code Cabang')

@section('content')
<div class="space-y-6" x-data="{ showKioskModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700">
                    Phase 10 • Touchless QR Kiosk
                </span>
                <span class="text-xs text-slate-400">Section 10.C</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-1 tracking-tight">Kios Presensi QR Code Per Cabang</h1>
            <p class="text-xs text-slate-500 mt-1">Presensi nirkontak (touchless clock-in) menggunakan QR Code terenkripsi dengan validasi geofencing GPS kantor cabang.</p>
        </div>

        <div>
            <button type="button" @click="showKioskModal = true" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Aktifkan Kios Cabang Baru
            </button>
        </div>
    </div>

    <!-- Kiosks Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($kiosks as $kiosk)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $kiosk->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $kiosk->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">ID #{{ $kiosk->id }}</span>
                    </div>

                    <h3 class="text-base font-black text-slate-900 mt-2">{{ $kiosk->kiosk_name }}</h3>
                    <div class="text-xs font-semibold text-red-600 mt-0.5 flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        Cabang: {{ $kiosk->branch->name }}
                    </div>

                    <p class="text-[11px] text-slate-500 mt-2">
                        Radius Kantor: <strong>{{ $kiosk->branch->radius_meters }} meter</strong> • Koordinat: {{ $kiosk->branch->latitude }}, {{ $kiosk->branch->longitude }}
                    </p>

                    <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Token Keamanan Kios:</div>
                        <div class="text-xs font-mono text-slate-700 truncate font-semibold">{{ $kiosk->secret_token }}</div>
                        <div class="text-[10px] text-slate-400 pt-1">
                            Terakhir refresh: {{ $kiosk->qr_refreshed_at?->diffForHumans() ?? 'Baru dibuat' }}
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <form action="{{ route('company.qr.refresh', $kiosk->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-slate-500"></i>
                            Refresh Token
                        </button>
                    </form>

                    <a href="{{ route('company.qr.display', $kiosk->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-2xs transition flex items-center gap-1.5">
                        <i data-lucide="tv" class="w-3.5 h-3.5"></i>
                        Buka Layar Kios
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center bg-white rounded-2xl border border-slate-200 shadow-sm">
                <i data-lucide="qr-code" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <h3 class="text-sm font-bold text-slate-800">Belum Ada Kios Presensi QR</h3>
                <p class="text-xs text-slate-500 mt-1">Aktifkan kios pertama Anda untuk memungkinkan presensi nirkontak di lobby kantor cabang.</p>
            </div>
        @endforelse
    </div>

    <!-- MODAL: ADD KIOSK -->
    <div x-show="showKioskModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="showKioskModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="qr-code" class="w-4 h-4 text-red-600"></i>
                    Aktivasi Kios Presensi QR Baru
                </h3>
                <button type="button" @click="showKioskModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('company.qr.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Kantor Cabang *</label>
                    <select name="branch_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="">-- Pilih Cabang Penempatan Kios --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kios Presensi *</label>
                    <input type="text" name="kiosk_name" required placeholder="Contoh: Kios Lobby Utama Lt. 1" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-500 leading-relaxed">
                    Sistem akan membuat token otentikasi unik 256-bit dan menghasilkan QR code dinamis yang dapat ditampilkan pada tablet/monitor lobby kantor.
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showKioskModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Aktifkan Kios</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
