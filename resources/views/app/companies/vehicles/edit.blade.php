@extends('app.layouts.app')

@section('pageSubtitle')
Modifier {{ $vehicle->plate }} – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.vehicles.show', [$company, $vehicle]) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $vehicle->plate }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Modifier le véhicule</h1>
            <p class="mt-1 text-sm text-slate-400">Formulaire complet · Photo, tarification, conformité, financement.</p>
        </div>
        <a href="{{ route('app.companies.vehicles.show', [$company, $vehicle]) }}" class="glm-btn-secondary no-underline">Voir la fiche</a>
    </header>

    <form action="{{ route('app.companies.vehicles.update', [$company, $vehicle]) }}" method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('put')
        @include('app.companies.vehicles._form_sections', ['vehicle' => $vehicle, 'branches' => $branches])
        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Enregistrer</button>
            <a href="{{ route('app.companies.vehicles.show', [$company, $vehicle]) }}" class="glm-btn-secondary no-underline">Annuler</a>
        </div>
    </form>
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection
