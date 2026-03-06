<?php

namespace App\Notifications;

use App\Mail\DocumentExpiringMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VehicleDocumentExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $documentType,
        public string $vehiclePlate,
        public string $vehicleName,
        public string $expiryDate,
        public ?int $daysLeft = null,
        public ?string $vehicleUrl = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): DocumentExpiringMail
    {
        return new DocumentExpiringMail(
            $notifiable->name ?? $notifiable->email,
            $this->documentType,
            $this->vehiclePlate,
            $this->vehicleName,
            $this->expiryDate,
            $this->daysLeft,
            $this->vehicleUrl
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'document_expiring',
            'title' => 'Document a renouveler: ' . $this->documentType,
            'body' => $this->vehiclePlate . ' - ' . $this->vehicleName . ' expire le ' . $this->expiryDate,
            'url' => $this->vehicleUrl,
            'document_type' => $this->documentType,
            'vehicle_plate' => $this->vehiclePlate,
            'days_left' => $this->daysLeft,
        ];
    }
}
