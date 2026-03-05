@extends('app.layouts.app')

@section('pageSubtitle')
Consulter et traiter les demandes d'inscription entreprise
@endsection

@section('content')
<div
    class="space-y-8 glm-fade-in"
    x-data="approvalCenter()"
    x-init="init()"
>
    {{-- Header --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Demandes d'inscription</h1>
            <p class="mt-2 max-w-2xl text-base text-slate-400">
                Gérez les demandes d'accès entreprise. Consultez les dossiers, approuvez ou refusez avec des essais personnalisés.
            </p>
        </div>
        <div class="shrink-0">
            <a href="{{ route('app.dashboard') }}" class="glm-btn-primary inline-flex no-underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                Tableau de bord
            </a>
        </div>
    </header>

    {{-- Stats cards – glass, 18px, accent bar, hover lift --}}
    <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('app.registration-requests.index', array_merge(request()->query(), ['status' => null])) }}" class="glm-stat-card glm-stat-card-accent-blue block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">Total</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs font-medium text-blue-400">Toutes les demandes</p>
        </a>
        <a href="{{ route('app.registration-requests.index', array_merge(request()->query(), ['status' => 'pending'])) }}" class="glm-stat-card glm-stat-card-accent-amber block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">En attente</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['pending'] }}</p>
            <p class="mt-1 text-xs font-medium text-amber-400">À traiter</p>
        </a>
        <a href="{{ route('app.registration-requests.index', array_merge(request()->query(), ['status' => 'active'])) }}" class="glm-stat-card glm-stat-card-accent-emerald block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">Approuvées</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['approved'] }}</p>
            <p class="mt-1 text-xs font-medium text-emerald-400">Comptes créés</p>
        </a>
        <a href="{{ route('app.registration-requests.index', array_merge(request()->query(), ['status' => 'rejected'])) }}" class="glm-stat-card glm-stat-card-accent-red block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">Refusées</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['rejected'] }}</p>
            <p class="mt-1 text-xs font-medium text-red-400">Non retenues</p>
        </a>
    </section>

    {{-- Filters --}}
    <form method="get" action="{{ route('app.registration-requests.index') }}" class="glm-card-static flex flex-wrap items-end gap-5 p-5">
        <div class="flex flex-wrap items-center gap-4">
            <div>
                <label for="filter-status" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Statut</label>
                <select id="filter-status" name="status" class="w-40 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="">Tous</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Approuvées</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Refusées</option>
                </select>
            </div>
            <div>
                <label for="filter-plan" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Plan</label>
                <select id="filter-plan" name="plan" class="w-40 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="">Tous</option>
                    <option value="starter" {{ request('plan') === 'starter' ? 'selected' : '' }}>Starter</option>
                    <option value="professional" {{ request('plan') === 'professional' ? 'selected' : '' }}>Professional</option>
                    <option value="enterprise" {{ request('plan') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
            </div>
            <div>
                <label for="filter-from" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Du</label>
                <input type="date" id="filter-from" name="from" value="{{ request('from') }}" class="w-40 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
            </div>
            <div>
                <label for="filter-to" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Au</label>
                <input type="date" id="filter-to" name="to" value="{{ request('to') }}" class="w-40 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Appliquer les filtres</button>
            @if (request()->hasAny(['status', 'plan', 'from', 'to']))
                <a href="{{ route('app.registration-requests.index') }}" class="glm-btn-secondary no-underline">Effacer</a>
            @endif
        </div>
    </form>

    {{-- Table – theme-aware (light surface in light mode) --}}
    <div class="glm-table-wrap overflow-hidden rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="sticky top-0 z-[5]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Entreprise</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Responsable</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Date</th>
                        <th class="w-0 px-6 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr
                            class="transition-colors"
                            data-request="{{ json_encode([
                                'id' => $req->id,
                                'name' => $req->name,
                                'email' => $req->email,
                                'phone' => $req->phone,
                                'requested_company_name' => $req->requested_company_name,
                                'requested_ice' => $req->requested_ice,
                                'requested_plan' => $req->requested_plan,
                                'requested_country' => $req->requested_country,
                                'registration_message' => $req->registration_message,
                                'admin_notes' => $req->admin_notes,
                                'rejection_reason' => $req->rejection_reason,
                                'status' => $req->status,
                                'created_at' => $req->created_at->format('d/m/Y H:i'),
                                'registration_logs' => is_array($req->registration_logs) ? $req->registration_logs : (json_decode($req->registration_logs ?? '[]', true) ?: []),
                            ]) }}"
                        >
                            <td class="px-6 py-4 font-medium glm-text">{{ $req->requested_company_name ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm glm-text">{{ $req->name }}</td>
                            <td class="px-6 py-4 text-sm glm-muted">{{ $req->email }}</td>
                            <td class="px-6 py-4 text-sm glm-muted">{{ $req->requested_plan ?? '–' }}</td>
                            <td class="px-6 py-4">
                                @if ($req->status === 'pending')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium glm-badge-pending">En attente</span>
                                @elseif ($req->status === 'active')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium glm-badge-approved">Approuvé</span>
                                @else
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium glm-badge-rejected">Refusé</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm glm-muted">{{ $req->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <button type="button" @click="openMenu($event, {{ $req->id }})" class="rounded-lg p-2 glm-muted hover:opacity-80 transition-colors" aria-haspopup="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                {{-- Empty state --}}
                                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[color:var(--glm-border)] glm-muted">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold glm-text">Aucune demande</h3>
                                    <p class="mt-2 max-w-sm text-sm glm-muted">Aucune demande ne correspond à vos filtres ou la liste est vide.</p>
                                    <a href="{{ route('app.registration-requests.index') }}" class="mt-6 glm-btn-secondary no-underline">Réinitialiser les filtres</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="border-t border-[color:var(--glm-border)] px-6 py-4">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    {{-- Row actions dropdown (teleported to body so it always appears on top) --}}
    <template x-teleport="body">
        <div
            x-show="menuOpen"
            x-transition
            x-cloak
            @click.outside="closeMenu()"
            :style="menuRect && 'position:fixed;top:' + (menuRect.bottom + 6) + 'px;left:' + menuRect.left + 'px;transform:translateX(-100%);min-width:12rem;z-index:9999'"
            class="glm-dark-bg rounded-xl border border-white/10 bg-slate-800 py-2 shadow-2xl ring-1 ring-black/20"
            style="display: none;"
        >
            <button type="button" @click="openDrawerFromMenu()" class="block w-full px-4 py-2.5 text-left text-sm text-slate-300 hover:bg-white/5 hover:text-white">Voir le détail</button>
            <a :href="menuRequestId ? '/app/registration-requests/' + menuRequestId : '#'" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-white/5 hover:text-white">Ouvrir en page</a>
        </div>
    </template>

    {{-- Side drawer – full info, approve, reject, trial, notes --}}
    <div
        x-show="drawerOpen"
        x-cloak
        class="fixed inset-0 z-50 flex justify-end"
        aria-modal="true"
        role="dialog"
    >
        <div x-show="drawerOpen" x-transition:enter="glm-drawer-backdrop" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-black/50" @click="drawerOpen = false"></div>
        <aside
            :class="{ 'open': drawerOpen }"
            class="glm-dark-bg glm-drawer-panel fixed top-0 right-0 z-50 h-full w-full max-w-lg border-l border-white/5 bg-[#1e293b]"
        >
            <div class="flex h-full flex-col overflow-hidden">
                <div class="flex shrink-0 items-center justify-between border-b border-white/5 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Détail de la demande</h2>
                    <button type="button" @click="drawerOpen = false" class="rounded-lg p-2 text-slate-400 hover:bg-white/5 hover:text-white transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-6 py-5" x-show="selectedRequest">
                    <template x-if="selectedRequest">
                        <div class="space-y-6">
                            {{-- Company & contact --}}
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Entreprise & contact</h3>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div><dt class="text-slate-500">Entreprise</dt><dd class="mt-0.5 text-white" x-text="selectedRequest?.requested_company_name || '–'"></dd></div>
                                    <div><dt class="text-slate-500">ICE</dt><dd class="mt-0.5 text-slate-300" x-text="selectedRequest?.requested_ice || '–'"></dd></div>
                                    <div><dt class="text-slate-500">Responsable</dt><dd class="mt-0.5 text-slate-300" x-text="selectedRequest?.name"></dd></div>
                                    <div><dt class="text-slate-500">Email</dt><dd class="mt-0.5 text-slate-300" x-text="selectedRequest?.email"></dd></div>
                                    <div><dt class="text-slate-500">Téléphone</dt><dd class="mt-0.5 text-slate-300" x-text="selectedRequest?.phone || '–'"></dd></div>
                                    <div><dt class="text-slate-500">Pays</dt><dd class="mt-0.5 text-slate-300" x-text="selectedRequest?.requested_country || '–'"></dd></div>
                                    <div><dt class="text-slate-500">Plan</dt><dd class="mt-0.5 text-slate-300" x-text="selectedRequest?.requested_plan || '–'"></dd></div>
                                    <div><dt class="text-slate-500">Date d'inscription</dt><dd class="mt-0.5 text-slate-300" x-text="selectedRequest?.created_at"></dd></div>
                                </dl>
                                <template x-if="selectedRequest?.registration_message">
                                    <div class="mt-3 pt-3 border-t border-white/5">
                                        <dt class="text-slate-500">Message</dt>
                                        <dd class="mt-1 text-slate-300" x-text="selectedRequest.registration_message"></dd>
                                    </div>
                                </template>
                            </div>

                            {{-- Notes internes --}}
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Notes internes</h3>
                                <p class="mt-2 text-sm text-slate-300 whitespace-pre-wrap" x-text="selectedRequest?.admin_notes || '–'"></p>
                            </div>

                            {{-- Timeline (registration_logs) --}}
                            <div x-show="selectedRequest?.registration_logs?.length">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Timeline</h3>
                                <ul class="mt-3 space-y-2 border-l-2 border-white/10 pl-4">
                                    <template x-for="(log, i) in (selectedRequest?.registration_logs || []).slice().reverse()" :key="i">
                                        <li class="text-sm">
                                            <span class="font-medium text-white" x-text="log.action"></span>
                                            <span class="text-slate-500" x-text="' · ' + (log.user_name || '') + ' · ' + (log.timestamp || '')"></span>
                                            <p class="mt-0.5 text-slate-400" x-show="log.note" x-text="log.note"></p>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            {{-- Actions (only if pending) --}}
                            <div x-show="selectedRequest?.status === 'pending'" class="space-y-4 pt-4 border-t border-white/5">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Actions</h3>

                                @if($plans->isEmpty())
                                <p class="text-sm text-amber-400">Aucun plan actif. <a href="{{ route('app.admin.plans.index') }}" class="underline hover:text-amber-300">Créez un plan</a> pour pouvoir approuver.</p>
                                @else
                                {{-- Approve form --}}
                                <form :action="`{{ url('/app/registration-requests') }}/${selectedRequest?.id}/approve`" method="post" class="rounded-xl border border-white/5 bg-white/5 p-4 space-y-3" x-show="!showRejectForm">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-medium text-slate-400">Plan <span class="text-red-400">*</span></label>
                                        <select name="plan_id" required class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                                            @foreach ($plans as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} — {{ number_format($p->monthly_price, 0, ',', ' ') }} MAD/mois</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-400">Durée de l'essai (jours)</label>
                                        <select name="trial_days" class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                                            <option value="0">Aucun</option>
                                            <option value="7">7 jours</option>
                                            <option value="14" selected>14 jours</option>
                                            <option value="30">30 jours</option>
                                        </select>
                                    </div>
                                    <input type="text" name="custom_pricing" placeholder="Tarification personnalisée (optionnel)" class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50">
                                    <textarea name="admin_notes" rows="2" placeholder="Note interne admin" class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50"></textarea>
                                    <button type="submit" class="glm-btn-primary w-full">Approuver la demande</button>
                                </form>
                                @endif

                                {{-- Ask for info --}}
                                <form :action="`{{ url('/app/registration-requests') }}/${selectedRequest?.id}/ask-info`" method="post" class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4 space-y-3">
                                    @csrf
                                    <label class="block text-xs font-medium text-slate-400">Demander des informations au demandeur</label>
                                    <textarea name="message" required rows="2" placeholder="Message / informations à demander..." class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50"></textarea>
                                    <textarea name="admin_notes" rows="1" placeholder="Note interne (optionnel)" class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50"></textarea>
                                    <button type="submit" class="w-full rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2.5 text-sm font-medium text-amber-300 hover:bg-amber-500/20 transition">Demander des infos</button>
                                </form>

                                {{-- Reject form --}}
                                <div x-show="showRejectForm" class="rounded-xl border border-white/5 bg-white/5 p-4 space-y-3">
                                    <form :action="`{{ url('/app/registration-requests') }}/${selectedRequest?.id}/reject`" method="post" class="space-y-3">
                                        @csrf
                                        <label class="block text-xs font-medium text-slate-400">Raison du refus <span class="text-red-400">*</span></label>
                                        <textarea name="rejection_reason" required rows="3" placeholder="Indiquez la raison du refus..." class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50"></textarea>
                                        <textarea name="admin_notes" rows="2" placeholder="Note interne admin" class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50"></textarea>
                                        <div class="flex gap-3">
                                            <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-500 transition-colors">Refuser</button>
                                            <button type="button" @click="showRejectForm = false" class="glm-btn-secondary">Annuler</button>
                                        </div>
                                    </form>
                                </div>

                                <button type="button" x-show="!showRejectForm" @click="showRejectForm = true" class="glm-btn-secondary w-full">Refuser la demande</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function approvalCenter() {
    return {
        drawerOpen: false,
        selectedRequest: null,
        showRejectForm: false,
        menuOpen: false,
        menuRequestId: null,
        menuRequestData: null,
        menuRect: null,
        init() {},
        openMenu(ev, requestId) {
            const row = ev.target.closest('tr');
            if (!row || !row.dataset.request) return;
            this.menuRequestData = JSON.parse(row.dataset.request);
            this.menuRequestId = requestId;
            this.menuRect = ev.target.closest('button').getBoundingClientRect();
            this.menuOpen = true;
        },
        closeMenu() {
            this.menuOpen = false;
        },
        openDrawer(ev) {
            const row = ev.target.closest('tr');
            if (!row || !row.dataset.request) return;
            this.selectedRequest = JSON.parse(row.dataset.request);
            this.showRejectForm = false;
            this.drawerOpen = true;
            this.closeMenu();
        },
        openDrawerFromMenu() {
            if (this.menuRequestData) {
                this.selectedRequest = this.menuRequestData;
                this.showRejectForm = false;
                this.drawerOpen = true;
            }
            this.closeMenu();
        }
    };
}
</script>
@endpush
@endsection
