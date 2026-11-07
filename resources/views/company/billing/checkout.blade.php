@extends('layouts.app')

@section('title', 'Pembayaran Midtrans Snap - Faktur ' . $invoice->invoice_number)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-3">
        <a href="{{ route('company.billing.index') }}" class="p-2 bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Checkout Pembayaran SaaS</h1>
            <p class="text-sm text-slate-500 mt-0.5">Sistem Pembayaran Otomatis RiHRIS didukung oleh Midtrans Payment Gateway.</p>
        </div>
    </div>

    <!-- Payment Detail Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Invoice & Order Card -->
        <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex justify-between items-start pb-4 border-b border-slate-100">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Faktur Tagihan</span>
                    <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $invoice->invoice_number }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Jatuh Tempo: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d F Y') }}</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ strtoupper($invoice->status) }}
                </span>
            </div>

            <!-- Items Breakdown -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-slate-800">Rincian Layanan SaaS</h3>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Langganan Paket {{ $plan->name ?? 'Enterprise' }}</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>Alokasi Maksimal Karyawan</span>
                        <span>{{ $plan->max_employees ?? 50 }} Pegawai</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>Siklus Penagihan</span>
                        <span>Bulanan / Tahunan</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex justify-between text-base font-bold text-slate-900">
                        <span>Total Pembayaran</span>
                        <span class="text-red-600">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Midtrans Snap Action Section -->
            <div class="space-y-4 pt-2">
                @if($invoice->status === 'paid')
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3 text-emerald-800">
                        <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <div>
                            <div class="font-bold text-sm">Tagihan Telah Lunas</div>
                            <div class="text-xs text-emerald-700 mt-0.5">Langganan perusahaan Anda telah aktif dan diperpanjang secara otomatis.</div>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button id="pay-button" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-red-600 text-white rounded-xl text-sm font-bold hover:bg-red-700 transition shadow-lg shadow-red-500/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            Bayar dengan Midtrans Snap
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 text-center">Mendukung QRIS, GoPay, OVO, ShopeePay, Virtual Account BCA/BNI/BRI/Mandiri, dan Kartu Kredit.</p>
                @endif
            </div>
        </div>

        <!-- Security & Sandbox Simulation Sidebar -->
        <div class="space-y-4">
            <!-- Security Info -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                <div class="flex items-center gap-2 text-slate-800 font-bold text-sm">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Payment Gateway Terpercaya
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Transaksi diproses secara aman menggunakan enkripsi TLS 256-bit standar PCI-DSS Level 1 oleh Midtrans (GoTo Financial).
                </p>
                <div class="pt-2 border-t border-slate-100 font-mono text-[11px] text-slate-400 space-y-1">
                    <div>Order ID: <span class="text-slate-700 font-bold">{{ $transaction->order_id }}</span></div>
                    <div>Gateway: <span class="text-slate-700">MIDTRANS_SNAP</span></div>
                    <div>Mode: <span class="text-blue-600 font-bold">SANDBOX / PRODUCTION</span></div>
                </div>
            </div>

            <!-- Instant Sandbox Simulation Card -->
            @if($invoice->status !== 'paid')
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-200 p-5 space-y-3">
                <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Simulasi Sandbox Instant
                </div>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Uji coba alur pembayaran instan tanpa perlu kartu kredit asli. Ini akan mensimulasikan settlement Midtrans dan mengaktifkan invoice.
                </p>
                <form action="{{ route('company.payment-gateway.simulate-success', $transaction) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-xl text-xs font-bold hover:bg-amber-700 transition shadow-sm">
                        ⚡ Simulasikan Pembayaran Sukses
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Midtrans Snap JS Integration -->
@if($clientKey && $invoice->status !== 'paid')
<script type="text/javascript"
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ $clientKey }}">
</script>
<script type="text/javascript">
    const payBtn = document.getElementById('pay-button');
    if (payBtn) {
        payBtn.addEventListener('click', function () {
            @if($snapToken)
                window.snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        alert("Pembayaran Berhasil Diverifikasi!");
                        window.location.reload();
                    },
                    onPending: function(result) {
                        alert("Menunggu Pembayaran...");
                        window.location.reload();
                    },
                    onError: function(result) {
                        alert("Pembayaran Gagal: " + result.status_message);
                    },
                    onClose: function() {
                        console.log('Customer closed the popup without finishing the payment');
                    }
                });
            @else
                alert("Snap Token belum tersedia. Silakan gunakan tombol Simulasi Sandbox di samping.");
            @endif
        });
    }
</script>
@endif
@endsection
