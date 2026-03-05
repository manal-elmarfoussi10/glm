@extends('app.layouts.app')

@section('pageSubtitle')
Nouvel utilisateur plateforme
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.admin.users.index') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block transition-colors">← Retour à la liste</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Nouvel utilisateur plateforme</h1>
            <p class="mt-1 text-sm text-slate-400">Créer un compte Super Admin ou Support pour l’équipe GLM.</p>
        </div>
    </div>

    <form action="{{ route('app.admin.users.store') }}" method="post" class="glm-card-static p-6 max-w-2xl space-y-6">
        @csrf

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="mb-1.5 block text-sm font-medium text-slate-300">Nom <span class="text-red-400">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Prénom Nom">
                @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-300">Email <span class="text-red-400">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="email@glm.ma">
                @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-300">Mot de passe <span class="text-red-400">*</span></label>
                <input type="password" id="password" name="password" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Min. 8 caractères">
                @error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="role" class="mb-1.5 block text-sm font-medium text-slate-300">Rôle <span class="text-red-400">*</span></label>
                <select id="role" name="role" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    @foreach ($allowedRoles as $value => $label)
                        <option value="{{ $value }}" {{ old('role', 'support') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-slate-300">Statut <span class="text-red-400">*</span></label>
                <select id="status" name="status" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                </select>
                @error('status')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('app.admin.users.index') }}" class="glm-btn-secondary no-underline">Annuler</a>
            <button type="submit" class="glm-btn-primary">Créer l’utilisateur</button>
        </div>
    </form>
</div>
@endsection
