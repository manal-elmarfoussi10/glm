@extends('app.layouts.app')

@section('pageSubtitle')
Recherche globale
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Recherche</h1>
            <p class="mt-2 text-slate-400">Entreprises, utilisateurs, demandes d'inscription, tickets.</p>
        </div>
        <form action="{{ route('app.search.index') }}" method="get" class="flex gap-2 max-w-md flex-1 sm:max-w-sm">
            <input type="search" name="q" value="{{ $q }}" placeholder="Rechercher…" class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-white/40 focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
            <button type="submit" class="glm-btn-primary shrink-0">OK</button>
        </form>
    </header>

    @if (strlen($q) < 2)
        <div class="glm-card-static p-8 text-center text-slate-400">
            Saisissez au moins 2 caractères pour lancer la recherche.
        </div>
    @else
    <div class="space-y-8">
        @if ($companies->isNotEmpty())
        <section class="glm-card-static overflow-hidden p-0">
            <div class="border-b border-white/10 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Entreprises ({{ $companies->count() }})</h2>
            </div>
            <ul class="divide-y divide-white/10">
                @foreach ($companies as $c)
                    <li>
                        <a href="{{ route('app.companies.show', $c) }}" class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-white/5 transition no-underline">
                            <div>
                                <p class="font-semibold text-white">{{ $c->name }}</p>
                                <p class="text-sm text-slate-400">{{ $c->ice }} · {{ $c->email }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $c->status === 'active' ? 'glm-badge-approved' : 'glm-badge-pending' }}">{{ $c->status }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
        @endif

        @if ($users->isNotEmpty())
        <section class="glm-card-static overflow-hidden p-0">
            <div class="border-b border-white/10 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Utilisateurs ({{ $users->count() }})</h2>
            </div>
            <ul class="divide-y divide-white/10">
                @foreach ($users as $u)
                    <li>
                        <a href="{{ route('app.admin.users.index') }}?q={{ urlencode($q) }}" class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-white/5 transition no-underline">
                            <div>
                                <p class="font-semibold text-white">{{ $u->name }}</p>
                                <p class="text-sm text-slate-400">{{ $u->email }} · {{ $u->role }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $u->status === 'active' ? 'glm-badge-approved' : 'glm-badge-pending' }}">{{ $u->status }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
        @endif

        @if ($requests->isNotEmpty())
        <section class="glm-card-static overflow-hidden p-0">
            <div class="border-b border-white/10 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Demandes d'inscription ({{ $requests->count() }})</h2>
            </div>
            <ul class="divide-y divide-white/10">
                @foreach ($requests as $r)
                    <li>
                        <a href="{{ route('app.registration-requests.show', $r) }}" class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-white/5 transition no-underline">
                            <div>
                                <p class="font-semibold text-white">{{ $r->requested_company_name ?? $r->name }}</p>
                                <p class="text-sm text-slate-400">{{ $r->email }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $r->status === 'pending' ? 'bg-amber-500/20 text-amber-400' : ($r->status === 'active' ? 'glm-badge-approved' : 'glm-badge-pending') }}">{{ $r->status }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
        @endif

        @if ($tickets->isNotEmpty())
        <section class="glm-card-static overflow-hidden p-0">
            <div class="border-b border-white/10 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Tickets ({{ $tickets->count() }})</h2>
            </div>
            <ul class="divide-y divide-white/10">
                @foreach ($tickets as $t)
                    <li>
                        <a href="{{ route('app.inbox.show', $t) }}" class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-white/5 transition no-underline">
                            <div>
                                <p class="font-semibold text-white">{{ $t->subject }}</p>
                                <p class="text-sm text-slate-400">{{ $t->company?->name ?? $t->email ?? '–' }} · {{ $t->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $t->status === 'new' ? 'bg-blue-500/20 text-blue-400' : ($t->status === 'resolved' ? 'glm-badge-approved' : 'bg-amber-500/20 text-amber-400') }}">{{ $t->status }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
        @endif

        @if ($companies->isEmpty() && $users->isEmpty() && $requests->isEmpty() && $tickets->isEmpty())
            <div class="glm-card-static p-8 text-center text-slate-400">
                Aucun résultat pour « {{ $q }} ».
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
