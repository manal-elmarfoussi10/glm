@php
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $hasLogo = $this->hasLogo();
@endphp

<div class="fi-simple-page glm-auth-card rounded-2xl p-8 sm:p-10 md:p-12">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_START, scopes: $this->getRenderHookScopes()) }}

    <div class="glm-auth-page-content space-y-10">
        @if ($hasLogo)
            <div class="flex flex-col items-center lg:hidden space-y-4 pb-2">
                <img src="{{ url('/images/light-logo.png') }}" alt="GLM" class="h-20 w-20 object-contain" />
           
            </div>
        @endif

        @if (filled($heading) || filled($subheading))
            <header class="text-left space-y-4">
                <span class="inline-block text-xs font-semibold uppercase tracking-widest text-blue-400" style="font-family: 'Inter', sans-serif;">Connexion</span>
                @if (filled($heading))
                    <h1 class="text-2xl font-bold text-white tracking-tight leading-tight mb-4" style="font-family: 'Montserrat', sans-serif;">
                        {{ $heading }}
                    </h1>
                @endif
                @if (filled($subheading))
                    <p class="text-base text-white/80 leading-relaxed glm-auth-subheading">
                        {!! $subheading !!}
                    </p>
                @endif
            </header>
        @endif

        <div class="glm-auth-form fi-simple-page-form space-y-6 mt-6">
            {{ $this->content }}
        </div>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_END, scopes: $this->getRenderHookScopes()) }}

    {{-- Fallback: ensure submit button always shows label (Filament/Alpine can fail to render) --}}
    <script>
        (function ensureLoginButtonLabel() {
            function inject() {
                const btn = document.querySelector('.glm-auth-card button[type="submit"], .fi-simple-page button[type="submit"]');
                if (btn && !btn.textContent.trim()) {
                    const span = document.createElement('span');
                    span.className = 'fi-btn-label';
                    span.textContent = 'Se connecter';
                    btn.insertBefore(span, btn.firstChild);
                }
            }
            document.addEventListener('DOMContentLoaded', inject);
            document.addEventListener('livewire:navigated', inject);
            setTimeout(inject, 500);
            setTimeout(inject, 1500);
        })();
    </script>
</div>
