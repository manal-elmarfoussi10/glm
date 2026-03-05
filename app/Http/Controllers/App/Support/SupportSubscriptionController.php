<?php

namespace App\Http\Controllers\App\Support;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Company::query()
            ->with(['subscription', 'planRelation'])
            ->whereHas('subscription');

        if ($request->filled('status')) {
            $query->where('subscription_status', $request->status);
        }
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        $companies = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('app.support.subscriptions.index', [
            'title' => 'Abonnements',
            'companies' => $companies,
        ]);
    }

    public function extendTrial(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'trial_ends_at' => 'required|date|after_or_equal:today',
        ]);

        $subscription = $company->getOrCreateSubscription();
        $subscription->update([
            'trial_ends_at' => Carbon::parse($validated['trial_ends_at']),
            'status' => 'trial',
        ]);
        $subscription->syncToCompany();

        return back()->with('success', 'Essai prolongé jusqu\'au ' . Carbon::parse($validated['trial_ends_at'])->format('d/m/Y') . '.');
    }

    public function updateStatus(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,trial,suspended,expired',
        ]);

        $subscription = $company->getOrCreateSubscription();
        $subscription->update(['status' => $validated['status']]);
        $subscription->syncToCompany();

        return back()->with('success', 'Statut mis à jour.');
    }

    public function updateNotes(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate(['notes' => 'nullable|string|max:5000']);

        $subscription = $company->getOrCreateSubscription();
        $subscription->update(['notes' => $validated['notes'] ?? null]);

        return back()->with('success', 'Notes enregistrées.');
    }
}
