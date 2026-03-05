<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PartnerRequest;
use App\Services\PlanGateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerRequestController extends Controller
{
    public function __construct(
        private PlanGateService $planGate
    ) {}

    public function index(Company $company): View|RedirectResponse
    {
        if (! $this->planGate->can($company, PlanGateService::FEATURE_PARTNER_AVAILABILITY)) {
            return redirect()->route('app.companies.upgrade', $company);
        }

        $received = $company->partnerRequestsReceived()
            ->with(['requesterCompany', 'branch'])
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'received_page');
        $sent = $company->partnerRequestsSent()
            ->with(['partnerCompany', 'branch'])
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'sent_page');

        return view('app.companies.partner-requests.index', [
            'title' => 'Demandes partenaires – ' . $company->name,
            'company' => $company,
            'received' => $received,
            'sent' => $sent,
        ]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        if (! $this->planGate->can($company, PlanGateService::FEATURE_PARTNER_AVAILABILITY)) {
            return redirect()->route('app.companies.upgrade', $company);
        }

        $validated = $request->validate([
            'partner_company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'category' => 'nullable|string|in:economy,sedan,suv',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'message' => 'nullable|string|max:1000',
        ]);

        if ((int) $validated['partner_company_id'] === $company->id) {
            return back()->with('error', 'Vous ne pouvez pas vous envoyer une demande à vous-même.');
        }

        $partner = Company::findOrFail($validated['partner_company_id']);
        if ($validated['branch_id']) {
            $branch = \App\Models\Branch::find($validated['branch_id']);
            if (! $branch || $branch->company_id !== $partner->id) {
                return back()->with('error', 'Agence invalide.');
            }
        }

        PartnerRequest::create([
            'requester_company_id' => $company->id,
            'partner_company_id' => $partner->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'category' => $validated['category'] ?? null,
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'message' => $validated['message'] ?? null,
            'status' => PartnerRequest::STATUS_PENDING,
        ]);

        return back()->with('success', 'Demande envoyée. Le partenaire peut vous contacter par téléphone ou WhatsApp.');
    }

    public function accept(Company $company, PartnerRequest $partnerRequest): RedirectResponse
    {
        if ($partnerRequest->partner_company_id !== $company->id) {
            abort(404);
        }
        if (! $this->planGate->can($company, PlanGateService::FEATURE_PARTNER_AVAILABILITY)) {
            return redirect()->route('app.companies.upgrade', $company);
        }

        $partnerRequest->update([
            'status' => PartnerRequest::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        return back()->with('success', 'Demande acceptée.');
    }

    public function reject(Company $company, PartnerRequest $partnerRequest): RedirectResponse
    {
        if ($partnerRequest->partner_company_id !== $company->id) {
            abort(404);
        }
        if (! $this->planGate->can($company, PlanGateService::FEATURE_PARTNER_AVAILABILITY)) {
            return redirect()->route('app.companies.upgrade', $company);
        }

        $partnerRequest->update([
            'status' => PartnerRequest::STATUS_REJECTED,
            'responded_at' => now(),
        ]);

        return back()->with('success', 'Demande refusée.');
    }
}
