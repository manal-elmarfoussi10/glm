@extends('app.layouts.app')

@section('pageSubtitle')
{{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in" x-data="{ tab: 'profil' }">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.companies.index') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block transition-colors">← Retour aux entreprises</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">{{ $company->name }}</h1>
            <p class="mt-1 text-sm text-slate-400">{{ $company->ice ?? 'Sans ICE' }} · {{ $company->city ?? 'Ville non renseignée' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('app.companies.reservations.index', $company) }}" class="glm-btn-secondary inline-flex no-underline">Réservations</a>
            <a href="{{ route('app.companies.customers.index', $company) }}" class="glm-btn-secondary inline-flex no-underline">Clients</a>
            <a href="{{ route('app.companies.vehicles.index', $company) }}" class="glm-btn-secondary inline-flex no-underline">Flotte</a>
            <a href="{{ route('app.companies.edit', $company) }}" class="glm-btn-secondary inline-flex no-underline">Modifier</a>
            <a href="{{ route('app.companies.users.index', $company) }}" class="glm-btn-primary inline-flex no-underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Ajouter un utilisateur
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-white/10">
        <nav class="flex gap-1" aria-label="Onglets">
            <button type="button" @click="tab = 'profil'" :class="tab === 'profil' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Profil</button>
            <button type="button" @click="tab = 'users'" :class="tab === 'users' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Utilisateurs ({{ $company->users_count }})</button>
            <button type="button" @click="tab = 'branches'" :class="tab === 'branches' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Branches / Agences ({{ $company->branches_count }})</button>
            <button type="button" @click="tab = 'contracts'" :class="tab === 'contracts' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Contrats</button>
            <button type="button" @click="tab = 'subscription'" :class="tab === 'subscription' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Abonnement</button>
            <button type="button" @click="tab = 'activity'" :class="tab === 'activity' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Activité</button>
            <button type="button" @click="tab = 'settings'" :class="tab === 'settings' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition-colors">Paramètres</button>
        </nav>
    </div>

    {{-- Tab: Profil --}}
    <div x-show="tab === 'profil'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Informations générales</h2>
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Nom</dt><dd class="mt-0.5 text-slate-200">{{ $company->name }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">ICE</dt><dd class="mt-0.5 text-slate-200">{{ $company->ice ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Téléphone</dt><dd class="mt-0.5 text-slate-200">{{ $company->phone ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Email</dt><dd class="mt-0.5 text-slate-200">{{ $company->email ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Ville</dt><dd class="mt-0.5 text-slate-200">{{ $company->city ?? '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Statut</dt><dd class="mt-0.5"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $company->status === 'active' ? 'glm-badge-approved' : 'glm-badge-pending' }}">{{ $company->status === 'active' ? 'Actif' : 'Suspendu' }}</span></dd></div>
            <div class="sm:col-span-2"><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Adresse</dt><dd class="mt-0.5 text-slate-200">{{ $company->address ?? '–' }}</dd></div>
        </dl>
    </div>

    {{-- Tab: Utilisateurs --}}
    <div x-show="tab === 'users'" x-cloak class="glm-card-static p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">Utilisateurs</h2>
            <a href="{{ route('app.companies.users.index', $company) }}" class="glm-btn-primary text-sm py-2 no-underline">Gérer les utilisateurs</a>
        </div>
        @php $companyUsers = $company->users()->orderBy('name')->limit(10)->get(); @endphp
        @if ($companyUsers->isEmpty())
            <p class="text-slate-400 text-sm">Aucun utilisateur. <a href="{{ route('app.companies.users.create', $company) }}" class="text-[#60a5fa] hover:underline">Ajouter un utilisateur</a>.</p>
        @else
            <div class="overflow-hidden rounded-xl border border-white/5">
                <table class="min-w-full border-collapse">
                    <thead class="bg-white/5"><tr><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Nom</th><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Email</th><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Rôle</th><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Statut</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($companyUsers as $u)
                            <tr><td class="px-4 py-3 text-sm text-white">{{ $u->name }}</td><td class="px-4 py-3 text-sm text-slate-400">{{ $u->email }}</td><td class="px-4 py-3 text-sm text-slate-400">{{ $u->role }}</td><td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $u->status === 'active' ? 'glm-badge-approved' : 'glm-badge-pending' }}">{{ $u->status }}</span></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($company->users_count > 10)
                <p class="mt-3 text-sm text-slate-400">{{ $company->users_count - 10 }} autre(s). <a href="{{ route('app.companies.users.index', $company) }}" class="text-[#60a5fa] hover:underline">Voir tout</a>.</p>
            @endif
        @endif
    </div>

    {{-- Tab: Branches / Agences --}}
    <div x-show="tab === 'branches'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Branches / Agences</h2>
        @php $branches = $company->branches; @endphp
        @if ($branches->isEmpty())
            <p class="text-slate-400 text-sm">Aucune agence enregistrée. Cette section pourra être complétée plus tard (CRUD agences).</p>
        @else
            <div class="overflow-hidden rounded-xl border border-white/5">
                <table class="min-w-full border-collapse">
                    <thead class="bg-white/5"><tr><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Nom</th><th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-400">Statut</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($branches as $b)
                            <tr><td class="px-4 py-3 text-sm text-white">{{ $b->name }}</td><td class="px-4 py-3"><span class="inline-flex rounded-full px-2 py-0.5 text-xs glm-badge-approved">{{ $b->status }}</span></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Tab: Contrats --}}
    <div x-show="tab === 'contracts'" x-cloak class="glm-card-static p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">Modèles de contrats</h2>
            <a href="{{ route('app.companies.contract-templates.index', $company) }}" class="glm-btn-primary text-sm py-2 no-underline">Gérer les modèles</a>
        </div>
        <p class="text-slate-400 text-sm">Modèles de contrats propres à cette entreprise et modèle par défaut pour les réservations. Les mises à jour des modèles globaux GLM n’écrasent pas les copies de l’entreprise.</p>
        @if ($company->defaultContractTemplate)
            <p class="mt-3 text-sm text-white">Modèle par défaut : <strong>{{ $company->defaultContractTemplate->name }}</strong> (v{{ $company->defaultContractTemplate->version }})</p>
        @else
            <p class="mt-3 text-sm text-slate-500">Aucun modèle par défaut défini.</p>
        @endif
    </div>

    {{-- Tab: Abonnement --}}
    <div x-show="tab === 'subscription'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Abonnement</h2>
        @php
            $currentPlanName = $company->planRelation?->name ?? $company->plan ?? '–';
            $statusLabels = [
                'trial' => 'Essai',
                'active' => 'Actif',
                'past_due' => 'Paiement en retard',
                'canceled' => 'Résilié',
                'suspended' => 'Suspendu',
                'expired' => 'Expiré',
            ];
            $subStatus = $company->subscription_status ?? 'trial';
            $statusLabel = $statusLabels[$subStatus] ?? $subStatus;
        @endphp
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Plan actuel</dt><dd class="mt-0.5 text-slate-200">{{ $currentPlanName }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Statut</dt><dd class="mt-0.5"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $subStatus === 'active' ? 'glm-badge-approved' : ($subStatus === 'trial' ? 'bg-blue-500/20 text-blue-300' : 'glm-badge-pending') }}">{{ $statusLabel }}</span></dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Début abonnement</dt><dd class="mt-0.5 text-slate-200">{{ $company->subscription_started_at ? $company->subscription_started_at->format('d/m/Y') : '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Prochaine facturation</dt><dd class="mt-0.5 text-slate-200">{{ $company->next_billing_date ? $company->next_billing_date->format('d/m/Y') : '–' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Fin d’essai</dt><dd class="mt-0.5 text-slate-200">{{ $company->trial_ends_at ? $company->trial_ends_at->format('d/m/Y') : '–' }}</dd></div>
        </dl>
        <div class="mt-6 flex flex-wrap gap-3" id="subscription">
            @if (in_array(auth()->user()?->role ?? null, ['super_admin', 'support'], true))
                {{-- Prolonger l'essai (plateforme uniquement) --}}
                <form action="{{ route('app.companies.subscription.extend-trial', $company) }}" method="post" class="inline-flex items-center gap-2">
                    @csrf
                    <label for="trial_ends_at" class="text-sm text-slate-400">Prolonger l’essai jusqu’au</label>
                    <input type="date" id="trial_ends_at" name="trial_ends_at" value="{{ $company->trial_ends_at?->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white">
                    <button type="submit" class="glm-btn-primary text-sm py-2">Appliquer</button>
                </form>
                @if ($subStatus !== 'active')
                    <form action="{{ route('app.companies.subscription.activate', $company) }}" method="post" class="inline">@csrf <button type="submit" class="glm-btn-primary text-sm py-2">Activer l’abonnement</button></form>
                @endif
                @if ($subStatus !== 'suspended')
                    <form action="{{ route('app.companies.subscription.suspend', $company) }}" method="post" class="inline">@csrf <button type="submit" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2 text-sm font-medium text-red-300 hover:bg-red-500/20">Suspendre</button></form>
                @endif
                <a href="{{ route('app.companies.subscription.change-plan', $company) }}" class="glm-btn-primary text-sm py-2 no-underline">Changer de plan</a>
            @endif
        </div>
        @if ($companyAdmin && in_array(auth()->user()?->role ?? null, ['super_admin', 'support'], true) && $companyAdmin->role !== 'super_admin')
        <p class="mt-4 text-sm text-slate-400">
            Admin entreprise : <strong>{{ $companyAdmin->name }}</strong> ({{ $companyAdmin->email }}).
            <form action="{{ route('app.admin.users.force-password-reset', $companyAdmin) }}" method="post" class="inline mt-2">@csrf <button type="submit" class="text-amber-400 hover:text-white text-sm">Réinitialiser le mot de passe</button></form>
        </p>
        @endif
        <p class="mt-4 text-sm text-slate-400">
            <a href="{{ route('app.companies.upgrade', $company) }}" class="text-[#93C5FD] hover:text-white font-medium">Passer à un plan supérieur</a> pour débloquer des modules ou augmenter les limites.
        </p>
    </div>

    {{-- Tab: Activité --}}
    <div x-show="tab === 'activity'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Journal d’activité</h2>
        @if (isset($activityLogs) && $activityLogs->isNotEmpty())
            <ul class="space-y-3 divide-y divide-white/10">
                @foreach ($activityLogs as $log)
                    <li class="pt-3 first:pt-0">
                        <span class="font-medium text-white">{{ $log->action }}</span>
                        <span class="text-slate-500 text-sm"> · {{ $log->user?->name ?? 'Système' }} · {{ $log->created_at->format('d/m/Y H:i') }}</span>
                        @if ($log->new_values)
                            <pre class="mt-1 text-xs text-slate-400 overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-slate-400 text-sm">Aucune activité enregistrée pour cette entreprise.</p>
        @endif
    </div>

    {{-- Tab: Paramètres --}}
    <div x-show="tab === 'settings'" x-cloak class="glm-card-static p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Paramètres</h2>
        <p class="text-slate-400 text-sm">Options de l’entreprise (suspension, suppression, export…) à implémenter selon les besoins.</p>
    </div>
</div>

<style>[x-cloak]{display:none!important}</style>
@endsection
