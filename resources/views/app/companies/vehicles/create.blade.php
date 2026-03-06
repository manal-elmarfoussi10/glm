@extends('app.layouts.app')

@section('pageSubtitle')
Nouveau véhicule – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.vehicles.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Flotte · {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Nouveau véhicule</h1>
            @if ($full ?? false)
                <p class="mt-1 text-sm text-slate-400">Identification, tarification, conformité Maroc et financement.</p>
            @else
                <p class="mt-1 text-sm text-slate-400">Saisissez les informations minimales. Vous compléterez le reste sur la fiche véhicule.</p>
            @endif
        </div>
    </header>

    @if ($full ?? false)
        <form action="{{ route('app.companies.vehicles.store', $company) }}" method="post" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('app.companies.vehicles._form_sections', ['vehicle' => null, 'branches' => $branches, 'preselected_branch_id' => $preselected_branch_id ?? null])
            <div class="flex flex-wrap gap-3 items-center">
                <button type="submit" class="glm-btn-primary">Créer le véhicule</button>
                <a href="{{ route('app.companies.vehicles.index', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
                <a href="{{ route('app.companies.vehicles.create', $company) }}" class="text-sm text-slate-400 hover:text-white no-underline">← Formulaire rapide</a>
            </div>
        </form>
    @else
        <form action="{{ route('app.companies.vehicles.store', $company) }}" method="post" class="space-y-6">
            @csrf
            <input type="hidden" name="quick_create" value="1">
            @include('app.companies.vehicles._form_quick', ['branches' => $branches, 'preselected_branch_id' => $preselected_branch_id ?? null])
            <div class="flex flex-wrap gap-3 items-center">
                <button type="submit" class="glm-btn-primary">Créer le véhicule</button>
                <a href="{{ route('app.companies.vehicles.index', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
                <a href="{{ route('app.companies.vehicles.create', $company) }}?full=1" class="text-sm text-slate-400 hover:text-white no-underline">Formulaire complet →</a>
            </div>
        </form>
    @endif
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection
