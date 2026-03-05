@extends('app.layouts.app')

@section('pageSubtitle')
Équipe GLM – Super Admin & Support
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    {{-- Header --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Utilisateurs plateforme</h1>
            <p class="mt-2 max-w-2xl text-base text-slate-400">
                Gérer les comptes de l’équipe GLM (super administrateurs et support).
            </p>
        </div>
        <div class="flex shrink-0 gap-3">
            <a href="{{ route('app.dashboard') }}" class="glm-btn-secondary inline-flex no-underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                Tableau de bord
            </a>
            <a href="{{ route('app.admin.users.create') }}" class="glm-btn-primary inline-flex no-underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Ajouter un utilisateur
            </a>
        </div>
    </header>

    @if (session('new_password'))
        <div class="glm-fade-in rounded-2xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200" role="alert">
            <p class="font-semibold">Mot de passe réinitialisé. Transmettez-le à l’utilisateur (affiché une seule fois) :</p>
            <p class="mt-2 font-mono text-base break-all select-all">{{ session('new_password') }}</p>
            <p class="mt-1 text-amber-300/90 text-xs">Copiez ce mot de passe et transmettez-le de manière sécurisée.</p>
        </div>
    @endif
    @if (session('success'))
        <div class="glm-fade-in rounded-2xl bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 text-sm text-emerald-300" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="glm-fade-in rounded-2xl bg-red-500/10 border border-red-500/20 px-4 py-3 text-sm text-red-300" role="alert">{{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <section class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <a href="{{ route('app.admin.users.index', array_merge(request()->query(), ['status' => null])) }}" class="glm-stat-card glm-stat-card-accent-blue block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">Total</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs font-medium text-blue-400">Utilisateurs plateforme</p>
        </a>
        <a href="{{ route('app.admin.users.index', array_merge(request()->query(), ['status' => 'active'])) }}" class="glm-stat-card glm-stat-card-accent-emerald block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">Actifs</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['active'] }}</p>
            <p class="mt-1 text-xs font-medium text-emerald-400">En activité</p>
        </a>
        <a href="{{ route('app.admin.users.index', array_merge(request()->query(), ['status' => 'suspended'])) }}" class="glm-stat-card glm-stat-card-accent-amber block p-6 no-underline">
            <p class="text-sm font-medium text-slate-400">Suspendus</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-white">{{ $stats['suspended'] }}</p>
            <p class="mt-1 text-xs font-medium text-amber-400">Comptes désactivés</p>
        </a>
    </section>

    {{-- Filters --}}
    <form method="get" action="{{ route('app.admin.users.index') }}" class="glm-card-static flex flex-wrap items-end gap-5 p-5">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="filter-search" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Recherche</label>
                <input type="text" id="filter-search" name="search" value="{{ request('search') }}" placeholder="Nom, email…" class="w-56 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50">
            </div>
            <div>
                <label for="filter-status" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Statut</label>
                <select id="filter-status" name="status" class="w-40 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                </select>
            </div>
            <div>
                <label for="filter-role" class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-500">Rôle</label>
                <select id="filter-role" name="role" class="w-44 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="">Tous</option>
                    <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="support" {{ request('role') === 'support' ? 'selected' : '' }}>Support</option>
                </select>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Appliquer</button>
            @if (request()->hasAny(['search', 'status', 'role']))
                <a href="{{ route('app.admin.users.index') }}" class="glm-btn-secondary no-underline">Effacer</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="glm-table-wrap overflow-hidden rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Rôle</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Statut</th>
                        <th class="w-0 px-6 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($users as $u)
                        <tr class="transition-colors hover:bg-white/[0.03]">
                            <td class="px-6 py-4 font-medium text-white">{{ $u->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $u->email }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">
                                @if ($u->role === 'super_admin')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-[#2563EB]/20 text-blue-300">Super Admin</span>
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium glm-badge-role-support">Support</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($u->status === 'active')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium glm-badge-approved">Actif</span>
                                @else
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium glm-badge-pending">Suspendu</span>
                                @endif
                            </td>
                            <td class="relative px-6 py-4" x-data="{ open: false }">
                                <button type="button" @click="open = !open" @click.outside="open = false" class="rounded-lg p-2 text-slate-400 hover:bg-white/5 hover:text-white transition-colors" aria-haspopup="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                </button>
                                <div x-show="open" x-transition class="glm-dark-bg absolute right-6 top-full z-50 mt-1 w-56 origin-top-right rounded-xl border border-white/5 bg-slate-800 py-2 shadow-xl" style="display: none;">
                                    <a href="{{ route('app.admin.users.edit', $u) }}" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-white/5 hover:text-white">Modifier le rôle</a>
                                    @if ($u->status === 'suspended')
                                        <form action="{{ route('app.admin.users.activate', $u) }}" method="post" class="block">
                                            @csrf
                                            <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-slate-300 hover:bg-white/5 hover:text-white">Activer le compte</button>
                                        </form>
                                    @else
                                        @if ($u->id !== auth()->id())
                                            <form action="{{ route('app.admin.users.suspend', $u) }}" method="post" class="block">
                                                @csrf
                                                <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-slate-300 hover:bg-white/5 hover:text-white">Suspendre le compte</button>
                                            </form>
                                        @endif
                                    @endif
                                    <form action="{{ route('app.admin.users.force-password-reset', $u) }}" method="post" class="block">
                                        @csrf
                                        <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-slate-300 hover:bg-white/5 hover:text-white">Forcer réinitialisation mot de passe</button>
                                    </form>
                                    @if ($u->id !== auth()->id() && (auth()->user()->role === 'super_admin' || $u->role !== 'super_admin'))
                                        <form action="{{ route('app.admin.users.destroy', $u) }}" method="post" class="block" onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-red-400 hover:bg-red-500/10">Supprimer</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/5 text-slate-500">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold text-white">Aucun utilisateur</h3>
                                    <p class="mt-2 max-w-sm text-sm text-slate-400">Aucun utilisateur plateforme ne correspond aux filtres.</p>
                                    <a href="{{ route('app.admin.users.create') }}" class="mt-6 glm-btn-primary no-underline">Ajouter un utilisateur</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-white/5 px-6 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
