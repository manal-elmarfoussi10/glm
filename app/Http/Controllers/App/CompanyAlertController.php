<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyAlertDismissal;
use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyAlertController extends Controller
{
    public function __construct(
        private AlertService $alertService
    ) {}

    public function index(Request $request, Company $company): View
    {
        $all = $this->alertService->forCompany($company);

        if ($request->filled('type')) {
            $all = array_values(array_filter($all, fn ($a) => $a['type'] === $request->type));
        }
        if ($request->filled('severity')) {
            $all = array_values(array_filter($all, fn ($a) => $a['severity'] === $request->severity));
        }

        $typeLabels = [
            AlertService::TYPE_VEHICLE_COMPLIANCE => 'Conformité véhicule',
            AlertService::TYPE_RESERVATION_START => 'Départ demain',
            AlertService::TYPE_RESERVATION_RETURN => 'Retour aujourd\'hui',
            AlertService::TYPE_RESERVATION_LATE => 'Retour en retard',
            AlertService::TYPE_PAYMENT_DUE => 'Paiement',
        ];
        $severityLabels = [
            AlertService::SEVERITY_INFO => 'Info',
            AlertService::SEVERITY_WARNING => 'Attention',
            AlertService::SEVERITY_URGENT => 'Urgent',
        ];

        return view('app.companies.alerts.index', [
            'title' => 'Alertes – ' . $company->name,
            'company' => $company,
            'alerts' => $all,
            'typeLabels' => $typeLabels,
            'severityLabels' => $severityLabels,
        ]);
    }

    public function markDone(Request $request, Company $company): RedirectResponse
    {
        $identifier = $request->input('identifier');
        if (!$identifier) {
            return back()->with('error', 'Identifiant manquant.');
        }
        CompanyAlertDismissal::updateOrCreate(
            ['company_id' => $company->id, 'identifier' => $identifier],
            [
                'action' => CompanyAlertDismissal::ACTION_DONE,
                'dismissed_at' => now(),
                'user_id' => auth()->id(),
                'snooze_until' => null,
            ]
        );
        return back()->with('success', 'Alerte marquée comme traitée.');
    }

    public function snooze(Request $request, Company $company): RedirectResponse
    {
        $identifier = $request->input('identifier');
        $days = (int) $request->input('days', 1);
        $days = max(1, min(30, $days));
        if (!$identifier) {
            return back()->with('error', 'Identifiant manquant.');
        }
        $snoozeUntil = now()->addDays($days);
        CompanyAlertDismissal::updateOrCreate(
            ['company_id' => $company->id, 'identifier' => $identifier],
            [
                'action' => CompanyAlertDismissal::ACTION_SNOOZE,
                'snooze_until' => $snoozeUntil,
                'dismissed_at' => now(),
                'user_id' => auth()->id(),
            ]
        );
        return back()->with('success', 'Alerte reportée de ' . $days . ' jour(s).');
    }
}
