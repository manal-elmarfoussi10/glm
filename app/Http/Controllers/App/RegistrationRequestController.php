<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationRequestController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = User::query()
            ->where('role', 'company_admin')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('plan'), fn ($q) => $q->where('requested_plan', $request->plan))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to));

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'active')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $requests = (clone $baseQuery)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $plans = Plan::active()->orderBy('monthly_price')->get();

        return view('app.registration-requests.index', [
            'title' => "Demandes d'inscription",
            'requests' => $requests,
            'stats' => $stats,
            'plans' => $plans,
        ]);
    }

    public function show(User $user): View|RedirectResponse
    {
        $request = User::where('role', 'company_admin')->findOrFail($user->id);
        $plans = Plan::active()->orderBy('monthly_price')->get();

        return view('app.registration-requests.show', [
            'title' => 'Détail demande',
            'request' => $request,
            'plans' => $plans,
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $user = User::where('role', 'company_admin')->findOrFail($user->id);
        if ($user->status !== 'pending') {
            abort(403);
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'trial_days' => 'required|integer|min:0|max:365',
            'custom_pricing' => 'nullable|string|max:500',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $trialDays = (int) $validated['trial_days'];
        $trialEndsAt = $trialDays > 0 ? now()->addDays($trialDays) : null;

        \DB::transaction(function () use ($user, $validated, $plan, $trialEndsAt) {
            $company = Company::create([
                'name' => $user->requested_company_name,
                'ice' => $user->requested_ice ?? '',
                'status' => 'active',
            ]);

            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => 'trial',
                'trial_ends_at' => $trialEndsAt,
                'started_at' => now(),
                'next_renewal_at' => $trialEndsAt ?? now()->addMonth(),
            ]);
            $subscription->syncToCompany();

            $user->update([
                'company_id' => $company->id,
                'status' => 'active',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);

            $user->logRegistrationAction('approved', $validated['admin_notes'] ?? null, [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'trial_days' => $validated['trial_days'],
                'company_id' => $company->id,
                'custom_pricing' => $validated['custom_pricing'] ?? null,
            ]);

            AuditLog::log('registration.approved', User::class, (int) $user->id, null, [
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'trial_ends_at' => $trialEndsAt?->toDateTimeString(),
            ]);

            $user->notify(new \App\Notifications\CompanyApprovedNotification($company->name, url('/app')));
        });

        return redirect()
            ->route('app.registration-requests.index')
            ->with('success', "L'inscription a été approuvée. Entreprise et abonnement créés (plan : {$plan->name}).");
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $user = User::where('role', 'company_admin')->findOrFail($user->id);
        if ($user->status !== 'pending') {
            abort(403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $user->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        $user->logRegistrationAction('rejected', $validated['admin_notes'] ?? null, [
            'reason' => $validated['rejection_reason'],
        ]);

        AuditLog::log('registration.rejected', User::class, (int) $user->id, null, [
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()
            ->route('app.registration-requests.index')
            ->with('success', "La demande a été refusée.");
    }

    public function askInfo(Request $request, User $user): RedirectResponse
    {
        $user = User::where('role', 'company_admin')->findOrFail($user->id);
        if ($user->status !== 'pending') {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $note = $validated['message'];
        $adminNotes = ($user->admin_notes ? $user->admin_notes . "\n\n" : '') . '[Demande d’info ' . now()->format('d/m/Y H:i') . '] ' . $validated['message'];
        if (! empty($validated['admin_notes'])) {
            $adminNotes .= "\n" . $validated['admin_notes'];
        }

        $user->update(['admin_notes' => $adminNotes]);
        $user->logRegistrationAction('info_requested', $note, ['message' => $validated['message']]);

        AuditLog::log('registration.ask_info', User::class, (int) $user->id, null, ['message' => $validated['message']]);

        return redirect()
            ->route('app.registration-requests.index')
            ->with('success', 'Demande d’informations enregistrée. Vous pouvez contacter le demandeur par email.');
    }
}
