@php
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $hasLogo = $this->hasLogo();
@endphp

<div class="fi-simple-page glm-auth-card rounded-2xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200/50 dark:border-gray-700 p-8 sm:p-10">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_START, scopes: $this->getRenderHookScopes()) }}

    <div class="glm-auth-page-content space-y-6">
        @if ($hasLogo)
            <div class="flex justify-center lg:hidden mb-6">
                <img src="{{ asset('images/light-logo.png') }}" alt="GLM" class="h-9 dark:hidden" />
                <img src="{{ asset('images/dark-logo.png') }}" alt="GLM" class="h-9 hidden dark:block" />
            </div>
        @endif

        @if (filled($heading) || filled($subheading))
            <header class="text-center space-y-2">
                @if (filled($heading))
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight" style="font-family: 'Montserrat', sans-serif;">
                        {{ $heading }}
                    </h1>
                @endif
                @if (filled($subheading))
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
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
