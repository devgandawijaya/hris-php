@extends('layouts.auth')

@section('title', 'Masuk Akun')

@section('content')
@php
    $demoParam = request('demo');
    $defaultEmail = old('email');
    $defaultPassword = '';

    if ($demoParam === 'superadmin') {
        $defaultEmail = 'superadmin@rihris.id';
        $defaultPassword = 'Password123#';
    } elseif ($demoParam === 'owner') {
        $defaultEmail = 'owner@solusidigital.id';
        $defaultPassword = 'Password123#';
    } elseif ($demoParam === 'hr') {
        $defaultEmail = 'hr@solusidigital.id';
        $defaultPassword = 'Password123#';
    } elseif ($demoParam === 'employee') {
        $defaultEmail = 'budi@solusidigital.id';
        $defaultPassword = 'Password123#';
    }
@endphp

<div class="mb-5">
    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Masuk ke Ruang Kerja</h2>
    <p class="text-xs text-slate-500 mt-1">Gunakan kredensial perusahaan atau pilih akun demo instan di bawah.</p>
</div>

<!-- 1-Click Demo Accounts Quick-Fill -->
<div class="mb-5 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
    <div class="flex items-center justify-between mb-2.5">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
            Isi Cepat Akun Demo (1-Click)
        </span>
        <span class="text-[10px] text-slate-400">Klik untuk autofill</span>
    </div>
    <div class="grid grid-cols-2 gap-2">
        <button type="button" onclick="setDemo('superadmin@rihris.id', 'Password123#', 'Super Admin')"
                class="p-2 rounded-lg border border-slate-200 bg-white hover:border-red-400 hover:bg-red-50 text-left transition group shadow-xs">
            <div class="font-bold text-xs text-slate-800 group-hover:text-red-700 flex items-center gap-1">
                <span>👑</span> Super Admin
            </div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">superadmin@rihris.id</div>
        </button>
        <button type="button" onclick="setDemo('owner@solusidigital.id', 'Password123#', 'Company Owner')"
                class="p-2 rounded-lg border border-slate-200 bg-white hover:border-red-400 hover:bg-red-50 text-left transition group shadow-xs">
            <div class="font-bold text-xs text-slate-800 group-hover:text-red-700 flex items-center gap-1">
                <span>🏢</span> Company Owner
            </div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">owner@solusidigital.id</div>
        </button>
        <button type="button" onclick="setDemo('hr@solusidigital.id', 'Password123#', 'HR Manager')"
                class="p-2 rounded-lg border border-slate-200 bg-white hover:border-red-400 hover:bg-red-50 text-left transition group shadow-xs">
            <div class="font-bold text-xs text-slate-800 group-hover:text-red-700 flex items-center gap-1">
                <span>👔</span> HR Manager
            </div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">hr@solusidigital.id</div>
        </button>
        <button type="button" onclick="setDemo('budi@solusidigital.id', 'Password123#', 'Karyawan (ESS)')"
                class="p-2 rounded-lg border border-slate-200 bg-white hover:border-red-400 hover:bg-red-50 text-left transition group shadow-xs">
            <div class="font-bold text-xs text-slate-800 group-hover:text-red-700 flex items-center gap-1">
                <span>👤</span> Karyawan ESS
            </div>
            <div class="text-[10px] text-slate-400 truncate mt-0.5">budi@solusidigital.id</div>
        </button>
    </div>
    <div id="demo-badge" class="{{ $demoParam ? 'flex' : 'hidden' }} mt-2.5 p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs items-center gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
        <span id="demo-badge-text">Kredensial {{ $demoParam ?: 'demo' }} terpasang! Klik "Masuk ke Sistem".</span>
    </div>
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
            <input type="email" id="email" name="email" value="{{ $defaultEmail }}" required autofocus
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
            <input type="password" id="password" name="password" value="{{ $defaultPassword }}" required
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                   placeholder="••••••••">
        </div>
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" checked class="w-4 h-4 rounded border-slate-300 text-[#DC2626] focus:ring-red-500">
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

<!-- Password reminder box -->
<div class="mt-5 p-3 rounded-lg bg-amber-50/70 border border-amber-200/80 text-[11px] text-amber-900 flex items-center gap-2">
    <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0"></i>
    <span>Password default seluruh akun demo: <strong class="font-mono text-amber-950">Password123#</strong></span>
</div>

<script>
function setDemo(email, pass, label) {
    const emailInput = document.getElementById('email');
    const passInput = document.getElementById('password');
    if (emailInput && passInput) {
        emailInput.value = email;
        passInput.value = pass;
        const badge = document.getElementById('demo-badge');
        const badgeText = document.getElementById('demo-badge-text');
        if (badge && badgeText) {
            badgeText.textContent = 'Kredensial ' + label + ' berhasil terpasang. Klik tombol "Masuk ke Sistem"!';
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        }
    }
}
</script>
@endsection
