@extends('app.layouts.app')

@section('pageSubtitle')
Agences – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Agences</h1>
            <p class="mt-1 text-sm text-slate-400">Liste des agences : ville, responsable, véhicules et statut.</p>
        </div>
        <a href="{{ route('app.companies.branches.create', $company) }}" class="glm-btn-primary inline-flex no-underline">Nouvelle agence</a>
    </header>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif

    <form method="get" action="{{ route('app.companies.branches.index', $company) }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div class="flex-1 min-w-[200px]">
            <label for="search" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Recherche</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Nom, ville, adresse, tél." class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Statut</label>
            <select id="status" name="status" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Tous</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
            </select>
        </div>
        <button type="submit" class="glm-btn-primary">Filtrer</button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('app.companies.branches.index', $company) }}" class="glm-btn-secondary no-underline">Réinitialiser</a>
        @endif
    </form>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Ville</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Adresse</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Téléphone</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Responsable</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Véhicules</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Statut</th>
                        <th class="w-0 px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($branches as $b)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 font-semibold text-white"><a href="{{ route('app.companies.branches.show', [$company, $b]) }}" class="text-white hover:text-[#93C5FD] transition no-underline">{{ $b->name }}</a></td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $b->city ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300 max-w-[200px] truncate" title="{{ $b->address }}">{{ $b->address ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $b->phone ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $b->manager?->name ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $b->vehicles_count ?? 0 }}</td>
                            <td class="px-6 py-4">
                                @if ($b->status === 'active')
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-500/20 text-emerald-400">Actif</span>
                                @else
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-500/20 text-slate-400">Suspendu</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.branches.edit', [$company, $b]) }}" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline">Modifier</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                Aucune agence. <a href="{{ route('app.companies.branches.create', $company) }}" class="text-[#93C5FD] hover:underline">Créer une agence</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($branches->hasPages())
            <div class="border-t border-white/10 px-6 py-4">{{ $branches->links() }}</div>
        @endif
    </div>
</div>
@endsection
