@extends('layouts.app')

@section('title', 'Mass Disbursal Penggajian Perbankan (BCA, Mandiri, BNI, BRI)')

@section('content')
<div class="space-y-6" x-data="{ showGenModal: false }">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700">
                    Phase 10 • Bank Mass Transfer
                </span>
                <span class="text-xs text-slate-400">Section 10.F & 19</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-1 tracking-tight">Mass Disbursal Penggajian Perbankan Nasional</h1>
            <p class="text-xs text-slate-500 mt-1">Ekspor berkas transfer massal terstandar untuk KlikBCA Bisnis, Mandiri Cash Management (MCM), BNI Direct, dan BRI CMS.</p>
        </div>

        <div>
            <button type="button" @click="showGenModal = true" class="px-3.5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-sm flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Buat Batch Transfer Baru
            </button>
        </div>
    </div>

    <!-- Supported Banks Badge Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs flex items-center justify-center font-mono">BCA</div>
            <div>
                <div class="text-xs font-bold text-slate-900">KlikBCA Bisnis</div>
                <div class="text-[10px] text-slate-400">Payroll Text / CSV Format</div>
            </div>
        </div>
        <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 font-bold text-xs flex items-center justify-center font-mono">MDR</div>
            <div>
                <div class="text-xs font-bold text-slate-900">Mandiri MCM 2.0</div>
                <div class="text-[10px] text-slate-400">Corporate Bulk CSV</div>
            </div>
        </div>
        <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 font-bold text-xs flex items-center justify-center font-mono">BNI</div>
            <div>
                <div class="text-xs font-bold text-slate-900">BNI Direct</div>
                <div class="text-[10px] text-slate-400">Mass Payment File</div>
            </div>
        </div>
        <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-900 font-bold text-xs flex items-center justify-center font-mono">BRI</div>
            <div>
                <div class="text-xs font-bold text-slate-900">BRI CMS</div>
                <div class="text-[10px] text-slate-400">Cash Management System</div>
            </div>
        </div>
    </div>

    <!-- Bank Account Validation Summary (Audit Status) -->
    @if($validation)
        <div class="p-5 rounded-2xl {{ $validation['invalid_count'] > 0 ? 'bg-amber-50/70 border border-amber-200' : 'bg-emerald-50/70 border border-emerald-200' }} flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl {{ $validation['invalid_count'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }} flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="{{ $validation['invalid_count'] > 0 ? 'alert-triangle' : 'check-circle' }}" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold {{ $validation['invalid_count'] > 0 ? 'text-amber-900' : 'text-emerald-900' }}">
                        Validasi Rekening Bank Periode: {{ $selectedPeriod->name }}
                    </h3>
                    <p class="text-[11px] {{ $validation['invalid_count'] > 0 ? 'text-amber-700' : 'text-emerald-700' }} mt-0.5">
                        {{ $validation['valid_count'] }} dari {{ $validation['total_employees'] }} rekening siap ditransfer (Total: Rp {{ number_format($validation['total_disbursal_amount'], 2, ',', '.') }}).
                        @if($validation['invalid_count'] > 0)
                            Terdapat {{ $validation['invalid_count'] }} karyawan yang data rekening banknya belum lengkap.
                        @endif
                    </p>
                </div>
            </div>

            @if($validation['invalid_count'] > 0)
                <div class="text-xs">
                    <span class="font-bold text-amber-900 block mb-1">Perlu Dilengkapi:</span>
                    <ul class="text-[11px] text-amber-800 list-disc list-inside space-y-0.5">
                        @foreach(array_slice($validation['invalid_records'], 0, 3) as $inv)
                            <li>{{ $inv['name'] }} ({{ $inv['missing'] }})</li>
                        @endforeach
                        @if(count($validation['invalid_records']) > 3)
                            <li>+ {{ count($validation['invalid_records']) - 3 }} karyawan lainnya</li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
                Riwayat Berkas Batch Disbursal
            </h2>
        </div>

        <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold">
                <tr>
                    <th class="px-5 py-3">Nomor Batch Disbursal</th>
                    <th class="px-5 py-3">Periode Payroll</th>
                    <th class="px-5 py-3">Format Bank Sasaran</th>
                    <th class="px-5 py-3">Total Rekening</th>
                    <th class="px-5 py-3">Total Nominal Disbursal</th>
                    <th class="px-5 py-3">Dibuat Oleh</th>
                    <th class="px-5 py-3 text-right">Unduh Berkas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($batches as $batch)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-3.5">
                            <span class="font-mono font-bold text-slate-900">{{ $batch->batch_number }}</span>
                            <div class="text-[10px] text-slate-400">{{ $batch->created_at->format('d M Y, H:i') }} WIB</div>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-800">
                            {{ $batch->payrollPeriod->name }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono
                                {{ $batch->bank_target === 'bca' ? 'bg-blue-100 text-blue-800' :
                                   ($batch->bank_target === 'mandiri' ? 'bg-amber-100 text-amber-800' :
                                   ($batch->bank_target === 'bni' ? 'bg-teal-100 text-teal-800' : 'bg-blue-100 text-blue-900')) }}">
                                {{ $batch->bank_target }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-800">
                            {{ $batch->total_records }} Karyawan
                        </td>
                        <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                            Rp {{ number_format($batch->total_amount, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-[11px]">
                            {{ $batch->createdByUser?->name ?? 'System' }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('company.disbursal.download', $batch->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-2xs transition">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Unduh File CSV
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                            Belum ada batch disbursal yang dibuat. Klik tombol di atas untuk membuat berkas transfer payroll pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($batches->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $batches->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: GENERATE DISBURSAL BATCH -->
    <div x-show="showGenModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="showGenModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4 text-red-600"></i>
                    Buat Batch Disbursal Bank
                </h3>
                <button type="button" @click="showGenModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('company.disbursal.generate') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Periode Penggajian *</label>
                    <select name="payroll_period_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="">-- Pilih Periode Selesai / Disetujui --</option>
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Status: {{ ucfirst($p->status) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target Format Perbankan *</label>
                    <select name="bank_target" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">
                        <option value="bca">BCA (KlikBCA Bisnis Payroll CSV)</option>
                        <option value="mandiri">Bank Mandiri (Mandiri Cash Management - MCM)</option>
                        <option value="bni">Bank BNI (BNI Direct Bulk Payment)</option>
                        <option value="bri">Bank BRI (Corporate CMS Disbursal)</option>
                    </select>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-500 leading-relaxed">
                    Sistem akan memvalidasi nomor rekening dan nama pemilik rekening seluruh karyawan aktif pada periode tersebut.
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="showGenModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-[#DC2626] text-white hover:bg-[#991B1B]">Kompilasi & Buat Batch</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
