<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Company show/edit context: platform staff (super_admin, support) can access any company;
 * company_admin and agent can access only their own company.
 */
class EnsureCanAccessCompany
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
