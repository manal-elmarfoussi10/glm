<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Vehicle;

class VehicleObserver
{
    public function created(Vehicle $vehicle): void
    {
        $companyId = $vehicle->branch?->company_id;
        ActivityLog::log(
            action: 'vehicle_created',
            subject: $vehicle,
            description: "Nouveau véhicule ajouté : {$vehicle->brand} {$vehicle->model} ({$vehicle->plate})",
            companyId: $companyId
        );
    }

    public function updated(Vehicle $vehicle): void
    {
        $companyId = $vehicle->branch?->company_id;
        ActivityLog::log(
            action: 'vehicle_updated',
            subject: $vehicle,
            description: "Véhicule mis à jour : {$vehicle->plate}",
            companyId: $companyId
        );
    }

    public function deleted(Vehicle $vehicle): void
    {
        $companyId = $vehicle->branch?->company_id;
        ActivityLog::log(
            action: 'vehicle_deleted',
            subject: $vehicle,
            description: "Véhicule supprimé : {$vehicle->plate}",
            companyId: $companyId
        );
    }
}
