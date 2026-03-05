<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Reservation;
use App\Models\ReservationPayment;
use Carbon\Carbon;

class ReportService
{
    /**
     * Build report data for a company over a date range.
     * from/to are Carbon or date strings (start and end of range, inclusive).
     */
    public function forCompany(Company $company, Carbon $from, Carbon $to): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $daysInRange = $from->diffInDays($to) + 1;
        $daysInRange = max(1, $daysInRange);

        $vehicleIds = $company->vehicles()->pluck('vehicles.id')->toArray();
        $vehicleCount = count($vehicleIds);
        if ($vehicleCount === 0) {
            $vehicleCount = 1; // avoid division by zero
        }

        // Reservations overlapping the range (any status for stats; completed for revenue)
        $reservationsQuery = Reservation::where('company_id', $company->id)
            ->where(function ($q) use ($from, $to) {
                $q->where('start_at', '<=', $to)->where('end_at', '>=', $from);
            });

        $reservations = (clone $reservationsQuery)->with(['vehicle', 'customer'])->get();

        // Revenue: use total_price for completed (and in_progress) reservations, prorated by days in range
        $revenueTotal = 0;
        $revenueByMonth = [];
        $revenueByVehicle = [];
        $daysRentedByVehicle = [];

        foreach ($reservations as $reservation) {
            if (! in_array($reservation->status, [Reservation::STATUS_COMPLETED, Reservation::STATUS_IN_PROGRESS], true)) {
                continue;
            }
            $start = Carbon::parse($reservation->start_at)->startOfDay();
            $end = Carbon::parse($reservation->end_at)->endOfDay();
            $totalDays = max(1, $start->diffInDays($end) + 1);
            $price = (float) $reservation->total_price;

            // Days of this reservation that fall inside [from, to]
            $overlapStart = $start->lt($from) ? $from : $start;
            $overlapEnd = $end->gt($to) ? $to : $end;
            $daysInPeriod = max(0, $overlapStart->diffInDays($overlapEnd) + 1);
            $prorate = $daysInPeriod / $totalDays;
            $revenueTotal += $price * $prorate;

            $vid = $reservation->vehicle_id;
            if ($vid) {
                $revenueByVehicle[$vid] = ($revenueByVehicle[$vid] ?? 0) + $price * $prorate;
                $daysRentedByVehicle[$vid] = ($daysRentedByVehicle[$vid] ?? 0) + $daysInPeriod;
            }

            // Monthly breakdown: for each month in range, add prorated share
            $current = $from->copy()->startOfMonth();
            while ($current->lte($to)) {
                $monthEnd = $current->copy()->endOfMonth();
                $rangeStart = $overlapStart->gt($current) ? $overlapStart : $current;
                $rangeEnd = $overlapEnd->lt($monthEnd) ? $overlapEnd : $monthEnd;
                if ($rangeStart->lte($rangeEnd)) {
                    $daysInMonth = $rangeStart->diffInDays($rangeEnd) + 1;
                    $revenueInMonth = $price * ($daysInMonth / $totalDays);
                    $key = $current->format('Y-m');
                    $revenueByMonth[$key] = ($revenueByMonth[$key] ?? 0) + $revenueInMonth;
                }
                $current->addMonth()->startOfMonth();
            }
        }

        ksort($revenueByMonth);

        // Fleet utilization: total days rented / (vehicle count * days in range)
        $totalDaysRented = array_sum($daysRentedByVehicle);
        $utilizationRate = $vehicleCount > 0 ? min(100, ($totalDaysRented / ($vehicleCount * $daysInRange)) * 100) : 0;

        // Most / least rented (by days)
        $vehicles = $company->vehicles()->with('branch')->get()->keyBy('id');
        $mostRented = collect($daysRentedByVehicle)
            ->sortDesc()
            ->take(10)
            ->map(fn ($days, $vehicleId) => [
                'vehicle' => $vehicles->get($vehicleId),
                'days_rented' => (int) $days,
                'revenue' => $revenueByVehicle[$vehicleId] ?? 0,
            ])
            ->values()
            ->filter(fn ($r) => $r['vehicle'] !== null)
            ->values()
            ->all();

        $leastRented = collect($daysRentedByVehicle)
            ->sort()
            ->take(10)
            ->map(fn ($days, $vehicleId) => [
                'vehicle' => $vehicles->get($vehicleId),
                'days_rented' => (int) $days,
                'revenue' => $revenueByVehicle[$vehicleId] ?? 0,
            ])
            ->values()
            ->filter(fn ($r) => $r['vehicle'] !== null)
            ->values()
            ->all();

        // Vehicles with zero rentals in period
        foreach ($vehicles as $vid => $v) {
            if (! isset($daysRentedByVehicle[$vid])) {
                $leastRented[] = ['vehicle' => $v, 'days_rented' => 0, 'revenue' => 0];
            }
        }
        usort($leastRented, fn ($a, $b) => $a['days_rented'] <=> $b['days_rented']);
        $leastRented = array_slice($leastRented, 0, 10);

        // Revenue by vehicle (sorted by revenue desc) for chart/table
        $revenueByVehicleList = [];
        foreach ($revenueByVehicle as $vid => $rev) {
            $v = $vehicles->get($vid);
            if ($v) {
                $revenueByVehicleList[] = ['vehicle' => $v, 'revenue' => round($rev, 2), 'days_rented' => (int) ($daysRentedByVehicle[$vid] ?? 0)];
            }
        }
        usort($revenueByVehicleList, fn ($a, $b) => $b['revenue'] <=> $a['revenue']);
        $revenueByVehicleList = array_slice($revenueByVehicleList, 0, 15);

        // Reservation statistics
        $byStatus = $reservations->groupBy('status')->map->count();
        $reservationStats = [
            'total' => $reservations->count(),
            'draft' => $byStatus->get(Reservation::STATUS_DRAFT, 0),
            'confirmed' => $byStatus->get(Reservation::STATUS_CONFIRMED, 0),
            'cancelled' => $byStatus->get(Reservation::STATUS_CANCELLED, 0),
            'in_progress' => $byStatus->get(Reservation::STATUS_IN_PROGRESS, 0),
            'completed' => $byStatus->get(Reservation::STATUS_COMPLETED, 0),
        ];

        // Cost overview: financing + insurance (prorated for range)
        $vehiclesForCost = $company->vehicles()->get();
        $monthsInRange = $daysInRange / 30.0;
        $yearsInRange = $daysInRange / 365.0;

        $financingTotal = 0;
        $insuranceTotal = 0;
        foreach ($vehiclesForCost as $v) {
            if ($v->is_financed && $v->financing_monthly_payment) {
                $financingTotal += (float) $v->financing_monthly_payment * $monthsInRange;
            }
            if ($v->insurance_annual_cost) {
                $insuranceTotal += (float) $v->insurance_annual_cost * $yearsInRange;
            }
        }

        return [
            'from' => $from,
            'to' => $to,
            'days_in_range' => $daysInRange,
            'vehicle_count' => count($vehicleIds),
            'revenue_total' => round($revenueTotal, 2),
            'revenue_by_month' => $revenueByMonth,
            'revenue_by_vehicle' => $revenueByVehicle,
            'utilization_rate' => round($utilizationRate, 1),
            'total_days_rented' => $totalDaysRented,
            'most_rented' => $mostRented,
            'least_rented' => $leastRented,
            'revenue_by_vehicle_list' => $revenueByVehicleList,
            'reservation_stats' => $reservationStats,
            'cost_financing' => round($financingTotal, 2),
            'cost_insurance' => round($insuranceTotal, 2),
            'cost_total' => round($financingTotal + $insuranceTotal, 2),
        ];
    }

    /**
     * Export report data as CSV-ready rows (array of arrays).
     */
    public function exportCsvRows(Company $company, Carbon $from, Carbon $to): array
    {
        $data = $this->forCompany($company, $from, $to);
        $rows = [];

        $rows[] = ['Rapport', $company->name, $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y')];
        $rows[] = [];
        $rows[] = ['Revenus total (MAD)', $data['revenue_total']];
        $rows[] = ['Taux utilisation (%)', $data['utilization_rate']];
        $rows[] = ['Coût financement (MAD)', $data['cost_financing']];
        $rows[] = ['Coût assurance (MAD)', $data['cost_insurance']];
        $rows[] = ['Coût total (MAD)', $data['cost_total']];
        $rows[] = [];
        $rows[] = ['Réservations', 'Total', $data['reservation_stats']['total']];
        $rows[] = ['', 'Terminées', $data['reservation_stats']['completed']];
        $rows[] = ['', 'Confirmées', $data['reservation_stats']['confirmed']];
        $rows[] = ['', 'Annulées', $data['reservation_stats']['cancelled']];
        $rows[] = [];
        $rows[] = ['Revenus par mois'];
        foreach ($data['revenue_by_month'] as $month => $value) {
            $rows[] = [$month, $value];
        }
        $rows[] = [];
        $rows[] = ['Véhicule', 'Plaque', 'Jours loués', 'Revenus (MAD)'];
        foreach ($data['most_rented'] as $r) {
            $v = $r['vehicle'];
            $rows[] = [$v->brand . ' ' . $v->model, $v->plate, $r['days_rented'], $r['revenue']];
        }

        return $rows;
    }
}
