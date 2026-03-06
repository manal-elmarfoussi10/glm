<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $recipientName,
        public ?string $ticketUrl = null
    ) {
        $this->ticketUrl = $ticketUrl ?? url('/app/inbox/' . $ticket->id);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nouveau ticket #' . $this->ticket->id);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.ticket-created',
            with: [
                'subject' => 'Nouveau ticket support',
                'recipientName' => $this->recipientName,
                'ticketSubject' => $this->ticket->subject,
                'ticketId' => $this->ticket->id,
                'companyName' => $this->ticket->company?->name,
                'ticketUrl' => $this->ticketUrl,
            ]
        );
    }
}
