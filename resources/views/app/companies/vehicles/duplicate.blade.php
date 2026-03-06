@extends('app.layouts.app')

@section('pageSubtitle')
Dupliquer {{ $vehicle->plate }} – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header>
        <a href="{{ route('app.companies.vehicles.show', [$company, $vehicle]) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $vehicle->plate }}</a>
        <h1 class="text-2xl font-bold tracking-tight text-white">Dupliquer ce véhicule</h1>
        <p class="mt-1 text-sm text-slate-400">Un nouveau véhicule sera créé avec les mêmes infos (marque, modèle, tarifs, assurance, etc.). Indiquez uniquement la nouvelle plaque.</p>
    </header>

    <div class="glm-card-static p-6 max-w-md">
        <p class="text-sm text-slate-300 mb-4">Véhicule à dupliquer : <strong class="text-white">{{ $vehicle->brand }} {{ $vehicle->model }}@if($vehicle->year) ({{ $vehicle->year }})@endif</strong> · {{ $vehicle->branch->name ?? '–' }}</p>
        <form action="{{ route('app.companies.vehicles.store-duplicate', [$company, $vehicle]) }}" method="post">
            @csrf
            <div class="mb-4">
                <label for="plate" class="mb-1 block text-sm font-medium text-slate-300">Nouvelle plaque *</label>
                <input type="text" id="plate" name="plate" value="{{ old('plate') }}" required autofocus class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white" placeholder="12345-A-2">
                @error('plate')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="glm-btn-primary">Créer le véhicule (copie)</button>
                <a href="{{ route('app.companies.vehicles.show', [$company, $vehicle]) }}" class="glm-btn-secondary no-underline">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
