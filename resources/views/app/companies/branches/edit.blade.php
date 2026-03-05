@extends('app.layouts.app')

@section('pageSubtitle')
Modifier {{ $branch->name }} – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.branches.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Agences · {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Modifier {{ $branch->name }}</h1>
            <p class="mt-1 text-sm text-slate-400">Nom, adresse, téléphone et responsable.</p>
        </div>
    </header>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif

    <form action="{{ route('app.companies.branches.update', [$company, $branch]) }}" method="post" class="space-y-6">
        @csrf
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Informations</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-300">Nom *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $branch->name) }}" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="city" class="mb-1 block text-sm font-medium text-slate-300">Ville</label>
                    <input type="text" id="city" name="city" value="{{ old('city', $branch->city) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    @error('city')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="mb-1 block text-sm font-medium text-slate-300">Adresse</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $branch->address) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    @error('address')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-slate-300">Téléphone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $branch->phone) }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                    @error('phone')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="manager_id" class="mb-1 block text-sm font-medium text-slate-300">Responsable</label>
                    <select id="manager_id" name="manager_id" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                        <option value="">– Aucun –</option>
                        @foreach ($managers as $u)
                            <option value="{{ $u->id }}" {{ old('manager_id', $branch->manager_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    @error('manager_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-slate-300">Statut *</label>
                    <select id="status" name="status" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white">
                        <option value="active" {{ old('status', $branch->status) === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="suspended" {{ old('status', $branch->status) === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="glm-btn-primary">Enregistrer</button>
            <a href="{{ route('app.companies.branches.index', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
        </div>
    </form>
</div>
@endsection
