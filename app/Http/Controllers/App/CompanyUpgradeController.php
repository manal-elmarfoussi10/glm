<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\UpgradeRequest;
use App\Services\PlanGateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyUpgradeController extends Controller
{
    public function show(Company $company, PlanGateService $planGate): View
    {
        $plan = $planGate->getPlanForCompany($company);
        $lockedFeatures = [];
        foreach (PlanGateService::FEATURES as $feature) {
            if (! $planGate->can($company, $feature)) {
                $lockedFeatures[$feature] = $planGate->getFeatureLabel($feature);
            }
        }
        $limitsInfo = [];
        foreach ([PlanGateService::LIMIT_VEHICLES, PlanGateService::LIMIT_USERS, PlanGateService::LIMIT_BRANCHES] as $key) {
            $limit = $planGate->getLimit($company, $key);
            if ($limit !== null && $limit > 0) {
                $current = $planGate->getCurrentCount($company, $key);
                $limitsInfo[$key] = [
                    'label' => $planGate->getLimitLabel($key),
                    'current' => $current,
                    'limit' => $limit,
                    'reached' => $current >= $limit,
                ];
            }
        }
        $plans = Plan::active()->orderBy('monthly_price')->get();
        $pendingRequest = UpgradeRequest::where('company_id', $company->id)->where('status', 'pending')->with('requestedPlan')->first();

        return view('app.companies.upgrade.show', [
            'title' => 'Passer à un plan supérieur – ' . $company->name,
            'company' => $company,
            'currentPlan' => $plan,
            'lockedFeatures' => $lockedFeatures,
            'limitsInfo' => $limitsInfo,
            'plans' => $plans,
            'pendingRequest' => $pendingRequest,
            'limitReached' => request()->query('limit'),
        ]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'requested_plan_id' => 'required|exists:plans,id',
            'message' => 'nullable|string|max:2000',
        ]);

        $existing = UpgradeRequest::where('company_id', $company->id)->where('status', 'pending')->first();
        if ($existing) {
            return redirect()->route('app.companies.upgrade', $company)
                ->with('info', 'Vous avez déjà une demande d’upgrade en attente.');
        }

        UpgradeRequest::create([
            'company_id' => $company->id,
            'requested_plan_id' => $validated['requested_plan_id'],
            'requested_by' => auth()->id(),
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('app.companies.upgrade', $company)
            ->with('success', 'Demande d’upgrade envoyée. Notre équipe vous recontactera après validation.');
    }
}
