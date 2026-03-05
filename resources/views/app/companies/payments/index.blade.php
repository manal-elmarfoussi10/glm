@extends('app.layouts.app')

@section('pageSubtitle')
Paiements – {{ $company->name }}
@endsection

@section('content')
@php
    $methodLabels = ['cash' => 'Espèces', 'virement' => 'Virement', 'TPE' => 'TPE', 'cheque' => 'Chèque'];
    $typeLabels = ['deposit' => 'Caution', 'rental' => 'Location', 'fee' => 'Frais', 'refund' => 'Remboursement'];
@endphp
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Paiements</h1>
            <p class="mt-1 text-sm text-slate-400">Tous les paiements manuels. Recherche par réservation, véhicule, client.</p>
        </div>
    </header>

    <form method="get" action="{{ route('app.companies.payments.index', $company) }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div class="flex-1 min-w-[180px]">
            <label for="search" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Recherche (réf., plaque, client)</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Réf., plaque, client…" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
        </div>
        <div>
            <label for="method" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Moyen</label>
            <select id="method" name="method" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Tous</option>
                @foreach ($methodLabels as $k => $v)<option value="{{ $k }}" {{ request('method') === $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="type" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Type</label>
            <select id="type" name="type" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Tous</option>
                @foreach ($typeLabels as $k => $v)<option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach
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
        @if (isset($branches) && $branches->isNotEmpty())
        <div>
            <label for="branch_id" class="mb-1 block text-xs font-medium uppercase tracking-wider text-slate-500">Agence</label>
            <select id="branch_id" name="branch_id" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Toutes</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="glm-btn-primary">Filtrer</button>
        @if (request()->hasAny(['search', 'method', 'type', 'date_from', 'date_to', 'branch_id']))
            <a href="{{ route('app.companies.payments.index', $company) }}" class="glm-btn-secondary no-underline">Réinitialiser</a>
        @endif
    </form>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Réservation</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Moyen</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-slate-400">Montant</th>
                        <th class="w-0 px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($payments as $p)
                        @php $res = $p->reservation; @endphp
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 text-sm text-slate-200">{{ $p->paid_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 font-mono text-sm font-semibold text-white">{{ $res->reference }}</td>
                            <td class="px-6 py-4 text-sm text-slate-200">{{ $res->customer->name }}</td>
                            <td class="px-6 py-4"><span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $p->type === 'refund' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300' }}">{{ $typeLabels[$p->type] ?? $p->type }}</span></td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $methodLabels[$p->method] ?? $p->method }}</td>
                            <td class="px-6 py-4 text-right font-medium {{ $p->isRefund() ? 'text-emerald-400' : 'text-white' }}">{{ $p->isRefund() ? '-' : '' }}{{ number_format($p->amount, 2, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.reservations.show', [$company, $res]) }}?tab=payments" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Aucun paiement. Les paiements ajoutés dans les réservations apparaissent ici.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())
            <div class="border-t border-white/10 px-6 py-4">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
