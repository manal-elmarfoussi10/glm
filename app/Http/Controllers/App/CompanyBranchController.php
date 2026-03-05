<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Services\PlanGateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyBranchController extends Controller
{
    private function ensureBranchBelongsToCompany(Branch $branch, Company $company): void
    {
        if ($branch->company_id != $company->id) {
            abort(404);
        }
    }

    public function index(Request $request, Company $company): View
    {
        $query = $company->branches()->withCount('vehicles')->with('manager');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        $branches = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('app.companies.branches.index', [
            'title' => 'Agences – ' . $company->name,
            'company' => $company,
            'branches' => $branches,
        ]);
    }

    public function show(Company $company, Branch $branch): View
    {
        $this->ensureBranchBelongsToCompany($branch, $company);
        $branch->loadCount('vehicles')->load(['vehicles' => fn ($q) => $q->orderBy('plate')], 'users');

        return view('app.companies.branches.show', [
            'title' => $branch->name . ' – ' . $company->name,
            'company' => $company,
            'branch' => $branch,
        ]);
    }

    public function create(Company $company): View
    {
        $managers = $company->users()->orderBy('name')->get();

        return view('app.companies.branches.create', [
            'title' => 'Nouvelle agence – ' . $company->name,
            'company' => $company,
            'managers' => $managers,
        ]);
    }

    public function store(Request $request, Company $company, PlanGateService $planGate): RedirectResponse
    {
        if ($planGate->isLimitReached($company, PlanGateService::LIMIT_BRANCHES)) {
            return redirect()->to(route('app.companies.upgrade', $company) . '?limit=' . PlanGateService::LIMIT_BRANCHES)
                ->with('error', 'Limite d’agences atteinte pour votre plan. Passez à un plan supérieur.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:128',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:32',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:active,suspended',
        ]);

        $validated['company_id'] = $company->id;
        if (!empty($validated['manager_id'])) {
            $user = $company->users()->find($validated['manager_id']);
            if (!$user) {
                $validated['manager_id'] = null;
            }
        }

        Branch::create($validated);

        return redirect()
            ->route('app.companies.branches.index', $company)
            ->with('success', 'Agence créée.');
    }

    public function edit(Company $company, Branch $branch): View
    {
        $this->ensureBranchBelongsToCompany($branch, $company);

        $managers = $company->users()->orderBy('name')->get();

        return view('app.companies.branches.edit', [
            'title' => 'Modifier ' . $branch->name . ' – ' . $company->name,
            'company' => $company,
            'branch' => $branch,
            'managers' => $managers,
        ]);
    }

    public function update(Request $request, Company $company, Branch $branch): RedirectResponse
    {
        $this->ensureBranchBelongsToCompany($branch, $company);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:128',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:32',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:active,suspended',
        ]);

        if (!empty($validated['manager_id'])) {
            $user = $company->users()->find($validated['manager_id']);
            if (!$user) {
                $validated['manager_id'] = null;
            }
        }

        $branch->update($validated);

        return redirect()
            ->route('app.companies.branches.index', $company)
            ->with('success', 'Agence mise à jour.');
    }
}
