<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    /**
     * Search across companies, reservations, customers, and vehicles.
     *
     * @param string $query
     * @param int|null $companyId If provided, restricts search to this company's data.
     * @return Collection
     */
    public function search(string $query, ?int $companyId = null): Collection
    {
        if (empty($query)) {
            return collect();
        }

        $results = collect();

        // 1. Companies (Only for SuperAdmin/Support)
        if (!$companyId) {
            $companies = Company::query()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('ice', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'title' => $c->name,
                    'subtitle' => "ICE: {$c->ice}",
                    'url' => route('app.admin.companies.show', $c),
                    'type' => 'company',
                    'icon' => 'office-building',
                ]);
            $results = $results->concat($companies);
        }

        // 2. Reservations
        $reservationsQuery = Reservation::query()
            ->with(['customer', 'vehicle'])
            ->where(function(Builder $q) use ($query) {
                $q->where('reference', 'like', "%{$query}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$query}%")->orWhere('cin', 'like', "%{$query}%"))
                  ->orWhereHas('vehicle', fn($vq) => $vq->where('plate', 'like', "%{$query}%"));
            });

        if ($companyId) {
            $reservationsQuery->where('company_id', $companyId);
        }

        $reservations = $reservationsQuery->limit(5)->get()->map(fn($r) => [
            'id' => $r->id,
            'title' => $r->reference,
            'subtitle' => ($r->customer?->name ?? 'Client inconnu') . ' - ' . ($r->vehicle?->plate ?? 'N/A'),
            'url' => $companyId 
                ? route('app.companies.reservations.show', [$companyId, $r])
                : route('app.admin.reservations.show', $r),
            'type' => 'reservation',
            'icon' => 'calendar',
        ]);
        $results = $results->concat($reservations);

        // 3. Customers (Clients)
        $customersQuery = Customer::query()
            ->where(function(Builder $q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('cin', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            });

        if ($companyId) {
            $customersQuery->where('company_id', $companyId);
        }

        $customers = $customersQuery->limit(5)->get()->map(fn($c) => [
            'id' => $c->id,
            'title' => $c->name,
            'subtitle' => "CIN: {$c->cin} | {$c->phone}",
            'url' => $companyId
                ? route('app.companies.customers.show', [$companyId, $c])
                : route('app.admin.customers.show', $c),
            'type' => 'customer',
            'icon' => 'user-group',
        ]);
        $results = $results->concat($customers);

        // 4. Vehicles
        $vehiclesQuery = Vehicle::query()
            ->where(function(Builder $q) use ($query) {
                $q->where('plate', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('model', 'like', "%{$query}%");
            });

        if ($companyId) {
            $vehiclesQuery->where('company_id', $companyId);
        }

        $vehicles = $vehiclesQuery->limit(5)->get()->map(fn($v) => [
            'id' => $v->id,
            'title' => "{$v->brand} {$v->model}",
            'subtitle' => $v->plate,
            'url' => $companyId
                ? route('app.companies.vehicles.show', [$companyId, $v])
                : route('app.admin.vehicles.show', $v),
            'type' => 'vehicle',
            'icon' => 'truck',
        ]);
        $results = $results->concat($vehicles);

        return $results;
    }
}
