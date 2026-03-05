<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ReservationPayment;

class PaymentObserver
{
    public function created(ReservationPayment $payment): void
    {
        ActivityLog::log(
            action: 'payment_created',
            subject: $payment,
            description: "Nouveau paiement de {$payment->amount} MAD pour la réservation {$payment->reservation?->reference}",
            companyId: $payment->reservation?->company_id
        );
    }
}
