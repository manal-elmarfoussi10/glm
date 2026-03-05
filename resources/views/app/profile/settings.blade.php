@extends('app.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 glm-fade-in">
    <div>
        <h1 class="text-2xl font-bold glm-text">Paramètres</h1>
        <p class="glm-muted mt-1">Personnalisez votre expérience sur la plateforme.</p>
    </div>

    <div class="space-y-6">
        {{-- Appearance --}}
        <div class="p-8 rounded-3xl border bg-[color:var(--surface)] border-[color:var(--border)] shadow-[var(--shadow-soft)] space-y-6">
            <div class="flex items-center gap-4">
                <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-[color:var(--primary)]/10 text-[color:var(--primary)]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold glm-text">Apparence</h3>
                    <p class="text-sm glm-muted">Choisissez le thème qui vous convient le mieux.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <button 
                    onclick="document.documentElement.classList.remove('dark'); localStorage.setItem('glm_theme', 'light');"
                    class="flex items-center justify-between p-4 rounded-2xl border bg-[color:var(--surface)] border-[color:var(--border)] hover:border-[color:var(--primary)] transition group"
                >
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-[color:var(--surface-2)] flex items-center justify-center text-[color:var(--primary)]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <span class="font-medium glm-text">Mode Clair</span>
                    </div>
                </button>

                <button 
                    onclick="document.documentElement.classList.add('dark'); localStorage.setItem('glm_theme', 'dark');"
                    class="glm-dark-bg flex items-center justify-between p-4 rounded-2xl border border-slate-700 bg-slate-800 hover:border-[color:var(--primary)] transition group"
                >
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-slate-700 flex items-center justify-center text-slate-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        </div>
                        <span class="font-medium text-white">Mode Sombre</span>
                    </div>
                </button>
            </div>
        </div>

        {{-- Notifications Placeholder --}}
        <div class="p-8 rounded-3xl border bg-[color:var(--surface)] border-[color:var(--border)] shadow-[var(--shadow-soft)] opacity-60">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-[color:var(--surface-2)] glm-muted">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold glm-text">Notifications</h3>
                        <p class="text-sm glm-muted">Gérez vos préférences de notification email.</p>
                    </div>
                </div>
                <div class="px-3 py-1 rounded-full bg-[color:var(--surface-2)] text-[10px] font-bold glm-muted uppercase tracking-widest">Bientôt</div>
            </div>
        </div>

        {{-- Language Placeholder --}}
        <div class="p-8 rounded-3xl border bg-[color:var(--surface)] border-[color:var(--border)] shadow-[var(--shadow-soft)] opacity-60">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-[color:var(--surface-2)] glm-muted">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold glm-text">Langue</h3>
                        <p class="text-sm glm-muted">Choisissez votre langue préférée.</p>
                    </div>
                </div>
                <div class="px-3 py-1 rounded-full bg-[color:var(--surface-2)] text-[10px] font-bold glm-muted uppercase tracking-widest">Bientôt</div>
            </div>
        </div>
    </div>
</div>
@endsection
