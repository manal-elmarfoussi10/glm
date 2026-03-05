@extends('app.layouts.app')

@section('pageSubtitle')
Demande d’upgrade – {{ $request->company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.admin.upgrade-requests.index') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Demandes d’upgrade</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Demande d’upgrade</h1>
            <p class="mt-1 text-sm text-slate-400">{{ $request->company->name }} · {{ $request->requestedPlan?->name ?? '–' }} · {{ $request->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('app.companies.show', $request->company) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white hover:bg-white/10 no-underline">Voir l’entreprise</a>
        </div>
    </header>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Détails demande --}}
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <h2 class="text-lg font-semibold text-white mb-4">Détails de la demande</h2>
            <dl class="space-y-4">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Entreprise</dt>
                    <dd class="mt-0.5"><a href="{{ route('app.companies.show', $request->company) }}" class="font-medium text-[#93C5FD] hover:text-white no-underline">{{ $request->company->name }}</a></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Plan demandé</dt>
                    <dd class="mt-0.5 text-slate-200">{{ $request->requestedPlan?->name ?? '–' }} @if($request->requestedPlan?->monthly_price)<span class="text-slate-500">({{ number_format($request->requestedPlan->monthly_price, 0, ',', ' ') }} MAD/mois)</span>@endif</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Demandé par</dt>
                    <dd class="mt-0.5 text-slate-200">{{ $request->requestedByUser?->name ?? '–' }} <span class="text-slate-500">{{ $request->requestedByUser?->email }}</span></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Statut</dt>
                    <dd class="mt-0.5">
                        @if ($request->status === 'pending')
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400">En attente</span>
                        @elseif ($request->status === 'approved')
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-500/20 text-emerald-400">Approuvée</span>
                            @if ($request->reviewed_at) <span class="text-slate-500 text-xs ml-2">le {{ $request->reviewed_at->format('d/m/Y H:i') }} par {{ $request->reviewedByUser?->name }}</span>@endif
                        @else
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-500/20 text-slate-400">Refusée</span>
                            @if ($request->reviewed_at) <span class="text-slate-500 text-xs ml-2">le {{ $request->reviewed_at->format('d/m/Y H:i') }} par {{ $request->reviewedByUser?->name }}</span>@endif
                        @endif
                    </dd>
                </div>
                @if ($request->message)
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Message du client</dt>
                    <dd class="mt-1 rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-slate-200 whitespace-pre-wrap">{{ $request->message }}</dd>
                </div>
                @endif
                @if ($request->notes && in_array($request->status, ['approved', 'rejected'], true))
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ $request->status === 'rejected' ? 'Motif de refus' : 'Notes (révision)' }}</dt>
                    <dd class="mt-1 rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-slate-200 whitespace-pre-wrap">{{ $request->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Notes internes + Assignation + Actions --}}
        <div class="space-y-6">
            <form action="{{ route('app.admin.upgrade-requests.update', $request) }}" method="post" class="rounded-2xl border border-white/10 bg-white/[0.06] p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
                @csrf
                @method('put')
                <h2 class="text-lg font-semibold text-white mb-4">Notes internes & assignation</h2>
                <div class="space-y-4">
                    <div>
                        <label for="internal_notes" class="mb-1.5 block text-sm font-medium text-slate-300">Notes internes (visibles uniquement par le support)</label>
                        <textarea id="internal_notes" name="internal_notes" rows="4" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50">{{ old('internal_notes', $request->internal_notes) }}</textarea>
                    </div>
                    <div>
                        <label for="assigned_to" class="mb-1.5 block text-sm font-medium text-slate-300">Assigner à</label>
                        <select id="assigned_to" name="assigned_to" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                            <option value="">– Non assigné –</option>
                            @foreach ($agents as $u)
                                <option value="{{ $u->id }}" {{ old('assigned_to', $request->assigned_to) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#2563EB]/90 transition">Enregistrer</button>
                </div>
            </form>

            @if ($request->status === 'pending')
            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
                <h2 class="text-lg font-semibold text-white mb-4">Décision</h2>
                <div class="flex flex-wrap gap-4">
                    <form action="{{ route('app.admin.upgrade-requests.approve', $request) }}" method="post" class="flex flex-col gap-2 max-w-xs" onsubmit="return confirm('Approuver cette demande et passer l’entreprise au plan {{ $request->requestedPlan?->name }} ?');">
                        @csrf
                        <label for="approve_notes" class="text-xs font-medium text-slate-400">Notes (optionnel)</label>
                        <input type="text" id="approve_notes" name="notes" placeholder="Notes d’approbation" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white">
                        <button type="submit" class="rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-4 py-2.5 text-sm font-semibold hover:bg-emerald-500/30 transition">Approuver</button>
                    </form>
                    <form action="{{ route('app.admin.upgrade-requests.reject', $request) }}" method="post" class="flex flex-col gap-2 max-w-xs">
                        @csrf
                        <label for="reject_notes" class="text-xs font-medium text-slate-400">Motif de refus (recommandé)</label>
                        <input type="text" id="reject_notes" name="notes" placeholder="Motif de refus" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white">
                        <button type="submit" class="rounded-xl bg-red-500/20 text-red-400 border border-red-500/30 px-4 py-2.5 text-sm font-semibold hover:bg-red-500/30 transition">Refuser</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
