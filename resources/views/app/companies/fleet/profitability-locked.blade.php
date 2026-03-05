@extends('app.layouts.app')

@section('pageSubtitle')
Rentabilité flotte – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header>
        <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
        <h1 class="text-2xl font-bold tracking-tight text-white">Rentabilité flotte</h1>
        <p class="mt-1 text-sm text-slate-400">Revenus, coûts et profit par véhicule.</p>
    </header>

    <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 backdrop-blur-xl p-8 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-center">
        <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/20 mb-4">
            <svg class="h-8 w-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h2 class="text-xl font-bold text-white">Fonctionnalité réservée aux plans Pro et Business</h2>
        <p class="mt-2 text-slate-300 max-w-md mx-auto">
            La rentabilité par véhicule (revenus, coûts, profit, ROI) est disponible sur les offres Pro et Business. Passez à un plan supérieur pour débloquer cette analyse.
        </p>
        <a href="{{ route('app.companies.upgrade', $company) }}" class="mt-6 inline-flex glm-btn-primary no-underline">
            Voir les plans et mettre à niveau
        </a>
    </div>
</div>
@endsection
