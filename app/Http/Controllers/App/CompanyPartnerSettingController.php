<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyPartnerSetting;
use App\Services\PartnerAvailabilityCacheService;
use App\Services\PlanGateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyPartnerSettingController extends Controller
{
    public function __construct(
        private PlanGateService $planGate,
        private PartnerAvailabilityCacheService $cacheService
    ) {}

    public function edit(Company $company): View|RedirectResponse
    {
        if (! $this->planGate->can($company, PlanGateService::FEATURE_PARTNER_AVAILABILITY)) {
            return redirect()->route('app.companies.upgrade', $company)
                ->with('info', 'Disponibilité partenaires disponible sur les plans Pro et Business.');
        }

        $setting = $company->partnerSetting ?? new CompanyPartnerSetting(['company_id' => $company->id]);
        $branches = $company->branches()->orderBy('city')->orderBy('name')->get();

        return view('app.companies.partner-settings.edit', [
            'title' => 'Partage disponibilité partenaires – ' . $company->name,
            'company' => $company,
            'setting' => $setting,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        if (! $this->planGate->can($company, PlanGateService::FEATURE_PARTNER_AVAILABILITY)) {
            return redirect()->route('app.companies.upgrade', $company);
        }

        $validated = $request->validate([
            'share_enabled' => 'boolean',
            'shared_branch_ids' => 'nullable|array',
            'shared_branch_ids.*' => 'exists:branches,id',
            'shared_categories' => 'nullable|array',
            'shared_categories.*' => 'in:economy,sedan,suv',
            'show_price' => 'boolean',
        ]);

        $branchIds = $validated['shared_branch_ids'] ?? [];
        foreach ($branchIds as $id) {
            $branch = \App\Models\Branch::find($id);
            if (! $branch || $branch->company_id !== $company->id) {
                return back()->with('error', 'Agence invalide.');
            }
        }

        $setting = $company->partnerSetting()->firstOrCreate(
            ['company_id' => $company->id],
            ['share_enabled' => false, 'shared_branch_ids' => [], 'shared_categories' => [], 'show_price' => false]
        );
        $setting->share_enabled = $request->boolean('share_enabled');
        $setting->shared_branch_ids = $branchIds;
        $setting->shared_categories = $validated['shared_categories'] ?? [];
        $setting->show_price = $request->boolean('show_price');
        $setting->save();

        $this->cacheService->rebuildForCompany($company);

        return redirect()
            ->route('app.companies.partner-settings.edit', $company)
            ->with('success', 'Paramètres partenaires enregistrés. Le cache de disponibilité a été mis à jour.');
    }
}
