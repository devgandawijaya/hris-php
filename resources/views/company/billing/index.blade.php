@extends('layouts.app')

@section('title', 'Paket & Langganan SaaS')

@section('content')
<div class="space-y-6" x-data="{ openUpgradeModal: false, openPayModal: false, selectedInvoiceId: '', selectedInvoiceNo: '', selectedInvoiceAmount: 0 }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Paket & Langganan SaaS Perusahaan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola lisensi software RiHRIS, alokasi kuota karyawan, tagihan faktur, dan upgrade paket enterprise.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openUpgradeModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="zap" class="w-4 h-4"></i>
                Upgrade / Ubah Paket
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
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

    <!-- Subscription Status Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600 text-white">
                    Paket {{ $usage['plan_name'] }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize bg-white/20 backdrop-blur-sm text-white">
                    Status: {{ $usage['status'] }}
                </span>
            </div>
            <h2 class="text-xl font-black tracking-tight">Siklus Pembayaran: {{ ucfirst($usage['billing_cycle']) }}</h2>
            <p class="text-xs text-slate-300">
                @if($usage['ends_at'])
                    Masa berlaku aktif hingga: <strong>{{ \Carbon\Carbon::parse($usage['ends_at'])->format('d M Y') }}</strong>
                @else
                    Masa uji coba / berlangganan tanpa batas waktu.
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="openUpgradeModal = true" class="px-4 py-2.5 rounded-xl bg-white text-slate-900 text-xs font-black hover:bg-slate-100 transition shadow-sm">
                Lihat Opsi Paket
            </button>
        </div>
    </div>

    <!-- Quota & Metering Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Employee Quota Meter -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700">Kuota Karyawan Aktif</span>
                <span class="font-bold text-xs font-mono text-slate-900">{{ $usage['employee_count'] }} / {{ $usage['max_employees'] }}</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div class="h-2.5 rounded-full {{ $usage['employee_pct'] >= 90 ? 'bg-red-600' : 'bg-emerald-600' }}" style="width: {{ $usage['employee_pct'] }}%"></div>
            </div>
            <div class="text-[10px] text-slate-400">Terpakai {{ $usage['employee_pct'] }}% dari kapasitas paket</div>
        </div>

        <!-- 2. Admin Seats Meter -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700">Kuota Admin Perusahaan</span>
                <span class="font-bold text-xs font-mono text-slate-900">{{ $usage['admin_count'] }} / {{ $usage['max_admins'] }}</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div class="h-2.5 rounded-full bg-blue-600" style="width: {{ $usage['admin_pct'] }}%"></div>
            </div>
            <div class="text-[10px] text-slate-400">Akses role HR Manager & Owner</div>
        </div>

        <!-- 3. Cloud Storage Limit -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700">Penyimpanan Dokumen Cloud</span>
                <span class="font-bold text-xs font-mono text-slate-900">{{ $usage['storage_limit_mb'] }} MB</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div class="h-2.5 rounded-full bg-purple-600" style="width: 15%"></div>
            </div>
            <div class="text-[10px] text-slate-400">Penyimpanan dokumen KTP, NPWP & slip gaji</div>
        </div>
    </div>

    <!-- Invoices History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Riwayat Faktur & Tagihan Langganan</h2>
            <span class="text-xs text-slate-500">Bukti bayar elektronik</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <th class="px-5 py-3.5">Nomor Faktur</th>
                        <th class="px-5 py-3.5">Nominal Pokok</th>
                        <th class="px-5 py-3.5">PPN 11%</th>
                        <th class="px-5 py-3.5">Total Tagihan</th>
                        <th class="px-5 py-3.5">Jatuh Tempo</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                {{ $inv->invoice_number }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                Rp {{ number_format($inv->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-500">
                                Rp {{ number_format($inv->tax_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 font-mono font-black text-slate-900 text-sm">
                                Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 font-mono text-[11px]">
                                {{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($inv->status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($inv->status !== 'paid')
                                    <button 
                                        @click="selectedInvoiceId = '{{ $inv->id }}'; selectedInvoiceNo = '{{ $inv->invoice_number }}'; selectedInvoiceAmount = '{{ $inv->total_amount }}'; openPayModal = true"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#DC2626] text-white text-[11px] font-bold hover:bg-[#991B1B] transition shadow-sm"
                                    >
                                        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                        Bayar Sekarang
                                    </button>
                                @else
                                    <span class="text-slate-400 font-medium text-[11px]">Lunas via {{ strtoupper($inv->payment_method ?? 'Transfer') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                Belum ada faktur tagihan yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- Modal 1: Ganti / Tingkatkan Paket -->
    <div x-show="openUpgradeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openUpgradeModal = false" class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-black text-slate-900">Pilih Paket Langganan Perusahaan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tingkatkan kapasitas karyawan dan fitur sesuai skala organisasi Anda.</p>
                </div>
                <button @click="openUpgradeModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.billing.change-plan') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="flex items-center justify-center gap-4 p-2 bg-slate-50 rounded-xl border border-slate-200">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="billing_cycle" value="monthly" checked class="text-red-600 focus:ring-red-500">
                        Bulanan (Monthly)
                    </label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="billing_cycle" value="yearly" class="text-red-600 focus:ring-red-500">
                        Tahunan (Yearly - Hemat 2 Bulan)
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    @foreach($plans as $p)
                        <label class="p-4 rounded-xl border-2 border-slate-200 hover:border-red-500 transition cursor-pointer flex flex-col justify-between space-y-3">
                            <div class="space-y-1">
                                <input type="radio" name="plan_id" value="{{ $p->id }}" {{ $p->slug === $usage['plan_slug'] ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                                <span class="font-black text-slate-900 text-sm block mt-1">{{ $p->name }}</span>
                                <div class="font-mono text-base font-black text-red-600">
                                    Rp {{ number_format($p->price_monthly / 1000, 0) }}rb<span class="text-[10px] text-slate-400 font-normal">/bln</span>
                                </div>
                            </div>
                            <ul class="text-[11px] text-slate-600 space-y-1 border-t border-slate-100 pt-2">
                                <li>• Hingga <strong>{{ $p->max_employees }}</strong> Karyawan</li>
                                <li>• <strong>{{ $p->max_admins }}</strong> Akun Admin</li>
                                <li>• {{ $p->storage_limit_mb }} MB Storage</li>
                            </ul>
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="openUpgradeModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] shadow-sm shadow-red-500/20">Konfirmasi Pemilihan Paket</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Bayar Tagihan (Simulator Payment Gateway) -->
    <div x-show="openPayModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="openPayModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-base font-black text-slate-900">Pembayaran Tagihan SaaS</h3>
            <p class="text-xs text-slate-500 mt-1" x-text="'No Faktur: ' + selectedInvoiceNo"></p>

            <form :action="'/company/billing/invoices/' + selectedInvoiceId + '/pay'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Metode Pembayaran</label>
                    <select name="payment_method" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 font-bold text-slate-700 focus:ring-2 focus:ring-red-500 focus:outline-none">
                        <option value="bank_transfer">Transfer Bank Manual (BCA / Mandiri)</option>
                        <option value="virtual_account">BCA Virtual Account (Instant Confirm)</option>
                        <option value="qris">QRIS Standar Bank Indonesia</option>
                        <option value="credit_card">Kartu Kredit Korporat (Visa / Mastercard)</option>
                    </select>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Total Pembayaran:</span>
                        <strong class="text-slate-900 font-mono" x-text="'Rp ' + Number(selectedInvoiceAmount).toLocaleString('id-ID')"></strong>
                    </div>
                    <div class="text-[10px] text-slate-400">Pembayaran akan langsung mengaktifkan lisensi software seketika.</div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="openPayModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-sm shadow-emerald-500/20">Konfirmasi Pembayaran</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

