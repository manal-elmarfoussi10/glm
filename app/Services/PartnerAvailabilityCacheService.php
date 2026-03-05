<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanyPartnerSetting;
use App\Models\PartnerAvailabilityCache;
use App\Models\Reservation;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PartnerAvailabilityCacheService
{
    /** Number of days ahead to cache (e.g. 90). */
    public const CACHE_DAYS_AHEAD = 90;

    /**
     * Rebuild cache for all companies that have share_enabled, or for one company.
     */
    public function rebuildForAll(?int $companyId = null): int
    {
        $query = CompanyPartnerSetting::where('share_enabled', true)->with('company');
        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }
        $settings = $query->get();
        $updated = 0;
        foreach ($settings as $setting) {
            $this->rebuildForCompany($setting->company);
            $updated++;
        }
        return $updated;
    }

    /**
     * Rebuild cache for one company: for each shared branch × category × date, compute available count and price range.
     */
    public function rebuildForCompany(Company $company): void
    {
        $setting = $company->partnerSetting;
        if (! $setting || ! $setting->share_enabled) {
            PartnerAvailabilityCache::where('company_id', $company->id)->delete();
            return;
        }

        $branchIds = $setting->shared_branch_ids ?? [];
        $categories = $setting->shared_categories ?? [];
        $showPrice = $setting->show_price ?? false;

        if (empty($branchIds) || empty($categories)) {
            PartnerAvailabilityCache::where('company_id', $company->id)->delete();
            return;
        }

        $from = Carbon::today();
        $to = Carbon::today()->addDays(self::CACHE_DAYS_AHEAD);

        $vehicleIdsByBranchCategory = $this->getVehicleIdsByBranchAndCategory($branchIds, $categories);

        $dates = [];
        $current = $from->copy();
        while ($current->lte($to)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        foreach ($branchIds as $branchId) {
            $branch = Branch::find($branchId);
            if (! $branch || $branch->company_id !== $company->id) {
                continue;
            }
            foreach ($categories as $category) {
                $vehicleIds = $vehicleIdsByBranchCategory[$branchId][$category] ?? [];
                foreach ($dates as $dateStr) {
                    $availableCount = $this->countAvailableOnDate($vehicleIds, $dateStr, $company->id);
                    $priceMin = null;
                    $priceMax = null;
                    if ($showPrice && $availableCount > 0) {
                        [$priceMin, $priceMax] = $this->priceRangeForAvailableOnDate($vehicleIds, $dateStr, $company->id);
                    }
                    PartnerAvailabilityCache::updateOrInsert(
                        [
                            'company_id' => $company->id,
                            'branch_id' => $branchId,
                            'category' => $category,
                            'date' => $dateStr,
                        ],
                        [
                            'available_count' => $availableCount,
                            'price_min' => $priceMin,
                            'price_max' => $priceMax,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        // Delete cache rows for (company, branch, category, date) that are no longer in scope
        PartnerAvailabilityCache::where('company_id', $company->id)
            ->where(function ($q) use ($branchIds, $categories, $from, $to) {
                $q->whereNotIn('branch_id', $branchIds)
                    ->orWhereNotIn('category', $categories)
                    ->orWhere('date', '<', $from->format('Y-m-d'))
                    ->orWhere('date', '>', $to->format('Y-m-d'));
            })
            ->delete();
    }

    /**
     * @return array<int, array<string, array<int>>>
     */
    private function getVehicleIdsByBranchAndCategory(array $branchIds, array $categories): array
    {
        $vehicles = Vehicle::whereIn('branch_id', $branchIds)
            ->whereIn('partner_category', $categories)
            ->where('status', Vehicle::STATUS_AVAILABLE)
            ->get(['id', 'branch_id', 'partner_category', 'daily_price']);

        $result = [];
        foreach ($vehicles as $v) {
            $result[$v->branch_id][$v->partner_category][] = $v->id;
        }
        return $result;
    }

    /**
     * @param array<int> $vehicleIds
     */
    private function countAvailableOnDate(array $vehicleIds, string $dateStr, int $companyId): int
    {
        if (empty($vehicleIds)) {
            return 0;
        }
        $start = $dateStr . ' 00:00:00';
        $end = $dateStr . ' 23:59:59';
        $bookedIds = Reservation::where('company_id', $companyId)
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
            ->where('start_at', '<=', $end)
            ->where('end_at', '>=', $start)
            ->pluck('vehicle_id')
            ->unique()
            ->values()
            ->all();
        return count($vehicleIds) - count(array_intersect($vehicleIds, $bookedIds));
    }

    /**
     * @param array<int> $vehicleIds
     * @return array{0: ?float, 1: ?float}
     */
    private function priceRangeForAvailableOnDate(array $vehicleIds, string $dateStr, int $companyId): array
    {
        $start = $dateStr . ' 00:00:00';
        $end = $dateStr . ' 23:59:59';
        $bookedIds = Reservation::where('company_id', $companyId)
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
            ->where('start_at', '<=', $end)
            ->where('end_at', '>=', $start)
            ->pluck('vehicle_id')
            ->unique()
            ->all();
        $availableIds = array_diff($vehicleIds, $bookedIds);
        if (empty($availableIds)) {
            return [null, null];
        }
        $prices = Vehicle::whereIn('id', $availableIds)->whereNotNull('daily_price')->pluck('daily_price')->map(fn ($p) => (float) $p)->all();
        if (empty($prices)) {
            return [null, null];
        }
        return [min($prices), max($prices)];
    }
}
