<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $registrant) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouvelle demande d'inscription GLM")
            ->line($this->registrant->requested_company_name . ' - ' . $this->registrant->email . ' a soumis une demande.')
            ->action('Voir les demandes', route('app.registration-requests.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_registration',
            'title' => "Nouvelle demande d'inscription",
            'body' => $this->registrant->requested_company_name . ' - ' . $this->registrant->email,
            'url' => route('app.registration-requests.index'),
            'registrant_id' => $this->registrant->id,
        ];
    }
}
