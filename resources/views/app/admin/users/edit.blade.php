@extends('app.layouts.app')

@section('pageSubtitle')
Modifier l’utilisateur plateforme
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.admin.users.index') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block transition-colors">← Retour à la liste</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Modifier l’utilisateur</h1>
            <p class="mt-1 text-sm text-slate-400">{{ $user->email }}</p>
        </div>
    </div>

    <form action="{{ route('app.admin.users.update', $user) }}" method="post" class="glm-card-static p-6 max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="mb-1.5 block text-sm font-medium text-slate-300">Nom <span class="text-red-400">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50">
                @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <p class="text-sm text-slate-500">Email : <span class="text-slate-300">{{ $user->email }}</span> (non modifiable)</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Rôle</label>
                @if ($canChangeRole ?? true)
                    <select id="role" name="role" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                        @foreach ($allowedRoles as $value => $label)
                            <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <p class="rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-slate-300">{{ $allowedRoles[$user->role] ?? $user->role }}</p>
                    <input type="hidden" name="role" value="{{ $user->role }}">
                @endif
                @error('role')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-slate-300">Statut <span class="text-red-400">*</span></label>
                <select id="status" name="status" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                </select>
                @error('status')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <a href="{{ route('app.admin.users.index') }}" class="glm-btn-secondary no-underline">Annuler</a>
            <button type="submit" class="glm-btn-primary">Enregistrer</button>
            <form action="{{ route('app.admin.users.force-password-reset', $user) }}" method="post" class="inline">
                @csrf
                <button type="submit" class="glm-btn-secondary">Forcer réinit. mot de passe</button>
            </form>
            @if ($user->id !== auth()->id() && (auth()->user()->role === 'super_admin' || $user->role !== 'super_admin'))
                <form action="{{ route('app.admin.users.destroy', $user) }}" method="post" class="inline" onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm font-medium text-red-400 hover:bg-red-500/20 transition-colors">Supprimer</button>
                </form>
            @endif
        </div>
    </form>
</div>
@endsection
