@extends('layouts.app')

@section('title', 'Offboarding Clearance Checklist & Paklaring')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Offboarding & Clearance Checklist</h1>
            <p class="text-sm text-slate-500 mt-1">Matriks serah terima aset 4 pilar (HR, IT, Finance, Supervisor) dan penerbitan Surat Pengalaman Kerja (Paklaring) resmi.</p>
        </div>
        <button onclick="document.getElementById('modal-add-clearance').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Inisiasi Clearance Baru
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Proses Offboarding</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">Pemberhentian / Resignasi</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Berjalan (In Progress)</p>
            <h3 class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['in_progress'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">Menunggu penyelesaian signoff departemen</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Selesai & Paklaring Diterbitkan</p>
            <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['completed'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">4 pilar departemen tuntas</p>
        </div>
    </div>

    <!-- Clearance Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-800">Matriks Status Clearance Karyawan</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('company.clearances.index') }}" class="px-3 py-1 rounded text-xs {{ !request('status') ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Semua</a>
                <a href="{{ route('company.clearances.index', ['status' => 'in_progress']) }}" class="px-3 py-1 rounded text-xs {{ request('status') === 'in_progress' ? 'bg-amber-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">In Progress</a>
                <a href="{{ route('company.clearances.index', ['status' => 'completed']) }}" class="px-3 py-1 rounded text-xs {{ request('status') === 'completed' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Selesai</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Hari Terakhir</th>
                        <th class="px-5 py-3 text-center">1. HR</th>
                        <th class="px-5 py-3 text-center">2. IT</th>
                        <th class="px-5 py-3 text-center">3. Finance</th>
                        <th class="px-5 py-3 text-center">4. Atasan</th>
                        <th class="px-5 py-3 text-center">Status Akhir</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($clearances as $clr)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-800">{{ $clr->employee->full_name }}</div>
                                <div class="text-xs text-slate-400">{{ $clr->employee->department ? $clr->employee->department->name : '-' }} - {{ $clr->employee->employee_number }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-semibold text-slate-800">{{ $clr->last_working_day->format('d/m/Y') }}</div>
                                <div class="text-xs text-slate-500 capitalize">{{ str_replace('_', ' ', $clr->separation_reason) }}</div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($clr->hr_status === 'cleared')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">✓ Lunas</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($clr->it_status === 'cleared')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">✓ Wipe</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($clr->finance_status === 'cleared')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">✓ Lunas</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($clr->supervisor_status === 'cleared')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">✓ Handover</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($clr->overall_status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Clear</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">In Progress</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('company.clearances.show', $clr) }}" class="text-xs px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition font-medium">Buka Checklist</a>
                                    @if($clr->paklaringLetter)
                                        <a href="{{ route('company.clearances.paklaring.print', $clr->paklaringLetter) }}" target="_blank" class="text-xs px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded transition font-medium">Paklaring</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400">Belum ada proses offboarding clearance yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clearances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $clearances->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Inisiasi Clearance -->
<div id="modal-add-clearance" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800">Inisiasi Offboarding Clearance</h3>
            <button onclick="document.getElementById('modal-add-clearance').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form method="POST" action="{{ route('company.clearances.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Pilih Karyawan *</label>
                <select name="employee_id" required class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }}) - {{ $emp->department ? $emp->department->name : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Surat Resignasi / PHK *</label>
                <input type="date" name="resignation_date" required value="{{ date('Y-m-d') }}" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Hari Kerja Terakhir (Last Working Day) *</label>
                <input type="date" name="last_working_day" required value="{{ date('Y-m-d', strtotime('+1 month')) }}" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Alasan Pemberhentian *</label>
                <select name="separation_reason" required class="w-full text-sm border-slate-300 rounded-lg px-3 py-2">
                    <option value="resigned">Pengunduran Diri Sukarela (Resign)</option>
                    <option value="contract_end">Habis Masa Kontrak PKWT</option>
                    <option value="retired">Pensiun</option>
                    <option value="terminated">Pemutusan Hubungan Kerja (PHK)</option>
                    <option value="deceased">Meninggal Dunia</option>
                </select>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-clearance').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-sm text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Mulai Clearance</button>
            </div>
        </form>
    </div>
</div>
@endsection
