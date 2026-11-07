<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kios Presensi QR — {{ $kiosk->kiosk_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .pulse-qr { animation: pulse-ring 4s infinite ease-in-out; }
    </style>
</head>
<body class="h-full flex flex-col justify-between p-8 sm:p-12 overflow-hidden select-none">

    <!-- Header Bar -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-red-600 flex items-center justify-center text-white shadow-lg shadow-red-600/30">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tight">Ri<span class="text-red-500">HRIS</span> Kiosk Touchless</h1>
                <p class="text-xs text-slate-400 mt-0.5">{{ $kiosk->branch->name }} • {{ $kiosk->kiosk_name }}</p>
            </div>
        </div>

        <div class="text-right">
            <div id="liveClock" class="text-3xl font-black font-mono text-white">00:00:00</div>
            <div id="liveDate" class="text-xs text-slate-400 font-medium mt-0.5">Hari, 00 Bulan 2026</div>
        </div>
    </div>

    <!-- Center Content: QR Code Card -->
    <div class="max-w-md w-full mx-auto my-auto text-center space-y-6">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-xs font-semibold text-emerald-400">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            Kios Aktif • Validasi Geofencing Aktif
        </div>

        <div class="p-8 rounded-3xl bg-white text-slate-900 shadow-2xl mx-auto w-72 h-72 flex items-center justify-center pulse-qr">
            <div id="qrcode"></div>
        </div>

        <div class="space-y-2">
            <h2 class="text-lg font-bold text-white tracking-tight">Pindai QR Code Untuk Presensi</h2>
            <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">
                Buka menu <strong>"Presensi Masuk / Pulang"</strong> di aplikasi RiHRIS ESS ponsel Anda, lalu arahkan kamera ke kode di atas.
            </p>
        </div>
    </div>

    <!-- Footer Security Notice -->
    <div class="flex flex-col sm:flex-row items-center justify-between border-t border-slate-800/80 pt-6 text-[11px] text-slate-500">
        <div class="flex items-center gap-2">
            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
            <span>Verifikasi Koordinat Geofence: <strong>{{ $kiosk->branch->radius_meters }}m Radius</strong></span>
        </div>
        <div class="mt-2 sm:mt-0 font-mono">
            Payload: {{ Str::limit($kiosk->current_qr_code, 28) }}
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Generate QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ $kiosk->current_qr_code }}",
            width: 224,
            height: 224,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Live Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('liveClock').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('liveDate').textContent = now.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>
