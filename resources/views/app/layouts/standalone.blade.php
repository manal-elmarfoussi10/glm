<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Aperçu' }} – {{ config('app.name', 'GLM') }}</title>
    <link rel="icon" href="{{ url('images/Icon%20Blue.png') }}">
    @vite(['resources/css/app.css', 'resources/css/app-admin.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-[var(--color-navy-950,#0a0f1a)] font-sans antialiased text-[#e2e8f0]">
    <div class="flex min-h-screen w-full flex-col">
        @yield('content')
    </div>
</body>
</html>
