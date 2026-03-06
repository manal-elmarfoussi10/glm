@extends('app.layouts.app')

@section('pageSubtitle')
Ajouter un utilisateur
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.companies.users.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block transition-colors">← Utilisateurs · {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Nouvel utilisateur</h1>
            <p class="mt-1 text-sm text-slate-400">Créer un compte pour cette entreprise.</p>
        </div>
    </div>

    <form action="{{ route('app.companies.users.store', $company) }}" method="post" class="glm-card-static p-6 max-w-2xl space-y-6">
        @csrf

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="mb-1.5 block text-sm font-medium text-slate-300">Nom <span class="text-red-400">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Prénom Nom">
                @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-300">Email <span class="text-red-400">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="email@exemple.ma">
                @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-300">Téléphone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="+212 6…">
                @error('phone')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-300">Mot de passe</label>
                <input type="password" id="password" name="password" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Laisser vide pour générer un mot de passe">
                <p class="mt-1 text-xs text-slate-500">Min. 8 caractères. Vide = génération automatique.</p>
                @error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="role" class="mb-1.5 block text-sm font-medium text-slate-300">Rôle <span class="text-red-400">*</span></label>
                <select id="role" name="role" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="company_admin" {{ old('role') === 'company_admin' ? 'selected' : '' }}>Administrateur</option>
                    <option value="staff" {{ old('role', 'staff') === 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
                @error('role')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-slate-300">Statut <span class="text-red-400">*</span></label>
                <select id="status" name="status" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                    <option value="invited" {{ old('status') === 'invited' ? 'selected' : '' }}>Invité</option>
                </select>
                @error('status')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('app.companies.users.index', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
            <button type="submit" class="glm-btn-primary">Créer l’utilisateur</button>
        </div>
    </form>
</div>
@endsection
