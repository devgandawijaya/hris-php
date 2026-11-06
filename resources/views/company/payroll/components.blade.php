@extends('layouts.app')

@section('title', 'Master Komponen Gaji & Tunjangan')

@section('content')
<div class="space-y-6" x-data="{ openCreateModal: false, openAssignModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Komponen Gaji & Tunjangan</h1>
            <p class="text-sm text-slate-500 mt-1">Konfigurasikan tunjangan tetap, tunjangan variabel, dan potongan kustom karyawan.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openAssignModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-sm">
                <i data-lucide="user-plus" class="w-4 h-4 text-slate-500"></i>
                Tetapkan ke Karyawan
            </button>
            <button @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm shadow-red-500/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Tambah Komponen Baru
            </button>
        </div>
    </div>

    <!-- Master Components Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1: Master Components List -->
        <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center justify-between">
                <span>Daftar Master Komponen</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold">{{ $components->count() }} Komponen</span>
            </h2>

            <div class="space-y-2.5">
                @forelse($components as $comp)
                    <div class="p-3.5 rounded-xl border border-slate-200/80 hover:border-slate-300 transition flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-900">{{ $comp->name }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $comp->type === 'allowance' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $comp->type === 'allowance' ? 'Tunjangan' : 'Potongan' }}
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    {{ $comp->is_fixed ? 'Tetap' : 'Variabel' }}
                                </span>
                                @if($comp->is_taxable)
                                    <span class="text-[10px] text-amber-600 font-medium">Kena Pajak</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-slate-500">{{ $comp->employee_components_count }} Pegawai</span>
                            <form action="{{ route('company.components.destroy', $comp->id) }}" method="POST" onsubmit="return confirm('Hapus komponen gaji ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-400 hover:text-red-600 transition" title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada komponen kustom.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Employee Assignment Overview -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Penetapan Komponen per Karyawan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rincian tunjangan dan potongan yang aktif per individu.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <th class="px-5 py-3.5">Karyawan</th>
                            <th class="px-5 py-3.5">Gaji Pokok</th>
                            <th class="px-5 py-3.5">Komponen Ditetapkan</th>
                            <th class="px-5 py-3.5 text-right">Total Tunjangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900">{{ $emp->full_name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $emp->position?->title ?? 'Staf' }} • {{ $emp->department?->name ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 font-bold text-slate-800">
                                    Rp {{ number_format($emp->basic_salary, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($emp->salaryComponents as $sc)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold {{ $sc->salaryComponent?->type === 'allowance' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                                {{ $sc->salaryComponent?->name }}: Rp {{ number_format($sc->amount, 0, ',', '.') }}
                                            </span>
                                        @empty
                                            <span class="text-slate-400 text-[11px] italic">Hanya Gaji Pokok</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right font-black text-emerald-600">
                                    Rp {{ number_format($emp->salaryComponents->where('salaryComponent.type', 'allowance')->sum('amount'), 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-400">
                                    Belum ada data karyawan aktif.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Buat Komponen Baru -->
    <div x-show="openCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openCreateModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Komponen Gaji</h3>
                <button @click="openCreateModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.components.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Komponen <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Tunjangan Jabatan, Uang Makan"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Komponen <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="allowance">Tunjangan (Menambah Penghasilan)</option>
                        <option value="deduction">Potongan (Mengurangi Penghasilan)</option>
                    </select>
                </div>

                <div class="space-y-2 pt-2">
                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_fixed" value="1" checked class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        <span>Tunjangan Tetap (Masuk perhitungan dasar BPJS & Pesangon)</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_taxable" value="1" checked class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        <span>Objek Pajak Penghasilan (PPh 21 TER)</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Komponen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tetapkan ke Karyawan -->
    <div x-show="openAssignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openAssignModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tetapkan Komponen ke Karyawan</h3>
                <button @click="openAssignModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('company.components.assign') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_number }}) - {{ $emp->position?->title ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Komponen Gaji <span class="text-red-500">*</span></label>
                    <select name="salary_component_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">-- Pilih Komponen --</option>
                        @foreach($components as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->type === 'allowance' ? 'Tunjangan' : 'Potongan' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal per Bulan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" step="1000" min="0" name="amount" required placeholder="Contoh: 1500000"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openAssignModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#991B1B] transition shadow-sm">
                        Simpan Penetapan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
