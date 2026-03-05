@extends('app.layouts.app')

@section('pageSubtitle')
Passer à un plan supérieur – {{ $company->name }}
@endsection

@section('content')
<div class="space-y-8 glm-fade-in" x-data="{ showLimitModal: {{ request()->query('limit') ? 'true' : 'false' }} }">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            @if (auth()->user()?->company_id == $company->id)
                <a href="{{ route('app.dashboard') }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Dashboard</a>
            @else
                <a href="{{ route('app.companies.show', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← {{ $company->name }}</a>
            @endif
            <h1 class="text-2xl font-bold tracking-tight text-white">Passer à un plan supérieur</h1>
            <p class="mt-1 text-sm text-slate-400">Débloquez plus de fonctionnalités et de limites pour votre entreprise.</p>
        </div>
    </header>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div class="rounded-xl border border-[#2563EB]/30 bg-[#2563EB]/10 px-4 py-3 text-sm text-[#93C5FD]">{{ session('info') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Current plan & locked modules --}}
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Votre plan actuel</h2>
            <p class="text-slate-200 font-medium">{{ $currentPlan?->name ?? 'Aucun plan attribué' }}</p>
            @if ($currentPlan?->monthly_price)
                <p class="text-sm text-slate-400 mt-1">{{ number_format($currentPlan->monthly_price, 0, ',', ' ') }} MAD / mois</p>
            @endif

            @if (count($lockedFeatures) > 0)
                <h3 class="text-base font-semibold text-white mt-6 mb-3">Modules verrouillés</h3>
                <ul class="space-y-2">
                    @foreach ($lockedFeatures as $key => $label)
                        <li class="flex items-center gap-3 rounded-xl border border-amber-500/20 bg-amber-500/5 px-4 py-2.5">
                            <svg class="h-5 w-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="text-white font-medium">{{ $label }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-4 text-sm text-slate-400">Tous les modules inclus dans votre plan sont accessibles.</p>
            @endif

            @if (count($limitsInfo) > 0)
                <h3 class="text-base font-semibold text-white mt-6 mb-3">Limites d’usage</h3>
                <ul class="space-y-2">
                    @foreach ($limitsInfo as $key => $info)
                        <li class="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 px-4 py-2.5">
                            <span class="text-slate-300">{{ $info['label'] }}</span>
                            <span class="{{ $info['reached'] ? 'text-amber-400 font-semibold' : 'text-slate-400' }}">
                                {{ $info['current'] }} / {{ $info['limit'] }}
                                @if ($info['reached'])
                                    <span class="text-xs">(limite atteinte)</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Request upgrade --}}
        <div class="glm-card-static p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Demander un upgrade</h2>
            @if ($pendingRequest)
                <div class="rounded-xl border border-[#2563EB]/30 bg-[#2563EB]/10 px-4 py-3 text-sm text-[#93C5FD] mb-4">
                    Vous avez une demande en attente pour le plan <strong>{{ $pendingRequest->requestedPlan->name ?? 'N/A' }}</strong>. Notre équipe va la traiter sous peu.
                    @if ($pendingRequest->message)
                        <p class="mt-2 text-white/90">Votre message : « {{ Str::limit($pendingRequest->message, 100) }} »</p>
                    @endif
                </div>
            @else
                <form action="{{ route('app.companies.upgrade-request.store', $company) }}" method="post" class="space-y-4">
                    @csrf
                    <div>
                        <label for="requested_plan_id" class="mb-1.5 block text-sm font-medium text-slate-300">Plan souhaité *</label>
                        <select id="requested_plan_id" name="requested_plan_id" required class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50">
                            @foreach ($plans as $p)
                                <option value="{{ $p->id }}" {{ $currentPlan?->id === $p->id ? 'disabled' : '' }}>
                                    {{ $p->name }} – {{ number_format($p->monthly_price ?? 0, 0, ',', ' ') }} MAD/mois
                                </option>
                            @endforeach
                        </select>
                        @error('requested_plan_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="message" class="mb-1.5 block text-sm font-medium text-slate-300">Message (optionnel)</label>
                        <textarea id="message" name="message" rows="3" placeholder="Précisez votre besoin ou contexte…" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-[#2563EB]/50">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <p class="text-xs text-slate-500">Votre demande sera examinée par notre équipe. Vous serez recontacté après validation.</p>
                    <button type="submit" class="glm-btn-primary">Envoyer la demande d’upgrade</button>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- Limit reached modal --}}
@if ($limitReached)
<div x-show="showLimitModal" x-cloak
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">
    <div @click.outside="showLimitModal = false"
         class="rounded-2xl border border-white/10 bg-[var(--color-navy-950)] p-6 shadow-xl max-w-md w-full"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center gap-3 mb-4">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/20">
                <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
            <div>
                <h3 class="text-lg font-bold text-white">Limite atteinte</h3>
                <p class="text-sm text-slate-400">Vous avez atteint la limite de votre plan pour cette ressource.</p>
            </div>
        </div>
        <p class="text-slate-300 text-sm mb-6">
            Passez à un plan supérieur pour augmenter vos limites et débloquer plus de fonctionnalités.
        </p>
        <div class="flex gap-3">
            <a href="{{ route('app.companies.upgrade', $company) }}" class="glm-btn-primary no-underline flex-1 text-center">Voir les plans</a>
            <button type="button" @click="showLimitModal = false" class="glm-btn-secondary">Fermer</button>
        </div>
    </div>
</div>
@endif
@endsection
