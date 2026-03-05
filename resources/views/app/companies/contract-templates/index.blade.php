@extends('app.layouts.app')

@section('pageSubtitle')
Modèles de contrats – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-[color:var(--muted)] hover:text-[color:var(--text)] mb-2 inline-block transition-colors">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-[color:var(--text)]">Modèles de contrats</h1>
            <p class="mt-1 text-sm text-[color:var(--muted)]">
                Modèles propres à cette entreprise. Vous pouvez dupliquer un modèle global (les mises à jour globales n’écrasent pas vos copies) ou en créer un nouveau.
            </p>
        </div>
        <div class="flex shrink-0 gap-3">
            <a href="{{ route('app.companies.contract-templates.create', $company) }}" class="glm-btn-primary inline-flex no-underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Nouveau modèle
            </a>
        </div>
    </header>

    @if (session('success'))
        <div class="glm-fade-in rounded-2xl bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 text-sm text-emerald-300" role="alert">{{ session('success') }}</div>
    @endif

    {{-- Default template --}}
    <div class="glm-card-static p-5">
        <h2 class="text-base font-semibold text-[color:var(--text)] mb-3">Modèle par défaut (réservations / contrats)</h2>
        <form action="{{ route('app.companies.contract-templates.set-default', $company) }}" method="post" class="flex flex-wrap items-center gap-3">
            @csrf
            <select name="template_id" class="rounded-xl border-0 bg-[color:var(--surface-2)] px-4 py-2.5 text-sm text-[color:var(--text)] focus:ring-2 focus:ring-[color:var(--primary)]/50 w-64">
                <option value="">— Aucun —</option>
                @foreach ($templates as $t)
                    <option value="{{ $t->id }}" {{ $company->default_contract_template_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="glm-btn-primary text-sm py-2">Enregistrer</button>
        </form>
    </div>

    {{-- Company templates list --}}
    <div class="glm-card-static overflow-hidden">
        <h2 class="text-base font-semibold text-[color:var(--text)] px-6 py-4 border-b border-[color:var(--border)]">Modèles de l’entreprise</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="border-b border-white/5 bg-[color:var(--surface-2)]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Version</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Origine</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-[color:var(--muted)]">Par défaut</th>
                        <th class="w-0 px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[color:var(--border)]">
                    @forelse ($templates as $t)
                        <tr class="hover:bg-[color:var(--surface-2)] transition-colors">
                            <td class="px-6 py-4 font-medium text-[color:var(--text)]">{{ $t->name }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-[color:var(--muted)]">{{ $t->slug }}</td>
                            <td class="px-6 py-4 text-sm text-[color:var(--muted)]">{{ $t->version ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-[color:var(--muted)]">
                                @if ($t->source_global_id)
                                    <span class="text-[color:var(--muted)]">Copie d’un modèle global</span>
                                @else
                                    <span class="text-[color:var(--muted)]">Création propre</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($company->default_contract_template_id == $t->id)
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium glm-badge-approved">Par défaut</span>
                                @else
                                    –
                                @endif
                            </td>
                            <td class="px-6 py-4 flex items-center gap-2">
                                <a href="{{ route('app.companies.contract-templates.edit', [$company, $t]) }}" class="glm-btn-secondary text-sm py-2 no-underline">Modifier</a>
                                <form action="{{ route('app.companies.contract-templates.destroy', [$company, $t]) }}" method="post" class="inline" onsubmit="return confirm('Supprimer ce modèle ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-400 hover:text-red-300">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[color:var(--muted)]">
                                Aucun modèle. <a href="{{ route('app.companies.contract-templates.create', $company) }}" class="text-[#60a5fa] hover:underline">Créer un modèle</a> ou dupliquer un modèle global ci‑dessous.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Copy from global --}}
    @if ($globalTemplates->isNotEmpty())
        <div class="glm-card-static p-6">
            <h2 class="text-base font-semibold text-[color:var(--text)] mb-3">Dupliquer un modèle global</h2>
            <p class="text-sm text-[color:var(--muted)] mb-4">La copie sera propre à cette entreprise. Les futures mises à jour du modèle global ne l’écraseront pas.</p>
            <ul class="space-y-2">
                @foreach ($globalTemplates as $gt)
                    <li class="flex items-center justify-between gap-4 rounded-xl border border-white/5 bg-[color:var(--surface-2)] px-4 py-3">
                        <div>
                            <span class="font-medium text-[color:var(--text)]">{{ $gt->name }}</span>
                            <span class="text-[color:var(--muted)] text-sm ml-2">({{ $gt->slug }})</span>
                        </div>
                        <a href="{{ route('app.companies.contract-templates.create', [$company, 'from_global' => $gt->id]) }}" class="glm-btn-primary text-sm py-2 no-underline">Dupliquer</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
