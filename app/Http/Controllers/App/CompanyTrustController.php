<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\TrustVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyTrustController extends Controller
{
    public function __construct(
        private TrustVerificationService $trust
    ) {}

    private function authorizeCompany(Company $company): void
    {
        $user = auth()->user();
        $isPlatformStaff = in_array($user->role ?? null, ['super_admin', 'support'], true);
        $belongsToCompany = (int) $user->company_id === (int) $company->id;
        if (! $isPlatformStaff && ! $belongsToCompany) {
            abort(403, 'Accès non autorisé à cette entreprise.');
        }
    }

    /**
     * Check client: form + results (verification status, trust badge, internal warning if flagged by this company).
     */
    public function index(Request $request, Company $company): View
    {
        $this->authorizeCompany($company);

        $phone = $request->input('phone');
        $email = $request->input('email');
        $trustData = null;
        $companyFlag = null;
        $identifier = null;

        if ($phone !== null && $phone !== '' || $email !== null && $email !== '') {
            $identifier = $this->trust->clientIdentifierFromInput($phone, $email);
            $trustData = $this->trust->getTrustData($identifier);
            $companyFlag = $this->trust->getFlagForCompany($company->id, $identifier);
        }

        return view('app.companies.trust.index', [
            'title' => 'Confiance & Vérification – ' . $company->name,
            'company' => $company,
            'phone' => $phone,
            'email' => $email,
            'trustData' => $trustData,
            'companyFlag' => $companyFlag,
            'identifier' => $identifier,
        ]);
    }

    /**
     * List this company's private flags (not shared).
     */
    public function flags(Company $company): View
    {
        $this->authorizeCompany($company);

        $flags = $company->clientFlags()->orderByDesc('created_at')->paginate(20);

        return view('app.companies.trust.flags', [
            'title' => 'Signalisations clients – ' . $company->name,
            'company' => $company,
            'flags' => $flags,
        ]);
    }

    /**
     * Add or update a private flag for this company.
     */
    public function flag(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeCompany($company);

        $validated = $request->validate([
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $phone = $validated['phone'] ?? null;
        $email = $validated['email'] ?? null;
        if (($phone === null || trim($phone) === '') && ($email === null || trim($email) === '')) {
            return back()->with('error', 'Indiquez au moins un téléphone ou un email.');
        }

        $identifier = $this->trust->clientIdentifierFromInput($phone, $email);
        $this->trust->flagClient(
            $company->id,
            $identifier,
            $validated['reason'] ?? null,
            $validated['notes'] ?? null
        );

        return redirect()
            ->route('app.companies.trust.flags', $company)
            ->with('success', 'Client signalé (visible uniquement par votre entreprise).');
    }

    /**
     * Remove private flag.
     */
    public function unflag(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeCompany($company);

        $validated = $request->validate([
            'client_identifier' => 'required|string|size:64',
        ]);

        $this->trust->unflagClient($company->id, $validated['client_identifier']);

        return back()->with('success', 'Signalisation retirée.');
    }

    /**
     * Record a successful rental (increments shared anonymized count).
     */
    public function recordSuccess(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeCompany($company);

        $validated = $request->validate([
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email',
            'client_identifier' => 'nullable|string|size:64',
        ]);

        $identifier = $validated['client_identifier'] ?? null;
        if (! $identifier) {
            $phone = $validated['phone'] ?? null;
            $email = $validated['email'] ?? null;
            if (($phone === null || trim($phone) === '') && ($email === null || trim($email) === '')) {
                return back()->with('error', 'Indiquez au moins un téléphone ou un email.');
            }
            $identifier = $this->trust->clientIdentifierFromInput($phone, $email);
        }

        $this->trust->recordSuccessfulRental($identifier);

        return back()->with('success', 'Location réussie enregistrée (donnée partagée de façon anonyme).');
    }

    /**
     * Set verified identity badge (shared positive data).
     */
    public function setVerified(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeCompany($company);

        $validated = $request->validate([
            'client_identifier' => 'required|string|size:64',
            'verified' => 'required|boolean',
        ]);

        $this->trust->setVerified($validated['client_identifier'], (bool) $validated['verified']);

        return back()->with('success', $validated['verified'] ? 'Identité marquée comme vérifiée.' : 'Badge vérification retiré.');
    }
}
