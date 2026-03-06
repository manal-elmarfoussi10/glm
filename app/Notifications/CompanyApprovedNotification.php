<?php

namespace App\Notifications;

use App\Mail\WelcomeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CompanyApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $companyName, public ?string $loginUrl = null)
    {
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
            'type' => 'company_approved',
            'title' => 'Inscription approuvée',
            'body' => 'Votre entreprise ' . $this->companyName . ' a été approuvée.',
            'url' => $this->loginUrl,
        ];
    }
}
