@php
    use Filament\Support\Enums\Width;

    $livewire ??= null;
    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= (filament()->getSimplePageMaxContentWidth() ?? Width::Large);
    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    {{-- Load auth CSS directly so it always applies (Filament base may not render @stack('styles')) --}}
    @vite(['resources/css/app.css', 'resources/css/auth.css'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap');

        @keyframes glm-fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes glm-slide-up { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

        .glm-fade-in { animation: glm-fade-in 0.6s ease-out forwards; }
        .glm-slide-up { animation: glm-slide-up 0.7s ease-out 0.2s forwards; opacity: 0; }

        /* ✅ Email input: no extra padding – wrapper provides 8px so text starts near left edge */
        .glm-auth-card input[type="email"]{
            padding-left: 0 !important;
            padding-right: 0 !important;
            text-indent: 0 !important;
            background-position: 8px center !important; /* if browser adds icons */
        }

        /* ✅ If Filament keeps adding "prefix space", force all auth inputs to match */
        .glm-auth-card input[type="text"],
        .glm-auth-card input[type="password"]{
            text-indent: 0 !important;
        }

        /* ✅ Optional: if Filament renders an empty prefix icon container, hide it */
        .glm-auth-card .fi-input-wrp-prefix:empty{
            display: none !important;
        }
    </style>

    <div class="glm-auth-layout fi-body min-h-screen flex flex-col lg:flex-row">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

        {{-- Left panel: branding + logo --}}
        <div class="glm-auth-left hidden lg:flex lg:w-1/2 xl:w-[55%] bg-[#0F172A] text-white flex-col justify-between p-10 xl:p-16 relative overflow-hidden">
            <div class="glm-auth-shapes absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute top-20 left-10 w-72 h-72 bg-[#2563EB]/20 rounded-full blur-3xl glm-orb glm-orb-1"></div>
                <div class="absolute bottom-32 right-20 w-96 h-96 bg-[#2563EB]/10 rounded-full blur-3xl glm-orb glm-orb-2"></div>
                <div class="absolute top-1/2 left-1/3 w-64 h-64 border border-[#2563EB]/30 rounded-full glm-ring glm-orb-3"></div>
            </div>

            <div class="relative z-10 flex items-center gap-4 glm-fade-in">
                <a href="{{ filament()->getUrl() }}" class="flex items-center gap-4">
                    <img src="{{ url('images/light-logo.png') }}" alt="GLM" class="h-20 w-20 lg:h-28 lg:w-28 object-contain flex-shrink-0" onerror="this.style.display='none'; this.nextElementSibling?.classList.remove('hidden');" />
                    <span class="hidden text-2xl lg:text-3xl font-bold text-white tracking-tight" style="font-family: 'Montserrat', sans-serif;">GLM</span>
                </a>
            </div>

            <div class="relative z-10 space-y-8 glm-slide-up">
                <h2 class="text-6xl xl:text-7xl font-bold tracking-tight leading-[1.05] text-white max-w-xl" style="font-family: 'Montserrat', sans-serif;">
                    Gestion Location Maroc
                </h2>
                <p class="text-white/85 text-xl max-w-lg leading-relaxed mt-8" style="font-family: 'Inter', sans-serif;">
                    Gérez vos locations et contrats en toute simplicité. Accédez à votre espace professionnel.
                </p>
            </div>

            <div class="relative z-10 text-white/55 text-sm" style="font-family: 'Inter', sans-serif;">
                &copy; {{ date('Y') }} GLM. Tous droits réservés.
            </div>
        </div>

        {{-- Right panel: form --}}
        <div class="glm-auth-right flex-1 flex flex-col justify-center items-center p-8 sm:p-12 lg:p-16 bg-[#0B1220]">
            <div class="w-full max-w-[420px] glm-fade-in">
                {{ $slot }}
            </div>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>