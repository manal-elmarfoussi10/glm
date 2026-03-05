@php
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $hasLogo = $this->hasLogo();
@endphp

<div class="fi-simple-page glm-auth-card glm-register-card rounded-2xl border-0 p-8 sm:p-10 md:p-12">
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
                <span class="inline-block text-xs font-semibold uppercase tracking-widest text-[#2563EB]" style="font-family: 'Inter', sans-serif;">Inscription</span>
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

        <div class="fi-simple-page-form glm-register-form-fullwidth">
            {{ $this->content }}
        </div>
    </div>

    {{-- Force full-width inputs: inline so it overrides Filament panel CSS --}}
    <style>
        .glm-register-card .glm-register-form-fullwidth,
        .glm-register-card .glm-register-form-fullwidth > div,
        .glm-register-card .glm-register-form-fullwidth > div > div,
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
    </style>

    {{-- Force full width via JS; re-run after Livewire re-renders (e.g. after validation) so design stays --}}
    <script>
        (function () {
            function applyFullWidth() {
                var form = document.querySelector('.glm-register-form-fullwidth');
                if (!form) return;
                form.style.width = '100%';
                form.style.maxWidth = '100%';
                form.style.boxSizing = 'border-box';
                var inputs = form.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]), textarea');
                for (var j = 0; j < inputs.length; j++) {
                    var el = inputs[j];
                    el.style.width = '100%';
                    el.style.minWidth = '0';
                    el.style.maxWidth = '100%';
                    el.style.boxSizing = 'border-box';
                    var parent = el.parentElement;
                    while (parent && parent !== form) {
                        if (parent.tagName === 'DIV') {
                            parent.style.width = '100%';
                            parent.style.maxWidth = '100%';
                            parent.style.boxSizing = 'border-box';
                        }
                        parent = parent.parentElement;
                    }
                }
            }
            function scheduleApply() {
                applyFullWidth();
                setTimeout(applyFullWidth, 50);
                setTimeout(applyFullWidth, 200);
                setTimeout(applyFullWidth, 500);
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', scheduleApply);
            } else {
                scheduleApply();
            }
            document.addEventListener('livewire:load', scheduleApply);
            document.addEventListener('livewire:navigated', scheduleApply);
            document.addEventListener('livewire:morph.updated', scheduleApply);
            document.addEventListener('livewire:update', scheduleApply);
            var card = document.querySelector('.glm-register-card');
            if (card && typeof MutationObserver !== 'undefined') {
                var obs = new MutationObserver(function () { scheduleApply(); });
                obs.observe(card, { childList: true, subtree: true });
            }
            card && card.addEventListener('click', function (e) {
                if (e.target.closest('button[type="submit"]')) scheduleApply();
            });
        })();
    </script>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_END, scopes: $this->getRenderHookScopes()) }}
</div>
