<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        if (in_array($user->role ?? null, ['company_admin', 'agent'], true) && $user->company_id) {
            $company = Company::find($user->company_id);
            if ($company) {
                return redirect()->route('app.companies.show', $company);
            }
        }
        if (! in_array($user->role ?? null, ['super_admin', 'support'], true)) {
            abort(403, 'Accès réservé à l’équipe plateforme.');
        }

        $query = Company::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('ice', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('city', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('plan'), fn ($q) => $q->where('plan', $request->plan))
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->city));

        $stats = [
            'total' => (clone $query)->count(),
            'active' => Company::where('status', 'active')->count(),
            'suspended' => Company::where('status', 'suspended')->count(),
        ];

        $companies = (clone $query)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $cities = Company::whereNotNull('city')->distinct()->pluck('city')->sort()->values();

        return view('app.companies.index', [
            'title' => 'Entreprises',
            'companies' => $companies,
            'stats' => $stats,
            'cities' => $cities,
        ]);
    }

    public function create(): View
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Réservé aux super administrateurs.');
        }
        return view('app.companies.create', [
            'title' => 'Nouvelle entreprise',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Réservé aux super administrateurs.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ice' => 'nullable|string|max:32',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:128',
            'address' => 'nullable|string|max:500',
            'plan' => 'nullable|string|in:starter,professional,enterprise',
            'status' => 'required|string|in:active,suspended',
        ]);

        $validated['subscription_status'] = 'trial';
        $validated['trial_ends_at'] = now()->addDays(14);

        Company::create($validated);

        return redirect()
            ->route('app.companies.index')
            ->with('success', 'Entreprise créée avec succès.');
    }

    public function edit(Company $company): View
    {
        return view('app.companies.edit', [
            'title' => 'Modifier – ' . $company->name,
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ice' => 'nullable|string|max:32',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:128',
            'address' => 'nullable|string|max:500',
            'plan' => 'nullable|string|in:starter,professional,enterprise',
            'status' => 'required|string|in:active,suspended',
        ]);

        $company->update($validated);

        return redirect()
            ->route('app.companies.show', $company)
            ->with('success', 'Entreprise mise à jour.');
    }

    public function show(Company $company): View
    {
        $company->loadCount(['users', 'branches'])->load(['defaultContractTemplate', 'planRelation']);
        $subscription = $company->getOrCreateSubscription();
        $subscription->load('plan');

        $activityLogs = AuditLog::query()
            ->where(function ($q) use ($company) {
                $q->where('subject_type', Company::class)
                    ->where('subject_id', $company->id)
                    ->orWhereJsonContains('new_values->company_id', $company->id);
            })
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $companyAdmin = $company->users()->where('role', 'company_admin')->first();

        return view('app.companies.show', [
            'title' => $company->name,
            'company' => $company,
            'subscription' => $subscription,
            'activityLogs' => $activityLogs,
            'companyAdmin' => $companyAdmin,
        ]);
    }
}
