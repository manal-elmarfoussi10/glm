<?php

namespace App\Notifications;

use App\Mail\WelcomeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewUserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?string $loginUrl = null
    ) {
        $this->loginUrl = $loginUrl ?? url('/app');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): WelcomeMail
    {
        return new WelcomeMail($notifiable, $this->loginUrl);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_user_created',
            'title' => 'Bienvenue sur GLM',
            'body' => 'Votre compte a été créé. Connectez-vous pour accéder à votre tableau de bord.',
            'url' => $this->loginUrl ?? url('/app'),
        ];
    }
}
