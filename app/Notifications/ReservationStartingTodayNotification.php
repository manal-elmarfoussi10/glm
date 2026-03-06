<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReservationStartingTodayNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Reservation $reservation,
        public string $reservationUrl
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ref = $this->reservation->reference ?? ('#' . $this->reservation->id);
        $customerName = $this->reservation->customer ? $this->reservation->customer->name : '-';
        $vehiclePlate = $this->reservation->vehicle ? $this->reservation->vehicle->plate : '-';
        return (new MailMessage)
            ->subject('Reservation aujourd hui : ' . $ref)
            ->line('La reservation ' . $ref . ' commence aujourd hui.')
            ->line('Client : ' . $customerName . ' - Vehicule : ' . $vehiclePlate)
            ->action('Voir la reservation', $this->reservationUrl);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reservation_starting_today',
            'title' => 'Reservation aujourd hui',
            'body' => ($this->reservation->reference ?? '#' . $this->reservation->id) . ' - ' . ($this->reservation->customer ? $this->reservation->customer->name : 'Client'),
            'url' => $this->reservationUrl,
            'reservation_id' => $this->reservation->id,
        ];
    }
}
