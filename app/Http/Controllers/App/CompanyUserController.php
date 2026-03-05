<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CompanyUserController extends Controller
{
    public function index(Company $company): View
    {
        $users = $company->users()->orderBy('name')->paginate(15);

        return view('app.companies.users.index', [
            'title' => 'Utilisateurs – ' . $company->name,
            'company' => $company,
            'users' => $users,
        ]);
    }

    public function create(Company $company): View
    {
        return view('app.companies.users.create', [
            'title' => 'Ajouter un utilisateur – ' . $company->name,
            'company' => $company,
        ]);
    }

    public function store(Request $request, Company $company, \App\Services\PlanGateService $planGate): RedirectResponse
    {
        if ($planGate->isLimitReached($company, \App\Services\PlanGateService::LIMIT_USERS)) {
            return redirect()->to(route('app.companies.upgrade', $company) . '?limit=' . \App\Services\PlanGateService::LIMIT_USERS)
                ->with('error', 'Limite d’utilisateurs atteinte pour votre plan. Passez à un plan supérieur.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:32',
            'password' => ['nullable', 'string', 'min:8', Password::default()],
            'role' => 'required|string|in:company_admin,manager,staff,accountant',
            'status' => 'required|string|in:active,suspended,invited',
        ]);

        $validated['company_id'] = $company->id;
        $validated['password'] = Hash::make($validated['password'] ?? str()->random(12));

        User::create($validated);

        return redirect()
            ->route('app.companies.users.index', $company)
            ->with('success', 'Utilisateur créé avec succès.');
    }
}
