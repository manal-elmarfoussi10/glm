@extends('app.layouts.app')

@section('pageSubtitle')
{{ $company?->name ?? 'Tableau de bord' }}
@endsection

@php
    $company = $company ?? null;
    $isAgent = $isAgent ?? false;
    $branches = $branches ?? collect();
    $reservationsToday = $reservationsToday ?? collect();
    $pickupsToday = $pickupsToday ?? collect();
    $returnsToday = $returnsToday ?? collect();
    $vehiclesAvailableCount = $vehiclesAvailableCount ?? 0;
    $vehiclesCount = $vehiclesCount ?? 0;
    $revenueMonth = $revenueMonth ?? 0;
    $paymentsPending = $paymentsPending ?? 0;
    $returnsTodayCount = $returnsTodayCount ?? 0;
    $criticalAlertsCount = $criticalAlertsCount ?? 0;
    $alerts = $alerts ?? [];
    $chartRevenue30 = $chartRevenue30 ?? ['labels' => [], 'values' => []];
    $chartReservationsByStatus = $chartReservationsByStatus ?? ['labels' => ['Aucune'], 'values' => [1]];
    $chartFleetUtilization = $chartFleetUtilization ?? ['labels' => ['—'], 'values' => [0]];
    $todoItems = $todoItems ?? [];
    $hasAiAccess = $hasAiAccess ?? false;
    $lastActivityAt = $lastActivityAt ?? null;
    $onboardingChecklist = $onboardingChecklist ?? [];
    $showOnboardingChecklist = $showOnboardingChecklist ?? false;
@endphp

@section('content')
<div class="space-y-8 glm-fade-in">
    {{-- 1) Header + Actions --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Tableau de bord</h1>
            <p class="mt-1 text-sm text-slate-400">
                {{ $company?->name }}
                @if ($branches->isNotEmpty())
                    <span class="inline-flex items-center gap-1.5 ml-2">
                        <select class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1.5 text-sm text-white focus:ring-2 focus:ring-[#2563EB]/50 focus:border-transparent" aria-label="Agence">
                            <option>Toutes les agences</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </span>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if ($lastActivityAt)
                <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-400">Activité {{ $lastActivityAt }}</span>
            @endif
            @if ($company)
                <a href="{{ route('app.companies.reservations.create', $company) }}" class="glm-btn-primary inline-flex items-center gap-2 no-underline transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Réservation
                </a>
                <a href="{{ route('app.companies.customers.create', $company) }}" class="glm-btn-secondary inline-flex items-center gap-2 no-underline transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">+ Client</a>
                <a href="{{ route('app.companies.alerts.index', $company) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/10 transition-all duration-200 hover:-translate-y-0.5 no-underline">
                    <span class="inline-flex h-2 w-2 rounded-full {{ count($alerts) > 0 ? 'bg-amber-400' : 'bg-slate-500' }}"></span>
                    Voir alertes
                    @if (count($alerts) > 0)<span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs font-bold text-amber-300">{{ count($alerts) }}</span>@endif
                </a>
            @endif
        </div>
    </header>

    {{-- Setup checklist (when onboarding done but something still missing) --}}
    @if ($company && $showOnboardingChecklist && !empty($onboardingChecklist))
    <div class="glm-card-static rounded-2xl border border-[#2563EB]/20 bg-[#2563EB]/5 p-6">
        <h2 class="text-base font-semibold text-white mb-2">Configuration initiale</h2>
        <p class="text-sm text-slate-400 mb-4">Complétez ces étapes pour tirer le meilleur parti de GLM.</p>
        <ul class="space-y-2">
            @foreach ($onboardingChecklist as $item)
                <li class="flex items-center gap-3">
                    @if ($item['done'])
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </span>
                        <span class="text-slate-400">{{ $item['label'] }}</span>
                    @else
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-500/20 text-red-400" aria-hidden="true">•</span>
                        @if ($item['route'])
                            <a href="{{ route($item['route'], $company) }}" class="text-[#93C5FD] hover:text-white font-medium no-underline">{{ $item['label'] }}</a>
                        @else
                            <span class="text-slate-300">{{ $item['label'] }}</span>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- 2) KPI Row --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <x-kpi-card label="Réservations aujourd'hui" :value="$reservationsToday->count()" trend="{{ $reservationsToday->count() > 0 ? 'vs hier' : null }}" icon="calendar" class="transition-all duration-300" />
        <x-kpi-card label="Véhicules disponibles" :value="$vehiclesAvailableCount . ' / ' . $vehiclesCount" icon="car" class="transition-all duration-300" />
        @if (!$isAgent)
            <x-kpi-card label="CA (mois)" :value="number_format($revenueMonth, 0, ',', ' ') . ' MAD'" trend="{{ $revenueMonth > 0 ? '+12%' : null }}" icon="money" class="transition-all duration-300" />
            <x-kpi-card label="Paiements en attente" :value="$paymentsPending" icon="clock" class="transition-all duration-300" />
        @endif
        <x-kpi-card label="Retours aujourd'hui" :value="$returnsTodayCount" icon="calendar" class="transition-all duration-300" />
        <x-kpi-card label="Alertes critiques" :value="$criticalAlertsCount" :trend="$criticalAlertsCount > 0 ? 'À traiter' : null" :trend-up="false" icon="alert" class="transition-all duration-300" />
    </div>

    {{-- 3) Charts (Admin: 3 charts; Agent: optional) --}}
    @if (!$isAgent && $company)
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
        <x-chart-card title="Revenus (30 jours)" subtitle="CA des 30 derniers jours" class="xl:col-span-2">
            <canvas id="chart-revenue-30" class="w-full" height="240" role="img" aria-label="Revenus 30 jours"></canvas>
        </x-chart-card>
        <x-chart-card title="Réservations par statut" subtitle="30 derniers jours">
            <canvas id="chart-reservations-status" class="w-full" height="240" role="img" aria-label="Réservations par statut"></canvas>
        </x-chart-card>
        <x-chart-card title="Utilisation flotte" subtitle="Top véhicules (30 j)" class="xl:col-span-3">
            <canvas id="chart-fleet-util" class="w-full" height="240" role="img" aria-label="Utilisation flotte"></canvas>
        </x-chart-card>
    </div>
    @endif

    {{-- 4) Today timeline: Départs + Retours --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-timeline-list
            title="Départs aujourd'hui"
            :subtitle="$pickupsToday->count() . ' réservation(s)'"
            :view-all-url="$company ? route('app.companies.reservations.index', $company) : null"
            empty-message="Aucun départ prévu aujourd'hui"
            :empty="$pickupsToday->isEmpty()"
        >
            @foreach ($pickupsToday->take(8) as $r)
                <li>
                    <a href="{{ route('app.companies.reservations.show', [$company, $r]) }}" class="group flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/5 p-3 transition-all duration-200 hover:-translate-y-0.5 hover:border-white/20 hover:bg-white/10 no-underline">
                        <div class="min-w-0">
                            <span class="font-semibold text-white group-hover:text-[#93C5FD]">{{ $r->start_at->format('H:i') }}</span>
                            <span class="mx-2 text-slate-500">·</span>
                            <span class="text-slate-300">{{ $r->customer?->name ?? '–' }}</span>
                            <span class="block truncate text-sm text-slate-500">{{ $r->vehicle?->plate ?? $r->vehicle?->name ?? '–' }}</span>
                        </div>
                        <span class="shrink-0 rounded-lg bg-[#2563EB]/20 px-2.5 py-1 text-xs font-semibold text-[#93C5FD]">Ouvrir</span>
                    </a>
                </li>
            @endforeach
        </x-timeline-list>
        <x-timeline-list
            title="Retours aujourd'hui"
            :subtitle="$returnsToday->count() . ' réservation(s)'"
            :view-all-url="$company ? route('app.companies.reservations.index', $company) : null"
            empty-message="Aucun retour prévu aujourd'hui"
            :empty="$returnsToday->isEmpty()"
        >
            @foreach ($returnsToday->take(8) as $r)
                <li>
                    <a href="{{ route('app.companies.reservations.show', [$company, $r]) }}" class="group flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-white/5 p-3 transition-all duration-200 hover:-translate-y-0.5 hover:border-white/20 hover:bg-white/10 no-underline">
                        <div class="min-w-0">
                            <span class="font-semibold text-white group-hover:text-[#93C5FD]">{{ $r->end_at->format('H:i') }}</span>
                            <span class="mx-2 text-slate-500">·</span>
                            <span class="text-slate-300">{{ $r->customer?->name ?? '–' }}</span>
                            <span class="block truncate text-sm text-slate-500">{{ $r->vehicle?->plate ?? '–' }}</span>
                        </div>
                        <span class="shrink-0 rounded-lg bg-[#2563EB]/20 px-2.5 py-1 text-xs font-semibold text-[#93C5FD]">Ouvrir</span>
                    </a>
                </li>
            @endforeach
        </x-timeline-list>
    </div>

    {{-- 5) Alerts & Tasks + 6) AI Assistant --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Alerts --}}
            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-6 shadow-lg transition-all duration-300 hover:shadow-xl hover:border-white/15">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-base font-bold text-white">Alertes & tâches</h2>
                    @if ($company)
                        <a href="{{ route('app.companies.alerts.index', $company) }}" class="text-sm font-semibold text-[#93C5FD] hover:text-white transition">Tout voir →</a>
                    @endif
                </div>
                <div class="space-y-3">
                    @forelse ($alerts as $a)
                        @php
                            $severity = $a['severity'] ?? $a['type'] ?? 'info';
                            $chipClass = match($severity) {
                                'urgent', 'warning' => 'bg-amber-500/15 text-amber-200 border-amber-500/30',
                                'info' => 'bg-[#2563EB]/15 text-[#93C5FD] border-[#2563EB]/30',
                                default => 'bg-white/10 text-slate-300 border-white/10',
                            };
                        @endphp
                        <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/5 p-4 transition-all duration-200 hover:bg-white/10">
                            <span class="shrink-0 rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $chipClass }}">{{ $severity === 'urgent' ? 'Critique' : ($severity === 'warning' ? 'Attention' : 'Info') }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-white">{{ $a['title'] ?? 'Alerte' }}</p>
                                <p class="text-sm text-slate-400">{{ $a['body'] ?? $a['desc'] ?? '' }}</p>
                                @if (!empty($a['related_url']))
                                    <a href="{{ $a['related_url'] }}" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-[#93C5FD] hover:text-white transition">Ouvrir <span>→</span></a>
                                @endif
                            </div>
                            <button type="button" class="shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white transition" aria-label="Marquer">✓</button>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 text-center rounded-xl border border-white/10 bg-white/5">
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <p class="mt-3 text-sm text-slate-500">Aucune alerte pour le moment</p>
                        </div>
                    @endforelse
                </div>
                {{-- To-do checklist --}}
                @if (count($todoItems) > 0)
                <div class="mt-6 pt-4 border-t border-white/10">
                    <h3 class="text-sm font-bold text-white mb-3">À faire</h3>
                    <ul class="space-y-2">
                        @foreach ($todoItems as $todo)
                            <li>
                                <a href="{{ $todo['url'] ?? '#' }}" class="flex items-center gap-3 rounded-lg py-2 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition no-underline">
                                    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border border-white/20 bg-white/5"></span>
                                    {{ $todo['label'] }}
                                    @if (($todo['count'] ?? 0) > 0)
                                        <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-300">{{ $todo['count'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        {{-- 6) AI Assistant --}}
        <div>
            <x-ai-card :available="$hasAiAccess" class="h-full">
                <button type="button" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white transition">Créer une réservation</button>
                <button type="button" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white transition">Générer un contrat</button>
                <button type="button" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-white/10 hover:text-white transition">Rentabilité véhicule</button>
            </x-ai-card>
        </div>
    </div>

    {{-- 7) Admin shortcuts (profitability, expenses) --}}
    @if (!$isAgent && $company)
    @php $planGate = app(\App\Services\PlanGateService::class); $canProfitability = $planGate->can($company, \App\Services\PlanGateService::FEATURE_PROFITABILITY); @endphp
    <div class="flex flex-wrap gap-3">
        @if ($canProfitability)
            <a href="{{ route('app.companies.fleet.profitability.index', $company) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/10 hover:text-white transition-all duration-200 hover:-translate-y-0.5 no-underline">Rentabilité flotte</a>
        @endif
        <a href="{{ route('app.companies.expenses.index', $company) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/10 hover:text-white transition-all duration-200 hover:-translate-y-0.5 no-underline">Dépenses</a>
        <a href="{{ route('app.companies.reports.index', $company) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/10 hover:text-white transition-all duration-200 hover:-translate-y-0.5 no-underline">Rapports</a>
        <a href="{{ route('app.companies.show', $company) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/10 hover:text-white transition-all duration-200 hover:-translate-y-0.5 no-underline">Paramètres</a>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const theme = {
        text: 'rgba(226, 232, 240, 0.9)',
        grid: 'rgba(255, 255, 255, 0.06)',
        blue: 'rgba(59, 130, 246, 0.8)',
        blueFill: 'rgba(59, 130, 246, 0.15)',
        colors: ['#3b82f6', '#60a5fa', '#93c5fd', '#2563eb', '#1d4ed8', '#6366f1', '#818cf8', '#a5b4fc'],
    };

    @if (!$isAgent)
    const revenueCtx = document.getElementById('chart-revenue-30');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($chartRevenue30['labels']),
                datasets: [{
                    label: 'Revenus (MAD)',
                    data: @json($chartRevenue30['values']),
                    borderColor: theme.blue,
                    backgroundColor: theme.blueFill,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: theme.grid }, ticks: { color: theme.text, maxTicksLimit: 8 } },
                    y: { grid: { color: theme.grid }, ticks: { color: theme.text } },
                },
            },
        });
    }

    const statusCtx = document.getElementById('chart-reservations-status');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: @json($chartReservationsByStatus['labels']),
                datasets: [{ data: @json($chartReservationsByStatus['values']), backgroundColor: theme.colors, borderWidth: 0 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: theme.text, padding: 16 } } },
            },
        });
    }

    const fleetCtx = document.getElementById('chart-fleet-util');
    if (fleetCtx) {
        new Chart(fleetCtx, {
            type: 'bar',
            data: {
                labels: @json($chartFleetUtilization['labels']),
                datasets: [{ label: 'Réservations', data: @json($chartFleetUtilization['values']), backgroundColor: theme.blue, borderRadius: 6 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: theme.grid }, ticks: { color: theme.text } },
                    y: { grid: { display: false }, ticks: { color: theme.text } },
                },
            },
        });
    }
    @endif
});
</script>
@endpush
@endsection
