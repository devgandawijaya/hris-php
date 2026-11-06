@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Data Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola biodata induk, status kepegawaian (PKWT/PKWTT), PTKP, dan berkas digital.</p>
        </div>
        <div>
            <a href="{{ route('company.employees.create') }}" class="px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-semibold hover:bg-[#991B1B] transition shadow-md shadow-red-600/20 inline-flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Tambah Karyawan Baru
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('company.employees.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-xl border border-slate-300 pl-10 pr-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                       placeholder="Cari nama, NIK karyawan, atau email...">
            </div>

            <div class="sm:col-span-3">
                <select name="department_id" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="employment_status" class="w-full rounded-xl border border-slate-300 px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    <option value="">Semua Status Kerja</option>
                    <option value="pkwtt" {{ request('employment_status') === 'pkwtt' ? 'selected' : '' }}>PKWTT (Tetap)</option>
                    <option value="pkwt" {{ request('employment_status') === 'pkwt' ? 'selected' : '' }}>PKWT (Kontrak)</option>
                    <option value="probation" {{ request('employment_status') === 'probation' ? 'selected' : '' }}>Probation (Masa Percobaan)</option>
                    <option value="internship" {{ request('employment_status') === 'internship' ? 'selected' : '' }}>Internship (Magang)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl text-xs font-semibold bg-slate-900 text-white hover:bg-slate-800 transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'department_id', 'employment_status', 'status']))
                    <a href="{{ route('company.employees.index') }}" class="py-2 px-3 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition flex items-center justify-center" title="Reset filter">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Employees Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Departemen & Jabatan</th>
                        <th class="px-5 py-3.5">Status & Masa Kerja</th>
                        <th class="px-5 py-3.5">PTKP & Upah Pokok</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-red-50 text-[#DC2626] font-bold flex items-center justify-center text-xs shrink-0 border border-red-200">
                                        {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name ?? '', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $emp->full_name }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $emp->employee_number }} • {{ $emp->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-800">{{ $emp->position?->title ?? 'Belum Ditentukan' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $emp->department?->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $emp->employment_status === 'pkwtt' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ strtoupper($emp->employment_status) }}
                                </span>
                                <div class="text-[11px] text-slate-400 mt-1">Gabung: {{ $emp->join_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono">
                                <div class="font-semibold text-slate-900">Rp {{ number_format($emp->basic_salary, 0, ',', '.') }}</div>
                                <div class="text-[11px] text-red-600 font-bold">PTKP: {{ $emp->tax_ptkp_status }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($emp->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">{{ ucfirst($emp->status) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right space-x-1.5">
                                <a href="{{ route('company.employees.show', $emp->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition text-xs font-semibold">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Profil
                                </a>
                                <a href="{{ route('company.employees.edit', $emp->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition text-xs font-semibold">
                                    <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                Belum ada data karyawan yang cocok dengan kriteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

