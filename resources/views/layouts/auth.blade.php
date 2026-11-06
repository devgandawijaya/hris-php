<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Autentikasi') — RiHRIS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#DC2626',
                        'primary-dark': '#991B1B',
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 antialiased">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <a href="{{ route('landing') }}" class="inline-flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#DC2626] flex items-center justify-center text-white shadow-md shadow-red-500/20">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <span class="text-2xl font-black tracking-tight text-slate-900">Ri<span class="text-[#DC2626]">HRIS</span></span>
        </a>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white py-8 px-6 sm:px-10 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80">
            @yield('content')
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} RiHRIS SaaS Indonesia. Dilindungi keamanan enkripsi mutakhir.
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>

