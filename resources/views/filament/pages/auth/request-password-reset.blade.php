@php
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $hasLogo = $this->hasLogo();
@endphp

<div class="fi-simple-page glm-auth-card rounded-2xl border-0 p-8 sm:p-10 md:p-12">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_START, scopes: $this->getRenderHookScopes()) }}

    <div class="glm-auth-page-content space-y-8">
        @if ($hasLogo)
            <div class="flex justify-center lg:hidden">
                <img src="{{ url('images/light-logo.png') }}" alt="GLM" class="h-9 dark:hidden" />
                <img src="{{ url('images/dark-logo.png') }}" alt="GLM" class="h-9 hidden dark:block" />
            </div>
        @endif

        @if (filled($heading) || filled($subheading))
            <header class="text-left space-y-3">
                <span class="inline-block text-xs font-semibold uppercase tracking-widest text-[#2563EB]" style="font-family: 'Inter', sans-serif;">Réinitialisation</span>
                @if (filled($heading))
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight leading-tight" style="font-family: 'Montserrat', sans-serif;">
                        {{ $heading }}
                    </h1>
                @endif
                @if (filled($subheading))
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                        {!! $subheading !!}
                    </p>
                @endif
            </header>
        @endif

        <div class="fi-simple-page-form">
            {{ $this->content }}
        </div>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_END, scopes: $this->getRenderHookScopes()) }}
</div>
