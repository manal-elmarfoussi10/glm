{{-- GLM Sidebar – Modern SaaS design (bigger text, better hover/active, clear hierarchy) --}}
@php
    $isDashboard = request()->routeIs('app.dashboard');
    $isRequests  = request()->routeIs('app.registration-requests.*');
    $isCompanies = request()->routeIs('app.companies.*');
    $isReservations = request()->routeIs('app.companies.reservations.*') || request()->routeIs('app.reservations.redirect');
    $isContracts = request()->routeIs('app.companies.contracts.*') || request()->routeIs('app.contracts.redirect');
    $isPayments = request()->routeIs('app.companies.payments.*') || request()->routeIs('app.payments.redirect');
    $isAlerts = request()->routeIs('app.companies.alerts.*') || request()->routeIs('app.alerts.redirect');
    $isReports = request()->routeIs('app.companies.reports.*') || request()->routeIs('app.reports.redirect');
    $isBranches = request()->routeIs('app.companies.branches.*') || request()->routeIs('app.branches.redirect');
    $isVehicles = request()->routeIs('app.companies.vehicles.*') || request()->routeIs('app.vehicles.redirect');
    $isCompanyUsers = request()->routeIs('app.companies.users.*');
    $isAdminUsers = request()->routeIs('app.admin.users.*');
    $isAdminContracts = request()->routeIs('app.admin.contract-templates.*');
    $isAdminPlans = request()->routeIs('app.admin.plans.*');
    $isAdminSettings = request()->routeIs('app.admin.settings.*');
    $isSubscriptions = request()->routeIs('app.subscriptions.*');
    $isUpgradeRequests = request()->routeIs('app.admin.upgrade-requests.*');
    $isInbox = request()->routeIs('app.inbox.*');
    $isSupportPage = request()->routeIs('app.support.*');
    $isJournal = request()->routeIs('app.journal.*') || request()->routeIs('app.companies.activity.index') || request()->routeIs('app.activity.redirect');
    $isCustomers = request()->routeIs('app.companies.customers.*') || request()->routeIs('app.customers.redirect');
    $isProfitability = request()->routeIs('app.companies.fleet.profitability.*') || request()->routeIs('app.fleet.profitability.redirect');
    $isExpenses = request()->routeIs('app.companies.expenses.*') || request()->routeIs('app.expenses.redirect');
    $isPartners = request()->routeIs('app.companies.partners.*') || request()->routeIs('app.companies.partner-requests.*') || request()->routeIs('app.companies.partner-settings.*') || request()->routeIs('app.partners.redirect');

    $role = auth()->user()?->role ?? null;
    $isSuperAdmin = $role === 'super_admin';
    $isSupport = $role === 'support';
    $isCompanyAdmin = $role === 'company_admin';
    $isAgent = $role === 'agent';

    $isPlatformStaff = in_array($role, ['super_admin', 'support'], true);
    $isCompanySide = in_array($role, ['company_admin', 'agent'], true);
    $canAccessPlatformAdmin = $isPlatformStaff;

    $userCompany = $isCompanySide && auth()->user()?->company_id ? \App\Models\Company::find(auth()->user()->company_id) : null;
    $planGate = $isCompanySide ? app(\App\Services\PlanGateService::class) : null;

    $canReservations = !$userCompany || !$planGate || $planGate->can($userCompany, 'reservations');
    $canContracts = !$userCompany || !$planGate || $planGate->can($userCompany, 'contracts');
    $canPayments = !$userCompany || !$planGate || $planGate->can($userCompany, 'payments');
    $canAlerts = !$userCompany || !$planGate || $planGate->can($userCompany, 'alerts');
    $canBranches = !$userCompany || !$planGate || $planGate->can($userCompany, 'branches');
    $canReports = !$userCompany || !$planGate || $planGate->can($userCompany, 'reports');
    $canProfitability = !$userCompany || !$planGate || $planGate->can($userCompany, 'profitability');
    $canPartnerAvailability = !$userCompany || !$planGate || $planGate->can($userCompany, 'partner_availability');

    $openOperations = $isReservations || $isContracts || $isPayments || $isCustomers;
    $openFleet = $isVehicles || $isExpenses || $isProfitability;
    $openOrganisation = $isBranches || $isCompanies || $isCompanyUsers;
    $openOutils = $isReports || $isPartners || $isAlerts;
@endphp

<aside
    class="fixed left-0 inset-y-0 z-50 w-[280px] flex flex-col
           lg:top-3 lg:bottom-3 lg:left-3 lg:rounded-2xl lg:border lg:shadow-[0_20px_60px_rgba(0,0,0,.45)]
           bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border-r border-white/10 lg:border-white/10"
    aria-label="Sidebar"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    x-data="{
        openSections: {
            company_ops: @json($openOperations),
            company_fleet: @json($openFleet),
            company_org: @json($openOrganisation),
            company_tools: @json($openOutils),
            platform: true,
        },
        toggle(section) { this.openSections[section] = !this.openSections[section]; }
    }"
>
    {{-- helper classes: smaller text, active = gradient + thin left bar, nav scrolls inside --}}
    @php
        $baseItem = "group relative flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium transition";
        $iconWrap = "grid place-items-center h-9 w-9 shrink-0 rounded-lg border border-white/10 bg-white/5 text-white/80 transition";
        $activeItem = "bg-gradient-to-r from-[#2563EB]/90 to-[#3B82F6]/70 text-white shadow-[0_4px_16px_rgba(37,99,235,.12)]";
        $inactiveItem = "text-white/70 hover:text-white hover:bg-white/6 hover:-translate-y-[1px]";
        $activeLeftBar = "before:content-[''] before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-6 before:w-[3px] before:rounded-r before:bg-[#60A5FA]";
        $lockedHint = "text-amber-300/90 hover:bg-amber-500/10 hover:text-amber-200";
        $sectionTitle = "px-3 pt-3 pb-1.5 text-[10px] font-semibold uppercase tracking-widest text-white/35 border-t border-white/10 mt-2 first:border-0 first:mt-0";
        $sectionBtn = "flex w-full items-center justify-between gap-2 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-widest text-white/35 hover:text-white/55 transition";
        $chev = "h-3.5 w-3.5 shrink-0 text-white/35 transition-transform";
    @endphp

    <div class="h-full flex flex-col min-h-0">
    {{-- Header --}}
    <div class="relative shrink-0 px-4 py-3 border-b border-white/10">
        <a href="{{ route('app.dashboard') }}" class="flex items-center justify-center">
            <img src="{{ url('images/light-logo.png') }}" alt="GLM" class="h-10 w-auto object-contain" />
        </a>
        <button
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 lg:hidden inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 transition"
            @click="sidebarOpen = false"
            aria-label="Fermer"
        >
            <svg class="h-4 w-4 text-white/80" viewBox="0 0 24 24" fill="none">
                <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    {{-- Nav (scrollable; sidebar stays fixed height) --}}
    <nav class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden px-3 py-3 space-y-1.5 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-white/10">
        {{-- Primary --}}
        <a href="{{ route('app.dashboard') }}"
           class="{{ $baseItem }} {{ $isDashboard ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
            <span class="{{ $iconWrap }} {{ $isDashboard ? 'bg-white/10 border-white/15' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </span>
            <span class="truncate">Dashboard</span>
        </a>

        @if ($isPlatformStaff)
            <a href="{{ route('app.registration-requests.index') }}"
               class="{{ $baseItem }} {{ $isRequests ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                <span class="{{ $iconWrap }} {{ $isRequests ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7v1h14v-1a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <span class="truncate">Demandes d'inscription</span>
            </a>
        @endif

        {{-- COMPANY SIDE --}}
        @if ($isCompanySide)
            <div class="{{ $sectionTitle }}">Opérations</div>

            <a href="{{ $canReservations ? route('app.reservations.redirect') : route('app.companies.upgrade', $userCompany) }}"
               class="{{ $baseItem }} {{ ($canReservations && $isReservations) ? $activeItem.' '.$activeLeftBar : ($canReservations ? $inactiveItem : $lockedHint) }}">
                <span class="{{ $iconWrap }} {{ $isReservations ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
                <span class="truncate">Réservations</span>
                @if (!$canReservations)
                    <span class="ml-auto text-[11px] rounded-full border border-amber-400/25 bg-amber-400/10 px-2 py-0.5">Upgrade</span>
                @endif
            </a>

            <a href="{{ route('app.customers.redirect') }}"
               class="{{ $baseItem }} {{ $isCustomers ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                <span class="{{ $iconWrap }} {{ $isCustomers ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                    </svg>
                </span>
                <span class="truncate">Clients</span>
            </a>

            <a href="{{ $canPayments ? route('app.payments.redirect') : route('app.companies.upgrade', $userCompany) }}"
               class="{{ $baseItem }} {{ ($canPayments && $isPayments) ? $activeItem.' '.$activeLeftBar : ($canPayments ? $inactiveItem : $lockedHint) }}">
                <span class="{{ $iconWrap }} {{ $isPayments ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                </span>
                <span class="truncate">Paiements</span>
                @if (!$canPayments)
                    <span class="ml-auto text-[11px] rounded-full border border-amber-400/25 bg-amber-400/10 px-2 py-0.5">Upgrade</span>
                @endif
            </a>

            <a href="{{ $canContracts ? route('app.contracts.redirect') : route('app.companies.upgrade', $userCompany) }}"
               class="{{ $baseItem }} {{ ($canContracts && $isContracts) ? $activeItem.' '.$activeLeftBar : ($canContracts ? $inactiveItem : $lockedHint) }}">
                <span class="{{ $iconWrap }} {{ $isContracts ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span class="truncate">Contrats</span>
                @if (!$canContracts)
                    <span class="ml-auto text-[11px] rounded-full border border-amber-400/25 bg-amber-400/10 px-2 py-0.5">Upgrade</span>
                @endif
            </a>

            <div class="{{ $sectionTitle }}">Flotte</div>

            <a href="{{ $userCompany ? route('app.companies.vehicles.index', $userCompany) : route('app.dashboard') }}"
               class="{{ $baseItem }} {{ $isVehicles ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                <span class="{{ $iconWrap }} {{ $isVehicles ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7h12m0 0l-4-4m4 4l-4 4M6 17h12"/>
                    </svg>
                </span>
                <span class="truncate">Véhicules</span>
            </a>

            <a href="{{ route('app.expenses.redirect') }}"
               class="{{ $baseItem }} {{ $isExpenses ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                <span class="{{ $iconWrap }} {{ $isExpenses ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 1.343-3 3m6 0a3 3 0 10-6 0m9 7H6a2 2 0 01-2-2V8a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span class="truncate">Dépenses</span>
            </a>

            <a href="{{ $canProfitability ? route('app.fleet.profitability.redirect') : route('app.companies.upgrade', $userCompany) }}"
               class="{{ $baseItem }} {{ ($canProfitability && $isProfitability) ? $activeItem.' '.$activeLeftBar : ($canProfitability ? $inactiveItem : $lockedHint) }}">
                <span class="{{ $iconWrap }} {{ $isProfitability ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 3v18m4-14v14m4-10v10M7 13v8"/>
                    </svg>
                </span>
                <span class="truncate">Rentabilité</span>
                @if (!$canProfitability)
                    <span class="ml-auto text-[11px] rounded-full border border-amber-400/25 bg-amber-400/10 px-2 py-0.5">Upgrade</span>
                @endif
            </a>

            <div class="{{ $sectionTitle }}">Organisation</div>

            <a href="{{ $canBranches ? route('app.branches.redirect') : route('app.companies.upgrade', $userCompany) }}"
               class="{{ $baseItem }} {{ ($canBranches && $isBranches) ? $activeItem.' '.$activeLeftBar : ($canBranches ? $inactiveItem : $lockedHint) }}">
                <span class="{{ $iconWrap }} {{ $isBranches ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 21h18M6 21V7a2 2 0 012-2h8a2 2 0 012 2v14M9 9h6M9 13h6M9 17h6"/>
                    </svg>
                </span>
                <span class="truncate">Agences</span>
                @if (!$canBranches)
                    <span class="ml-auto text-[11px] rounded-full border border-amber-400/25 bg-amber-400/10 px-2 py-0.5">Upgrade</span>
                @endif
            </a>

            @if ($isCompanyAdmin && $userCompany)
                <a href="{{ route('app.companies.users.index', $userCompany) }}"
                   class="{{ $baseItem }} {{ $isCompanyUsers ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }} {{ $isCompanyUsers ? 'bg-white/10 border-white/15' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M12 12a4 4 0 100-8 4 4 0 000 8z"/>
                        </svg>
                    </span>
                    <span class="truncate">Équipe</span>
                </a>
            @endif

            @if ($userCompany)
                <a href="{{ route('app.companies.show', $userCompany) }}"
                   class="{{ $baseItem }} {{ ($isCompanies && $isCompanySide) ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }} {{ ($isCompanies && $isCompanySide) ? 'bg-white/10 border-white/15' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 21V7a2 2 0 012-2h12a2 2 0 012 2v14M8 10h8M8 14h8M8 18h8"/>
                        </svg>
                    </span>
                    <span class="truncate">Mon entreprise</span>
                </a>
            @endif

            <div class="{{ $sectionTitle }}">Outils</div>

            @if ($isCompanyAdmin)
                <a href="{{ $canReports ? route('app.reports.redirect') : route('app.companies.upgrade', $userCompany) }}"
                   class="{{ $baseItem }} {{ ($canReports && $isReports) ? $activeItem.' '.$activeLeftBar : ($canReports ? $inactiveItem : $lockedHint) }}">
                    <span class="{{ $iconWrap }} {{ $isReports ? 'bg-white/10 border-white/15' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17v-6a2 2 0 00-2-2H5v8m8 0V7a2 2 0 012-2h2v12m4 0V11a2 2 0 012-2h2v8"/>
                        </svg>
                    </span>
                    <span class="truncate">Rapports</span>
                    @if (!$canReports)
                        <span class="ml-auto text-[11px] rounded-full border border-amber-400/25 bg-amber-400/10 px-2 py-0.5">Upgrade</span>
                    @endif
                </a>
            @endif

            <a href="{{ $canPartnerAvailability ? route('app.partners.redirect') : route('app.companies.upgrade', $userCompany) }}"
               class="{{ $baseItem }} {{ ($canPartnerAvailability && $isPartners) ? $activeItem.' '.$activeLeftBar : ($canPartnerAvailability ? $inactiveItem : $lockedHint) }}">
                <span class="{{ $iconWrap }} {{ $isPartners ? 'bg-white/10 border-white/15' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M12 12a4 4 0 100-8 4 4 0 000 8z"/>
                    </svg>
                </span>
                <span class="truncate">Partenaires</span>
                @if (!$canPartnerAvailability)
                    <span class="ml-auto text-[11px] rounded-full border border-amber-400/25 bg-amber-400/10 px-2 py-0.5">Upgrade</span>
                @endif
            </a>

                @if ($canAlerts)
                <a href="{{ route('app.alerts.redirect') }}"
                   class="{{ $baseItem }} {{ $isAlerts ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }} {{ $isAlerts ? 'bg-white/10 border-white/15' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5"/>
                        </svg>
                    </span>
                    <span class="truncate">Alertes</span>
                </a>
                @endif

                <a href="{{ route('app.activity.redirect') }}"
                   class="{{ $baseItem }} {{ $isJournal && $isCompanySide ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }} {{ $isJournal && $isCompanySide ? 'bg-white/10 border-white/15' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="truncate">Journal d'activité</span>
                </a>

                <a href="{{ route('app.support.index') }}"
                   class="{{ $baseItem }} {{ $isSupportPage ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }} {{ $isSupportPage ? 'bg-white/10 border-white/15' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </span>
                    <span class="truncate">Support</span>
                </a>
@endif

        {{-- PLATFORM SIDE --}}
        @if ($canAccessPlatformAdmin ?? false)
            <div class="mt-4 pt-4 border-t border-white/10">
                <div class="{{ $sectionTitle }}">Plateforme</div>

                <a href="{{ route('app.companies.index') }}"
                   class="{{ $baseItem }} {{ ($isCompanies && !$isCompanySide) ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 21h18M6 21V7a2 2 0 012-2h8a2 2 0 012 2v14"/>
                        </svg>
                    </span>
                    <span class="truncate">Entreprises</span>
                </a>

                <a href="{{ route('app.admin.users.index') }}"
                   class="{{ $baseItem }} {{ $isAdminUsers ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4zm-7 10a7 7 0 0114 0"/>
                        </svg>
                    </span>
                    <span class="truncate">Équipe GLM</span>
                </a>

                <a href="{{ route('app.admin.contract-templates.index') }}"
                   class="{{ $baseItem }} {{ $isAdminContracts ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <span class="truncate">Bibliothèque contrats</span>
                </a>

                <a href="{{ route('app.subscriptions.index') }}"
                   class="{{ $baseItem }} {{ $isSubscriptions ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </span>
                    <span class="truncate">Abonnements</span>
                </a>

                <a href="{{ route('app.admin.upgrade-requests.index') }}"
                   class="{{ $baseItem }} {{ $isUpgradeRequests ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6v12m0-12l4 4m-4-4L8 10"/>
                        </svg>
                    </span>
                    <span class="truncate">Demandes d’upgrade</span>
                </a>

                <a href="{{ route('app.inbox.index') }}"
                   class="{{ $baseItem }} {{ $isInbox ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </span>
                    <span class="truncate">Support</span>
                </a>

                <a href="{{ route('app.journal.index') }}"
                   class="{{ $baseItem }} {{ $isJournal ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                    <span class="{{ $iconWrap }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                        </svg>
                    </span>
                    <span class="truncate">Journal d’audit</span>
                </a>

                @if ($isSuperAdmin)
                    <a href="{{ route('app.admin.plans.index') }}"
                       class="{{ $baseItem }} {{ $isAdminPlans ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                        <span class="{{ $iconWrap }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 7h16M4 12h16M4 17h16"/>
                            </svg>
                        </span>
                        <span class="truncate">Plans & tarifs</span>
                    </a>

                    <a href="{{ route('app.admin.settings.index') }}"
                       class="{{ $baseItem }} {{ $isAdminSettings ? $activeItem.' '.$activeLeftBar : $inactiveItem }}">
                        <span class="{{ $iconWrap }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15.5a3.5 3.5 0 110-7 3.5 3.5 0 010 7z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19.4 15a7.96 7.96 0 00.1-2l2-1.5-2-3.5-2.4.5a7.8 7.8 0 00-1.7-1L13 3h-2l-.4 2.5a7.8 7.8 0 00-1.7 1L6.5 6 4.5 9.5l2 1.5a7.96 7.96 0 000 2l-2 1.5 2 3.5 2.4-.5a7.8 7.8 0 001.7 1L11 21h2l.4-2.5a7.8 7.8 0 001.7-1l2.4.5 2-3.5-2-1.5z"/>
                            </svg>
                        </span>
                        <span class="truncate">Paramètres plateforme</span>
                    </a>
                @endif
            </div>
        @endif
    </nav>

    {{-- Footer --}}
    <div class="shrink-0 px-4 py-3 border-t border-white/10">
        <div class="flex items-center justify-between text-xs text-white/40">
            <span>© {{ date('Y') }} GLM</span>
            <span class="rounded-full border border-white/10 bg-white/5 px-2 py-0.5">v1</span>
        </div>
    </div>
    </div>
</aside>