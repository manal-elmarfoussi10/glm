<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $documentType,
        public string $vehiclePlate,
        public string $vehicleName,
        public string $expiryDate,
        public ?int $daysLeft = null,
        public ?string $vehicleUrl = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Document à renouveler : ' . $this->documentType . ' – ' . $this->vehiclePlate,
            replyTo: [config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerts.document-expiring',
            with: [
                'subject' => 'Document à renouveler',
                'recipientName' => $this->recipientName,
                'documentType' => $this->documentType,
                'vehiclePlate' => $this->vehiclePlate,
                'vehicleName' => $this->vehicleName,
                'expiryDate' => $this->expiryDate,
                'daysLeft' => $this->daysLeft,
                'vehicleUrl' => $this->vehicleUrl,
            ]
        );
    }
}
