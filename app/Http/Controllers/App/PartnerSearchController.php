<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanyPartnerSetting;
use App\Models\PartnerAvailabilityCache;
use App\Services\PlanGateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PartnerSearchController extends Controller
{
    public function __construct(
        private PlanGateService $planGate
    ) {}

    /**
     * Search partner availability: city + date range + category. Results show company, branch, category, available count, optional price. No reservations/clients/plates.
     */
    public function index(Request $request, Company $company): View
    {
        if (! $this->planGate->can($company, PlanGateService::FEATURE_PARTNER_AVAILABILITY)) {
            return view('app.partners.search-locked', [
                'company' => $company,
                'title' => 'Recherche partenaires – ' . $company->name,
            ]);
        }

        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;
        $city = $request->filled('city') ? trim($request->city) : null;
        $category = $request->filled('category') && in_array($request->category, ['economy', 'sedan', 'suv'], true) ? $request->category : null;

        $results = [];
        $cities = $this->getSearchableCities();

        if ($from && $to && $from->lte($to) && $city && $category) {
            $results = $this->searchAvailability($company->id, $city, $from, $to, $category);
        }

        return view('app.partners.search', [
            'title' => 'Recherche disponibilité partenaires – ' . $company->name,
            'company' => $company,
            'from' => $from,
            'to' => $to,
            'city' => $city,
            'category' => $category,
            'results' => $results,
            'cities' => $cities,
        ]);
    }

    /**
     * Cities that have at least one partner branch sharing availability.
     */
    private function getSearchableCities(): array
    {
        $branchIds = CompanyPartnerSetting::where('share_enabled', true)
            ->whereNotNull('shared_branch_ids')
            ->get()
            ->pluck('shared_branch_ids')
            ->flatten()
            ->unique()
            ->filter()
            ->values()
            ->all();
        if (empty($branchIds)) {
            return [];
        }
        return Branch::whereIn('id', $branchIds)->orderBy('city')->pluck('city')->unique()->filter()->values()->all();
    }

    /**
     * @return array<int, array{company: Company, branch: Branch, category: string, category_label: string, available_count: int, price_min: ?float, price_max: ?float}>
     */
    private function searchAvailability(int $excludeCompanyId, string $city, Carbon $from, Carbon $to, string $category): array
    {
        $branchIds = Branch::where('city', $city)->pluck('id')->all();
        if (empty($branchIds)) {
            return [];
        }

        $dateFrom = $from->format('Y-m-d');
        $dateTo = $to->format('Y-m-d');

        $rows = PartnerAvailabilityCache::query()
            ->whereNot('company_id', $excludeCompanyId)
            ->whereIn('branch_id', $branchIds)
            ->where('category', $category)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->select([
                'company_id',
                'branch_id',
                'category',
                DB::raw('MIN(available_count) as available_count'),
                DB::raw('MIN(price_min) as price_min'),
                DB::raw('MAX(price_max) as price_max'),
            ])
            ->groupBy('company_id', 'branch_id', 'category')
            ->get();

        $companies = Company::whereIn('id', $rows->pluck('company_id')->unique())->get()->keyBy('id');
        $branches = Branch::whereIn('id', $rows->pluck('branch_id')->unique())->with('company')->get()->keyBy('id');
        $settings = CompanyPartnerSetting::whereIn('company_id', $rows->pluck('company_id')->unique())
            ->where('share_enabled', true)
            ->get()
            ->keyBy('company_id');

        $out = [];
        foreach ($rows as $row) {
            $minAvailable = (int) $row->available_count;
            if ($minAvailable <= 0) {
                continue;
            }
            $company = $companies->get($row->company_id);
            $branch = $branches->get($row->branch_id);
            if (! $company || ! $branch || $branch->company_id !== $company->id) {
                continue;
            }
            $setting = $settings->get($row->company_id);
            $showPrice = $setting && $setting->show_price;
            $out[] = [
                'company' => $company,
                'branch' => $branch,
                'category' => $row->category,
                'category_label' => \App\Models\Vehicle::PARTNER_CATEGORIES[$row->category] ?? $row->category,
                'available_count' => $minAvailable,
                'price_min' => $showPrice ? (float) $row->price_min : null,
                'price_max' => $showPrice ? (float) $row->price_max : null,
            ];
        }
        return $out;
    }
}
