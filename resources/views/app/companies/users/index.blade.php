@extends('app.layouts.app')

@section('pageSubtitle')
Utilisateurs de l’entreprise
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block transition-colors">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Utilisateurs</h1>
            <p class="mt-1 text-sm text-slate-400">Gérer les comptes associés à cette entreprise.</p>
        </div>
        <a href="{{ route('app.companies.users.create', $company) }}" class="glm-btn-primary inline-flex no-underline">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Ajouter un utilisateur
        </a>
    </header>

    <div class="glm-table-wrap overflow-hidden rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Téléphone</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Rôle</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($users as $user)
                        <tr class="transition-colors hover:bg-white/[0.03]">
                            <td class="px-6 py-4 font-medium text-white">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $user->phone ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $user->role }}</td>
                            <td class="px-6 py-4">
                                @if ($user->status === 'active')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium glm-badge-approved">Actif</span>
                                @elseif ($user->status === 'suspended')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium glm-badge-pending">Suspendu</span>
                                @else
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium text-slate-400">Invité</span>
                                @endif
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
                                    <p class="mt-2 max-w-sm text-sm text-slate-400">Ajoutez le premier utilisateur pour cette entreprise.</p>
                                    <a href="{{ route('app.companies.users.create', $company) }}" class="mt-6 glm-btn-primary no-underline">Ajouter un utilisateur</a>
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
