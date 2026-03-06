<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Reservation;
use App\Models\ReservationPayment;
use App\Models\Ticket;
use App\Models\UpgradeRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AlertService;
use App\Services\PlanGateService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private AlertService $alertService,
        private PlanGateService $planGate
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->role === 'support') {
            return $this->supportDashboard();
        }

        if ($user->role === 'super_admin') {
            return $this->superAdminDashboard();
        }

        if ($user->role === 'agent') {
            return $this->agentDashboard($user);
        }

        return $this->companyDashboard($user);
    }

    private function companyDashboard(User $user): View|RedirectResponse
    {
        $company = $user->company_id ? Company::find($user->company_id) : null;
        if ($company && $company->needsOnboarding()) {
            return redirect()->route('app.onboarding.show');
        }
        return $this->companyAnalyticsDashboard($company, false);
    }

    private function agentDashboard(User $user): View|RedirectResponse
    {
        $company = $user->company_id ? Company::find($user->company_id) : null;
        if ($company && $company->needsOnboarding()) {
            return redirect()->route('app.onboarding.show');
        }
        return $this->companyAnalyticsDashboard($company, true);
    }

    /**
     * Shared analytics dashboard for company_admin and agent (GLM Analytics Dashboard).
     */
    private function companyAnalyticsDashboard(?Company $company, bool $isAgent): View
    {
        $alerts = $company ? $this->alertService->forCompany($company) : [];
        $alertsSlice = array_slice($alerts, 0, 10);
        $criticalAlertsCount = $company ? count(array_filter($alerts, fn ($a) => ($a['severity'] ?? '') === AlertService::SEVERITY_URGENT)) : 0;

        $branches = $company ? $company->branches()->orderBy('name')->get() : collect();
        $reservationsToday = collect();
        $pickupsToday = collect();
        $returnsToday = collect();
        $vehiclesCount = 0;
        $vehiclesAvailableCount = 0;
        $revenueMonth = 0;
        $paymentsPending = 0;
        $returnsTodayCount = 0;
        $chartRevenue30 = ['labels' => [], 'values' => []];
        $chartReservationsByStatus = ['labels' => [], 'values' => []];
        $chartFleetUtilization = ['labels' => [], 'values' => []];

        if ($company) {
            $todayStart = Carbon::today();
            $todayEnd = Carbon::today()->endOfDay();
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();

            $reservationsToday = $company->reservations()
                ->with(['vehicle', 'customer'])
                ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
                ->where('start_at', '<=', $todayEnd)
                ->where('end_at', '>=', $todayStart)
                ->orderBy('start_at')
                ->limit(20)
                ->get();

            $pickupsToday = $company->reservations()
                ->with(['vehicle', 'customer'])
                ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
                ->whereDate('start_at', Carbon::today())
                ->orderBy('start_at')
                ->limit(20)
                ->get();

            $returnsToday = $company->reservations()
                ->with(['vehicle', 'customer'])
                ->whereIn('status', [Reservation::STATUS_IN_PROGRESS, Reservation::STATUS_CONFIRMED])
                ->whereDate('end_at', Carbon::today())
                ->orderBy('end_at')
                ->limit(20)
                ->get();

            $returnsTodayCount = $returnsToday->count();
            $vehiclesCount = $company->vehicles()->count();
            $vehiclesAvailableCount = $company->vehicles()->where('vehicles.status', Vehicle::STATUS_AVAILABLE)->count();
            $revenueMonth = (float) ReservationPayment::query()
                ->whereHas('reservation', fn ($q) => $q->where('company_id', $company->id))
                ->where('paid_at', '>=', $monthStart)
                ->where('paid_at', '<=', $monthEnd)
                ->where('type', '!=', 'refund')
                ->sum('amount');

            $paymentsPending = (int) $company->reservations()
                ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
                ->whereDoesntHave('payments', fn ($q) => $q->where('type', 'rental'))
                ->count();

            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $chartRevenue30['labels'][] = $date->format('d/m');
                $chartRevenue30['values'][] = (float) ReservationPayment::query()
                    ->whereHas('reservation', fn ($q) => $q->where('company_id', $company->id))
                    ->whereDate('paid_at', $date)
                    ->where('type', '!=', 'refund')
                    ->sum('amount');
            }

            $statusCounts = $company->reservations()
                ->selectRaw('status, count(*) as cnt')
                ->where('start_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('status')
                ->pluck('cnt', 'status');
            $statusLabels = [
                Reservation::STATUS_CONFIRMED => 'Confirmées',
                Reservation::STATUS_IN_PROGRESS => 'En cours',
                Reservation::STATUS_COMPLETED => 'Terminées',
                Reservation::STATUS_CANCELLED => 'Annulées',
                Reservation::STATUS_DRAFT => 'Brouillons',
            ];
            foreach ($statusLabels as $status => $label) {
                if (($statusCounts[$status] ?? 0) > 0) {
                    $chartReservationsByStatus['labels'][] = $label;
                    $chartReservationsByStatus['values'][] = (int) $statusCounts[$status];
                }
            }
            if (empty($chartReservationsByStatus['labels'])) {
                $chartReservationsByStatus = ['labels' => ['Aucune'], 'values' => [1]];
            }

            $fleetUtil = $company->reservations()
                ->select('vehicle_id')
                ->selectRaw('count(*) as cnt')
                ->where('start_at', '>=', Carbon::now()->subDays(30))
                ->whereNotNull('vehicle_id')
                ->groupBy('vehicle_id')
                ->orderByDesc('cnt')
                ->limit(8)
                ->get();
            $vehicleIds = $fleetUtil->pluck('vehicle_id')->filter()->unique()->values()->all();
            $vehiclesMap = $vehicleIds ? Vehicle::whereIn('id', $vehicleIds)->get()->keyBy('id') : collect();
            foreach ($fleetUtil as $row) {
                $v = $vehiclesMap->get($row->vehicle_id);
                $chartFleetUtilization['labels'][] = $v ? ($v->plate ?? $v->name ?? 'Véhicule #'.$row->vehicle_id) : 'Véhicule #'.$row->vehicle_id;
                $chartFleetUtilization['values'][] = (int) $row->cnt;
            }
            if (empty($chartFleetUtilization['labels'])) {
                $chartFleetUtilization = ['labels' => ['—'], 'values' => [0]];
            }
        }

        $plan = $company ? $this->planGate->getPlanForCompany($company) : null;
        $hasAiAccess = $plan && $plan->ai_access;

        $todoItems = $company ? [
            ['label' => 'Assurance expirant bientôt', 'count' => 0, 'url' => $company ? route('app.companies.vehicles.index', $company) : null],
            ['label' => 'Facture impayée', 'count' => $paymentsPending, 'url' => $company ? route('app.companies.payments.index', $company) : null],
        ] : [];

        $lastActivityAt = $company && $company->updated_at ? $company->updated_at->diffForHumans() : null;

        $onboardingChecklist = $company && $company->onboarding_completed_at ? $company->onboardingChecklist() : [];
        $showOnboardingChecklist = count(array_filter($onboardingChecklist, fn ($i) => ! $i['done'])) > 0;

        return view('app.dashboard.analytics', [
            'title' => 'Tableau de bord',
            'company' => $company,
            'isAgent' => $isAgent,
            'branches' => $branches,
            'reservationsToday' => $reservationsToday,
            'pickupsToday' => $pickupsToday,
            'returnsToday' => $returnsToday,
            'vehiclesCount' => $vehiclesCount,
            'vehiclesAvailableCount' => $vehiclesAvailableCount,
            'revenueMonth' => $revenueMonth,
            'paymentsPending' => $paymentsPending,
            'returnsTodayCount' => $returnsTodayCount,
            'criticalAlertsCount' => $criticalAlertsCount,
            'alerts' => $alertsSlice,
            'chartRevenue30' => $chartRevenue30,
            'chartReservationsByStatus' => $chartReservationsByStatus,
            'chartFleetUtilization' => $chartFleetUtilization,
            'todoItems' => $todoItems,
            'hasAiAccess' => $hasAiAccess,
            'lastActivityAt' => $lastActivityAt,
            'onboardingChecklist' => $onboardingChecklist,
            'showOnboardingChecklist' => $showOnboardingChecklist,
        ]);
    }

    private function superAdminDashboard(): View
    {
        $query = User::where('role', 'company_admin');

        $total = (clone $query)->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $approved = (clone $query)->where('status', 'active')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();

        $stats = [
            'mrr' => '0 MAD',
            'companies' => $total,
            'active_users' => $approved,
            'pending' => $pending,
        ];

        $chartData = [
            'labels' => ['En attente', 'Approuvées', 'Refusées'],
            'values' => [$pending, $approved, $rejected],
        ];

        $revenueChart = [
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            'values' => [12, 19, 15, 25, 22, 28],
        ];

        $recentRegistrations = User::where('role', 'company_admin')
            ->with('company')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $platformAlerts = $this->alertService->platformAlerts();
        $pendingUpgradeRequests = UpgradeRequest::where('status', 'pending')->count();

        return view('app.dashboard.index', [
            'title' => 'Dashboard',
            'company' => null,
            'alerts' => [],
            'platformAlerts' => $platformAlerts,
            'pendingUpgradeRequests' => $pendingUpgradeRequests,
            'stats' => $stats,
            'chartData' => $chartData,
            'revenueChart' => $revenueChart,
            'recentRegistrations' => $recentRegistrations,
        ]);
    }

    private function supportDashboard(): View
    {
        $activeCompanies = Company::where('status', 'active')->count();
        $trialCompanies = Company::where('subscription_status', 'trial')->count();
        $expiringTrials = Company::where('subscription_status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(14)])
            ->count();
        $pendingRequests = User::where('role', 'company_admin')->where('status', 'pending')->count();
        $suspendedCompanies = Company::where('status', 'suspended')->count();
        $ticketsOpen = Ticket::whereIn('status', ['new', 'open', 'waiting'])->count();
        $pendingUpgradeRequests = UpgradeRequest::where('status', 'pending')->count();

        $stats = [
            'active_companies' => $activeCompanies,
            'trial_companies' => $trialCompanies,
            'expiring_trials' => $expiringTrials,
            'pending_requests' => $pendingRequests,
            'pending_upgrade_requests' => $pendingUpgradeRequests,
            'suspended_companies' => $suspendedCompanies,
            'tickets' => $ticketsOpen,
        ];

        $recentPending = User::where('role', 'company_admin')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $expiringList = Company::where('subscription_status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(14)])
            ->orderBy('trial_ends_at')
            ->limit(5)
            ->get();

        $urgentTickets = Ticket::whereIn('status', ['new', 'open'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('app.dashboard.support', [
            'title' => 'Dashboard Support',
            'stats' => $stats,
            'recentPending' => $recentPending,
            'expiringList' => $expiringList,
            'urgentTickets' => $urgentTickets,
        ]);
    }
}
