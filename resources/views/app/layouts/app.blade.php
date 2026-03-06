<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
<head>
    <script>
        (function () {
            var theme = localStorage.getItem('glm_theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
                if (theme !== 'light') localStorage.setItem('glm_theme', 'light');
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Tableau de bord' }} – {{ config('app.name', 'GLM') }}</title>
    <link rel="icon" href="{{ url('images/Icon%20Blue.png') }}">
    @vite(['resources/css/app.css', 'resources/css/app-admin.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen font-sans antialiased bg-[color:var(--bg)] text-[color:var(--text)]" x-data="{ sidebarOpen: false }" @toggle-sidebar.window="sidebarOpen = !sidebarOpen">
    <div class="flex min-h-screen overflow-hidden">
        @include('app.layouts.sidebar')

        <div class="flex min-w-0 flex-1 flex-col lg:pl-[292px]">
            @include('app.layouts.navbar')

            <main class="glm-app-main flex-1 overflow-y-auto">
                <div class="py-8 px-6 sm:px-8 lg:px-10 max-w-[1600px] mx-auto">
                    @if (session('success'))
                        <div class="mb-6 glm-fade-in rounded-2xl px-4 py-3 text-sm border bg-[color:var(--success-bg)] border-[color:var(--success-border)] text-[color:var(--success)]" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-6 glm-fade-in rounded-2xl px-4 py-3 text-sm border bg-[color:var(--danger-bg)] border-[color:var(--danger-border)] text-[color:var(--danger)]" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('info'))
                        <div class="mb-6 glm-fade-in rounded-2xl px-4 py-3 text-sm border border-[#2563EB]/30 bg-[#2563EB]/10 text-[#93C5FD]" role="alert">
                            {{ session('info') }}
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    @stack('scripts')
</body>
</html>
