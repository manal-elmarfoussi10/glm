@php
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $hasLogo = $this->hasLogo();
@endphp

<div class="glm-register-wrap min-h-screen px-4 py-10 sm:py-14">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_START, scopes: $this->getRenderHookScopes()) }}

    <div class="mx-auto w-full max-w-6xl">
        {{-- Card --}}
        <div class="glm-register-card rounded-3xl border border-white/10 bg-slate-950/60 shadow-2xl backdrop-blur-xl px-6 py-8 sm:px-10 sm:py-10">
            {{-- Logo --}}
            @if ($hasLogo)
                <div class="flex justify-center mb-6">
                    <a href="{{ filament()->getUrl() }}" class="inline-flex items-center justify-center">
                        <img src="{{ app_asset('images/light-logo.png') }}" alt="GLM" class="h-12 w-auto object-contain" />
                    </a>
                </div>
            @endif

            {{-- Title / subtitle --}}
            @if (filled($heading) || filled($subheading))
                <header class="text-center mb-8">
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-white">
                        {{ $heading }}
                    </h1>
                    @if (filled($subheading))
                        <p class="mt-2 text-sm sm:text-base text-white/70">
                            {!! $subheading !!}
                        </p>
                    @endif
                </header>
            @endif

            {{-- Form (Filament renders fields here) --}}
            <div class="glm-register-form">
                {{ $this->content }}
            </div>
        </div>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_END, scopes: $this->getRenderHookScopes()) }}

    <style>
        /* Page background (keep your auth vibe) */
        .glm-register-wrap {
            background: radial-gradient(900px 500px at 20% 20%, rgba(59,130,246,.18), transparent 60%),
                        radial-gradient(900px 500px at 80% 70%, rgba(37,99,235,.14), transparent 60%),
                        linear-gradient(180deg, #050914 0%, #070B16 100%);
        }

        /* Make Filament form a 2-col grid */
        .glm-register-form form {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 18px !important;
        }
        @media (min-width: 768px) {
            .glm-register-form form {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 18px 22px !important;
            }
        }

        /* Ensure each field wrapper behaves */
        .glm-register-form form > * {
            min-width: 0 !important;
        }

        /* Full-width rows helper:
           Add class "glm-span-full" to the field wrapper via Filament ->extraAttributes(['class' => 'glm-span-full'])
        */
        .glm-register-form .glm-span-full {
            grid-column: 1 / -1 !important;
        }

        /* Also force common full-width cases (textarea + actions) */
        .glm-register-form textarea,
        .glm-register-form .fi-ac,
        .glm-register-form .fi-sc-actions {
            grid-column: 1 / -1 !important;
        }

        /* Inputs sizing */
        .glm-register-card input,
        .glm-register-card select,
        .glm-register-card textarea {
            width: 100% !important;
            min-width: 0 !important;
        }

        /* Fix password typing issue:
           Sometimes Filament eye button overlays the input and blocks click/typing.
        */
        .glm-register-card .fi-input-wrp {
            position: relative !important;
        }
        .glm-register-card .fi-input-wrp-content-ctn,
        .glm-register-card .fi-input-wrp-content-ctn input {
            pointer-events: auto !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .glm-register-card .fi-input-wrp-suffix,
        .glm-register-card .fi-input-wrp-prefix {
            z-index: 3 !important;
        }

        /* Button / actions full width */
        .glm-register-form .fi-ac {
            margin-top: 10px !important;
        }
        .glm-register-form .fi-ac button,
        .glm-register-form .fi-ac a {
            width: 100% !important;
            justify-content: center !important;
        }
    </style>
</div>