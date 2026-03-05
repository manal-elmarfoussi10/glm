<?php

namespace App\Http\Responses\Auth;

use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class PendingApprovalRegistrationResponse implements RegistrationResponse
{
    public function toResponse($request): Response|RedirectResponse
    {
        $url = Route::has('auth.pending-approval') ? route('auth.pending-approval') : url('/pending-approval');
        $response = new RedirectResponse($url);
        $response->setStatusCode(302);
        return $response;
    }
}
