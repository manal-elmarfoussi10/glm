@extends('app.layouts.app')

@section('pageSubtitle')
Contrats – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Contrats</h1>
            <p class="mt-1 text-sm text-slate-400">Contrats générés à partir des réservations. Recherche par référence, client, plaque.</p>
        </div>
    </header>

    <form method="get" action="{{ route('app.companies.contracts.index', $company) }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div class="flex-1 min-w-[180px]">
            <label for="search" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Recherche (réf., client, plaque)</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Réf., CIN, plaque…" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Statut</label>
            <select id="status" name="status" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Tous</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                <option value="generated" {{ request('status') === 'generated' ? 'selected' : '' }}>Généré</option>
                <option value="signed" {{ request('status') === 'signed' ? 'selected' : '' }}>Signé</option>
            </select>
        </div>
        <div>
            <label for="date_from" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Du</label>
            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="date_to" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Au</label>
            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <button type="submit" class="glm-btn-primary">Filtrer</button>
        @if (request()->hasAny(['search', 'status', 'date_from', 'date_to']))
            <a href="{{ route('app.companies.contracts.index', $company) }}" class="glm-btn-secondary no-underline">Réinitialiser</a>
        @endif
    </form>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Réservation</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Véhicule</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Généré le</th>
                        <th class="w-0 px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @php
                        $contractStatusLabels = ['draft' => 'Brouillon', 'generated' => 'Généré', 'signed' => 'Signé'];
                    @endphp
                    @forelse ($contracts as $c)
                        @php $res = $c->reservation; @endphp
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 font-mono text-sm font-semibold text-white">{{ $res->reference }}</td>
                            <td class="px-6 py-4 text-sm text-slate-200">{{ $res->customer->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $res->vehicle->plate }} <span class="text-slate-500">{{ $res->vehicle->brand }} {{ $res->vehicle->model }}</span></td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $c->status === 'generated' ? 'glm-badge-approved' : '' }}
                                    {{ $c->status === 'signed' ? 'bg-emerald-500/20 text-emerald-300' : '' }}
                                    {{ $c->status === 'draft' ? 'bg-slate-500/20 text-slate-400' : '' }}
                                ">{{ $contractStatusLabels[$c->status] ?? $c->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $c->generated_at ? $c->generated_at->format('d/m/Y H:i') : '–' }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.reservations.show', [$company, $res]) }}?tab=contract" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline mr-1">Réservation</a>
                                <a href="{{ route('app.companies.reservations.contract-print', [$company, $res]) }}" target="_blank" rel="noopener" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline">Imprimer / PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                Aucun contrat généré. Générez un contrat depuis l’onglet « Contrat » d’une réservation.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($contracts->hasPages())
            <div class="border-t border-white/10 px-6 py-4">{{ $contracts->links() }}</div>
        @endif
    </div>
</div>
@endsection
