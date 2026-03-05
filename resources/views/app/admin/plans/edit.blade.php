@extends('app.layouts.app')

@section('pageSubtitle')
Modifier le plan
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('app.admin.plans.index') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block transition-colors">← Plans & tarifs</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Modifier – {{ $plan->name }}</h1>
        </div>
    </div>

    <form action="{{ route('app.admin.plans.update', $plan) }}" method="post" class="glm-card-static p-6 max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-slate-300">Nom du plan <span class="text-red-400">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $plan->name) }}" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
            @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="monthly_price" class="mb-1.5 block text-sm font-medium text-slate-300">Prix mensuel (MAD) <span class="text-red-400">*</span></label>
                <input type="number" id="monthly_price" name="monthly_price" value="{{ old('monthly_price', $plan->monthly_price) }}" min="0" step="0.01" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                @error('monthly_price')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="yearly_price" class="mb-1.5 block text-sm font-medium text-slate-300">Prix annuel (MAD) <span class="text-red-400">*</span></label>
                <input type="number" id="yearly_price" name="yearly_price" value="{{ old('yearly_price', $plan->yearly_price) }}" min="0" step="0.01" required class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                @error('yearly_price')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="trial_days" class="mb-1.5 block text-sm font-medium text-slate-300">Jours d’essai <span class="text-red-400">*</span></label>
            <input type="number" id="trial_days" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" min="0" required class="w-24 rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
        </div>

        <div>
            <h3 class="text-sm font-semibold text-white mb-3">Limites</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="limit_vehicles" class="mb-1 block text-xs font-medium text-slate-400">Véhicules</label>
                    <input type="number" id="limit_vehicles" name="limit_vehicles" value="{{ old('limit_vehicles', $plan->limit_vehicles) }}" min="0" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                </div>
                <div>
                    <label for="limit_users" class="mb-1 block text-xs font-medium text-slate-400">Utilisateurs</label>
                    <input type="number" id="limit_users" name="limit_users" value="{{ old('limit_users', $plan->limit_users) }}" min="0" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                </div>
                <div>
                    <label for="limit_branches" class="mb-1 block text-xs font-medium text-slate-400">Agences</label>
                    <input type="number" id="limit_branches" name="limit_branches" value="{{ old('limit_branches', $plan->limit_branches) }}" min="0" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="ai_access" value="0">
                <input type="checkbox" name="ai_access" value="1" {{ old('ai_access', $plan->ai_access) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB] focus:ring-[#2563EB]/50">
                <span class="text-sm text-slate-300">Accès IA</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="custom_contracts" value="0">
                <input type="checkbox" name="custom_contracts" value="1" {{ old('custom_contracts', $plan->custom_contracts) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB] focus:ring-[#2563EB]/50">
                <span class="text-sm text-slate-300">Contrats personnalisés</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} class="rounded border-white/20 bg-white/5 text-[#2563EB] focus:ring-[#2563EB]/50">
                <span class="text-sm text-slate-300">Plan actif</span>
            </label>
        </div>

        <div>
            <label for="features_limits_json" class="mb-1.5 block text-sm font-medium text-slate-300">Fonctionnalités & limites (JSON)</label>
            <textarea id="features_limits_json" name="features_limits_json" rows="8" class="w-full rounded-xl border-0 bg-white/5 px-4 py-2.5 text-sm text-white font-mono focus:ring-2 focus:ring-[#2563EB]/50" placeholder='{"features":{"reports":true,"contracts":true,"branches":true},"limits":{"vehicles":10,"users":5,"branches":2}}'>{{ old('features_limits_json', $plan->features_limits ? json_encode($plan->features_limits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Optionnel. <code class="text-slate-400">features</code> : clés reports, contracts, damages, payments, alerts, branches, reservations, fleet, customers (true/false). <code class="text-slate-400">limits</code> : vehicles, users, branches (nombre ou null = illimité).</p>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('app.admin.plans.index') }}" class="glm-btn-secondary no-underline">Annuler</a>
            <button type="submit" class="glm-btn-primary">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
