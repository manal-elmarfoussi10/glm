@extends('app.layouts.app')

@section('pageSubtitle')
Modifier l’entreprise
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block transition-colors">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Modifier l’entreprise</h1>
            <p class="mt-1 text-sm text-slate-400">Mettez à jour les informations de l’entreprise.</p>
        </div>
    </div>

    <form action="{{ route('app.companies.update', $company) }}" method="post" class="glm-card-static p-6 max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="mb-1.5 block text-sm font-medium text-slate-300">Nom de l’entreprise <span class="text-red-400">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $company->name) }}" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Ex. Atlas Rent Cars">
                @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="ice" class="mb-1.5 block text-sm font-medium text-slate-300">ICE</label>
                <input type="text" id="ice" name="ice" value="{{ old('ice', $company->ice) }}" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Identifiant commun entreprise">
                @error('ice')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-300">Téléphone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $company->phone) }}" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="+212 6…">
                @error('phone')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-300">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $company->email) }}" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="contact@entreprise.ma">
                @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="city" class="mb-1.5 block text-sm font-medium text-slate-300">Ville</label>
                <input type="text" id="city" name="city" value="{{ old('city', $company->city) }}" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Casablanca">
                @error('city')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="plan" class="mb-1.5 block text-sm font-medium text-slate-300">Plan</label>
                <select id="plan" name="plan" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="starter" {{ old('plan', $company->plan) === 'starter' ? 'selected' : '' }}>Starter</option>
                    <option value="professional" {{ old('plan', $company->plan) === 'professional' ? 'selected' : '' }}>Professional</option>
                    <option value="enterprise" {{ old('plan', $company->plan) === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
                @error('plan')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="mb-1.5 block text-sm font-medium text-slate-300">Adresse</label>
                <textarea id="address" name="address" rows="2" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50" placeholder="Adresse complète">{{ old('address', $company->address) }}</textarea>
                @error('address')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-slate-300">Statut</label>
                <select id="status" name="status" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    <option value="active" {{ old('status', $company->status) === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="suspended" {{ old('status', $company->status) === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                </select>
                @error('status')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('app.companies.show', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
            <button type="submit" class="glm-btn-primary">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
