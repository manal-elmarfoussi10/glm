@extends('app.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 glm-fade-in">
    <div>
        <h1 class="text-2xl font-bold glm-text">Mon Profil</h1>
        <p class="glm-muted mt-1">Gérez vos informations personnelles et votre mot de passe.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left: User Info --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="p-6 rounded-3xl border bg-[color:var(--surface)] border-[color:var(--border)] shadow-[var(--shadow-soft)] flex flex-col items-center text-center">
                <div class="h-24 w-24 flex items-center justify-center rounded-3xl bg-[color:var(--primary)]/10 text-[color:var(--primary)] text-3xl font-bold mb-4">
                    {{ strtoupper(substr($user->name ?? 'A', 0, 2)) }}
                </div>
                <h2 class="text-xl font-bold glm-text">{{ $user->name }}</h2>
                <p class="text-sm glm-muted uppercase tracking-wider font-medium mt-1">{{ str_replace('_', ' ', $user->role) }}</p>
                <div class="mt-4 px-4 py-1.5 rounded-full bg-[color:var(--surface-2)] text-xs font-semibold glm-text border border-[color:var(--border)]">
                    {{ $user->company?->name ?? 'GLM Platform' }}
                </div>
            </div>

            <div class="p-6 rounded-3xl border bg-[color:var(--surface)] border-[color:var(--border)] shadow-[var(--shadow-soft)] space-y-4">
                <h3 class="font-bold text-sm uppercase tracking-widest glm-muted">Informations</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-sm glm-text">
                        <svg class="h-4 w-4 glm-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        <span class="truncate glm-text">{{ $user->email }}</span>
                    </div>
                    @if($user->phone)
                    <div class="flex items-center gap-3 text-sm glm-text">
                        <svg class="h-4 w-4 glm-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        <span>{{ $user->phone }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Forms --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Profile Details --}}
            <form action="{{ route('app.profile.update') }}" method="POST" class="p-8 rounded-3xl border bg-[color:var(--surface)] border-[color:var(--border)] shadow-[var(--shadow-soft)] space-y-6">
                @csrf
                @method('PATCH')
                <h3 class="text-lg font-bold glm-text">Détails du profil</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="name" class="text-sm font-medium glm-muted">Nom complet</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="glm-input block w-full rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-[color:var(--primary)]/50 focus:outline-none transition" required>
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium glm-muted">Adresse email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="glm-input block w-full rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-[color:var(--primary)]/50 focus:outline-none transition" required>
                    </div>
                    <div class="space-y-2">
                        <label for="phone" class="text-sm font-medium glm-muted">Téléphone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="glm-input block w-full rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-[color:var(--primary)]/50 focus:outline-none transition">
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="glm-btn-primary">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>

            {{-- Change Password --}}
            <form action="{{ route('app.profile.password') }}" method="POST" class="p-8 rounded-3xl border bg-[color:var(--surface)] border-[color:var(--border)] shadow-[var(--shadow-soft)] space-y-6">
                @csrf
                @method('PATCH')
                <h3 class="text-lg font-bold glm-text">Changer le mot de passe</h3>
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label for="current_password" class="text-sm font-medium glm-muted">Mot de passe actuel</label>
                        <input type="password" name="current_password" id="current_password" class="glm-input block w-full rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-[color:var(--primary)]/50 focus:outline-none transition" required>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-medium glm-muted">Nouveau mot de passe</label>
                            <input type="password" name="password" id="password" class="glm-input block w-full rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-[color:var(--primary)]/50 focus:outline-none transition" required>
                        </div>
                        <div class="space-y-2">
                            <label for="password_confirmation" class="text-sm font-medium glm-muted">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="glm-input block w-full rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-[color:var(--primary)]/50 focus:outline-none transition" required>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="glm-btn-dark px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg transition">
                        Mettre à jour le mot de passe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
