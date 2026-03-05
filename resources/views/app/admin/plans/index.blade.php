@extends('app.layouts.app')

@section('pageSubtitle')
Plans et tarifs – Super Admin
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Plans & tarifs</h1>
            <p class="mt-2 max-w-2xl text-base text-slate-400">
                Définir les offres (mensuel / annuel, essai, limites véhicules / utilisateurs / agences, IA, contrats personnalisés).
            </p>
        </div>
        <div class="flex shrink-0 gap-3">
            <a href="{{ route('app.dashboard') }}" class="glm-btn-secondary inline-flex no-underline">Tableau de bord</a>
            <a href="{{ route('app.admin.plans.create') }}" class="glm-btn-primary inline-flex no-underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Nouveau plan
            </a>
        </div>
    </header>

    @if (session('success'))
        <div class="glm-fade-in rounded-2xl bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 text-sm text-emerald-300" role="alert">{{ session('success') }}</div>
    @endif

    <div class="glm-table-wrap overflow-hidden rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Mensuel</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Annuel</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Essai (j)</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Limites</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Actif</th>
                        <th class="w-0 px-6 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($plans as $plan)
                        <tr class="transition-colors hover:bg-white/[0.03]">
                            <td class="px-6 py-4 font-medium text-white">{{ $plan->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ number_format($plan->monthly_price, 0, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ number_format($plan->yearly_price, 0, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $plan->trial_days }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">
                                Véhicules {{ $plan->limit_vehicles ?? '∞' }} · Users {{ $plan->limit_users ?? '∞' }} · Agences {{ $plan->limit_branches ?? '∞' }}
                                @if ($plan->ai_access)<span class="text-amber-400">· IA</span>@endif
                                @if ($plan->custom_contracts)<span class="text-slate-500">· Contrats</span>@endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($plan->is_active)
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium glm-badge-approved">Oui</span>
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium glm-badge-pending">Non</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 flex items-center gap-2">
                                <a href="{{ route('app.admin.plans.edit', $plan) }}" class="glm-btn-secondary text-sm py-2 no-underline">Modifier</a>
                                <form action="{{ route('app.admin.plans.destroy', $plan) }}" method="post" class="inline" onsubmit="return confirm('Supprimer ce plan ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-400 hover:text-red-300 transition-colors">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                                    <p class="text-slate-400">Aucun plan. Créez le premier pour l’assigner aux entreprises.</p>
                                    <a href="{{ route('app.admin.plans.create') }}" class="mt-4 glm-btn-primary no-underline">Nouveau plan</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($plans->hasPages())
            <div class="border-t border-white/5 px-6 py-4">{{ $plans->links() }}</div>
        @endif
    </div>
</div>
@endsection
