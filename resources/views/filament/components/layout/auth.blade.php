@php
    use Filament\Support\Enums\Width;

    $livewire ??= null;
    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= (filament()->getSimplePageMaxContentWidth() ?? Width::Large);
    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }
@endphp

@push('styles')
    @vite(['resources/css/app.css', 'resources/css/auth.css'])
@endpush

<x-filament-panels::layout.base :livewire="$livewire">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap');
        @keyframes glm-fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes glm-slide-up { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .glm-fade-in { animation: glm-fade-in 0.6s ease-out forwards; }
        .glm-slide-up { animation: glm-slide-up 0.7s ease-out 0.2s forwards; opacity: 0; }
    </style>
    <div class="glm-auth-layout fi-body min-h-screen flex flex-col lg:flex-row">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

        {{-- Left panel: branding + logo --}}
        <div class="glm-auth-left hidden lg:flex lg:w-1/2 xl:w-[55%] bg-[#0F172A] text-white flex-col justify-between p-10 xl:p-16 relative overflow-hidden">
            <div class="glm-auth-shapes absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute top-20 left-10 w-72 h-72 bg-[#2563EB]/20 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-32 right-20 w-96 h-96 bg-[#2563EB]/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
                <div class="absolute top-1/2 left-1/3 w-64 h-64 border border-[#2563EB]/30 rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
            </div>
            <div class="relative z-10">
                <a href="{{ filament()->getUrl() }}" class="inline-block glm-fade-in">
                    <img src="{{ asset('images/light-logo.png') }}" alt="GLM" class="h-10 xl:h-12 w-auto" />
                </a>
            </div>
            <div class="relative z-10 space-y-6 glm-slide-up">
                <h2 class="text-2xl xl:text-3xl font-bold tracking-tight" style="font-family: 'Montserrat', sans-serif;">
                    Gestion Location Maroc
                </h2>
                <p class="text-white/80 text-lg max-w-md leading-relaxed" style="font-family: 'Inter', sans-serif;">
                    Gérez vos locations et contrats en toute simplicité. Accédez à votre espace professionnel.
                </p>
            </div>
            <div class="relative z-10 text-white/50 text-sm" style="font-family: 'Inter', sans-serif;">
                &copy; {{ date('Y') }} GLM. Tous droits réservés.
            </div>
        </div>

        {{-- Right panel: form --}}
        <div class="glm-auth-right flex-1 flex flex-col justify-center items-center p-6 sm:p-10 bg-[#F2F4F7] dark:bg-gray-900">
            <div class="w-full max-w-md glm-fade-in">
                {{ $slot }}
            </div>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
