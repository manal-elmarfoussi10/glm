@extends('app.layouts.app')

@section('pageSubtitle')
Demandes d’upgrade
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Demandes d’upgrade</h1>
            <p class="mt-2 text-slate-400">Approuver ou refuser les demandes de changement de plan des entreprises.</p>
        </div>
        <a href="{{ route('app.dashboard') }}" class="glm-btn-secondary inline-flex no-underline">Dashboard</a>
    </header>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    <form method="get" action="{{ route('app.upgrade-requests.index') }}" class="glm-card-static flex flex-wrap items-end gap-4 p-5">
        <div>
            <label for="status" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Statut</label>
            <select id="status" name="status" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                <option value="">Tous</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvées</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Refusées</option>
            </select>
        </div>
        <button type="submit" class="glm-btn-primary">Filtrer</button>
    </form>

    <div class="glm-card-static overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Entreprise</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Plan demandé</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Demandé par</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($requests as $r)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4">
                                <a href="{{ route('app.companies.show', $r->company) }}" class="font-semibold text-white hover:text-[#93C5FD] no-underline">{{ $r->company->name }}</a>
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ $r->requestedPlan?->name ?? '–' }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ $r->requestedByUser?->name ?? '–' }} <span class="text-slate-500 text-xs">{{ $r->requestedByUser?->email }}</span></td>
                            <td class="px-6 py-4 text-slate-300">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                @if ($r->status === 'pending')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400">En attente</span>
                                @elseif ($r->status === 'approved')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-500/20 text-emerald-400">Approuvée</span>
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-500/20 text-slate-400">Refusée</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($r->status === 'pending')
                                    <div class="flex flex-wrap gap-2">
                                        <form action="{{ route('app.upgrade-requests.approve', $r) }}" method="post" class="inline" onsubmit="return confirm('Approuver cette demande et passer l’entreprise au plan {{ $r->requestedPlan?->name }} ?');">
                                            @csrf
                                            <input type="text" name="notes" placeholder="Notes (optionnel)" class="rounded border border-white/10 bg-white/5 px-2 py-1 text-xs text-white w-32 mb-1">
                                            <button type="submit" class="block w-full mt-1 rounded bg-emerald-500/20 text-emerald-400 px-2 py-1 text-xs font-medium hover:bg-emerald-500/30">Approuver</button>
                                        </form>
                                        <form action="{{ route('app.upgrade-requests.reject', $r) }}" method="post" class="inline">
                                            @csrf
                                            <input type="text" name="notes" placeholder="Motif (optionnel)" class="rounded border border-white/10 bg-white/5 px-2 py-1 text-xs text-white w-32 mb-1">
                                            <button type="submit" class="block w-full mt-1 rounded bg-red-500/20 text-red-400 px-2 py-1 text-xs font-medium hover:bg-red-500/30">Refuser</button>
                                        </form>
                                    </div>
                                @else
                                    @if ($r->notes)
                                        <p class="text-xs text-slate-500 max-w-xs truncate" title="{{ $r->notes }}">{{ Str::limit($r->notes, 40) }}</p>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">Aucune demande d’upgrade.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/10 px-6 py-3">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
