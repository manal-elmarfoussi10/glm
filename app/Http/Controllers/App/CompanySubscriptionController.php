<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanySubscriptionController extends Controller
{
    /** Only super_admin and support may manage subscription (extend trial, activate, suspend, change plan). */
    private function ensurePlatformCanManageSubscription(): void
    {
        $role = auth()->user()?->role ?? null;
        if (! in_array($role, ['super_admin', 'support'], true)) {
            abort(403, 'Réservé à l’équipe plateforme (Super Admin / Support).');
        }
    }

    public function changePlan(Company $company): View
    {
        $this->ensurePlatformCanManageSubscription();
        $subscription = $company->getOrCreateSubscription();
        $plans = Plan::active()->orderBy('monthly_price')->get();

        return view('app.companies.subscription.change-plan', [
            'title' => 'Changer de plan – ' . $company->name,
            'company' => $company,
            'subscription' => $subscription,
            'plans' => $plans,
        ]);
    }

    public function updatePlan(Request $request, Company $company): RedirectResponse
    {
        $this->ensurePlatformCanManageSubscription();
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $subscription = $company->getOrCreateSubscription();
        $plan = Plan::findOrFail($validated['plan_id']);

        $oldPlanId = $subscription->plan_id;
        $subscription->update([
            'plan_id' => $plan->id,
            'status' => $subscription->status === 'trial' ? 'trial' : 'active',
            'next_renewal_at' => $subscription->next_renewal_at ?? now()->addMonth(),
        ]);
        $subscription->syncToCompany();

        AuditLog::log('subscription.plan_changed', Company::class, (int) $company->id, ['plan_id' => $oldPlanId], ['plan_id' => $plan->id, 'plan_name' => $plan->name]);

        return redirect()
            ->route('app.companies.show', $company)
            ->with('success', 'Plan mis à jour : ' . $plan->name . '.');
    }

    public function activate(Company $company): RedirectResponse
    {
        $this->ensurePlatformCanManageSubscription();
        $subscription = $company->getOrCreateSubscription();
        $previousStatus = $subscription->status;
        $subscription->update(['status' => 'active']);
        $subscription->syncToCompany();

        AuditLog::log('subscription.activated', Company::class, (int) $company->id, ['status' => $previousStatus], ['status' => 'active']);

        return back()->with('success', 'Abonnement activé.');
    }

    public function suspend(Company $company): RedirectResponse
    {
        $this->ensurePlatformCanManageSubscription();
        $subscription = $company->getOrCreateSubscription();
        $previousStatus = $subscription->status;
        $subscription->update(['status' => 'suspended']);
        $subscription->syncToCompany();

        AuditLog::log('subscription.suspended', Company::class, (int) $company->id, ['status' => $previousStatus], ['status' => 'suspended']);

        return back()->with('success', 'Abonnement suspendu.');
    }

    public function extendTrial(Request $request, Company $company): RedirectResponse
    {
        $this->ensurePlatformCanManageSubscription();
        $validated = $request->validate([
            'trial_ends_at' => 'required|date|after_or_equal:today',
        ]);

        $subscription = $company->getOrCreateSubscription();
        $subscription->update([
            'trial_ends_at' => $validated['trial_ends_at'],
            'status' => 'trial',
        ]);
        $subscription->syncToCompany();

        AuditLog::log('subscription.trial_extended', Company::class, (int) $company->id, null, ['trial_ends_at' => $validated['trial_ends_at']]);

        return back()->with('success', 'Essai prolongé jusqu’au ' . \Carbon\Carbon::parse($validated['trial_ends_at'])->format('d/m/Y') . '.');
    }
}
