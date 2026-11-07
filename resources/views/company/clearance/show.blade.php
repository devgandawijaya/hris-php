@extends('layouts.app')

@section('title', 'Checklist Clearance - ' . $clearance->employee->full_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('company.clearances.index') }}" class="text-slate-400 hover:text-slate-600">← Kembali</a>
                <h1 class="text-2xl font-bold text-slate-800">Clearance: {{ $clearance->employee->full_name }}</h1>
                @if($clearance->overall_status === 'completed')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Tuntas (Completed)</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Sedang Berlangsung</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-1">Hari Kerja Terakhir: <strong>{{ $clearance->last_working_day->translatedFormat('d F Y') }}</strong> | Alasan: <span class="capitalize">{{ str_replace('_', ' ', $clearance->separation_reason) }}</span></p>
        </div>
        <div>
            @if($clearance->paklaringLetter)
                <a href="{{ route('company.clearances.paklaring.print', $clearance->paklaringLetter) }}" target="_blank" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Cetak Surat Paklaring ({{ $clearance->paklaringLetter->letter_number }})
                </a>
            @endif
        </div>
    </div>

    <!-- 4 Department Pillars -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 1. HR Clearance -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold">1</div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Departemen HR / Kepegawaian</h3>
                        <p class="text-xs text-slate-400">Pengembalian ID Card, kartu asuransi fisik, exit interview</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $clearance->hr_status === 'cleared' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $clearance->hr_status === 'cleared' ? '✓ Cleared' : 'Pending' }}
                </span>
            </div>
            @if($clearance->hr_status === 'cleared')
                <div class="text-xs text-slate-500 bg-slate-50 p-3 rounded-lg">
                    Disetujui oleh: <strong>{{ $clearance->hrOfficer ? $clearance->hrOfficer->name : 'HR' }}</strong> pada {{ $clearance->hr_cleared_at ? $clearance->hr_cleared_at->format('d/m/Y H:i') : '' }}
                    <div class="mt-1 text-slate-700 italic">"{{ $clearance->hr_notes ?? 'Dokumen dan kartu identitas telah diserahkan lengkap.' }}"</div>
                </div>
            @endif
            <form method="POST" action="{{ route('company.clearances.signoff', [$clearance, 'hr']) }}" class="space-y-3">
                @csrf
                <textarea name="notes" rows="2" placeholder="Catatan checklist HR..." class="w-full text-xs border-slate-300 rounded-lg px-3 py-2">{{ $clearance->hr_notes }}</textarea>
                <div class="flex items-center gap-2">
                    <button type="submit" name="status" value="cleared" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-medium">Tandai HR Cleared</button>
                    <button type="submit" name="status" value="pending" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Reset Pending</button>
                </div>
            </form>
        </div>

        <!-- 2. IT Clearance -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold">2</div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Departemen IT / Infrastruktur</h3>
                        <p class="text-xs text-slate-400">Pengembalian laptop, monitor, pencabutan akun email & SSO</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $clearance->it_status === 'cleared' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $clearance->it_status === 'cleared' ? '✓ Cleared' : 'Pending' }}
                </span>
            </div>
            @if($clearance->it_status === 'cleared')
                <div class="text-xs text-slate-500 bg-slate-50 p-3 rounded-lg">
                    Disetujui oleh: <strong>{{ $clearance->itOfficer ? $clearance->itOfficer->name : 'IT' }}</strong> pada {{ $clearance->it_cleared_at ? $clearance->it_cleared_at->format('d/m/Y H:i') : '' }}
                    <div class="mt-1 text-slate-700 italic">"{{ $clearance->it_notes ?? 'Laptop dikembalikan, akun Google Workspace dinonaktifkan.' }}"</div>
                </div>
            @endif
            <form method="POST" action="{{ route('company.clearances.signoff', [$clearance, 'it']) }}" class="space-y-3">
                @csrf
                <textarea name="notes" rows="2" placeholder="Catatan checklist IT..." class="w-full text-xs border-slate-300 rounded-lg px-3 py-2">{{ $clearance->it_notes }}</textarea>
                <div class="flex items-center gap-2">
                    <button type="submit" name="status" value="cleared" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-medium">Tandai IT Cleared</button>
                    <button type="submit" name="status" value="pending" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Reset Pending</button>
                </div>
            </form>
        </div>

        <!-- 3. Finance Clearance -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">3</div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Departemen Keuangan (Finance)</h3>
                        <p class="text-xs text-slate-400">Pelunasan pinjaman karyawan, kasbon, dan advance SPPD</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $clearance->finance_status === 'cleared' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $clearance->finance_status === 'cleared' ? '✓ Cleared' : 'Pending' }}
                </span>
            </div>
            @if($clearance->finance_status === 'cleared')
                <div class="text-xs text-slate-500 bg-slate-50 p-3 rounded-lg">
                    Disetujui oleh: <strong>{{ $clearance->financeOfficer ? $clearance->financeOfficer->name : 'Finance' }}</strong> pada {{ $clearance->finance_cleared_at ? $clearance->finance_cleared_at->format('d/m/Y H:i') : '' }}
                    <div class="mt-1 text-slate-700 italic">"{{ $clearance->finance_notes ?? 'Kewajiban pinjaman dan kasbon lunas.' }}"</div>
                </div>
            @endif
            <form method="POST" action="{{ route('company.clearances.signoff', [$clearance, 'finance']) }}" class="space-y-3">
                @csrf
                <textarea name="notes" rows="2" placeholder="Catatan checklist Keuangan..." class="w-full text-xs border-slate-300 rounded-lg px-3 py-2">{{ $clearance->finance_notes }}</textarea>
                <div class="flex items-center gap-2">
                    <button type="submit" name="status" value="cleared" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-medium">Tandai Finance Cleared</button>
                    <button type="submit" name="status" value="pending" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Reset Pending</button>
                </div>
            </form>
        </div>

        <!-- 4. Supervisor Clearance -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center font-bold">4</div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Atasan Langsung / Divisi</h3>
                        <p class="text-xs text-slate-400">Serah terima tugas, transfer knowledge, repository handover</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $clearance->supervisor_status === 'cleared' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $clearance->supervisor_status === 'cleared' ? '✓ Cleared' : 'Pending' }}
                </span>
            </div>
            @if($clearance->supervisor_status === 'cleared')
                <div class="text-xs text-slate-500 bg-slate-50 p-3 rounded-lg">
                    Disetujui oleh: <strong>{{ $clearance->supervisorUser ? $clearance->supervisorUser->name : 'Atasan' }}</strong> pada {{ $clearance->supervisor_cleared_at ? $clearance->supervisor_cleared_at->format('d/m/Y H:i') : '' }}
                    <div class="mt-1 text-slate-700 italic">"{{ $clearance->supervisor_notes ?? 'Serah terima tugas pekerjaan dan file project tuntas.' }}"</div>
                </div>
            @endif
            <form method="POST" action="{{ route('company.clearances.signoff', [$clearance, 'supervisor']) }}" class="space-y-3">
                @csrf
                <textarea name="notes" rows="2" placeholder="Catatan checklist Atasan..." class="w-full text-xs border-slate-300 rounded-lg px-3 py-2">{{ $clearance->supervisor_notes }}</textarea>
                <div class="flex items-center gap-2">
                    <button type="submit" name="status" value="cleared" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-medium">Tandai Atasan Cleared</button>
                    <button type="submit" name="status" value="pending" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Reset Pending</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Paklaring Issuance Card -->
    @if($clearance->overall_status === 'completed' && !$clearance->paklaringLetter)
        <div class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 rounded-2xl p-6 shadow-sm">
            <h3 class="text-base font-bold text-red-900">4 Pilar Clearance Telah Tuntas!</h3>
            <p class="text-sm text-red-700 mt-1">Seluruh kewajiban telah diselesaikan. Anda sekarang dapat menerbitkan Surat Pengalaman Kerja (Paklaring) resmi sesuai Pasal 1603p KUHPerdata.</p>
            <form method="POST" action="{{ route('company.clearances.paklaring.issue', $clearance) }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-red-900 mb-1">Jabatan Penandatangan *</label>
                    <input type="text" name="signer_position" value="Human Resource Manager" required class="w-full text-sm border-red-300 rounded-lg px-3 py-2 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-red-900 mb-1">Penilaian Kinerja *</label>
                    <select name="conduct_appraisal" required class="w-full text-sm border-red-300 rounded-lg px-3 py-2 bg-white">
                        <option value="exemplary">Sangat Memuaskan (Exemplary)</option>
                        <option value="very_good" selected>Baik Sekali (Very Good)</option>
                        <option value="good">Baik (Good)</option>
                        <option value="satisfactory">Cukup (Satisfactory)</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition">
                        Terbitkan Surat Paklaring
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
