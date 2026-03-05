<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Reservation;
use App\Models\Vehicle;
use App\Services\AlertService;
use App\Services\PlanGateService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyVehicleController extends Controller
{
    private function ensureVehicleBelongsToCompany(Vehicle $vehicle, Company $company): void
    {
        if ($vehicle->branch->company_id != $company->id) {
            abort(404);
        }
    }

    public function index(Request $request, Company $company): View
    {
        $query = $company->vehicles()->with('branch')->orderBy('plate');

        if ($request->boolean('expiring_soon')) {
            $query->expiringSoon();
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->paginate(20)->withQueryString();
        $branches = $company->branches()->orderBy('name')->get();

        return view('app.companies.vehicles.index', [
            'title' => 'Flotte – ' . $company->name,
            'company' => $company,
            'vehicles' => $vehicles,
            'branches' => $branches,
        ]);
    }

    public function create(Request $request, Company $company): View
    {
        $branches = $company->branches()->orderBy('name')->get();
        if ($branches->isEmpty()) {
            abort(403, 'Créez d\'abord au moins une agence (branche).');
        }

        return view('app.companies.vehicles.create', [
            'title' => 'Nouveau véhicule – ' . $company->name,
            'company' => $company,
            'branches' => $branches,
            'preselected_branch_id' => $request->input('branch_id'),
        ]);
    }

    public function store(Request $request, Company $company, PlanGateService $planGate): RedirectResponse
    {
        if ($planGate->isLimitReached($company, PlanGateService::LIMIT_VEHICLES)) {
            return redirect()->to(route('app.companies.upgrade', $company) . '?limit=' . PlanGateService::LIMIT_VEHICLES)
                ->with('error', 'Limite de véhicules atteinte pour votre plan. Passez à un plan supérieur.');
        }
        $validated = $this->validateVehicle($request);
        $branch = $company->branches()->findOrFail($validated['branch_id']);
        $validated['branch_id'] = $branch->id;
        $validated['status'] = $validated['status'] ?? Vehicle::STATUS_AVAILABLE;
        $validated['is_financed'] = $request->boolean('is_financed');
        $validated['insurance_reminder'] = $request->boolean('insurance_reminder');
        $validated['vignette_reminder'] = $request->boolean('vignette_reminder');
        $validated['visite_reminder'] = $request->boolean('visite_reminder');
        unset($validated['insurance_document'], $validated['vignette_receipt'], $validated['visite_document'], $validated['financing_contract']);
        $vehicle = Vehicle::create($validated);
        $this->handleUploads($request, $vehicle);

        return redirect()
            ->route('app.companies.vehicles.index', $company)
            ->with('success', 'Véhicule créé.');
    }

    public function show(Company $company, Vehicle $vehicle, AlertService $alertService): View
    {
        $this->ensureVehicleBelongsToCompany($vehicle, $company);
        $vehicle->load('branch');

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $daysInMonth = (int) $startOfMonth->diffInDays($endOfMonth) + 1;

        $revenueThisMonth = (float) $vehicle->reservations()
            ->where('status', Reservation::STATUS_COMPLETED)
            ->where('end_at', '>=', $startOfMonth)
            ->where('end_at', '<=', $endOfMonth)
            ->sum('total_price');
        $totalRevenueLifetime = (float) $vehicle->reservations()->where('status', Reservation::STATUS_COMPLETED)->sum('total_price');
        $totalRentals = $vehicle->reservations()->where('status', Reservation::STATUS_COMPLETED)->count();

        $expensesThisMonth = (float) $vehicle->expenses()
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<=', $endOfMonth)
            ->sum('amount');
        $netThisMonth = $revenueThisMonth - $expensesThisMonth;

        $completedOverlapping = $vehicle->reservations()
            ->where('status', Reservation::STATUS_COMPLETED)
            ->where('start_at', '<=', $endOfMonth)
            ->where('end_at', '>=', $startOfMonth)
            ->get();
        $daysRentedThisMonth = (int) $completedOverlapping->sum(fn ($r) => max(0, $r->start_at->copy()->max($startOfMonth)->diffInDays($r->end_at->copy()->min($endOfMonth)) + 1));
        $utilizationPercent = $daysInMonth > 0 ? min(100, round($daysRentedThisMonth / $daysInMonth * 100)) : 0;

        $reservationsForVehicle = $vehicle->reservations()->with(['customer', 'inspectionOut', 'inspectionIn'])
            ->orderByDesc('start_at')->limit(50)->get();
        $totalKmDriven = $reservationsForVehicle->filter(fn ($r) => $r->status === Reservation::STATUS_COMPLETED && $r->inspectionOut && $r->inspectionIn && $r->inspectionOut->mileage && $r->inspectionIn->mileage)
            ->sum(fn ($r) => max(0, (int) $r->inspectionIn->mileage - (int) $r->inspectionOut->mileage));

        $reservationsPaginated = $vehicle->reservations()->with('customer')->orderByDesc('start_at')->paginate(10)->withQueryString();
        $expensesPaginated = $vehicle->expenses()->orderByDesc('date')->paginate(10)->withQueryString();

        return view('app.companies.vehicles.show', [
            'title' => $vehicle->plate . ' – ' . $company->name,
            'company' => $company,
            'vehicle' => $vehicle,
            'vehicleAlerts' => $alertService->forVehicle($company, $vehicle),
            'revenueThisMonth' => $revenueThisMonth,
            'expensesThisMonth' => $expensesThisMonth,
            'netThisMonth' => $netThisMonth,
            'totalRevenueLifetime' => $totalRevenueLifetime,
            'totalRentals' => $totalRentals,
            'utilizationPercent' => $utilizationPercent,
            'totalKmDriven' => $totalKmDriven,
            'reservationsPaginated' => $reservationsPaginated,
            'expensesPaginated' => $expensesPaginated,
        ]);
    }

    public function edit(Company $company, Vehicle $vehicle): View
    {
        $this->ensureVehicleBelongsToCompany($vehicle, $company);
        $branches = $company->branches()->orderBy('name')->get();

        return view('app.companies.vehicles.edit', [
            'title' => 'Modifier ' . $vehicle->plate . ' – ' . $company->name,
            'company' => $company,
            'vehicle' => $vehicle,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, Company $company, Vehicle $vehicle): RedirectResponse
    {
        $this->ensureVehicleBelongsToCompany($vehicle, $company);
        $validated = $this->validateVehicle($request, $vehicle);
        $validated['branch_id'] = $company->branches()->findOrFail($validated['branch_id'])->id;
        $validated['is_financed'] = $request->boolean('is_financed');
        $validated['insurance_reminder'] = $request->boolean('insurance_reminder');
        $validated['vignette_reminder'] = $request->boolean('vignette_reminder');
        $validated['visite_reminder'] = $request->boolean('visite_reminder');
        unset($validated['insurance_document'], $validated['vignette_receipt'], $validated['visite_document'], $validated['financing_contract']);
        $vehicle->update($validated);
        $this->handleUploads($request, $vehicle);

        return redirect()
            ->route('app.companies.vehicles.show', [$company, $vehicle])
            ->with('success', 'Véhicule mis à jour.');
    }

    public function destroy(Company $company, Vehicle $vehicle): RedirectResponse
    {
        $this->ensureVehicleBelongsToCompany($vehicle, $company);
        $vehicle->delete();
        return redirect()
            ->route('app.companies.vehicles.index', $company)
            ->with('success', 'Véhicule supprimé.');
    }

    private function validateVehicle(Request $request, ?Vehicle $vehicle = null): array
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'status' => 'nullable|string|in:available,maintenance,inactive',
            'plate' => 'required|string|max:32',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'partner_category' => 'nullable|string|in:economy,sedan,suv',
            'year' => 'nullable|integer|min:1900|max:2100',
            'vin' => 'nullable|string|max:50',
            'fuel' => 'nullable|string|max:32|in:essence,diesel,hybrid,electric',
            'transmission' => 'nullable|string|max:32|in:manual,automatic',
            'mileage' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:64',
            'seats' => 'nullable|integer|min:1|max:99',
            'daily_price' => 'nullable|numeric|min:0',
            'weekly_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_type' => 'nullable|string|max:64',
            'insurance_start_date' => 'nullable|date',
            'insurance_end_date' => 'nullable|date',
            'insurance_annual_cost' => 'nullable|numeric|min:0',
            'insurance_reminder' => 'boolean',
            'vignette_year' => 'nullable|integer|min:2000|max:2100',
            'vignette_amount' => 'nullable|numeric|min:0',
            'vignette_paid_date' => 'nullable|date',
            'vignette_reminder' => 'boolean',
            'visite_last_date' => 'nullable|date',
            'visite_expiry_date' => 'nullable|date',
            'visite_reminder' => 'boolean',
            'financing_type' => 'nullable|string|max:32|in:credit,leasing',
            'financing_bank' => 'nullable|string|max:255',
            'financing_monthly_payment' => 'nullable|numeric|min:0',
            'financing_start_date' => 'nullable|date',
            'financing_end_date' => 'nullable|date',
            'financing_remaining_amount' => 'nullable|numeric|min:0',
        ];
        if ($request->hasFile('insurance_document')) {
            $rules['insurance_document'] = 'file|mimes:pdf,jpg,jpeg,png|max:10240';
        }
        if ($request->hasFile('vignette_receipt')) {
            $rules['vignette_receipt'] = 'file|mimes:pdf,jpg,jpeg,png|max:10240';
        }
        if ($request->hasFile('visite_document')) {
            $rules['visite_document'] = 'file|mimes:pdf,jpg,jpeg,png|max:10240';
        }
        if ($request->hasFile('financing_contract')) {
            $rules['financing_contract'] = 'file|mimes:pdf,jpg,jpeg,png|max:10240';
        }
        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,jpg,png,webp|max:5120';
        }

        return $request->validate($rules);
    }

    private function handleUploads(Request $request, Vehicle $vehicle): void
    {
        $updates = [];
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('vehicles/' . $vehicle->id, 'public');
            $updates['image_path'] = $path;
        }
        foreach (['insurance_document' => 'insurance_document_path', 'vignette_receipt' => 'vignette_receipt_path', 'visite_document' => 'visite_document_path', 'financing_contract' => 'financing_contract_path'] as $input => $field) {
            if ($request->hasFile($input)) {
                $path = $request->file($input)->store('vehicles/' . $vehicle->id, 'public');
                $updates[$field] = $path;
            }
        }
        if (! empty($updates)) {
            $vehicle->update($updates);
        }
    }
}
