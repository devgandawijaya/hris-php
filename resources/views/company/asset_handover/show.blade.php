@extends('layouts.app')

@section('title', 'Detail BAST Pengembalian Aset - ' . $bast->bast_number)

@section('content')
<div class="space-y-6">

    <!-- Top Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('company.asset-handover.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar BAST
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('company.asset-handover.print', $bast->id) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition inline-flex items-center gap-1.5 shadow-sm">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                Cetak Format BAST Sah
            </a>
        </div>
    </div>

    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                    Berita Acara Serah Terima Aset
                </span>
                <h1 class="text-xl font-black text-slate-900 mt-1">{{ $bast->bast_number }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Tanggal Serah Terima: {{ $bast->handover_date->format('d F Y') }}</p>
            </div>

            <div class="text-right">
                @if($bast->status === 'completed')
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        Ditandatangani Lengkap
                    </div>
                    <div class="text-[10px] text-slate-400 mt-1">Ditandatangani pada: {{ $bast->signed_at ? $bast->signed_at->format('d/m/Y H:i') : '-' }}</div>
                @else
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        Menunggu Tanda Tangan
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Participants Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Pihak Pertama (Menyerahkan Aset)</h2>
            <div class="text-sm font-bold text-slate-900">{{ $bast->employee->full_name }}</div>
            <div class="text-xs text-slate-600">NIK: {{ $bast->employee->employee_number }}</div>
            <div class="text-xs text-slate-600">Divisi / Posisi: {{ $bast->employee->department->name ?? 'Dept' }} - {{ $bast->employee->position->title ?? 'Staff' }}</div>
            <div class="text-xs text-slate-600">Email: {{ $bast->employee->email }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Pihak Kedua (Menerima / Petugas)</h2>
            <div class="text-sm font-bold text-slate-900">{{ $bast->officer_name }}</div>
            <div class="text-xs text-slate-600">Jabatan: {{ $bast->officer_position }}</div>
            <div class="text-xs text-slate-600">Entitas: {{ $bast->company->legal_name ?? $bast->company->name }}</div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Rincian Fisik Inventaris yang Dikembalikan</h2>
            <div class="text-xs text-slate-600">
                Total Kompensasi Ganti Rugi: <strong class="text-rose-600">Rp {{ number_format($bast->total_compensation_due, 0, ',', '.') }}</strong>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">No.</th>
                        <th class="px-4 py-3 font-bold">Nama Item Aset</th>
                        <th class="px-4 py-3 font-bold">Nomor Seri / Tag</th>
                        <th class="px-4 py-3 font-bold">Kondisi Pengembalian</th>
                        <th class="px-4 py-3 font-bold">Kewajiban Ganti Rugi</th>
                        <th class="px-4 py-3 font-bold">Catatan Fisik</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($bast->items as $idx => $it)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 font-semibold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-bold text-slate-800">{{ $it->asset_name }}</td>
                            <td class="px-4 py-3 font-mono text-slate-600">{{ $it->serial_number ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($it->condition_on_return === 'good')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Baik & Lengkap
                                    </span>
                                @elseif($it->condition_on_return === 'minor_damage')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Rusak Ringan
                                    </span>
                                @elseif($it->condition_on_return === 'severe_damage')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Rusak Berat
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-900">
                                        Hilang
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold {{ $it->compensation_amount > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                                Rp {{ number_format($it->compensation_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $it->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Signing Section -->
    @if($bast->status === 'draft')
        <div class="bg-gradient-to-r from-slate-50 to-red-50 rounded-2xl border border-red-200 p-6">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Tanda Tangan Digital BAST & Picu Revokasi Akses SSO</h3>
            <p class="text-xs text-slate-600 mb-4">
                Dengan menandatangani berkas ini, Pihak Pertama dan Pihak Kedua menyetujui rincian fisik inventaris yang telah diserahkan. Sistem akan secara otomatis mencatat pengembalian aset dan memicu proses pencabutan hak akses akun Google Workspace, Slack, VPN, dan portal perusahaan.
            </p>

            <form action="{{ route('company.asset-handover.sign', $bast->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanda Tangan Karyawan (Ketik Nama Lengkap)</label>
                    <input type="text" name="employee_signature" placeholder="Ketik nama lengkap karyawan" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanda Tangan Petugas HR/IT (Ketik Nama Lengkap)</label>
                    <input type="text" name="officer_signature" placeholder="Ketik nama lengkap petugas penerima" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 bg-white" required>
                </div>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#DC2626] text-white hover:bg-[#991B1B] transition shadow-sm">
                        Selesaikan & Tanda Tangani BAST Resmi
                    </button>
                </div>
            </form>
        </div>
    @else
        <!-- Verified Digital Signatures -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-emerald-50 rounded-2xl border border-emerald-200 p-5 text-xs text-emerald-900">
            <div>
                <span class="font-bold block uppercase text-[10px] text-emerald-700">Tanda Tangan Elektronik Karyawan</span>
                <span class="font-mono font-bold">{{ $bast->employee_signature_token }}</span>
                <div class="text-[10px] text-emerald-600 mt-0.5">Terverifikasi pada {{ $bast->signed_at ? $bast->signed_at->format('d/m/Y H:i:s') : '-' }}</div>
            </div>
            <div>
                <span class="font-bold block uppercase text-[10px] text-emerald-700">Tanda Tangan Elektronik Petugas</span>
                <span class="font-mono font-bold">{{ $bast->officer_signature_token }}</span>
                <div class="text-[10px] text-emerald-600 mt-0.5">Petugas: {{ $bast->officer_name }} ({{ $bast->officer_position }})</div>
            </div>
        </div>
    @endif

</div>
@endsection
