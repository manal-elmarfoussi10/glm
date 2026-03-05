@extends('app.layouts.app')

@section('pageSubtitle')
{{ $vehicle->plate }} – {{ $company->name }}
@endsection

@section('content')
@php
    use Carbon\Carbon;
    $ins = $vehicle->insuranceStatus();
    $vig = $vehicle->vignetteStatus();
    $vis = $vehicle->visiteStatus();
    $COMP = \App\Models\Vehicle::class;
    $insuranceEnd = $vehicle->insurance_end_date;
    $insuranceDaysRemaining = $insuranceEnd ? max(0, now()->diffInDays(Carbon::parse($insuranceEnd), false)) : null;
    $insuranceDaysTotal = $insuranceEnd ? 365 : null;
    $insuranceProgress = $insuranceDaysTotal && $insuranceDaysRemaining !== null ? min(100, round($insuranceDaysRemaining / $insuranceDaysTotal * 100)) : null;
    $vignetteExpiry = $vehicle->vignette_year ? Carbon::createFromFormat('Y', (string) $vehicle->vignette_year)->endOfYear() : null;
    $vignetteDaysRemaining = $vignetteExpiry ? max(0, now()->diffInDays($vignetteExpiry, false)) : null;
    $vignetteDaysTotal = $vignetteExpiry ? 365 : null;
    $vignetteProgress = $vignetteDaysTotal && $vignetteDaysRemaining !== null ? min(100, round($vignetteDaysRemaining / $vignetteDaysTotal * 100)) : null;
    $st = $vehicle->status ?? 'available';
    $statusLabel = $st === 'available' ? 'Disponible' : ($st === 'maintenance' ? 'Maintenance' : 'Inactif');
    $statusClass = $st === 'available' ? 'bg-emerald-500/20 text-emerald-400' : ($st === 'maintenance' ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-500/20 text-slate-400');
    $vehicleAlerts = $vehicleAlerts ?? [];
    $timeline = $vehicle->complianceAlertTimeline();
@endphp
<div class="space-y-6 glm-fade-in" x-data="{ tab: (function(){ var p = new URLSearchParams(window.location.search).get('tab'); return p && ['overview','reservations','expenses','inspections','documents','activity'].includes(p) ? p : 'overview'; })() }">
    <a href="{{ route('app.companies.vehicles.index', $company) }}" class="text-sm font-medium text-slate-400 hover:text-white mb-2 inline-block no-underline">← Flotte · {{ $company->name }}</a>

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04] shadow-xl">
        <div class="aspect-video w-full overflow-hidden bg-slate-800/50">
            @if ($vehicle->image_path)
                <img src="{{ asset('storage/' . $vehicle->image_path) }}" alt="{{ $vehicle->plate }}" class="h-full w-full object-cover transition duration-300 hover:scale-[1.02]" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="h-full w-full flex items-center justify-center text-[color:var(--muted)] bg-[color:var(--surface-2)]" style="display:none;">
                    <svg class="h-24 w-24 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                </div>
            @else
                <div class="flex h-full w-full items-center justify-center text-slate-500">
                    <svg class="h-24 w-24 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                </div>
            @endif
        </div>
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#0a0f1a] via-[#0a0f1a]/90 to-transparent pt-16 pb-5 px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-400">{{ $vehicle->branch->name ?? '–' }}</p>
                    <h1 class="text-3xl font-bold tracking-tight text-white mt-0.5">{{ $vehicle->plate }}</h1>
                    <p class="text-slate-300 mt-1">{{ $vehicle->brand }} {{ $vehicle->model }}@if($vehicle->year) <span class="text-slate-500">({{ $vehicle->year }})</span>@endif</p>
                    <span class="inline-flex mt-2 rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('app.companies.vehicles.edit', [$company, $vehicle]) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/20 transition no-underline">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Modifier
                    </a>
                    <a href="{{ route('app.companies.reservations.create', $company) }}?vehicle_id={{ $vehicle->id }}" class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-medium text-white hover:bg-[#1d4ed8] transition no-underline">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Nouvelle réservation
                    </a>
                    <a href="{{ route('app.companies.expenses.create', $company) }}?vehicle_id={{ $vehicle->id }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/20 transition no-underline">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        Nouvelle dépense
                    </a>
                    <button type="button" @click="tab = 'inspections'" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/20 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        État des lieux
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Compliance cards with progress bars --}}
    <div class="grid gap-4 sm:grid-cols-3">
        @php
            $cards = [
                'insurance' => [
                    'label' => 'Assurance',
                    'status' => $ins,
                    'date' => $insuranceEnd?->format('d/m/Y'),
                    'days_remaining' => $insuranceDaysRemaining,
                    'progress' => $insuranceProgress,
                    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                ],
                'vignette' => [
                    'label' => 'Vignette (Dariba)',
                    'status' => $vig,
                    'date' => $vehicle->vignette_year ? 'Fin ' . $vehicle->vignette_year : null,
                    'days_remaining' => $vignetteDaysRemaining,
                    'progress' => $vignetteProgress,
                    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2v-4a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                ],
                'visite' => [
                    'label' => 'Visite technique',
                    'status' => $vis,
                    'date' => $vehicle->visite_expiry_date?->format('d/m/Y'),
                    'days_remaining' => $vehicle->visite_expiry_date ? max(0, now()->diffInDays($vehicle->visite_expiry_date, false)) : null,
                    'progress' => null,
                    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                ],
            ];
        @endphp
        @foreach ($cards as $key => $c)
            @php
                $border = $c['status'] === $COMP::COMPLIANCE_EXPIRED ? 'border-red-500/30' : ($c['status'] === $COMP::COMPLIANCE_EXPIRING ? 'border-amber-500/30' : 'border-white/10');
                $bg = $c['status'] === $COMP::COMPLIANCE_EXPIRED ? 'bg-red-500/5' : ($c['status'] === $COMP::COMPLIANCE_EXPIRING ? 'bg-amber-500/5' : 'bg-white/[0.04]');
                $statusText = $c['status'] === $COMP::COMPLIANCE_OK ? 'OK' : ($c['status'] === $COMP::COMPLIANCE_EXPIRING ? 'Bientôt expiré' : ($c['status'] === $COMP::COMPLIANCE_EXPIRED ? 'Expiré' : 'Non renseigné'));
                $statusColor = $c['status'] === $COMP::COMPLIANCE_OK ? 'text-emerald-400' : ($c['status'] === $COMP::COMPLIANCE_EXPIRING ? 'text-amber-400' : ($c['status'] === $COMP::COMPLIANCE_EXPIRED ? 'text-red-400' : 'text-slate-500'));
            @endphp
            <div class="rounded-xl border {{ $border }} {{ $bg }} p-4 transition hover:border-white/20">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/10">
                        <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c['icon'] }}" /></svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $c['label'] }}</p>
                        <p class="font-semibold {{ $statusColor }}">{{ $statusText }}</p>
                        @if ($c['date'])
                            <p class="text-xs text-slate-400 mt-0.5">Expire le {{ $c['date'] }}@if($c['days_remaining'] !== null) · {{ $c['days_remaining'] }} j restants @endif</p>
                        @endif
                    </div>
                </div>
                @if ($c['progress'] !== null)
                    <div class="mt-3">
                        <div class="h-1.5 w-full rounded-full bg-white/10 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $c['status'] === $COMP::COMPLIANCE_OK ? 'bg-emerald-500' : ($c['status'] === $COMP::COMPLIANCE_EXPIRING ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $c['progress'] }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Financial snapshot --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="glm-card-static p-5 rounded-xl hover:border-white/20 transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Revenus ce mois</p>
            <p class="mt-1 text-2xl font-bold text-emerald-400">{{ number_format($revenueThisMonth ?? 0, 0, ',', ' ') }} <span class="text-base font-normal text-slate-400">MAD</span></p>
        </div>
        <div class="glm-card-static p-5 rounded-xl hover:border-white/20 transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Dépenses ce mois</p>
            <p class="mt-1 text-2xl font-bold text-red-400">{{ number_format($expensesThisMonth ?? 0, 0, ',', ' ') }} <span class="text-base font-normal text-slate-400">MAD</span></p>
        </div>
        <div class="glm-card-static p-5 rounded-xl hover:border-white/20 transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Net ce mois</p>
            <p class="mt-1 text-2xl font-bold {{ ($netThisMonth ?? 0) >= 0 ? 'text-white' : 'text-red-400' }}">{{ number_format($netThisMonth ?? 0, 0, ',', ' ') }} <span class="text-base font-normal text-slate-400">MAD</span></p>
        </div>
        <div class="glm-card-static p-5 rounded-xl hover:border-white/20 transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Revenus total</p>
            <p class="mt-1 text-2xl font-bold text-white">{{ number_format($totalRevenueLifetime ?? 0, 0, ',', ' ') }} <span class="text-base font-normal text-slate-400">MAD</span></p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="glm-card-static p-5 rounded-xl hover:border-white/20 transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Locations totales</p>
            <p class="mt-1 text-2xl font-bold text-white">{{ (int) ($totalRentals ?? 0) }}</p>
        </div>
        <div class="glm-card-static p-5 rounded-xl hover:border-white/20 transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kilométrage (locations)</p>
            <p class="mt-1 text-2xl font-bold text-white">{{ isset($totalKmDriven) && $totalKmDriven > 0 ? number_format($totalKmDriven, 0, ',', ' ') . ' km' : '–' }}</p>
        </div>
        <div class="glm-card-static p-5 rounded-xl hover:border-white/20 transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Taux d'utilisation (ce mois)</p>
            <p class="mt-1 text-2xl font-bold text-[#93C5FD]">{{ (int) ($utilizationPercent ?? 0) }}%</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-white/10">
        <nav class="flex gap-1 flex-wrap" aria-label="Onglets">
            <button type="button" @click="tab = 'overview'" :class="tab === 'overview' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition">Vue d'ensemble</button>
            <button type="button" @click="tab = 'reservations'" :class="tab === 'reservations' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition">Réservations</button>
            <button type="button" @click="tab = 'expenses'" :class="tab === 'expenses' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition">Dépenses</button>
            <button type="button" @click="tab = 'inspections'" :class="tab === 'inspections' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition">État des lieux</button>
            <button type="button" @click="tab = 'documents'" :class="tab === 'documents' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition">Documents</button>
            <button type="button" @click="tab = 'activity'" :class="tab === 'activity' ? 'border-[#2563EB] text-white' : 'border-transparent text-slate-400 hover:text-white'" class="border-b-2 px-4 py-3 text-sm font-medium transition">Activité</button>
        </nav>
    </div>

    {{-- Tab: Overview --}}
    <div x-show="tab === 'overview'" x-cloak class="space-y-6">
        @if (count($vehicleAlerts) > 0)
            <div class="glm-card-static p-6 border-amber-500/20 bg-amber-500/5 rounded-xl">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-lg font-semibold text-white">Alertes conformité</h2>
                    <a href="{{ route('app.companies.alerts.index', $company) }}" class="text-sm font-bold text-amber-400 hover:text-amber-300 transition no-underline">Voir toutes →</a>
                </div>
                <ul class="space-y-2">
                    @foreach ($vehicleAlerts as $a)
                        @php $badge = match($a['severity'] ?? 'info') { 'urgent' => 'bg-red-500/20 text-red-300', 'warning' => 'bg-amber-500/20 text-amber-300', default => 'bg-slate-500/20 text-slate-300' }; @endphp
                        <li class="flex items-center gap-3 rounded-xl border border-white/10 p-3"><span class="rounded px-2 py-0.5 text-xs font-semibold {{ $badge }}">{{ $a['severity'] ?? 'info' }}</span><span class="text-sm text-slate-200">{{ $a['type'] ?? $a['title'] ?? 'Conformité' }}</span><span class="text-xs text-slate-500 ml-auto">{{ $a['body'] ?? '' }}</span></li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="glm-card-static p-6 rounded-xl">
                <h2 class="text-lg font-semibold text-white mb-4">Identification</h2>
                <dl class="grid gap-2 sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Plaque</dt><dd class="text-slate-200">{{ $vehicle->plate }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Marque / Modèle</dt><dd class="text-slate-200">{{ $vehicle->brand }} {{ $vehicle->model }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Année</dt><dd class="text-slate-200">{{ $vehicle->year ?? '–' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">VIN</dt><dd class="text-slate-200">{{ $vehicle->vin ?? '–' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Carburant / Transmission</dt><dd class="text-slate-200">{{ $vehicle->fuel ?? '–' }} / {{ $vehicle->transmission ?? '–' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Kilométrage</dt><dd class="text-slate-200">{{ $vehicle->mileage ? number_format($vehicle->mileage, 0, ',', ' ') . ' km' : '–' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Couleur / Places</dt><dd class="text-slate-200">{{ $vehicle->color ?? '–' }} / {{ $vehicle->seats ?? '–' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Agence</dt><dd class="text-slate-200">{{ $vehicle->branch->name ?? '–' }}</dd></div>
                </dl>
            </div>
            <div class="glm-card-static p-6 rounded-xl">
                <h2 class="text-lg font-semibold text-white mb-4">Tarification</h2>
                <dl class="grid gap-2 sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Jour</dt><dd class="text-slate-200">{{ $vehicle->daily_price ? number_format($vehicle->daily_price, 0, ',', ' ') . ' MAD' : '–' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Semaine</dt><dd class="text-slate-200">{{ $vehicle->weekly_price ? number_format($vehicle->weekly_price, 0, ',', ' ') . ' MAD' : '–' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Mois</dt><dd class="text-slate-200">{{ $vehicle->monthly_price ? number_format($vehicle->monthly_price, 0, ',', ' ') . ' MAD' : '–' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Caution</dt><dd class="text-slate-200">{{ $vehicle->deposit ? number_format($vehicle->deposit, 0, ',', ' ') . ' MAD' : '–' }}</dd></div>
                </dl>
                @if ($vehicle->is_financed)
                    <h3 class="text-base font-medium text-slate-300 mt-4 mb-2">Financement</h3>
                    <p class="text-sm text-slate-400">{{ $vehicle->financing_type }} · {{ $vehicle->financing_bank ?? '–' }} · {{ $vehicle->financing_monthly_payment ? number_format($vehicle->financing_monthly_payment, 0, ',', ' ') . ' MAD/mois' : '–' }}</p>
                @endif
            </div>
        </div>
        @if (!empty($timeline))
            <div class="glm-card-static p-6 rounded-xl">
                <h2 class="text-lg font-semibold text-white mb-4">Échéances</h2>
                <ul class="space-y-3">
                    @foreach ($timeline as $item)
                        <li class="flex items-center gap-4 rounded-xl border border-white/10 p-3 {{ $item['status'] === $COMP::COMPLIANCE_EXPIRED ? 'bg-red-500/5 border-red-500/20' : ($item['status'] === $COMP::COMPLIANCE_EXPIRING ? 'bg-amber-500/5 border-amber-500/20' : '') }}">
                            <span class="font-medium text-white">{{ $item['label'] }}</span>
                            <span class="text-sm text-slate-400">{{ $item['date']->format('d/m/Y') }}</span>
                            @if ($item['status'] === $COMP::COMPLIANCE_OK)<span class="rounded px-2 py-0.5 text-xs font-medium bg-emerald-500/20 text-emerald-400">OK</span>@elseif ($item['status'] === $COMP::COMPLIANCE_EXPIRING)<span class="rounded px-2 py-0.5 text-xs font-medium bg-amber-500/20 text-amber-400">Bientôt</span>@else<span class="rounded px-2 py-0.5 text-xs font-medium bg-red-500/20 text-red-400">Expiré</span>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Tab: Reservations --}}
    <div x-show="tab === 'reservations'" x-cloak class="glm-card-static overflow-hidden rounded-xl p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5"><tr><th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-400">Référence</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-400">Client</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-400">Période</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-400">Statut</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-400">Montant</th><th class="w-0 px-6 py-3"></th></tr></thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($reservationsPaginated ?? [] as $r)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4 font-mono text-sm text-white">{{ $r->reference }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $r->customer->name ?? '–' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $r->start_at->format('d/m/Y') }} → {{ $r->end_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4"><span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $r->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : ($r->status === 'in_progress' ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-500/20 text-slate-400') }}">{{ $r->status === 'draft' ? 'Brouillon' : ($r->status === 'confirmed' ? 'Confirmée' : ($r->status === 'in_progress' ? 'En cours' : ($r->status === 'completed' ? 'Terminée' : 'Annulée'))) }}</span></td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ number_format($r->total_price ?? 0, 0, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4"><a href="{{ route('app.companies.reservations.show', [$company, $r]) }}" class="text-sm text-[#93C5FD] hover:text-white no-underline">Voir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">Aucune réservation.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (isset($reservationsPaginated) && $reservationsPaginated->hasPages())
            <div class="border-t border-white/10 px-6 py-4">{{ $reservationsPaginated->links() }}</div>
        @endif
    </div>

    {{-- Tab: Expenses --}}
    <div x-show="tab === 'expenses'" x-cloak class="glm-card-static overflow-hidden rounded-xl p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-white/5"><tr><th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-400">Date</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-400">Catégorie</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-400">Description</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-400">Montant</th><th class="w-0 px-6 py-3"></th></tr></thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($expensesPaginated ?? [] as $e)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4 text-sm text-slate-300">{{ $e->date?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ \App\Models\Expense::CATEGORIES[$e->category] ?? $e->category }}</td>
                            <td class="px-6 py-4 text-sm text-slate-300">{{ Str::limit($e->description, 40) }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-red-400">{{ number_format($e->amount ?? 0, 0, ',', ' ') }} MAD</td>
                            <td class="px-6 py-4"><a href="{{ route('app.companies.expenses.index', $company) }}" class="text-sm text-[#93C5FD] hover:text-white no-underline">Liste</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">Aucune dépense.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (isset($expensesPaginated) && $expensesPaginated->hasPages())
            <div class="border-t border-white/10 px-6 py-4">{{ $expensesPaginated->links() }}</div>
        @endif
    </div>

    {{-- Tab: Inspections --}}
    <div x-show="tab === 'inspections'" x-cloak class="space-y-6">
        <p class="text-sm text-slate-400">Les états des lieux (départ / retour) sont enregistrés sur chaque réservation.</p>
        <ul class="space-y-3">
            @php $reservationsForInspections = $vehicle->reservations()->with(['inspectionOut', 'inspectionIn', 'customer'])->orderByDesc('start_at')->limit(20)->get(); @endphp
            @forelse ($reservationsForInspections as $r)
                <li class="glm-card-static p-4 rounded-xl flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <a href="{{ route('app.companies.reservations.show', [$company, $r]) }}?tab=inspections" class="font-medium text-white hover:text-[#93C5FD] no-underline">{{ $r->reference }}</a>
                        <p class="text-sm text-slate-400">{{ $r->customer->name ?? '–' }} · {{ $r->start_at->format('d/m/Y') }} → {{ $r->end_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex gap-2">
                        @if ($r->inspectionOut)<span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-500/20 text-emerald-400">Départ</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-500/20 text-slate-400">Pas de départ</span>@endif
                        @if ($r->inspectionIn)<span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-500/20 text-emerald-400">Retour</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-500/20 text-slate-400">Pas de retour</span>@endif
                    </div>
                    <a href="{{ route('app.companies.reservations.show', [$company, $r]) }}?tab=inspections" class="glm-btn-secondary text-sm py-1.5 px-3 no-underline">Voir</a>
                </li>
            @empty
                <li class="glm-card-static p-6 rounded-xl text-center text-slate-500">Aucune réservation avec état des lieux.</li>
            @endforelse
        </ul>
    </div>

    {{-- Tab: Documents --}}
    <div x-show="tab === 'documents'" x-cloak class="glm-card-static p-6 rounded-xl">
        <h2 class="text-lg font-semibold text-white mb-4">Documents</h2>
        @if ($vehicle->insurance_document_path || $vehicle->vignette_receipt_path || $vehicle->visite_document_path || $vehicle->financing_contract_path)
            <div class="flex flex-wrap gap-3">
                @if ($vehicle->insurance_document_path)<a href="{{ asset('storage/' . $vehicle->insurance_document_path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white hover:bg-white/10 transition no-underline"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>Assurance</a>@endif
                @if ($vehicle->vignette_receipt_path)<a href="{{ asset('storage/' . $vehicle->vignette_receipt_path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white hover:bg-white/10 transition no-underline"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>Reçu vignette</a>@endif
                @if ($vehicle->visite_document_path)<a href="{{ asset('storage/' . $vehicle->visite_document_path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white hover:bg-white/10 transition no-underline"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>Visite technique</a>@endif
                @if ($vehicle->financing_contract_path)<a href="{{ asset('storage/' . $vehicle->financing_contract_path) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white hover:bg-white/10 transition no-underline"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>Contrat financement</a>@endif
            </div>
        @else
            <p class="text-slate-500">Aucun document enregistré.</p>
        @endif
    </div>

    {{-- Tab: Activity --}}
    <div x-show="tab === 'activity'" x-cloak class="glm-card-static p-6 rounded-xl">
        <h2 class="text-lg font-semibold text-white mb-4">Activité récente</h2>
        @php
            $activities = collect();
            foreach ($vehicle->reservations()->orderByDesc('updated_at')->limit(15)->get() as $r) {
                $activities->push(['date' => $r->updated_at, 'type' => 'reservation', 'label' => 'Réservation ' . $r->reference, 'url' => route('app.companies.reservations.show', [$company, $r])]);
            }
            foreach ($vehicle->expenses()->orderByDesc('created_at')->limit(10)->get() as $e) {
                $activities->push(['date' => $e->created_at, 'type' => 'expense', 'label' => 'Dépense ' . number_format($e->amount, 0, ',', ' ') . ' MAD', 'url' => route('app.companies.expenses.index', $company)]);
            }
            $activities = $activities->sortByDesc('date')->take(20)->values();
        @endphp
        @if ($activities->isNotEmpty())
            <ul class="space-y-2">
                @foreach ($activities as $a)
                    <li class="flex items-center gap-3 rounded-lg py-2 px-3 hover:bg-white/5 transition">
                        <span class="text-xs text-slate-500 w-28">{{ $a['date']->format('d/m/Y H:i') }}</span>
                        <span class="text-slate-200">{{ $a['label'] }}</span>
                        @if (!empty($a['url']))<a href="{{ $a['url'] }}" class="ml-auto text-sm text-[#93C5FD] hover:text-white no-underline">Voir</a>@endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-slate-500">Aucune activité récente.</p>
        @endif
    </div>
</div>
<style>[x-cloak]{display:none!important}</style>
@endsection
