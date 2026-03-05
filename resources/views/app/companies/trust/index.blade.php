@extends('app.layouts.app')

@section('pageSubtitle')
Confiance & Vérification
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Confiance & Vérification</h1>
            <p class="mt-1 text-sm text-slate-400">Données partagées positives uniquement. Signalisations privées à votre entreprise.</p>
        </div>
        <a href="{{ route('app.companies.trust.flags', $company) }}" class="glm-btn-secondary no-underline">Mes signalisations</a>
    </header>

    <div class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Vérifier un client</h2>
        <p class="text-sm text-slate-400 mb-4">Saisissez au moins le téléphone ou l'email pour afficher le statut de confiance.</p>
        <form method="get" action="{{ route('app.companies.trust.index', $company) }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="phone" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Téléphone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $phone) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white w-56">
            </div>
            <div>
                <label for="email" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white w-56">
            </div>
            <button type="submit" class="glm-btn-primary">Vérifier</button>
        </form>
    </div>

    @if ($identifier !== null)
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Statut de confiance (partagé, anonymisé)</h2>
            @if ($trustData)
                <div class="flex flex-wrap items-center gap-4">
                    @if ($trustData->verified_identity)
                        <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-300">Identité vérifiée</span>
                    @endif
                    @if ($trustData->successful_rentals_count > 0)
                        <span class="inline-flex items-center gap-2 rounded-xl border border-blue-500/30 bg-blue-500/10 px-4 py-2.5 text-sm font-semibold text-blue-300">{{ $trustData->successful_rentals_count }} location(s) réussie(s)</span>
                    @endif
                    @if (! $trustData->hasTrustBadge())
                        <span class="text-slate-400 text-sm">Aucune donnée de confiance partagée.</span>
                    @endif
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <form action="{{ route('app.companies.trust.record-success', $company) }}" method="post" class="inline">@csrf <input type="hidden" name="client_identifier" value="{{ $identifier }}"> <button type="submit" class="text-sm text-[#93C5FD] hover:text-white">Enregistrer une location réussie</button></form>
                    <form action="{{ route('app.companies.trust.set-verified', $company) }}" method="post" class="inline">@csrf <input type="hidden" name="client_identifier" value="{{ $identifier }}"> <input type="hidden" name="verified" value="{{ $trustData->verified_identity ? '0' : '1' }}"> <button type="submit" class="text-sm text-[#93C5FD] hover:text-white">{{ $trustData->verified_identity ? 'Retirer le badge vérifié' : 'Marquer identité vérifiée' }}</button></form>
                </div>
            @else
                <p class="text-slate-400 text-sm">Aucune donnée pour ce client.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <form action="{{ route('app.companies.trust.record-success', $company) }}" method="post" class="inline">@csrf <input type="hidden" name="client_identifier" value="{{ $identifier }}"> <button type="submit" class="glm-btn-primary text-sm py-2">Enregistrer une location réussie</button></form>
                    <form action="{{ route('app.companies.trust.set-verified', $company) }}" method="post" class="inline">@csrf <input type="hidden" name="client_identifier" value="{{ $identifier }}"> <input type="hidden" name="verified" value="1"> <button type="submit" class="glm-btn-secondary text-sm py-2">Marquer identité vérifiée</button></form>
                </div>
            @endif
        </div>

        @if ($companyFlag)
            <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-6">
                <h3 class="text-base font-semibold text-amber-300">Avertissement interne (votre entreprise uniquement)</h3>
                <p class="mt-2 text-sm text-slate-300">Ce client a été signalé par votre entreprise.</p>
                @if ($companyFlag->reason)
                    <p class="mt-2 text-sm text-slate-400">Raison : {{ $companyFlag->reason }}</p>
                @endif
                <form action="{{ route('app.companies.trust.unflag', $company) }}" method="post" class="mt-4 inline">@csrf <input type="hidden" name="client_identifier" value="{{ $identifier }}"> <button type="submit" class="text-sm text-slate-400 hover:text-white">Retirer la signalisation</button></form>
            </div>
        @else
            <div class="glm-card-static p-6">
                <h3 class="text-base font-semibold text-white">Signaler ce client (privé)</h3>
                <form action="{{ route('app.companies.trust.flag', $company) }}" method="post" class="mt-4 space-y-4 max-w-md">
                    @csrf
                    <input type="hidden" name="phone" value="{{ $phone }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    <div>
                        <label for="reason" class="mb-1 block text-xs font-medium text-slate-500">Raison (optionnel)</label>
                        <input type="text" id="reason" name="reason" maxlength="500" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label for="notes" class="mb-1 block text-xs font-medium text-slate-500">Notes (optionnel)</label>
                        <textarea id="notes" name="notes" rows="2" maxlength="2000" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white"></textarea>
                    </div>
                    <button type="submit" class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2.5 text-sm font-medium text-amber-300 hover:bg-amber-500/20">Signaler ce client</button>
                </form>
            </div>
        @endif
    @endif
</div>
@endsection
