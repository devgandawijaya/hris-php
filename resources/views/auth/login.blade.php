@extends('layouts.auth')

@section('title', 'Masuk Akun')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Masuk ke Ruang Kerja</h2>
    <p class="text-xs text-slate-500 mt-1">Gunakan kredensial perusahaan atau akun karyawan Anda.</p>
</div>

@if(session('error'))
    <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
        @foreach($errors->all() as $err)
            <div class="flex items-center gap-1.5">
                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                <span>{{ $err }}</span>
            </div>
        @endforeach
    </div>
@endif

<form action="{{ route('login') }}" method="POST" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i data-lucide="mail" class="w-4 h-4"></i>
            </div>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                   placeholder="nama@perusahaan.co.id">
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-1">
            <label for="password" class="block text-xs font-semibold text-slate-700">Kata Sandi</label>
            <a href="#" class="text-xs font-medium text-[#DC2626] hover:underline">Lupa sandi?</a>
        </div>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </div>
            <input type="password" id="password" name="password" required
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                   placeholder="••••••••">
        </div>
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-[#DC2626] focus:ring-red-500">
            <span class="text-xs text-slate-600">Ingat saya di perangkat ini</span>
        </label>
    </div>

    <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-[#DC2626] hover:bg-[#991B1B] shadow-sm shadow-red-600/30 transition flex items-center justify-center gap-2">
        <i data-lucide="log-in" class="w-4 h-4"></i>
        Masuk ke Sistem
    </button>
</form>

<div class="mt-6 pt-5 border-t border-slate-100 text-center">
    <p class="text-xs text-slate-600">
        Belum memiliki ruang kerja perusahaan?
        <a href="{{ route('register') }}" class="font-semibold text-[#DC2626] hover:underline">Daftar sekarang</a>
    </p>
</div>

<!-- Demo Accounts Helper Callout -->
<div class="mt-6 p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1.5">
    <div class="font-bold text-slate-800 flex items-center gap-1.5">
        <i data-lucide="key" class="w-3.5 h-3.5 text-amber-500"></i>
        Akun Bawaan (Default Demo)
    </div>
    <div class="flex justify-between items-center text-[11px]">
        <span>Super Admin:</span>
        <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 font-mono">superadmin@rihris.id / Password123#</code>
    </div>
    <div class="flex justify-between items-center text-[11px]">
        <span>Company Owner:</span>
        <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 font-mono">owner@solusidigital.id / Password123#</code>
    </div>
</div>
@endsection

