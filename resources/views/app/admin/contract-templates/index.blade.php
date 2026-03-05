@extends('app.layouts.app')

@section('pageSubtitle')
Modèles globaux GLM – réservés Super Admin
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Bibliothèque de contrats</h1>
            <p class="mt-2 max-w-2xl text-base text-slate-400">
                Modèles globaux GLM. Les entreprises peuvent les dupliquer en modèles personnalisés (non écrasés par les mises à jour globales).
            </p>
        </div>
        <div class="flex shrink-0 gap-3">
            <a href="{{ route('app.dashboard') }}" class="glm-btn-secondary inline-flex no-underline">Tableau de bord</a>
            <a href="{{ route('app.admin.contract-templates.create') }}" class="glm-btn-primary inline-flex no-underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Nouveau modèle
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
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Slug</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Version</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Modifié</th>
                        <th class="w-0 px-6 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($templates as $t)
                        <tr class="transition-colors hover:bg-white/[0.03]">
                            <td class="px-6 py-4 font-medium text-white">{{ $t->name }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-slate-400">{{ $t->slug }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $t->version ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $t->updated_at?->format('d/m/Y H:i') ?? '–' }}</td>
                            <td class="px-6 py-4 flex items-center gap-2">
                                <a href="{{ route('app.admin.contract-templates.show', $t) }}" target="_blank" rel="noopener" class="glm-btn-secondary text-sm py-2 no-underline">Voir</a>
                                <a href="{{ route('app.admin.contract-templates.edit', $t) }}" class="glm-btn-secondary text-sm py-2 no-underline">Modifier</a>
                                <form action="{{ route('app.admin.contract-templates.destroy', $t) }}" method="post" class="inline" onsubmit="return confirm('Supprimer ce modèle global ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-400 hover:text-red-300 transition-colors">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                                    <p class="text-slate-400">Aucun modèle global. Créez le premier pour que les entreprises puissent le dupliquer.</p>
                                    <a href="{{ route('app.admin.contract-templates.create') }}" class="mt-4 glm-btn-primary no-underline">Nouveau modèle</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($templates->hasPages())
            <div class="border-t border-white/5 px-6 py-4">{{ $templates->links() }}</div>
        @endif
    </div>
</div>
@endsection
