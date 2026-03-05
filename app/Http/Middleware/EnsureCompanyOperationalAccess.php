<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Company-side operational routes (fleet, reservations, alerts, reports, etc.).
 * - super_admin: allowed (can view all companies' operational data).
 * - support: allowed (company oversight).
 * - company_admin / agent: allowed only for their own company.
 */
class EnsureCompanyOperationalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'Non authentifié.');
        }

        $company = $request->route('company');
        if (! $company instanceof Company) {
            return $next($request);
        }

        $role = $user->role;

        if (in_array($role, ['super_admin', 'support'], true)) {
            return $next($request);
        }

        if (in_array($role, ['company_admin', 'agent'], true)) {
            if ((int) $user->company_id === (int) $company->id) {
                return $next($request);
            }
            abort(403, 'Accès non autorisé à cette entreprise.');
        }

        abort(403, 'Accès non autorisé.');
    }
}
