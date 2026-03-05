<?php

namespace App\Http\Controllers\App\Support;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\UpgradeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpgradeRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = UpgradeRequest::with(['company', 'requestedPlan', 'requestedByUser', 'assignedToUser'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20)->withQueryString();

        $view = request()->routeIs('app.admin.upgrade-requests.*')
            ? 'app.admin.upgrade-requests.index'
            : 'app.support.upgrade-requests.index';

        return view($view, [
            'title' => 'Demandes d’upgrade',
            'requests' => $requests,
        ]);
    }

    public function show(UpgradeRequest $upgradeRequest): View
    {
        $upgradeRequest->load(['company', 'requestedPlan', 'requestedByUser', 'reviewedByUser', 'assignedToUser']);
        $agents = \App\Models\User::whereIn('role', ['super_admin', 'support'])->orderBy('name')->get();

        return view('app.admin.upgrade-requests.show', [
            'title' => 'Demande d’upgrade – ' . $upgradeRequest->company->name,
            'request' => $upgradeRequest,
            'agents' => $agents,
        ]);
    }

    public function update(Request $request, UpgradeRequest $upgradeRequest): RedirectResponse
    {
        $validated = $request->validate([
            'internal_notes' => 'nullable|string|max:5000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $upgradeRequest->update([
            'internal_notes' => $validated['internal_notes'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
        ]);

        return back()->with('success', 'Demande mise à jour.');
    }

    public function approve(Request $request, UpgradeRequest $upgradeRequest): RedirectResponse
    {
        if ($upgradeRequest->status !== 'pending') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $validated = $request->validate(['notes' => 'nullable|string|max:2000']);

        $company = $upgradeRequest->company;
        $subscription = $company->getOrCreateSubscription();
        $subscription->update([
            'plan_id' => $upgradeRequest->requested_plan_id,
            'status' => $subscription->status === 'trial' ? 'trial' : 'active',
            'next_renewal_at' => $subscription->next_renewal_at ?? now()->addMonth(),
        ]);
        $subscription->syncToCompany();

        $upgradeRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::log('upgrade_request.approved', UpgradeRequest::class, (int) $upgradeRequest->id, [
            'company_id' => $company->id,
            'plan_id' => $upgradeRequest->requested_plan_id,
        ], ['notes' => $validated['notes'] ?? null]);

        return back()->with('success', 'Demande approuvée. Le plan de l’entreprise a été mis à jour.');
    }

    public function reject(Request $request, UpgradeRequest $upgradeRequest): RedirectResponse
    {
        if ($upgradeRequest->status !== 'pending') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $validated = $request->validate(['notes' => 'nullable|string|max:2000']);

        $upgradeRequest->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::log('upgrade_request.rejected', UpgradeRequest::class, (int) $upgradeRequest->id, [
            'company_id' => $upgradeRequest->company_id,
        ], ['notes' => $validated['notes'] ?? null]);

        return back()->with('success', 'Demande refusée.');
    }
}
