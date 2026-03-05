@extends('app.layouts.app')

@section('pageSubtitle')
Clients – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Clients</h1>
            <p class="mt-1 text-sm text-slate-400">Recherche par CIN, nom ou téléphone. Filtres par ville et signalement.</p>
        </div>
        <a href="{{ route('app.companies.customers.create', $company) }}" class="glm-btn-primary inline-flex no-underline">Nouveau client</a>
    </header>

    <form method="get" action="{{ route('app.companies.customers.index', $company) }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div class="flex-1 min-w-[200px]">
            <label for="search" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Recherche (CIN, nom, tél.)</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="CIN, nom, téléphone…" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="city" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Ville</label>
            <select id="city" name="city" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Toutes</option>
                @foreach ($cities as $c)
                    <option value="{{ $c }}" {{ request('city') === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="flagged" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Signalé</label>
            <select id="flagged" name="flagged" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Tous</option>
                <option value="1" {{ request('flagged') === '1' ? 'selected' : '' }}>Signalés</option>
                <option value="0" {{ request('flagged') === '0' ? 'selected' : '' }}>Non signalés</option>
            </select>
        </div>
        <button type="submit" class="glm-btn-primary">Filtrer</button>
        @if (request()->hasAny(['search', 'city', 'flagged']))
            <a href="{{ route('app.companies.customers.index', $company) }}" class="glm-btn-secondary no-underline">Réinitialiser</a>
        @endif
    </form>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">CIN</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Téléphone</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Ville</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Signalé</th>
                        <th class="w-0 px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($customers as $c)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.customers.show', [$company, $c]) }}" class="font-semibold text-white hover:text-[#93C5FD] no-underline">{{ $c->name }}</a>
                                @if ($c->email)
                                    <p class="text-sm text-slate-500">{{ $c->email }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-slate-300">{{ $c->cin }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $c->phone ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $c->city ?? '–' }}</td>
                            <td class="px-6 py-4">
                                @if ($c->is_flagged)
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400">Signalé</span>
                                @else
                                    <span class="text-slate-500">–</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.customers.show', [$company, $c]) }}" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                Aucun client. <a href="{{ route('app.companies.customers.create', $company) }}" class="text-[#93C5FD] hover:underline">Ajouter un client</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($customers->hasPages())
            <div class="border-t border-white/10 px-6 py-4">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
@endsection
