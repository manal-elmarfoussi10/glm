<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCreatedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Nouvelle reservation ' . $ref)
            ->line('Une nouvelle reservation a ete creee : ' . $ref . '.')
            ->line('Client : ' . $customerName . ' - Debut : ' . $this->reservation->start_at->format('d/m/Y'))
            ->action('Voir la reservation', $this->reservationUrl);
    }

    public function toArray(object $notifiable): array
    {
        $customerName = $this->reservation->customer ? $this->reservation->customer->name : 'Client';
        return [
            'type' => 'reservation_created',
            'title' => 'Nouvelle reservation',
            'body' => ($this->reservation->reference ?? '#' . $this->reservation->id) . ' - ' . $customerName,
            'url' => $this->reservationUrl,
            'reservation_id' => $this->reservation->id,
        ];
    }
}
