@extends('app.layouts.app')

@section('pageSubtitle')
Partage disponibilité partenaires – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Partage disponibilité partenaires</h1>
            <p class="mt-1 text-sm text-slate-400">Choisissez les agences et catégories de véhicules visibles par les autres partenaires. Aucune donnée de réservation ni plaque n’est partagée.</p>
        </div>
    </header>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    <form action="{{ route('app.companies.partner-settings.update', $company) }}" method="post" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="share_enabled" value="0">
                <input type="checkbox" name="share_enabled" value="1" {{ old('share_enabled', $setting->share_enabled) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                <span class="font-semibold text-white">Activer le partage de disponibilité</span>
            </label>
            <p class="mt-2 text-sm text-slate-400">Les autres partenaires pourront voir la disponibilité (nombre de véhicules, catégorie) pour les agences et catégories sélectionnées ci‑dessous.</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <h2 class="text-base font-extrabold text-white/90 mb-4">Agences à partager</h2>
            <p class="text-sm text-slate-400 mb-4">Sélectionnez les agences dont la disponibilité sera visible (ville utilisée pour la recherche).</p>
            <div class="flex flex-wrap gap-4">
                @foreach ($branches as $b)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="shared_branch_ids[]" value="{{ $b->id }}" {{ in_array($b->id, old('shared_branch_ids', $setting->shared_branch_ids ?? [])) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                        <span class="text-white">{{ $b->name }} <span class="text-slate-500">({{ $b->city }})</span></span>
                    </label>
                @endforeach
            </div>
            @if ($branches->isEmpty())
                <p class="text-slate-500 text-sm">Aucune agence. Créez une agence pour la partager.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <h2 class="text-base font-extrabold text-white/90 mb-4">Catégories à partager</h2>
            <p class="text-sm text-slate-400 mb-4">Catégories type « Économique / Berline / SUV » (ex. Clio ou similaire). Les véhicules doivent avoir une catégorie renseignée dans la fiche véhicule.</p>
            <div class="flex flex-wrap gap-4">
                @foreach (\App\Models\Vehicle::PARTNER_CATEGORIES as $key => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="shared_categories[]" value="{{ $key }}" {{ in_array($key, old('shared_categories', $setting->shared_categories ?? [])) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                        <span class="text-white">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="show_price" value="0">
                <input type="checkbox" name="show_price" value="1" {{ old('show_price', $setting->show_price) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                <span class="font-semibold text-white">Afficher le prix / jour dans les résultats partenaires</span>
            </label>
            <p class="mt-2 text-sm text-slate-400">Les partenaires qui recherchent verront une fourchette de prix (min–max) pour la catégorie.</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="allow_contact_requests" value="0">
                <input type="checkbox" name="allow_contact_requests" value="1" {{ old('allow_contact_requests', $setting->allow_contact_requests ?? false) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB]">
                <span class="font-semibold text-white">Autoriser d'autres agences à me contacter</span>
            </label>
            <p class="mt-2 text-sm text-slate-400">Les partenaires pourront vous envoyer une demande de contact depuis la recherche.</p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Enregistrer</button>
            <a href="{{ route('app.companies.show', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
        </div>
    </form>
</div>
@endsection
