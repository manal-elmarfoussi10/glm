<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractSignedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Contrat signe : ' . $ref)
            ->line('Le contrat pour la reservation ' . $ref . ' a ete signe et enregistre.')
            ->action('Voir la reservation', $this->reservationUrl);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'contract_signed',
            'title' => 'Contrat signe',
            'body' => 'Reservation ' . ($this->reservation->reference ?? '#' . $this->reservation->id),
            'url' => $this->reservationUrl,
            'reservation_id' => $this->reservation->id,
        ];
    }
}
