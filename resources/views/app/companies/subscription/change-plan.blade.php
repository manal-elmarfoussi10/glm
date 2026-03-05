@extends('app.layouts.app')

@section('pageSubtitle')
Changer de plan – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-white">Changer de plan</h1>
            <p class="mt-1 text-sm text-slate-400">Attribuer un plan d’abonnement à cette entreprise.</p>
        </div>
    </header>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif

    <div class="glm-card-static p-6 max-w-xl">
        <h2 class="text-lg font-semibold text-white mb-4">Plan actuel</h2>
        <p class="text-slate-200">{{ $subscription->plan?->name ?? 'Aucun plan' }}</p>
        @if ($subscription->plan?->monthly_price !== null)
            <p class="text-sm text-slate-400 mt-1">{{ number_format($subscription->plan->monthly_price, 0, ',', ' ') }} MAD / mois</p>
        @endif

        <form action="{{ route('app.companies.subscription.update-plan', $company) }}" method="post" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="plan_id" class="mb-1.5 block text-sm font-medium text-slate-300">Nouveau plan <span class="text-red-400">*</span></label>
                <select id="plan_id" name="plan_id" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                    @foreach ($plans as $p)
                        <option value="{{ $p->id }}" {{ old('plan_id', $subscription->plan_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} – {{ number_format($p->monthly_price ?? 0, 0, ',', ' ') }} MAD/mois
                        </option>
                    @endforeach
                </select>
                @error('plan_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2">
                <a href="{{ route('app.companies.show', $company) }}" class="glm-btn-secondary no-underline">Annuler</a>
                <button type="submit" class="glm-btn-primary">Enregistrer le plan</button>
            </div>
        </form>
    </div>
</div>
@endsection
