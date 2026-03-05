@extends('app.layouts.app')

@section('pageSubtitle')
Recherche partenaires – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header>
        <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
        <h1 class="text-2xl font-bold tracking-tight text-white">Recherche disponibilité partenaires</h1>
        <p class="mt-1 text-sm text-slate-400">Recherchez la disponibilité chez d’autres agences partenaires (ville, dates, catégorie).</p>
    </header>

    <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 backdrop-blur-xl p-8 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-center">
        <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/20 mb-4">
            <svg class="h-8 w-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-white">Fonctionnalité réservée aux plans Pro et Business</h2>
        <p class="mt-2 text-slate-300 max-w-md mx-auto">
            La recherche de disponibilité chez les partenaires est disponible sur les offres Pro et Business.
        </p>
        <a href="{{ route('app.companies.upgrade', $company) }}" class="mt-6 inline-flex glm-btn-primary no-underline">Voir les plans</a>
    </div>
</div>
@endsection
