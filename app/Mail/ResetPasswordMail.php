<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $resetUrl,
        public int $expireMinutes = 60
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Réinitialisation de votre mot de passe GLM');
    }

    public function content(): Content
    {
        return new Content(
            view: "emails.auth.reset-password",
            with: [
                'subject' => 'Réinitialisation de votre mot de passe GLM',
                "userName" => $this->userName,
                "resetUrl" => $this->resetUrl,
                "expireMinutes" => $this->expireMinutes,
            ]
        );
    }
}
