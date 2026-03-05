<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Reservation;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        ActivityLog::log(
            action: 'reservation_created',
            subject: $reservation,
            description: "Nouvelle réservation créée : {$reservation->reference}",
            companyId: $reservation->company_id
        );
    }

    public function updated(Reservation $reservation): void
    {
        if ($reservation->isDirty('status')) {
            ActivityLog::log(
                action: 'reservation_status_changed',
                subject: $reservation,
                description: "Statut de la réservation {$reservation->reference} changé en : {$reservation->status}",
                properties: [
                    'old' => $reservation->getOriginal('status'),
                    'new' => $reservation->status,
                ],
                companyId: $reservation->company_id
            );
        }
    }
}
