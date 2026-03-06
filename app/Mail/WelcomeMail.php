<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $loginUrl = null
    ) {
        $this->loginUrl = $loginUrl ?? url("/app");
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Bienvenue sur GLM");
    }

    public function content(): Content
    {
        return new Content(
            view: "emails.auth.welcome",
            with: [
                "subject" => "Bienvenue sur GLM",
                "userName" => $this->user->name,
                "loginUrl" => $this->loginUrl,
            ]
        );
    }
}
