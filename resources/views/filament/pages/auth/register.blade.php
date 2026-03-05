@php
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $hasLogo = $this->hasLogo();
@endphp

<div class="fi-simple-page glm-auth-card glm-register-card rounded-2xl border-0 p-6 sm:p-8 md:p-10 shadow-xl">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_START, scopes: $this->getRenderHookScopes()) }}

    <div class="glm-register-page-content space-y-6">
        {{-- Logo centered at top --}}
        @if ($hasLogo)
            <div class="flex justify-center">
                <a href="{{ filament()->getUrl() }}" class="inline-block">
                    <img src="{{ url('images/light-logo.png') }}" alt="GLM" class="h-10 dark:hidden" />
                    <img src="{{ url('images/dark-logo.png') }}" alt="GLM" class="h-10 hidden dark:block" />
                </a>
            </div>
        @endif

        {{-- Title + subtitle --}}
        @if (filled($heading) || filled($subheading))
            <header class="text-center space-y-2">
                <span class="inline-block text-xs font-semibold uppercase tracking-widest text-[#2563EB]" style="font-family: 'Inter', sans-serif;">Inscription</span>
                @if (filled($heading))
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight" style="font-family: 'Montserrat', sans-serif;">
                        {{ $heading }}
                    </h1>
                @endif
                @if (filled($subheading))
                    <p class="text-gray-600 dark:text-gray-400 text-sm max-w-md mx-auto">
                        {!! $subheading !!}
                    </p>
                @endif
            </header>
        @endif

        {{-- Full-width form --}}
        <div class="fi-simple-page-form glm-register-form-fullwidth">
            {{ $this->content }}
        </div>
    </div>

    <style>
        .glm-register-card .glm-register-form-fullwidth,
        .glm-register-card .glm-register-form-fullwidth > div,
        .glm-register-card .glm-register-form-fullwidth [class*="fi-fo-field"],
        .glm-register-card .glm-register-form-fullwidth [class*="fi-grid"],
        .glm-register-card .glm-register-form-fullwidth [class*="fi-input-wrp"] {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .glm-register-card .glm-register-form-fullwidth [class*="fi-input-wrp"] {
            display: flex !important;
            min-width: 0 !important;
        }
        .glm-register-card .glm-register-form-fullwidth input[type="text"],
        .glm-register-card .glm-register-form-fullwidth input[type="email"],
        .glm-register-card .glm-register-form-fullwidth input[type="password"],
        .glm-register-card .glm-register-form-fullwidth input[type="number"],
        .glm-register-card .glm-register-form-fullwidth [class*="fi-input-wrp"] input {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        /* Ensure password input is focusable and receives clicks (fix typing bug) */
        .glm-register-card input[type="password"] {
            pointer-events: auto !important;
            position: relative !important;
            z-index: 1 !important;
        }
        .glm-register-card .fi-input-wrp:has(input[type="password"]) .fi-input-wrp-content-ctn,
        .glm-register-card .fi-input-wrp:has(input[type="password"]) [class*="fi-input-wrp-content"] {
            pointer-events: none !important;
        }
        .glm-register-card .fi-input-wrp:has(input[type="password"]) .fi-input-wrp-content-ctn input,
        .glm-register-card .fi-input-wrp:has(input[type="password"]) input {
            pointer-events: auto !important;
        }
    </style>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_END, scopes: $this->getRenderHookScopes()) }}
</div>
