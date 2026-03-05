@extends('app.layouts.app')

@section('pageSubtitle')
Vue support — entreprises, essais et demandes en attente
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    {{-- Header + Global search + Quick actions --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white/95">Dashboard Support</h1>
            <p class="mt-1 text-sm text-white/60">
                Entreprises actives, essais, demandes en attente, tickets et comptes suspendus.
            </p>
            @if (in_array(auth()->user()?->role ?? null, ['super_admin', 'support'], true))
            <form action="{{ route('app.search.index') }}" method="get" class="mt-4 max-w-md">
                <div class="flex gap-2">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher (entreprises, utilisateurs, demandes, tickets…)" class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-white/40 focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                    <button type="submit" class="glm-btn-primary shrink-0">Rechercher</button>
                </div>
            </form>
            @endif
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('app.registration-requests.index', ['status' => 'pending']) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white/85 hover:bg-white/10 transition no-underline">
                <span class="inline-flex h-2 w-2 rounded-full bg-amber-400"></span>
                Demandes en attente
            </a>
            @if (in_array(auth()->user()?->role ?? null, ['super_admin', 'support'], true))
            <a href="{{ route('app.inbox.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white/85 hover:bg-white/10 transition no-underline">Inbox</a>
            <a href="{{ route('app.inbox.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#2563EB] to-[#2563EB]/70 px-4 py-2 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(37,99,235,0.22)] hover:brightness-[1.03] transition no-underline">Nouveau ticket</a>
            @endif
            <a href="{{ route('app.companies.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/5 px-4 py-2 text-sm font-semibold text-white/85 hover:bg-white/10 transition no-underline">Entreprises</a>
        </div>
    </header>

    {{-- 6 stat cards – GLM design --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7">
        <a href="{{ route('app.companies.index') }}?status=active" class="glm-stat-card glm-stat-card-accent-emerald block p-5 no-underline">
            <p class="text-sm font-semibold text-white/60">Entreprises actives</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-white/95">{{ $stats['active_companies'] }}</p>
            <p class="mt-1 text-xs font-semibold text-emerald-400">Abonnées</p>
        </a>
        <a href="{{ route('app.companies.index') }}" class="glm-stat-card glm-stat-card-accent-blue block p-5 no-underline">
            <p class="text-sm font-semibold text-white/60">En essai</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-white/95">{{ $stats['trial_companies'] }}</p>
            <p class="mt-1 text-xs font-semibold text-blue-400">Trial</p>
        </a>
        <a href="{{ route('app.companies.index') }}" class="glm-stat-card glm-stat-card-accent-amber block p-5 no-underline">
            <p class="text-sm font-semibold text-white/60">Essais bientôt finis</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-white/95">{{ $stats['expiring_trials'] }}</p>
            <p class="mt-1 text-xs font-semibold text-amber-400">Sous 14 jours</p>
        </a>
        <a href="{{ route('app.registration-requests.index', ['status' => 'pending']) }}" class="glm-stat-card glm-stat-card-accent-blue block p-5 no-underline">
            <p class="text-sm font-semibold text-white/60">Demandes en attente</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-white/95">{{ $stats['pending_requests'] }}</p>
            <p class="mt-1 text-xs font-semibold text-blue-400">À valider</p>
        </a>
        <a href="{{ route('app.admin.upgrade-requests.index', ['status' => 'pending']) }}" class="glm-stat-card glm-stat-card-accent-amber block p-5 no-underline">
            <p class="text-sm font-semibold text-white/60">Upgrades en attente</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-white/95">{{ $stats['pending_upgrade_requests'] ?? 0 }}</p>
            <p class="mt-1 text-xs font-semibold text-amber-400">Demandes d'upgrade</p>
        </a>
        <a href="{{ route('app.companies.index') }}?status=suspended" class="glm-stat-card glm-stat-card-accent-red block p-5 no-underline">
            <p class="text-sm font-semibold text-white/60">Entreprises suspendues</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-white/95">{{ $stats['suspended_companies'] }}</p>
            <p class="mt-1 text-xs font-semibold text-red-400">Comptes bloqués</p>
        </a>
        @if (isset($stats['tickets']) && in_array(auth()->user()?->role ?? null, ['super_admin', 'support'], true))
        <a href="{{ route('app.inbox.index') }}" class="glm-stat-card glm-stat-card-accent-blue block p-5 no-underline">
            <p class="text-sm font-semibold text-white/60">Tickets ouverts</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-white/95">{{ $stats['tickets'] }}</p>
            <p class="mt-1 text-xs font-semibold text-blue-400">New / Open / Waiting</p>
        </a>
        @endif
    </section>

    {{-- Urgent list + Two columns: recent pending + expiring trials --}}
    {{-- Urgent: new/open tickets --}}
    @if (isset($urgentTickets) && $urgentTickets->isNotEmpty() && in_array(auth()->user()?->role ?? null, ['super_admin', 'support'], true))
    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/5 backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-extrabold text-white/90">Tickets urgents (New / Open)</h2>
            <a href="{{ route('app.inbox.index', ['status' => 'new']) }}" class="text-sm font-bold text-[#93C5FD] hover:text-white transition no-underline">Inbox →</a>
        </div>
        <div class="mt-5 divide-y divide-white/10">
            @foreach ($urgentTickets as $t)
                <a href="{{ route('app.inbox.show', $t) }}" class="flex items-center justify-between gap-3 py-4 hover:bg-white/5 -mx-2 px-2 rounded-xl transition no-underline">
                    <div class="min-w-0">
                        <p class="font-semibold text-white/90 truncate">{{ $t->subject }}</p>
                        <p class="text-sm text-white/60">{{ $t->company?->name ?? $t->email ?? '–' }} · {{ $t->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $t->status === 'new' ? 'bg-blue-500/20 text-blue-400' : 'bg-amber-500/20 text-amber-400' }}">{{ $t->status }}</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Recent pending requests --}}
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-white/90">Demandes en attente</h2>
                    <p class="mt-1 text-sm text-white/60">Dernières à traiter</p>
                </div>
                <a href="{{ route('app.registration-requests.index', ['status' => 'pending']) }}" class="text-sm font-bold text-[#93C5FD] hover:text-white transition no-underline">Tout voir →</a>
            </div>
            <div class="mt-5 divide-y divide-white/10">
                @forelse ($recentPending as $req)
                    <a href="{{ route('app.registration-requests.show', $req) }}" class="flex items-center justify-between gap-3 py-4 hover:bg-white/5 -mx-2 px-2 rounded-xl transition no-underline">
                        <div class="min-w-0">
                            <p class="font-semibold text-white/90 truncate">{{ $req->requested_company_name ?? $req->name }}</p>
                            <p class="text-sm text-white/60 truncate">{{ $req->email }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-white/50">{{ $req->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-white/50">Aucune demande en attente.</p>
                @endforelse
            </div>
        </div>

        {{-- Expiring trials --}}
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-white/90">Essais bientôt terminés</h2>
                    <p class="mt-1 text-sm text-white/60">Sous 14 jours</p>
                </div>
                <a href="{{ route('app.companies.index') }}" class="text-sm font-bold text-[#93C5FD] hover:text-white transition no-underline">Toutes →</a>
            </div>
            <div class="mt-5 divide-y divide-white/10">
                @forelse ($expiringList as $company)
                    <a href="{{ route('app.companies.show', $company) }}" class="flex items-center justify-between gap-3 py-4 hover:bg-white/5 -mx-2 px-2 rounded-xl transition no-underline">
                        <div class="min-w-0">
                            <p class="font-semibold text-white/90 truncate">{{ $company->name }}</p>
                            <p class="text-sm text-white/60">Fin essai : {{ $company->trial_ends_at?->format('d/m/Y') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-500/20 px-2.5 py-0.5 text-xs font-medium text-amber-400">{{ $company->trial_ends_at?->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-white/50">Aucun essai à échéance proche.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
