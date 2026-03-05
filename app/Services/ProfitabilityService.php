<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Expense;
use App\Models\Reservation;
use App\Models\Vehicle;
use Carbon\Carbon;

class ProfitabilityService
{

    /**
     * Fleet profitability overview: KPIs + per-vehicle rows for the table.
     * Profit = revenue - financing - insurance - vignette - maintenance - manual expenses.
     */
    public function fleetOverview(Company $company, Carbon $from, Carbon $to, string $sort = 'profit_desc'): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $daysInRange = max(1, $from->diffInDays($to) + 1);
        $monthsInRange = $daysInRange / 30.0;
        $yearsInRange = $daysInRange / 365.0;

        $vehicles = $company->vehicles()->with('branch')->get();
        $vehicleRows = [];

        $revenueTotal = 0;
        $costTotal = 0;

        foreach ($vehicles as $vehicle) {
            $row = $this->vehicleProfitRow($company, $vehicle, $from, $to, $daysInRange, $monthsInRange, $yearsInRange);
            $vehicleRows[] = $row;
            $revenueTotal += $row['revenue'];
            $costTotal += $row['total_cost'];
        }

        // Sort
        $vehicleRows = $this->sortVehicleRows($vehicleRows, $sort, $daysInRange);

        $netProfit = $revenueTotal - $costTotal;
        $vehicleCount = $vehicles->count() ?: 1;

        return [
            'from' => $from,
            'to' => $to,
            'days_in_range' => $daysInRange,
            'revenue_total' => round($revenueTotal, 2),
            'cost_total' => round($costTotal, 2),
            'net_profit' => round($netProfit, 2),
            'avg_profit_per_vehicle' => round($netProfit / $vehicleCount, 2),
            'vehicle_count' => $vehicles->count(),
            'vehicle_rows' => $vehicleRows,
        ];
    }

    /**
     * Single vehicle profitability detail: revenue breakdown, cost breakdown, profit analysis, chart data.
     */
    public function vehicleDetail(Company $company, Vehicle $vehicle, Carbon $from, Carbon $to): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $daysInRange = max(1, $from->diffInDays($to) + 1);
        $monthsInRange = $daysInRange / 30.0;
        $yearsInRange = $daysInRange / 365.0;

        $row = $this->vehicleProfitRow($company, $vehicle, $from, $to, $daysInRange, $monthsInRange, $yearsInRange);

        // Reservations stats
        $reservations = Reservation::where('company_id', $company->id)
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('status', [Reservation::STATUS_COMPLETED, Reservation::STATUS_IN_PROGRESS])
            ->where(function ($q) use ($from, $to) {
                $q->where('start_at', '<=', $to)->where('end_at', '>=', $from);
            })
            ->get();

        $totalReservations = $reservations->count();
        $totalRevenue = $row['revenue'];
        $totalDaysRented = 0;
        foreach ($reservations as $r) {
            $start = Carbon::parse($r->start_at)->startOfDay();
            $end = Carbon::parse($r->end_at)->endOfDay();
            $overlapStart = $start->lt($from) ? $from : $start;
            $overlapEnd = $end->gt($to) ? $to : $end;
            $totalDaysRented += max(0, $overlapStart->diffInDays($overlapEnd) + 1);
        }
        $avgRentalDuration = $totalReservations > 0 ? round($totalDaysRented / $totalReservations, 1) : 0;
        $utilizationRate = $daysInRange > 0 ? min(100, ($totalDaysRented / $daysInRange) * 100) : 0;

        // Cost breakdown (same as row but named for view)
        $costFinancing = $row['financing_cost'];
        $costInsurance = $row['insurance_cost'];
        $costVignette = $row['vignette_cost'];
        $costMaintenance = $row['maintenance_cost'];
        $costOther = $row['other_expenses'];

        $netProfit = $row['net_profit'];
        $monthlyProfitAvg = $monthsInRange > 0 ? $netProfit / $monthsInRange : 0;
        $monthlyCost = $row['total_cost'] / max(0.01, $monthsInRange);
        $breakEvenDays = $monthlyCost > 0 && $row['revenue'] > 0
            ? (int) ceil(($monthlyCost / ($row['revenue'] / max(1, $totalDaysRented))) ?: 0)
            : null;
        if ($totalDaysRented > 0 && $monthlyCost > 0) {
            $dailyRevenue = $row['revenue'] / $totalDaysRented;
            $breakEvenDays = $dailyRevenue > 0 ? (int) ceil($monthlyCost / $dailyRevenue) : null;
        } else {
            $breakEvenDays = null;
        }

        // Chart: Revenue vs Cost (single bar pair for this vehicle)
        $chartRevenue = $row['revenue'];
        $chartCost = $row['total_cost'];

        return [
            'vehicle' => $vehicle,
            'from' => $from,
            'to' => $to,
            'days_in_range' => $daysInRange,
            'total_reservations' => $totalReservations,
            'total_revenue' => round($totalRevenue, 2),
            'avg_rental_duration' => $avgRentalDuration,
            'utilization_rate' => round($utilizationRate, 1),
            'cost_financing' => round($costFinancing, 2),
            'cost_insurance' => round($costInsurance, 2),
            'cost_vignette' => round($costVignette, 2),
            'cost_maintenance' => round($costMaintenance, 2),
            'cost_other' => round($costOther, 2),
            'total_cost' => round($row['total_cost'], 2),
            'net_profit' => round($netProfit, 2),
            'monthly_profit_avg' => round($monthlyProfitAvg, 2),
            'break_even_days' => $breakEvenDays,
            'chart_revenue' => $chartRevenue,
            'chart_cost' => $chartCost,
        ];
    }

    private function vehicleProfitRow(
        Company $company,
        Vehicle $vehicle,
        Carbon $from,
        Carbon $to,
        int $daysInRange,
        float $monthsInRange,
        float $yearsInRange
    ): array {
        $revenue = $this->vehicleRevenueInRange($company, $vehicle->id, $from, $to);
        $financingCost = 0;
        if ($vehicle->is_financed && $vehicle->financing_monthly_payment) {
            $financingCost = (float) $vehicle->financing_monthly_payment * $monthsInRange;
        }
        $insuranceCost = $vehicle->insurance_annual_cost
            ? (float) $vehicle->insurance_annual_cost * $yearsInRange
            : 0;
        $vignetteCost = 0;
        if ($vehicle->vignette_year && $vehicle->vignette_amount) {
            $vignetteEnd = Carbon::createFromFormat('Y', (string) $vehicle->vignette_year)->endOfYear();
            if ($vignetteEnd->between($from, $to) || $from->lte($vignetteEnd) && $to->gte(Carbon::createFromFormat('Y', (string) $vehicle->vignette_year)->startOfYear())) {
                $vignetteCost = (float) $vehicle->vignette_amount * $yearsInRange;
            }
        }
        $expenses = $this->vehicleExpensesInRange($vehicle->id, $company->id, $from, $to);
        $maintenanceCost = $expenses['maintenance'];
        $otherExpenses = $expenses['other'];
        $totalCost = $financingCost + $insuranceCost + $vignetteCost + $maintenanceCost + $otherExpenses;
        $netProfit = $revenue - $totalCost;
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;
        $roi = $totalCost > 0 ? ($netProfit / $totalCost) * 100 : ($revenue > 0 ? 100 : 0);

        return [
            'vehicle' => $vehicle,
            'revenue' => round($revenue, 2),
            'financing_cost' => round($financingCost, 2),
            'insurance_cost' => round($insuranceCost, 2),
            'vignette_cost' => round($vignetteCost, 2),
            'maintenance_cost' => round($maintenanceCost, 2),
            'other_expenses' => round($otherExpenses, 2),
            'total_cost' => round($totalCost, 2),
            'net_profit' => round($netProfit, 2),
            'profit_margin' => round($profitMargin, 1),
            'roi' => round($roi, 1),
            'days_rented' => $this->vehicleDaysRentedInRange($company, $vehicle->id, $from, $to),
        ];
    }

    private function vehicleRevenueInRange(Company $company, int $vehicleId, Carbon $from, Carbon $to): float
    {
        $reservations = Reservation::where('company_id', $company->id)
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', [Reservation::STATUS_COMPLETED, Reservation::STATUS_IN_PROGRESS])
            ->where('start_at', '<=', $to)
            ->where('end_at', '>=', $from)
            ->get();

        $total = 0;
        foreach ($reservations as $r) {
            $start = Carbon::parse($r->start_at)->startOfDay();
            $end = Carbon::parse($r->end_at)->endOfDay();
            $totalDays = max(1, $start->diffInDays($end) + 1);
            $overlapStart = $start->lt($from) ? $from : $start;
            $overlapEnd = $end->gt($to) ? $to : $end;
            $daysInPeriod = max(0, $overlapStart->diffInDays($overlapEnd) + 1);
            $prorate = $daysInPeriod / $totalDays;
            $total += (float) $r->total_price * $prorate;
        }
        return $total;
    }

    private function vehicleDaysRentedInRange(Company $company, int $vehicleId, Carbon $from, Carbon $to): int
    {
        $reservations = Reservation::where('company_id', $company->id)
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', [Reservation::STATUS_COMPLETED, Reservation::STATUS_IN_PROGRESS])
            ->where('start_at', '<=', $to)
            ->where('end_at', '>=', $from)
            ->get();

        $days = 0;
        foreach ($reservations as $r) {
            $start = Carbon::parse($r->start_at)->startOfDay();
            $end = Carbon::parse($r->end_at)->endOfDay();
            $overlapStart = $start->lt($from) ? $from : $start;
            $overlapEnd = $end->gt($to) ? $to : $end;
            $days += max(0, $overlapStart->diffInDays($overlapEnd) + 1);
        }
        return (int) $days;
    }

    /** @return array{maintenance: float, other: float} */
    private function vehicleExpensesInRange(int $vehicleId, int $companyId, Carbon $from, Carbon $to): array
    {
        $maintenance = 0;
        $other = 0;
        $query = Expense::where('company_id', $companyId)
            ->where('vehicle_id', $vehicleId)
            ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')]);
        foreach ($query->get() as $e) {
            $amount = (float) $e->amount;
            if (in_array($e->category, [Expense::CATEGORY_MAINTENANCE, Expense::CATEGORY_REPAIR], true)) {
                $maintenance += $amount;
            } else {
                $other += $amount;
            }
        }
        return ['maintenance' => $maintenance, 'other' => $other];
    }

    private function sortVehicleRows(array $rows, string $sort, int $daysInRange): array
    {
        $vehicleCount = count($rows) ?: 1;
        usort($rows, function ($a, $b) use ($sort, $daysInRange, $vehicleCount) {
            return match ($sort) {
                'profit_desc' => (int) round(($b['net_profit'] - $a['net_profit']) * 100),
                'profit_asc' => (int) round(($a['net_profit'] - $b['net_profit']) * 100),
                'cost_desc' => (int) round(($b['total_cost'] - $a['total_cost']) * 100),
                'cost_asc' => (int) round(($a['total_cost'] - $b['total_cost']) * 100),
                'utilization_desc' => ($b['days_rented'] ?? 0) <=> ($a['days_rented'] ?? 0),
                'utilization_asc' => ($a['days_rented'] ?? 0) <=> ($b['days_rented'] ?? 0),
                default => (int) round(($b['net_profit'] - $a['net_profit']) * 100),
            };
        });
        return $rows;
    }
}
