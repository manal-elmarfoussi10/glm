@extends('app.layouts.app')

@section('pageSubtitle')
Réservations – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-[color:var(--muted)] hover:text-[color:var(--text)] mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-[color:var(--text)]">Réservations</h1>
            <p class="mt-1 text-sm text-[color:var(--muted)]">Recherche par référence, CIN, plaque. Filtres par dates, statut, véhicule, paiement.</p>
        </div>
        <a href="{{ route('app.companies.reservations.create', $company) }}" class="glm-btn-primary inline-flex no-underline">Nouvelle réservation</a>
    </header>

    <form method="get" action="{{ route('app.companies.reservations.index', $company) }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div class="flex-1 min-w-[180px]">
            <label for="search" class="mb-1 block text-xs font-medium uppercase tracking-wider text-[color:var(--muted)]">Recherche (réf., CIN, plaque)</label>
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Réf., CIN, plaque…" class="glm-input w-full rounded-xl px-4 py-2.5 text-sm">
        </div>
        <div>
            <label for="date_from" class="mb-1 block text-xs font-medium uppercase tracking-wider text-[color:var(--muted)]">Du</label>
            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="glm-input glm-select rounded-xl px-4 py-2.5 text-sm">
        </div>
        <div>
            <label for="date_to" class="mb-1 block text-xs font-medium uppercase tracking-wider text-[color:var(--muted)]">Au</label>
            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="glm-input glm-select rounded-xl px-4 py-2.5 text-sm">
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-medium uppercase tracking-wider text-[color:var(--muted)]">Statut</label>
            <select id="status" name="status" class="glm-input glm-select rounded-xl px-4 py-2.5 text-sm">
                <option value="">Tous</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminée</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
            </select>
        </div>
        <div>
            <label for="payment_status" class="mb-1 block text-xs font-medium uppercase tracking-wider text-[color:var(--muted)]">Paiement</label>
            <select id="payment_status" name="payment_status" class="glm-input glm-select rounded-xl px-4 py-2.5 text-sm">
                <option value="">Tous</option>
                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Non payé</option>
                <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partiel</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Payé</option>
            </select>
        </div>
        <div>
            <label for="vehicle_id" class="mb-1 block text-xs font-medium uppercase tracking-wider text-[color:var(--muted)]">Véhicule</label>
            <select id="vehicle_id" name="vehicle_id" class="glm-input glm-select rounded-xl px-4 py-2.5 text-sm">
                <option value="">Tous</option>
                @foreach ($vehicles as $v)
                    <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->plate }} – {{ $v->brand }} {{ $v->model }}</option>
                @endforeach
            </select>
        </div>
        @if (isset($branches) && $branches->isNotEmpty())
        <div>
            <label for="branch_id" class="mb-1 block text-xs font-medium uppercase tracking-wider text-[color:var(--muted)]">Agence</label>
            <select id="branch_id" name="branch_id" class="glm-input glm-select rounded-xl px-4 py-2.5 text-sm">
                <option value="">Toutes</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="glm-btn-primary">Filtrer</button>
        @if (request()->hasAny(['search', 'date_from', 'date_to', 'status', 'payment_status', 'vehicle_id', 'customer_id', 'branch_id']))
            <a href="{{ route('app.companies.reservations.index', $company) }}" class="glm-btn-secondary no-underline">Réinitialiser</a>
        @endif
    </form>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-[color:var(--surface-2)]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Référence</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Véhicule</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Période</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Paiement</th>
                        <th class="w-0 px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[color:var(--border)]">
                    @php
                        $statusLabels = [
                            'draft' => 'Brouillon',
                            'confirmed' => 'Confirmée',
                            'in_progress' => 'En cours',
                            'completed' => 'Terminée',
                            'cancelled' => 'Annulée',
                        ];
                        $paymentLabels = ['unpaid' => 'Non payé', 'partial' => 'Partiel', 'paid' => 'Payé'];
                    @endphp
                    @forelse ($reservations as $r)
                        <tr class="hover:bg-[color:var(--surface-2)]">
                            <td class="px-6 py-4 font-mono text-sm font-semibold text-[color:var(--text)]">{{ $r->reference }}</td>
                            <td class="px-6 py-4 text-sm text-[color:var(--text)]">{{ $r->customer->name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if ($r->vehicle->image_url)
                                        <img src="{{ $r->vehicle->image_url }}" alt="" class="h-10 w-14 rounded border border-[color:var(--border)] object-cover shrink-0" onerror="this.style.display='none';this.nextElementSibling&&(this.nextElementSibling.style.display='flex');">
                                        <span class="h-10 w-14 rounded border border-[color:var(--border)] bg-[color:var(--surface-2)] shrink-0 flex items-center justify-center text-[color:var(--muted)]" style="display:none"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg></span>
                                    @else
                                        <span class="h-10 w-14 rounded border border-[color:var(--border)] bg-[color:var(--surface-2)] shrink-0 flex items-center justify-center text-[color:var(--muted)]"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg></span>
                                    @endif
                                    <span class="text-sm text-[color:var(--muted)]">{{ $r->vehicle->plate }} <span class="text-[color:var(--muted)]">{{ $r->vehicle->brand }} {{ $r->vehicle->model }}</span></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-[color:var(--muted)]">{{ $r->start_at->format('d/m/Y') }} → {{ $r->end_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-[color:var(--text)]">{{ number_format($r->total_price, 2, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4">
                                @php $s = $r->status; @endphp
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $s === 'draft' ? 'bg-[color:var(--muted)]/20 text-[color:var(--muted)]' : '' }}
                                    {{ $s === 'confirmed' ? 'bg-[color:var(--primary)]/20 text-[color:var(--primary)]' : '' }}
                                    {{ $s === 'in_progress' ? 'glm-badge-pending' : '' }}
                                    {{ $s === 'completed' ? 'glm-badge-approved' : '' }}
                                    {{ $s === 'cancelled' ? 'glm-badge-rejected' : '' }}
                                ">{{ $statusLabels[$s] ?? $s }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $r->payment_status === 'paid' ? 'glm-badge-approved' : ($r->payment_status === 'partial' ? 'glm-badge-pending' : 'bg-[color:var(--muted)]/20 text-[color:var(--muted)]') }}
                                ">{{ $paymentLabels[$r->payment_status] ?? $r->payment_status }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.reservations.show', [$company, $r]) }}" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-[color:var(--muted)]">
                                Aucune réservation. <a href="{{ route('app.companies.reservations.create', $company) }}" class="text-[color:var(--primary)] hover:underline">Créer une réservation</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reservations->hasPages())
            <div class="border-t border-[color:var(--border)] px-6 py-4">{{ $reservations->links() }}</div>
        @endif
    </div>
</div>
@endsection
