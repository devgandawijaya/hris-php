@extends('layouts.app')

@section('title', 'Detail Rekanan Alih Daya - ' . $vendor->name)

@section('content')
<div class="space-y-6" x-data="{ contractModal: false }">

    <!-- Back & Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('outsourcing.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
        <div>
            <span class="text-xs text-slate-400 font-mono">{{ $vendor->vendor_code }}</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $vendor->name }}</h1>
        </div>
    </div>

    <!-- Vendor Information Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3 md:col-span-1">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Legalitas & Manajemen</h3>
            <div class="space-y-2 text-xs">
                <div>
                    <span class="text-slate-400 block">Badan Hukum:</span>
                    <span class="font-bold text-slate-800">{{ $vendor->legal_entity_type }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Izin NIB OSS:</span>
                    <span class="font-mono text-slate-800">{{ $vendor->nib_oss_license }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">No. Registrasi Disnaker:</span>
                    <span class="font-mono text-slate-800">{{ $vendor->disnaker_registration_number ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">PIC Rekanan:</span>
                    <span class="font-semibold text-slate-800">{{ $vendor->pic_name }} ({{ $vendor->pic_contact }})</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Management Fee:</span>
                    <span class="font-black text-rose-600">{{ number_format($vendor->management_fee_percentage, 1) }}%</span>
                </div>
            </div>
            <button @click="contractModal = true" class="w-full mt-4 py-2 bg-[#DC2626] text-white text-xs font-semibold rounded-xl hover:bg-[#991B1B] transition flex items-center justify-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Kontrak SPK Baru
            </button>
        </div>

        <!-- Contracts & Workers List -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm md:col-span-2 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Kontrak Pekerjaan & Perlindungan TUPE (Putusan MK 27/2011)</h3>

            <div class="space-y-4">
                @forelse($vendor->contracts as $contract)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-900 font-mono">{{ $contract->contract_number }}</span>
                                <div class="text-[11px] text-slate-500 uppercase tracking-wider font-semibold">{{ $contract->service_type }}</div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $contract->tupe_protection_guarantee ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $contract->tupe_protection_guarantee ? 'TUPE Terlindungi' : 'Non-TUPE' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-xs text-slate-600">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Periode:</span>
                                {{ $contract->start_date->format('d M Y') }} - {{ $contract->end_date->format('d M Y') }}
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Kuota Personil:</span>
                                {{ $contract->workerAssignments->count() }} / {{ $contract->headcount_quota }} Orang
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Tagihan Bulanan:</span>
                                <span class="font-bold text-slate-900">Rp {{ number_format($contract->monthly_billing_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Worker Assignments -->
                        @if($contract->workerAssignments->isNotEmpty())
                            <div class="pt-2 border-t border-slate-200 text-xs">
                                <span class="text-[10px] font-bold uppercase text-slate-400 mb-1 block">Daftar Tenaga Kerja Alih Daya:</span>
                                <div class="space-y-1">
                                    @foreach($contract->workerAssignments as $assignment)
                                        <div class="flex items-center justify-between py-1 px-2 rounded bg-white border border-slate-100">
                                            <div>
                                                <span class="font-semibold text-slate-800">{{ $assignment->worker_name }}</span>
                                                <span class="text-[10px] text-slate-400 ml-1">({{ $assignment->assigned_role }})</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-[10px]">
                                                <span class="px-1.5 py-0.5 rounded {{ $assignment->bpjs_tk_registered ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">
                                                    BPJS TK: {{ $assignment->bpjs_tk_registered ? 'Ya' : 'Tidak' }}
                                                </span>
                                                <span class="px-1.5 py-0.5 rounded {{ $assignment->wage_compliant_with_umk ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $assignment->wage_compliant_with_umk ? 'Gaji >= UMK' : 'Gaji < UMK' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">Belum ada kontrak SPK untuk rekanan ini.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Create Contract Modal -->
    <div x-show="contractModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
        <div @click.away="contractModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-slate-900">Buat Kontrak SPK Alih Daya Baru</h3>
                <button @click="contractModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form action="{{ route('outsourcing.contracts.store', $vendor->id) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Nomor Kontrak SPK *</label>
                    <input type="text" name="contract_number" required placeholder="SPK-SEC/2026/001" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Jenis Pekerjaan Jasa *</label>
                        <select name="service_type" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                            <option value="security">Pengamanan (Security)</option>
                            <option value="cleaning">Kebersihan (Cleaning Service)</option>
                            <option value="catering">Katering (Food Service)</option>
                            <option value="driver">Pengemudi (Driver)</option>
                            <option value="it_support">Dukungan Teknis IT</option>
                            <option value="warehousing">Pergudangan & Logistik</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Target Kuota Personil *</label>
                        <input type="number" name="headcount_quota" required value="10" min="1" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Selesai *</label>
                        <input type="date" name="end_date" required class="w-full px-3 py-2 border rounded-xl border-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Nilai Tagihan Bulanan (Rp) *</label>
                    <input type="number" step="0.01" name="monthly_billing_amount" required value="55000000" class="w-full px-3 py-2 border rounded-xl border-slate-300">
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2">
                    <input type="checkbox" name="tupe_protection_guarantee" value="1" checked class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    <span class="text-slate-700 font-medium">Jaminan Perlindungan TUPE (Putusan MK 27/2011 & Pasal 19 PP 35/2021)</span>
                </div>

                <div class="pt-3 border-t flex justify-end gap-2">
                    <button type="button" @click="contractModal = false" class="px-4 py-2 border rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-[#DC2626] text-white rounded-xl hover:bg-[#991B1B] font-semibold">Simpan Kontrak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
