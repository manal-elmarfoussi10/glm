<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanyPartnerSetting;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    private function getCompany(): ?Company
    {
        $user = auth()->user();
        if (! in_array($user->role ?? null, ['company_admin', 'agent'], true) || ! $user->company_id) {
            return null;
        }
        return Company::find($user->company_id);
    }

    public function show(Request $request): View|RedirectResponse
    {
        $company = $this->getCompany();
        if (! $company) {
            return redirect()->route('app.dashboard');
        }
        if (! $company->needsOnboarding()) {
            return redirect()->route('app.dashboard')->with('info', 'Configuration déjà terminée.');
        }

        $step = max(1, min(4, (int) $request->get('step', 1)));
        $firstBranch = $company->branches()->first();
        $categories = \App\Models\Vehicle::PARTNER_CATEGORIES;

        return view('app.onboarding.wizard', [
            'title' => 'Configuration de votre espace – GLM',
            'company' => $company,
            'step' => $step,
            'firstBranch' => $firstBranch,
            'categories' => $categories,
        ]);
    }

    public function storeStep1(Request $request): RedirectResponse
    {
        $company = $this->getCompany();
        if (! $company) {
            return redirect()->route('app.dashboard');
        }

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_city' => 'nullable|string|max:128',
            'branch_name' => 'required|string|max:255',
            'branch_city' => 'nullable|string|max:128',
            'branch_address' => 'nullable|string|max:500',
            'branch_phone' => 'nullable|string|max:32',
        ]);

        if (! empty($validated['company_name'])) {
            $company->update(['name' => $validated['company_name']]);
        }
        if (array_key_exists('company_city', $validated)) {
            $company->update(['city' => $validated['company_city'] ?: null]);
        }

        Branch::create([
            'company_id' => $company->id,
            'name' => $validated['branch_name'],
            'city' => $validated['branch_city'] ?? null,
            'address' => $validated['branch_address'] ?? null,
            'phone' => $validated['branch_phone'] ?? null,
            'status' => Branch::STATUS_ACTIVE,
        ]);

        return redirect()->route('app.onboarding.show', ['step' => 2])->with('success', 'Agence créée.');
    }

    public function storeStep2(Request $request): RedirectResponse
    {
        $company = $this->getCompany();
        if (! $company) {
            return redirect()->route('app.dashboard');
        }

        $firstBranch = $company->branches()->first();
        if (! $firstBranch) {
            return redirect()->route('app.onboarding.show', ['step' => 1])->with('error', 'Créez d’abord une agence.');
        }

        $validated = $request->validate([
            'skip_vehicles' => 'nullable|boolean',
            'vehicles' => 'nullable|array',
            'vehicles.*.plate' => 'nullable|string|max:32',
            'vehicles.*.brand' => 'nullable|string|max:100',
            'vehicles.*.model' => 'nullable|string|max:100',
            'vehicles.*.daily_price' => 'nullable|numeric|min:0',
        ]);

        if (empty($validated['skip_vehicles']) && ! empty($validated['vehicles'])) {
            foreach ($validated['vehicles'] as $v) {
                if (empty($v['plate']) || empty($v['brand']) || empty($v['model'])) {
                    continue;
                }
                Vehicle::create([
                    'branch_id' => $firstBranch->id,
                    'status' => Vehicle::STATUS_AVAILABLE,
                    'plate' => $v['plate'],
                    'brand' => $v['brand'],
                    'model' => $v['model'],
                    'daily_price' => $v['daily_price'] ?? null,
                ]);
            }
        }

        return redirect()->route('app.onboarding.show', ['step' => 3])->with('success', 'Flotte enregistrée.');
    }

    public function storeStep3(Request $request): RedirectResponse
    {
        $company = $this->getCompany();
        if (! $company) {
            return redirect()->route('app.dashboard');
        }

        $validated = $request->validate([
            'join_network' => 'nullable|boolean',
            'show_company_name' => 'nullable|boolean',
            'show_city' => 'nullable|boolean',
            'show_phone' => 'nullable|boolean',
            'show_email' => 'nullable|boolean',
            'shared_categories' => 'nullable|array',
            'shared_categories.*' => 'string|in:' . implode(',', array_keys(Vehicle::PARTNER_CATEGORIES)),
            'allow_contact_requests' => 'nullable|boolean',
        ]);

        $joinNetwork = $request->boolean('join_network');
        $setting = $company->partnerSetting()->firstOrCreate(
            ['company_id' => $company->id],
            [
                'share_enabled' => false,
                'shared_branch_ids' => [],
                'shared_categories' => [],
                'show_price' => false,
                'allow_contact_requests' => false,
            ]
        );

        $setting->share_enabled = $joinNetwork;
        $setting->allow_contact_requests = $joinNetwork && $request->boolean('allow_contact_requests');
        if ($joinNetwork) {
            $firstBranch = $company->branches()->first();
            $setting->shared_branch_ids = $firstBranch ? [$firstBranch->id] : [];
            $setting->shared_categories = $validated['shared_categories'] ?? [];
        } else {
            $setting->shared_branch_ids = [];
            $setting->shared_categories = [];
        }
        $setting->save();

        return redirect()->route('app.onboarding.show', ['step' => 4])->with('success', 'Réseau partenaires configuré.');
    }

    public function storeStep4(Request $request): RedirectResponse
    {
        $company = $this->getCompany();
        if (! $company) {
            return redirect()->route('app.dashboard');
        }

        $company->update(['onboarding_completed_at' => now()]);

        return redirect()
            ->route('app.dashboard')
            ->with('success', 'Configuration terminée. Bienvenue sur GLM !');
    }
}
