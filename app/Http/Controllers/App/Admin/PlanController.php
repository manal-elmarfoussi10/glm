<?php

namespace App\Http\Controllers\App\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::orderBy('monthly_price')->paginate(15);

        return view('app.admin.plans.index', [
            'title' => 'Plans & tarifs',
            'plans' => $plans,
        ]);
    }

    public function create(): View
    {
        return view('app.admin.plans.create', [
            'title' => 'Nouveau plan',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'trial_days' => 'required|integer|min:0',
            'limit_vehicles' => 'nullable|integer|min:0',
            'limit_users' => 'nullable|integer|min:0',
            'limit_branches' => 'nullable|integer|min:0',
            'ai_access' => 'boolean',
            'custom_contracts' => 'boolean',
            'is_active' => 'boolean',
            'features_limits_json' => ['nullable', 'string', function (string $attr, $value, \Closure $fail) {
                if ($value === '' || $value === null) {
                    return;
                }
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                    $fail('Le JSON des fonctionnalités et limites n’est pas valide.');
                }
            }],
        ]);

        $validated['ai_access'] = $request->boolean('ai_access');
        $validated['custom_contracts'] = $request->boolean('custom_contracts');
        $validated['is_active'] = $request->boolean('is_active');

        if (! empty($request->input('features_limits_json'))) {
            $decoded = json_decode($request->input('features_limits_json'), true);
            $validated['features_limits'] = is_array($decoded) ? $decoded : null;
        } else {
            $validated['features_limits'] = null;
        }
        unset($validated['features_limits_json']);

        $plan = Plan::create($validated);
        AuditLog::log('plan.created', Plan::class, (int) $plan->id, null, ['name' => $plan->name, 'monthly_price' => $plan->monthly_price]);

        return redirect()
            ->route('app.admin.plans.index')
            ->with('success', 'Plan créé.');
    }

    public function edit(Plan $plan): View
    {
        return view('app.admin.plans.edit', [
            'title' => 'Modifier – ' . $plan->name,
            'plan' => $plan,
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'trial_days' => 'required|integer|min:0',
            'limit_vehicles' => 'nullable|integer|min:0',
            'limit_users' => 'nullable|integer|min:0',
            'limit_branches' => 'nullable|integer|min:0',
            'ai_access' => 'boolean',
            'custom_contracts' => 'boolean',
            'is_active' => 'boolean',
            'features_limits_json' => ['nullable', 'string', function (string $attr, $value, \Closure $fail) {
                if ($value === '' || $value === null) {
                    return;
                }
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                    $fail('Le JSON des fonctionnalités et limites n’est pas valide.');
                }
            }],
        ]);

        if (! empty($request->input('features_limits_json'))) {
            $decoded = json_decode($request->input('features_limits_json'), true);
            $validated['features_limits'] = is_array($decoded) ? $decoded : null;
        } else {
            $validated['features_limits'] = null;
        }
        unset($validated['features_limits_json']);
        $validated['ai_access'] = $request->boolean('ai_access');
        $validated['custom_contracts'] = $request->boolean('custom_contracts');
        $validated['is_active'] = $request->boolean('is_active');

        $old = $plan->only(array_keys($validated));
        $plan->update($validated);
        AuditLog::log('plan.updated', Plan::class, (int) $plan->id, $old, $validated);

        return redirect()
            ->route('app.admin.plans.index')
            ->with('success', 'Plan mis à jour.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $name = $plan->name;
        $id = (int) $plan->id;
        $plan->delete();
        AuditLog::log('plan.deleted', Plan::class, $id, ['name' => $name], null);

        return redirect()
            ->route('app.admin.plans.index')
            ->with('success', 'Plan supprimé.');
    }
}
