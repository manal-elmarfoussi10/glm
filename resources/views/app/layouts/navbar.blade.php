{{-- Topbar: modern SaaS navbar (matches sidebar) --}}
@php
    try {
        $alertService = app(\App\Services\AlertService::class);
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isSupport()) {
            $alertsCount = $alertService->countUnread();
            $alertsUrl = route('app.companies.activity.index', $user->company);
        } else {
            $alertsCount = $alertService->countUnread($user->company);
            $alertsUrl = route('app.alerts.redirect');
        }
    } catch (\Exception $e) {
        $alertsCount = 0;
        $alertsUrl = '#';
    }

    $profileUrl  = route('app.profile.show');
    $settingsUrl = route('app.profile.settings');
@endphp

<header class="sticky top-0 z-30 px-4 sm:px-6 pt-4" x-data="{ 
    search: '', 
    results: {}, 
    loading: false, 
    showResults: false, 
    focusedIndex: -1,
    get totalResults() {
        return Object.values(this.results).reduce((acc, curr) => acc + curr.length, 0);
    },
    async fetchResults() {
        if (this.search.length < 2) {
            this.results = {};
            this.showResults = false;
            return;
        }
        this.loading = true;
        try {
            const res = await fetch('/app/search/ajax?q=' + encodeURIComponent(this.search));
            this.results = await res.json();
            this.showResults = true;
            this.focusedIndex = -1;
        } catch (e) {
            console.error(e);
        } finally {
            this.loading = false;
        }
    }
}">
    <div class="glm-app-topbar glm-topbar-bg flex items-center gap-3 sm:gap-4 rounded-2xl px-4 sm:px-6 py-3 shadow-lg">
        {{-- Mobile sidebar button --}}
        <button
            type="button"
            @click="$dispatch('toggle-sidebar')"
            class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 border border-white/20 text-white hover:bg-white/25 transition"
            aria-label="Menu"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Left: Page title slot --}}
        <div class="min-w-0 flex-1"></div>

        {{-- Search (desktop) --}}
        <div class="hidden md:block w-full max-w-lg relative">
            <label for="search" class="sr-only">Rechercher</label>
            <form action="{{ route('app.search') }}" method="GET">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-white/70">
                        <svg class="h-4 w-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!loading" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            <circle x-show="loading" class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path x-show="loading" class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <input
                        id="search"
                        name="q"
                        type="search"
                        x-model="search"
                        @input.debounce.300ms="fetchResults"
                        @keydown.escape="showResults = false"
                        @click.outside="showResults = false"
                        autocomplete="off"
                        placeholder="Rechercher..."
                        class="block w-full rounded-xl border bg-white/15 border-white/25 py-2.5 pl-10 pr-4 text-sm text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/40 transition"
                    />
                </div>
            </form>

            {{-- Search Results Dropdown --}}
            <div 
                x-show="showResults" 
                x-transition:enter="transition ease-out duration-120"
                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-90"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                class="absolute left-0 right-0 z-50 mt-2 max-h-[400px] overflow-y-auto rounded-2xl border bg-slate-900 border-white/10 shadow-2xl p-2 backdrop-blur-xl"
                style="display: none;"
            >
                <template x-if="totalResults === 0">
                    <div class="p-4 text-center text-sm text-white/50">Aucun résultat pour "<span x-text="search" class="text-white"></span>"</div>
                </template>
                
                <template x-for="(group, type) in results" :key="type">
                    <div class="mb-2 last:mb-0">
                        <div class="px-3 py-1.5 text-[10px] font-bold text-white/40 uppercase tracking-widest" x-text="type === 'company' ? 'Entreprises' : (type === 'reservation' ? 'Réservations' : (type === 'customer' ? 'Clients' : 'Véhicules'))"></div>
                        <template x-for="item in group" :key="item.id">
                            <a :href="item.url" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-white hover:bg-white/10 transition group">
                                <span class="h-8 w-8 flex items-center justify-center rounded-lg bg-white/10 text-white transition">
                                    <template x-if="item.icon === 'office-building'">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2-2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    </template>
                                    <template x-if="item.icon === 'calendar'">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </template>
                                    <template x-if="item.icon === 'user-group'">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </template>
                                    <template x-if="item.icon === 'truck'">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    </template>
                                </span>
                                <div class="flex flex-col overflow-hidden">
                                    <span class="font-medium truncate" x-text="item.title"></span>
                                    <span class="text-xs text-white/50 truncate" x-text="item.subtitle"></span>
                                </div>
                            </a>
                        </template>
                    </div>
                </template>
                <div class="border-t border-white/10 pt-2 mt-1 px-2 pb-2">
                    <a :href="'/app/search?q=' + encodeURIComponent(search)" class="block text-center text-xs font-semibold text-blue-400 hover:text-blue-300 transition py-2.5 px-3 hover:bg-white/5 rounded-xl">
                        Voir tous les résultats
                    </a>
                </div>
            </div>
        </div>

        {{-- Theme toggle: contrast in both themes; ring shows active theme --}}
        <button
            type="button"
            aria-label="Basculer le thème"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 border border-white/20 text-white/80 hover:bg-white/25 hover:text-white transition ring-2 ring-amber-300/40 dark:ring-sky-300/50"
            onclick="(function(){ var html = document.documentElement; var isDark = html.classList.contains('dark'); html.classList.toggle('dark', !isDark); localStorage.setItem('glm_theme', isDark ? 'light' : 'dark'); })()"
        >
            <span class="dark:hidden" aria-hidden="true" title="Thème clair actif">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </span>
            <span class="hidden dark:inline" aria-hidden="true" title="Thème sombre actif">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </span>
        </button>

        {{-- Alerts icon --}}
        <a
            href="{{ $alertsUrl }}"
            class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 border border-white/20 text-white/80 hover:bg-white/25 hover:text-white transition"
            aria-label="Alertes"
            title="Alertes"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @if($alertsCount > 0)
                <span class="absolute -top-1 -right-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-white px-1 text-[11px] font-bold text-blue-600 shadow-sm">
                    {{ $alertsCount }}
                </span>
            @endif
        </a>

        {{-- Profile dropdown --}}
        <div class="relative shrink-0" x-data="{ open: false }">
            <button
                type="button"
                @click="open = !open"
                @click.outside="open = false"
                class="flex items-center gap-2 rounded-xl bg-white/15 border border-white/20 px-2.5 py-2 hover:bg-white/25 transition text-white"
                aria-haspopup="true"
                :aria-expanded="open"
            >
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-white font-bold">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                </span>
                <div class="hidden lg:flex flex-col items-start leading-tight">
                    <span class="text-sm font-semibold">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <span class="text-[10px] uppercase tracking-wider text-white/70">{{ str_replace('_',' ', auth()->user()->role ?? '') }}</span>
                </div>
                <svg class="h-4 w-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-120"
                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-90"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                class="absolute right-0 z-50 mt-2 w-56 origin-top-right overflow-hidden rounded-2xl border bg-slate-900 border-white/10 shadow-2xl backdrop-blur-xl"
                style="display:none;"
                role="menu"
            >
                <div class="px-3 py-3 border-b border-white/10 bg-white/5">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-[10px] text-white/50 truncate uppercase tracking-tight">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <div class="p-2">
                    <a href="{{ $profileUrl }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm text-white/80 hover:text-white hover:bg-white/10 transition" role="menuitem">
                        <svg class="h-4 w-4 text-white/40 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7v1h14v-1a7 7 0 00-7-7z"/></svg>
                        Mon profil
                    </a>
                    <a href="{{ $settingsUrl }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm text-white/80 hover:text-white hover:bg-white/10 transition" role="menuitem">
                        <svg class="h-4 w-4 text-white/40 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Paramètres
                    </a>
                    <div class="my-2 border-t border-white/10"></div>
                    <form method="POST" action="{{ url('/admin/logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition" role="menuitem">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>