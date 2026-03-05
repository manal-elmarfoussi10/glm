<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts route to company_admin (and platform staff). Agents cannot access
 * company edit, company users, etc. Use after EnsureCompanyOperationalAccess
 * or EnsureCanAccessCompany when the route is company-scoped.
 */
class EnsureCompanyAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'Non authentifié.');
        }

        if ($user->role === 'agent') {
            abort(403, 'Accès réservé à l’administrateur de l’entreprise.');
        }

        return $next($request);
    }
}
