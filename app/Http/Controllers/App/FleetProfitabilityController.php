<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Vehicle;
use App\Services\PlanGateService;
use App\Services\ProfitabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FleetProfitabilityController extends Controller
{
    public function __construct(
        private ProfitabilityService $profitabilityService,
        private PlanGateService $planGate
    ) {}

    public function index(Request $request, Company $company): View
    {
        $canAccess = $this->planGate->can($company, PlanGateService::FEATURE_PROFITABILITY);

        if (! $canAccess) {
            return view('app.companies.fleet.profitability-locked', [
                'company' => $company,
                'title' => 'Rentabilité flotte – ' . $company->name,
            ]);
        }

        $from = $this->parseFrom($request);
        $to = $this->parseTo($request);
        $sort = $request->get('sort', 'profit_desc');

        $data = $this->profitabilityService->fleetOverview($company, $from, $to, $sort);

        return view('app.companies.fleet.profitability', [
            'title' => 'Rentabilité flotte – ' . $company->name,
            'company' => $company,
            'from' => $from,
            'to' => $to,
            'sort' => $sort,
            'data' => $data,
        ]);
    }

    public function show(Request $request, Company $company, Vehicle $vehicle): View
    {
        $this->authorizeVehicle($company, $vehicle);

        $canAccess = $this->planGate->can($company, PlanGateService::FEATURE_PROFITABILITY);
        if (! $canAccess) {
            return view('app.companies.fleet.profitability-locked', [
                'company' => $company,
                'title' => 'Rentabilité véhicule – ' . $company->name,
            ]);
        }

        $from = $this->parseFrom($request);
        $to = $this->parseTo($request);

        $data = $this->profitabilityService->vehicleDetail($company, $vehicle, $from, $to);

        return view('app.companies.fleet.profitability-vehicle', [
            'title' => 'Rentabilité – ' . ($vehicle->brand . ' ' . $vehicle->model) . ' – ' . $company->name,
            'company' => $company,
            'vehicle' => $vehicle,
            'from' => $from,
            'to' => $to,
            'data' => $data,
        ]);
    }

    private function authorizeVehicle(Company $company, Vehicle $vehicle): void
    {
        $branch = $vehicle->branch;
        if (! $branch || $branch->company_id !== $company->id) {
            abort(404);
        }
    }

    private function parseFrom(Request $request): Carbon
    {
        if ($request->get('period') === 'this_month') {
            return now()->startOfMonth()->startOfDay();
        }
        if ($request->get('period') === 'last_month') {
            return now()->subMonth()->startOfMonth()->startOfDay();
        }
        if ($request->filled('from')) {
            return Carbon::parse($request->from)->startOfDay();
        }
        return now()->startOfMonth()->startOfDay();
    }

    private function parseTo(Request $request): Carbon
    {
        if ($request->get('period') === 'this_month') {
            return now()->endOfMonth()->endOfDay();
        }
        if ($request->get('period') === 'last_month') {
            return now()->subMonth()->endOfMonth()->endOfDay();
        }
        if ($request->filled('to')) {
            return Carbon::parse($request->to)->endOfDay();
        }
        return now()->endOfMonth()->endOfDay();
    }
}
