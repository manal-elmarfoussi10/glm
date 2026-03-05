<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminOrSupport
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            abort(403, 'Non authentifié.');
        }

        $role = $request->user()->role;
        if (! in_array($role, ['super_admin', 'support'], true)) {
            abort(403, 'Accès réservé à l’équipe plateforme (super admin ou support).');
        }

        return $next($request);
    }
}
