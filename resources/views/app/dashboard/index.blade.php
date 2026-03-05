@extends('app.layouts.app')

@section('content')
@php
    $dashboardMode = $dashboardMode ?? null;
    $company = $company ?? null;
    $alerts = $alerts ?? [];
    $complianceAlerts = $complianceAlerts ?? [];
    $platformAlerts = $platformAlerts ?? [];
    $stats = $stats ?? [];
    $reservationsToday = $reservationsToday ?? collect();
    $pickupsToday = $pickupsToday ?? collect();
    $returnsToday = $returnsToday ?? collect();
    $vehiclesCount = $vehiclesCount ?? 0;
    $recentRegistrations = $recentRegistrations ?? collect();
    $pendingUpgradeRequests = $pendingUpgradeRequests ?? 0;

    $defaultStats = ['mrr' => '0 MAD', 'companies' => 0, 'active_users' => 0, 'pending' => 0];
    $stats = array_merge($defaultStats, $stats);

    $kpisPlatform = [
        ['label' => 'MRR (Mensuel)', 'value' => $stats['mrr'], 'hint' => '+8% ce mois', 'icon' => 'money'],
        ['label' => 'Entreprises', 'value' => $stats['companies'], 'hint' => 'Total actives', 'icon' => 'building'],
        ['label' => 'Utilisateurs', 'value' => $stats['active_users'], 'hint' => 'Actifs cette semaine', 'icon' => 'users'],
        ['label' => 'Demandes en attente', 'value' => $stats['pending'], 'hint' => 'À valider', 'icon' => 'inbox'],
    ];
    $kpisCompany = [
        ['label' => 'Réservations aujourd\'hui', 'value' => $stats['reservations_today'] ?? 0, 'hint' => 'Départs / retours', 'icon' => 'calendar'],
        ['label' => 'Réservations cette semaine', 'value' => $stats['reservations_this_week'] ?? 0, 'hint' => 'Début de location', 'icon' => 'calendar'],
        ['label' => 'Revenus (semaine)', 'value' => isset($stats['revenue_this_week']) ? number_format($stats['revenue_this_week'], 0, ',', ' ') . ' MAD' : '0 MAD', 'hint' => 'Paiements manuels', 'icon' => 'money'],
        ['label' => 'Véhicules disponibles', 'value' => (($stats['vehicles_available_count'] ?? $stats['vehicles_count'] ?? 0) - ($stats['vehicles_in_use_today'] ?? 0)) . ' / ' . ($stats['vehicles_available_count'] ?? $stats['vehicles_count'] ?? 0), 'hint' => 'Hors location aujourd\'hui (statut disponible)', 'icon' => 'car'],
    ];
    $kpis = ($dashboardMode === 'company_admin' && $company) ? $kpisCompany : $kpisPlatform;
    $recent = $recentRegistrations->isEmpty() ? [
        ['company' => 'Atlas Rent Cars', 'owner' => 'Omar A.', 'plan' => 'Pro', 'status' => 'En attente', 'time' => 'Il y a 12 min'],
        ['company' => 'Marrakech Drive', 'owner' => 'Sara B.', 'plan' => 'Starter', 'status' => 'Approuvée', 'time' => 'Il y a 2 h'],
        ['company' => 'Rabat Mobility', 'owner' => 'Yassine K.', 'plan' => 'Business', 'status' => 'En attente', 'time' => 'Hier'],
    ] : $recentRegistrations->map(fn ($u) => ['company' => $u->company->name ?? '-', 'owner' => $u->name, 'plan' => '-', 'status' => $u->status === 'pending' ? 'En attente' : ($u->status === 'active' ? 'Approuvée' : 'Refusée'), 'time' => $u->created_at->diffForHumans()])->values()->all();

    $alertsFallback = [
        ['title' => 'Paiement échoué', 'desc' => '1 abonnement nécessite une relance.', 'type' => 'warning'],
        ['title' => 'Nouveau pic de demandes', 'desc' => '9 demandes aujourd’hui (record).', 'type' => 'info'],
        ['title' => 'Maintenance planifiée', 'desc' => 'Dimanche 02:00–03:00 (MA).', 'type' => 'neutral'],
    ];
    $alertsToShow = count($alerts) > 0 ? $alerts : $alertsFallback;
@endphp

<div class="space-y-6">

    {{-- Hero --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[color:var(--text)]">
                Dashboard
            </h1>
            <p class="mt-1 text-sm text-[color:var(--muted)]">
                @if($company){{ $company->name }} — vue d'ensemble et alertes.@else Vue d'ensemble — activité plateforme, demandes, revenus et opérations.@endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($company)
            <a href="{{ route('app.companies.alerts.index', $company) }}" class="inline-flex items-center gap-2 rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] px-4 py-2 text-sm font-semibold text-[color:var(--text)] hover:bg-[color:var(--surface)] transition">
                <span class="inline-flex h-2 w-2 rounded-full bg-[color:var(--warning)] shadow-[0_0_0_4px_rgba(217,119,6,0.2)]"></span>
                Voir les alertes @if (count($alerts) > 0)<span class="rounded-full bg-[color:var(--warning)]/20 px-2 py-0.5 text-xs font-bold text-[color:var(--warning)]">{{ count($alerts) }}</span>@endif
            </a>
            @else
            <a href="{{ route('app.registration-requests.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] px-4 py-2 text-sm font-semibold text-[color:var(--text)] hover:bg-[color:var(--surface)] transition">
                <span class="inline-flex h-2 w-2 rounded-full bg-[color:var(--primary)] shadow-[0_0_0_4px_rgba(37,99,235,0.18)]"></span>
                Voir les demandes
            </a>
            @endif
            @if ($company && $dashboardMode === 'company_admin')
            <a href="{{ route('app.companies.reservations.create', $company) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[color:var(--glm-primary)] to-[color:var(--glm-primary-2)] px-4 py-2 text-sm font-extrabold text-white shadow-[var(--shadow-soft)] hover:brightness-[1.03] transition no-underline">+ Nouvelle réservation</a>
            <a href="{{ route('app.companies.vehicles.create', $company) }}" class="inline-flex items-center gap-2 rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] px-4 py-2 text-sm font-semibold text-[color:var(--text)] hover:bg-[color:var(--surface)] transition no-underline">+ Véhicule</a>
            <a href="{{ route('app.companies.customers.create', $company) }}" class="inline-flex items-center gap-2 rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] px-4 py-2 text-sm font-semibold text-[color:var(--text)] hover:bg-[color:var(--surface)] transition no-underline">+ Client</a>
            @elseif ($company && $dashboardMode === 'agent')
            <a href="{{ route('app.companies.reservations.create', $company) }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[color:var(--glm-primary)] to-[color:var(--glm-primary-2)] px-4 py-2 text-sm font-extrabold text-white no-underline">+ Réservation</a>
            <a href="{{ route('app.companies.customers.create', $company) }}" class="inline-flex items-center gap-2 rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] px-4 py-2 text-sm font-semibold text-[color:var(--text)] no-underline">+ Client</a>
            @elseif (!$company)
            <button type="button" class="glm-btn-primary inline-flex items-center gap-2 px-4 py-2 text-sm font-extrabold">+ Nouvelle entreprise (bientôt)</button>
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kpis as $kpi)
            <div class="glm-card-static rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface)] p-5 backdrop-blur-xl transition hover:shadow-[var(--shadow-strong)]">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-[color:var(--muted)]">{{ $kpi['label'] }}</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-[color:var(--text)]">{{ $kpi['value'] }}</p>
                        <p class="mt-1 text-xs font-semibold text-[color:var(--primary)]">{{ $kpi['hint'] }}</p>
                    </div>

                    <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-[color:var(--border)] bg-[color:var(--primary)]/15 text-[color:var(--primary)]">
                        {{-- icons (simple, inline) --}}
                        @if($kpi['icon']==='money')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M3 7h18v10H3V7z" stroke="currentColor" stroke-width="2" />
                                <path d="M7 12h.01M17 12h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        @elseif($kpi['icon']==='building')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M3 21h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M6 21V4h8v17" stroke="currentColor" stroke-width="2"/>
                                <path d="M14 8h4v13" stroke="currentColor" stroke-width="2"/>
                                <path d="M9 7h2M9 11h2M9 15h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        @elseif($kpi['icon']==='users')
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M16 11a4 4 0 10-8 0 4 4 0 008 0z" stroke="currentColor" stroke-width="2"/>
                                <path d="M4 21a8 8 0 0116 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M4 4h16v16H4V4z" stroke="currentColor" stroke-width="2"/>
                                <path d="M7 8h10M7 12h10M7 16h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Platform: pending upgrade requests widget --}}
    @if (!$company && $pendingUpgradeRequests > 0)
    <a href="{{ route('app.admin.upgrade-requests.index', ['status' => 'pending']) }}" class="rounded-2xl border border-[color:var(--warning)]/20 bg-[color:var(--warning)]/5 backdrop-blur-xl p-5 shadow-[var(--shadow-soft)] hover:border-[color:var(--warning)]/30 transition block no-underline">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[color:var(--warning)]/20">
                    <svg class="h-6 w-6 text-[color:var(--warning)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-[color:var(--muted)]">Demandes d'upgrade</p>
                    <p class="text-2xl font-extrabold text-[color:var(--text)]">{{ $pendingUpgradeRequests }} en attente</p>
                </div>
            </div>
            <span class="text-sm font-bold text-[color:var(--warning)]">Voir →</span>
        </div>
    </a>
    @endif

    {{-- Agent: today's pickups & returns --}}
    @if ($dashboardMode === 'agent' && $company)
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="glm-card-static rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface)] p-6">
            <h2 class="text-base font-extrabold text-[color:var(--text)]">Départs aujourd'hui</h2>
            <p class="mt-1 text-sm text-[color:var(--muted)]">{{ $pickupsToday->count() }} réservation(s)</p>
            <ul class="mt-4 space-y-2">
                @forelse ($pickupsToday->take(8) as $r)
                    <li><a href="{{ route('app.companies.reservations.show', [$company, $r]) }}" class="block rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] p-3 hover:bg-[color:var(--surface)] no-underline"><span class="font-semibold text-[color:var(--text)]">{{ $r->reference }}</span> <span class="text-sm text-[color:var(--muted)]">· {{ $r->vehicle->plate ?? '–' }} · {{ $r->customer->name ?? '–' }}</span></a></li>
                @empty
                    <li class="text-sm text-[color:var(--muted)]">Aucun départ prévu.</li>
                @endforelse
            </ul>
            @if ($pickupsToday->count() > 8)<a href="{{ route('app.companies.reservations.index', $company) }}" class="mt-2 inline-block text-sm font-bold text-[color:var(--primary)]">Voir tout →</a>@endif
        </div>
        <div class="glm-card-static rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface)] p-6">
            <h2 class="text-base font-extrabold text-[color:var(--text)]">Retours aujourd'hui</h2>
            <p class="mt-1 text-sm text-[color:var(--muted)]">{{ $returnsToday->count() }} réservation(s)</p>
            <ul class="mt-4 space-y-2">
                @forelse ($returnsToday->take(8) as $r)
                    <li><a href="{{ route('app.companies.reservations.show', [$company, $r]) }}" class="block rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] p-3 hover:bg-[color:var(--surface)] no-underline"><span class="font-semibold text-[color:var(--text)]">{{ $r->reference }}</span> <span class="text-sm text-[color:var(--muted)]">· {{ $r->vehicle->plate ?? '–' }} · {{ $r->customer->name ?? '–' }}</span></a></li>
                @empty
                    <li class="text-sm text-[color:var(--muted)]">Aucun retour prévu.</li>
                @endforelse
            </ul>
            @if ($returnsToday->count() > 8)<a href="{{ route('app.companies.reservations.index', $company) }}" class="mt-2 inline-block text-sm font-bold text-[color:var(--primary)]">Voir tout →</a>@endif
        </div>
    </div>
    <div class="glm-card-static rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface)] p-6"><h2 class="text-base font-extrabold text-[color:var(--text)]">Véhicules</h2><p class="mt-1 text-sm text-[color:var(--muted)]">{{ $vehiclesCount }} véhicule(s).</p><a href="{{ route('app.companies.vehicles.index', $company) }}" class="mt-3 inline-block text-sm font-bold text-[color:var(--primary)]">Voir la flotte →</a></div>
    @endif

    {{-- Company admin: Reservations today + Compliance alerts --}}
    @if ($dashboardMode === 'company_admin' && $company)
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="xl:col-span-2 glm-card-static rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface)] p-6">
            <div class="flex items-center justify-between gap-3"><h2 class="text-base font-extrabold text-[color:var(--text)]">Réservations aujourd'hui</h2><a href="{{ route('app.companies.reservations.index', $company) }}" class="text-sm font-bold text-[color:var(--primary)] hover:opacity-90 transition">Toutes →</a></div>
            <ul class="mt-4 space-y-2">
                @forelse ($reservationsToday->take(10) as $r)
                    <li><a href="{{ route('app.companies.reservations.show', [$company, $r]) }}" class="block rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] p-3 hover:bg-[color:var(--surface)] no-underline"><span class="font-semibold text-[color:var(--text)]">{{ $r->reference }}</span> <span class="text-sm text-[color:var(--muted)]">· {{ $r->vehicle->plate ?? '–' }} · {{ $r->customer->name ?? '–' }} · {{ $r->start_at->format('H:i') }}–{{ $r->end_at->format('H:i') }}</span></a></li>
                @empty
                    <li class="text-sm text-[color:var(--muted)] py-4">Aucune réservation aujourd'hui.</li>
                @endforelse
            </ul>
        </div>
        <div class="glm-card-static rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface)] p-6">
            <div class="flex items-center justify-between gap-3"><h2 class="text-base font-extrabold text-[color:var(--text)]">Conformité (expirant)</h2><a href="{{ route('app.companies.alerts.index', $company) }}" class="text-sm font-bold text-[color:var(--primary)] hover:opacity-90 transition">Alertes →</a></div>
            <ul class="mt-4 space-y-2">
                @forelse ($complianceAlerts as $a)
                    <li><a href="{{ $a['related_url'] ?? '#' }}" class="block rounded-xl border border-[color:var(--warning)]/20 bg-[color:var(--warning)]/5 p-3 no-underline"><span class="font-semibold text-[color:var(--warning)]">{{ $a['title'] ?? 'Conformité' }}</span><p class="text-xs text-[color:var(--muted)] mt-0.5">{{ $a['body'] ?? '' }}</p></a></li>
                @empty
                    <li class="text-sm text-[color:var(--muted)] py-4">Aucune alerte conformité.</li>
                @endforelse
            </ul>
        </div>
    </div>
    @endif

    {{-- Grid: Chart + Alerts --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        @if ($dashboardMode !== 'company_admin' && $dashboardMode !== 'agent')
        {{-- Chart mock (platform only) --}}
        <div class="xl:col-span-2 glm-card-static rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface)] p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-[color:var(--text)]">Revenus</h2>
                    <p class="text-sm text-[color:var(--muted)]">Aperçu (mock) — connecte Stripe plus tard.</p>
                </div>

                <div class="flex gap-2">
                    <button class="rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] px-3 py-1.5 text-xs font-semibold text-white/75 hover:bg-[color:var(--surface)] transition">7j</button>
                    <button class="rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] px-3 py-1.5 text-xs font-semibold text-white/75 hover:bg-[color:var(--surface)] transition">30j</button>
                    <button class="rounded-xl bg-[#2563EB]/20 px-3 py-1.5 text-xs font-extrabold text-[color:var(--primary)] ring-1 ring-white/10">90j</button>
                </div>
            </div>

            {{-- Simple “fake” bars (front only) --}}
            <div class="mt-6 grid grid-cols-12 items-end gap-2 h-40">
                @foreach ([20,32,28,40,55,48,62,58,70,64,76,82] as $v)
                    <div class="h-full flex items-end">
                        <div
                            class="w-full rounded-xl bg-gradient-to-t from-[#2563EB]/65 to-[#2563EB]/20 ring-1 ring-white/10 hover:brightness-110 transition"
                            style="height: {{ $v }}%;"
                            title="{{ $v }}%"
                        ></div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between text-xs text-[color:var(--muted)]">
                <span>Jan</span><span>Fév</span><span>Mar</span>
            </div>
        </div>
        @endif

        {{-- Alerts (full width for company/agent when no chart) --}}
        <div class="{{ ($dashboardMode === 'company_admin' || $dashboardMode === 'agent') ? 'xl:col-span-3' : '' }} glm-card-static rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface)] p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-[color:var(--text)]">Alertes</h2>
                    <p class="mt-1 text-sm text-[color:var(--muted)]">@if ($company) À surveiller. @else À surveiller cette semaine. @endif</p>
                </div>
                @if ($company)
                <a href="{{ route('app.companies.alerts.index', $company) }}" class="text-sm font-bold text-[color:var(--primary)] hover:opacity-90 transition">Tout voir →</a>
                @endif
            </div>
            <div class="mt-5 space-y-3">
                @foreach ($alertsToShow as $a)
                    @php
                        $type = $a['type'] ?? $a['severity'] ?? 'neutral';
                        $chip = match($type) {
                            'warning', 'urgent' => 'bg-[color:var(--warning)]/15 text-[color:var(--warning)]',
                            'info' => 'bg-[color:var(--primary)]/15 text-[color:var(--primary)]',
                            default => 'bg-[color:var(--surface-2)] text-[color:var(--muted)]',
                        };
                        $label = $type === 'warning' || $type === 'urgent' ? 'Action' : ($type === 'info' ? 'Info' : 'Note');
                        $body = $a['desc'] ?? $a['body'] ?? '';
                    @endphp
                    <div class="rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface-2)] p-4 hover:bg-[color:var(--surface)] transition">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex rounded-full px-2.5 py-1 text-[11px] font-extrabold {{ $chip }}">{{ $label }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-[color:var(--text)]">{{ $a['title'] }}</p>
                                <p class="text-sm text-[color:var(--muted)]">{{ $body }}</p>
                                @if (!empty($a['related_url']))
                                <a href="{{ $a['related_url'] }}" class="mt-2 inline-block text-xs font-bold text-[color:var(--primary)] hover:opacity-90 transition">Ouvrir →</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if (count($platformAlerts) > 0)
            <div class="mt-6 pt-4 border-t border-[color:var(--border)]">
                <h3 class="text-sm font-extrabold text-[color:var(--text)]">Essais / abonnements</h3>
                <div class="mt-3 space-y-2">
                    @foreach ($platformAlerts as $pa)
                    <a href="{{ $pa['related_url'] ?? '#' }}" class="block rounded-xl border border-[color:var(--border)] bg-[color:var(--surface-2)] p-3 hover:bg-[color:var(--surface)] transition">
                        <p class="font-bold text-[color:var(--text)]">{{ $pa['title'] }}</p>
                        <p class="text-xs text-[color:var(--muted)]">{{ $pa['body'] ?? '' }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Recent activity + AI Assistant (platform only) --}}
    @if ($dashboardMode !== 'company_admin' && $dashboardMode !== 'agent')
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        {{-- Recent requests --}}
        <div class="xl:col-span-2 glm-card-static rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface)] p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-[color:var(--text)]">Activité récente</h2>
                    <p class="mt-1 text-sm text-[color:var(--muted)]">Dernières demandes (mock).</p>
                </div>
                <a href="{{ route('app.registration-requests.index') }}"
                   class="text-sm font-bold text-[color:var(--primary)] hover:opacity-90 transition">
                    Tout voir →
                </a>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-[color:var(--border)]">
                <div class="divide-y divide-[color:var(--border)] bg-[color:var(--surface-2)]">
                    @foreach ($recent as $r)
                        @php
                            $badge = $r['status'] === 'En attente'
                                ? 'bg-amber-500/15 text-amber-200'
                                : 'bg-emerald-500/15 text-emerald-200';
                        @endphp

                        <div class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between hover:bg-[color:var(--surface-2)] transition">
                            <div class="min-w-0">
                                <p class="font-extrabold text-[color:var(--text)] truncate">{{ $r['company'] }}</p>
                                <p class="text-sm text-[color:var(--muted)] truncate">
                                    {{ $r['owner'] }} • Plan {{ $r['plan'] }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ $badge }}">
                                    {{ $r['status'] }}
                                </span>
                                <span class="text-xs text-[color:var(--muted)]">{{ $r['time'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- AI Assistant teaser --}}
        <div class="rounded-2xl border border-[color:var(--border)] bg-gradient-to-b from-[color:var(--primary)]/10 to-transparent backdrop-blur-xl p-6 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <h2 class="text-base font-extrabold text-white/95">Assistant IA (bientôt)</h2>
            <p class="mt-1 text-sm text-[color:var(--muted)]">
                Gérez l’agence par WhatsApp : ajout client, réservation, contrat, disponibilité…
            </p>

            <div class="mt-5 space-y-3">
                <div class="rounded-2xl border border-[color:var(--border)] bg-[color:var(--surface-2)] p-4">
                    <p class="text-xs font-extrabold text-[color:var(--muted)]">Exemples</p>
                    <ul class="mt-2 space-y-2 text-sm text-[color:var(--text)]">
                        <li>• “Réserve Clio du 12 au 15 avril”</li>
                        <li>• “Génère le contrat et envoie au client”</li>
                        <li>• “Quels véhicules sont dispo ce week-end ?”</li>
                    </ul>
                </div>

                <button
                    type="button"
                    class="glm-btn-primary w-full px-4 py-2.5 text-sm font-extrabold"
                >
                    Activer l’IA (bientôt)
                </button>

                <p class="text-xs text-[color:var(--muted)]">
                    *Fonctionnalité premium — limites par plan.
                </p>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection