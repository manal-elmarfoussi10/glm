@extends('app.layouts.app')

@section('pageSubtitle')
Gérer les entreprises et leurs abonnements
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    {{-- Header --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Entreprises</h1>
            <p class="mt-2 max-w-2xl text-base text-slate-400">
                Liste des entreprises clientes. Consultez les profils, utilisateurs, agences et abonnements.
            </p>
        </div>
        <div class="flex shrink-0 gap-3">
            <a href="{{ route('app.dashboard') }}" class="glm-btn-secondary inline-flex no-underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                Tableau de bord
            </a>
            <a href="{{ route('app.companies.create') }}" class="glm-btn-primary inline-flex no-underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Ajouter une entreprise
            </a>
        </div>
    </header>

    {{-- Stats --}}
    <section class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <a href="{{ route('app.companies.index', array_merge(request()->query(), ['status' => null])) }}" class="glm-stat-card glm-stat-card-accent-blue block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">Total</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs font-medium text-blue-400">Entreprises</p>
        </a>
        <a href="{{ route('app.companies.index', array_merge(request()->query(), ['status' => 'active'])) }}" class="glm-stat-card glm-stat-card-accent-emerald block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">Actives</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['active'] }}</p>
            <p class="mt-1 text-xs font-medium text-emerald-400">En activité</p>
        </a>
        <a href="{{ route('app.companies.index', array_merge(request()->query(), ['status' => 'suspended'])) }}" class="glm-stat-card glm-stat-card-accent-amber block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">Suspendues</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['suspended'] }}</p>
            <p class="mt-1 text-xs font-medium text-amber-400">À traiter</p>
        </a>
    </section>

    {{-- Filters --}}
    <form method="get" action="{{ route('app.companies.index') }}" class="glm-card-static flex flex-wrap items-end gap-5 p-5">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="filter-search" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Recherche</label>
                <input type="text" id="filter-search" name="search" value="{{ request('search') }}" placeholder="Nom, ICE, email, ville…" class="w-56 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50">
            </div>
            <div>
                <label for="filter-status" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Statut</label>
                <select id="filter-status" name="status" class="w-40 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                </select>
            </div>
            <div>
                <label for="filter-plan" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Plan</label>
                <select id="filter-plan" name="plan" class="w-40 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="">Tous</option>
                    <option value="starter" {{ request('plan') === 'starter' ? 'selected' : '' }}>Starter</option>
                    <option value="professional" {{ request('plan') === 'professional' ? 'selected' : '' }}>Professional</option>
                    <option value="enterprise" {{ request('plan') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
            </div>
            <div>
                <label for="filter-city" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Ville</label>
                <select id="filter-city" name="city" class="w-40 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="">Toutes</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Appliquer</button>
            @if (request()->hasAny(['search', 'status', 'plan', 'city']))
                <a href="{{ route('app.companies.index') }}" class="glm-btn-secondary no-underline">Effacer</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="glm-table-wrap overflow-hidden rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Entreprise</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">ICE</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Ville</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Statut</th>
                        <th class="w-0 px-6 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($companies as $company)
                        <tr class="transition-colors hover:bg-white/[0.03]">
                            <td class="px-6 py-4 font-medium text-white">{{ $company->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $company->ice ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $company->email ?? $company->phone ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $company->city ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $company->plan ?? '–' }}</td>
                            <td class="px-6 py-4">
                                @if ($company->status === 'active')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium glm-badge-approved">Actif</span>
                                @else
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium glm-badge-pending">Suspendu</span>
                                @endif
                            </td>
                            <td class="relative px-6 py-4 flex items-center gap-2">
                                <a href="{{ route('app.companies.show', $company) }}" class="glm-btn-secondary inline-flex no-underline text-sm py-2">Voir</a>
                                <a href="{{ route('app.companies.edit', $company) }}" class="text-sm text-slate-400 hover:text-white transition-colors">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/5 text-slate-500">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold text-white">Aucune entreprise</h3>
                                    <p class="mt-2 max-w-sm text-sm text-slate-400">Aucun résultat pour ces filtres ou la liste est vide.</p>
                                    <a href="{{ route('app.companies.create') }}" class="mt-6 glm-btn-primary no-underline">Ajouter une entreprise</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($companies->hasPages())
            <div class="border-t border-white/5 px-6 py-4">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
