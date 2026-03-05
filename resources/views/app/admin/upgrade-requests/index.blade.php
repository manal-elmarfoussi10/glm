@extends('app.layouts.app')

@section('pageSubtitle')
Demandes d'upgrade
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.dashboard') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Dashboard</a>
            <h1 class="text-3xl font-bold tracking-tight text-white">Demandes d'upgrade</h1>
            <p class="mt-2 text-slate-400">Approuver, refuser ou assigner les demandes de changement de plan.</p>
        </div>
    </header>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    <form method="get" action="{{ route('app.admin.upgrade-requests.index') }}" class="rounded-2xl border border-white/10 bg-white/[0.06] p-5 flex flex-wrap items-end gap-4">
        <div>
            <label for="status" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Statut</label>
            <select id="status" name="status" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                <option value="">Tous</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvées</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Refusées</option>
            </select>
        </div>
        <button type="submit" class="rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white">Filtrer</button>
    </form>

    <div class="rounded-2xl border border-white/10 bg-white/[0.06] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Entreprise</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Plan demandé</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Demandé par</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Assigné à</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-400">Statut</th>
                        <th class="w-0 px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($requests as $r)
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4">
                                <a href="{{ route('app.admin.upgrade-requests.show', $r) }}" class="font-semibold text-white hover:text-[#93C5FD] no-underline">{{ $r->company->name }}</a>
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ $r->requestedPlan?->name ?? '–' }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ $r->requestedByUser?->name ?? '–' }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ $r->assignedToUser?->name ?? '–' }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                @if ($r->status === 'pending')
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400">En attente</span>
                                @elseif ($r->status === 'approved')
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-500/20 text-emerald-400">Approuvée</span>
                                @else
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-500/20 text-slate-400">Refusée</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('app.admin.upgrade-requests.show', $r) }}" class="rounded-xl border border-white/10 bg-white/5 px-3 py-1.5 text-sm font-medium text-white hover:bg-white/10 no-underline">Détail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">Aucune demande d'upgrade.</td>
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
