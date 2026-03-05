@extends('app.layouts.app')

@section('pageSubtitle')
Configuration globale — Super Admin uniquement
@endsection

@section('content')
<div class="space-y-8 glm-fade-in" x-data="{ tab: 'general' }">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight glm-text">Paramètres plateforme</h1>
            <p class="mt-2 max-w-2xl text-base glm-muted">
                Nom de l’app, email support, essai par défaut, pages légales (CGU, confidentialité).
            </p>
        </div>
        <a href="{{ route('app.dashboard') }}" class="glm-btn-secondary inline-flex no-underline">Tableau de bord</a>
    </header>

    @if (session('success'))
        <div class="glm-fade-in rounded-2xl bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300" role="alert">{{ session('success') }}</div>
    @endif

    <div class="border-b border-[color:var(--glm-border)]">
        <nav class="flex gap-1">
            <button type="button" @click="tab = 'general'" :class="tab === 'general' ? 'border-[#2563EB] glm-text font-semibold' : 'border-transparent glm-tab-inactive'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Général</button>
            <button type="button" @click="tab = 'legal'" :class="tab === 'legal' ? 'border-[#2563EB] glm-text font-semibold' : 'border-transparent glm-tab-inactive'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Légal</button>
        </nav>
    </div>

    {{-- General --}}
    <div x-show="tab === 'general'" x-cloak class="glm-card-static p-6 max-w-2xl">
        <h2 class="text-lg font-semibold glm-text mb-4">Paramètres généraux</h2>
        <form action="{{ route('app.admin.settings.update') }}" method="post">
            @csrf
            <input type="hidden" name="tab" value="general">
            <div class="space-y-4">
                <div>
                    <label for="app_name" class="mb-1.5 block text-sm font-medium glm-text">Nom de l’application</label>
                    <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $general['app_name']) }}" class="glm-input w-full rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50" placeholder="GLM">
                </div>
                <div>
                    <label for="support_email" class="mb-1.5 block text-sm font-medium glm-text">Email support</label>
                    <input type="email" id="support_email" name="support_email" value="{{ old('support_email', $general['support_email']) }}" class="glm-input w-full rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50" placeholder="support@glm.ma">
                </div>
                <div>
                    <label for="default_trial_days" class="mb-1.5 block text-sm font-medium glm-text">Jours d’essai par défaut</label>
                    <input type="number" id="default_trial_days" name="default_trial_days" value="{{ old('default_trial_days', $general['default_trial_days']) }}" min="0" max="365" class="glm-input w-24 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50" placeholder="14">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="glm-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>

    {{-- Legal --}}
    <div x-show="tab === 'legal'" x-cloak class="glm-card-static p-6 max-w-2xl">
        <h2 class="text-lg font-semibold glm-text mb-4">Paramètres légaux</h2>
        <p class="text-sm glm-muted mb-4">URLs ou contenu des pages CGU et Politique de confidentialité.</p>
        <form action="{{ route('app.admin.settings.update') }}" method="post">
            @csrf
            <input type="hidden" name="tab" value="legal">
            <div class="space-y-4">
                <div>
                    <label for="legal_terms_url" class="mb-1.5 block text-sm font-medium glm-text">URL CGU (conditions générales)</label>
                    <input type="url" id="legal_terms_url" name="legal_terms_url" value="{{ old('legal_terms_url', $legal['legal_terms_url']) }}" class="glm-input w-full rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50" placeholder="https://...">
                </div>
                <div>
                    <label for="legal_privacy_url" class="mb-1.5 block text-sm font-medium glm-text">URL Politique de confidentialité</label>
                    <input type="url" id="legal_privacy_url" name="legal_privacy_url" value="{{ old('legal_privacy_url', $legal['legal_privacy_url']) }}" class="glm-input w-full rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50" placeholder="https://...">
                </div>
                <div>
                    <label for="legal_terms_content" class="mb-1.5 block text-sm font-medium glm-text">Contenu CGU (texte brut, optionnel)</label>
                    <textarea id="legal_terms_content" name="legal_terms_content" rows="6" class="glm-input w-full rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Si pas d’URL, vous pouvez saisir le contenu ici.">{{ old('legal_terms_content', $legal['legal_terms_content']) }}</textarea>
                </div>
                <div>
                    <label for="legal_privacy_content" class="mb-1.5 block text-sm font-medium glm-text">Contenu Confidentialité (optionnel)</label>
                    <textarea id="legal_privacy_content" name="legal_privacy_content" rows="6" class="glm-input w-full rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50">{{ old('legal_privacy_content', $legal['legal_privacy_content']) }}</textarea>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="glm-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection
