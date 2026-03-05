@extends('app.layouts.app')

@section('pageSubtitle')
Liste des abonnements
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight glm-text">Abonnements</h1>
            <p class="mt-2 glm-muted">Prolonger un essai, changer le statut, ajouter des notes.</p>
        </div>
        <a href="{{ route('app.dashboard') }}" class="glm-btn-secondary inline-flex no-underline">Dashboard</a>
    </header>

    <form method="get" action="{{ route('app.subscriptions.index') }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div>
            <label for="filter-status" class="mb-1.5 block text-xs font-medium uppercase tracking-wider glm-muted">Statut</label>
            <select id="filter-status" name="status" class="glm-select rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#2563EB]/50">
                <option value="">Tous</option>
                <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Essai</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expiré</option>
            </select>
        </div>
        <button type="submit" class="glm-btn-primary">Filtrer</button>
    </form>

    <div class="glm-table-wrap overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="sticky top-0 z-[5]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Entreprise</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Fin essai</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        @php $sub = $company->subscription; @endphp
                        <tr class="transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.show', $company) }}" class="font-semibold glm-text hover:opacity-80 no-underline">{{ $company->name }}</a>
                                @if ($sub->notes)
                                    <p class="text-xs glm-muted mt-1 truncate max-w-xs" title="{{ $sub->notes }}">{{ Str::limit($sub->notes, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 glm-text">{{ $company->planRelation?->name ?? '–' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $sub->status === 'active' ? 'glm-badge-approved' : ($sub->status === 'trial' ? 'glm-badge-trial' : 'glm-badge-pending') }}">{{ $sub->status }}</span>
                            </td>
                            <td class="px-6 py-4 glm-text">{{ $sub->trial_ends_at?->format('d/m/Y') ?? '–' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-[color:var(--primary)] hover:opacity-80 no-underline">Détail / Prolonger</a>
                                    <form action="{{ route('app.subscriptions.update-status', $company) }}" method="post" class="inline">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="glm-select rounded border px-2 py-1 text-xs">
                                            <option value="trial" {{ $sub->status === 'trial' ? 'selected' : '' }}>Essai</option>
                                            <option value="active" {{ $sub->status === 'active' ? 'selected' : '' }}>Actif</option>
                                            <option value="suspended" {{ $sub->status === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                                            <option value="expired" {{ $sub->status === 'expired' ? 'selected' : '' }}>Expiré</option>
                                        </select>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center glm-muted">Aucun abonnement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($companies->hasPages())
            <div class="border-t border-[color:var(--glm-border)] px-6 py-4">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
