@php
    $livewire ??= null;
    $renderHookScopes = $livewire?->getRenderHookScopes();
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @vite(['resources/css/app.css', 'resources/css/auth.css'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap');
        @keyframes glm-fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .glm-fade-in { animation: glm-fade-in 0.6s ease-out forwards; }
        .glm-auth-card input[type="email"]{ padding-left: 1rem !important; padding-right: 1rem !important; text-indent: 0 !important; background-position: 1rem center !important; }
        .glm-auth-card input[type="text"], .glm-auth-card input[type="password"]{ text-indent: 0 !important; }
        .glm-auth-card .fi-input-wrp-prefix:empty{ display: none !important; }
    </style>

    {{-- Register: full-width dark page, single centered card (max-w-5xl) – no left hero --}}
    <div class="glm-auth-register-page fi-body min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8 bg-[#0B1220]">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

        <div class="w-full max-w-5xl glm-fade-in">
            {{ $slot }}
        </div>

        <p class="mt-8 text-center text-white/50 text-sm" style="font-family: 'Inter', sans-serif;">
            &copy; {{ date('Y') }} GLM. Tous droits réservés.
        </p>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
