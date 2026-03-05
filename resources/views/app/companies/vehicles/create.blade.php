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
            <p class="mt-1 text-sm text-slate-400">Identification, tarification, conformité Maroc et financement.</p>
        </div>
    </header>

    <form action="{{ route('app.companies.vehicles.store', $company) }}" method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('app.companies.vehicles._form_sections', ['vehicle' => null, 'branches' => $branches, 'preselected_branch_id' => $preselected_branch_id ?? null])
        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Créer le véhicule</button>
            <a href="{{ route('app.companies.vehicles.index', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
        </div>
    </form>
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection
