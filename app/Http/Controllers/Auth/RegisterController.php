<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('monthly_price')
            ->orderBy('name')
            ->get();

        return view('auth.register', [
            'plans' => $plans,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'requested_company_name' => ['required', 'string', 'max:255'],
            'requested_ice' => ['required', 'string', 'regex:/^\d+$/', 'min:12', 'max:20'],
            'phone' => ['required', 'string', 'max:255'],
            'requested_country' => ['required', 'string', 'max:255'],
            'plan_id' => ['required', 'exists:plans,id'],
            'fleet_size' => ['required', 'numeric', 'min:0'],
            'operating_cities' => ['nullable', 'string', 'max:1000'],
            'registration_message' => ['nullable', 'string', 'max:2000'],
        ], [
            'requested_ice.regex' => "L'ICE doit contenir uniquement des chiffres.",
            'requested_ice.min' => "L'ICE doit contenir entre 12 et 20 chiffres.",
            'requested_ice.max' => "L'ICE doit contenir entre 12 et 20 chiffres.",
        ]);

        $plan = Plan::find($validated['plan_id']);
        $operatingCities = $validated['operating_cities'] ?? '';
        $citiesArray = array_values(array_filter(array_map('trim', explode(',', $operatingCities))));

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'company_admin',
            'status' => 'pending',
            'phone' => $validated['phone'],
            'requested_company_name' => $validated['requested_company_name'],
            'requested_ice' => $validated['requested_ice'],
            'requested_country' => $validated['requested_country'],
            'requested_plan' => $plan?->name ?? (string) $validated['plan_id'],
            'fleet_size' => (int) ($validated['fleet_size'] ?? 0),
            'operating_cities' => $citiesArray,
            'registration_message' => $validated['registration_message'] ?? null,
        ]);

        return redirect()
            ->route('auth.pending-approval')
            ->with('success', 'Votre demande d\'inscription a été envoyée. Vous serez notifié après examen.');
    }
}
