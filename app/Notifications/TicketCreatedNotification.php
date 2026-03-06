<?php

namespace App\Notifications;

use App\Mail\TicketCreatedMail;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public ?string $ticketUrl = null
    ) {
        $this->ticketUrl = $ticketUrl ?? url('/app/inbox/' . $ticket->id);
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): TicketCreatedMail
    {
        return new TicketCreatedMail(
            $this->ticket,
            $notifiable->name ?? $notifiable->email,
            $this->ticketUrl
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_created',
            'title' => 'Nouveau ticket #' . $this->ticket->id,
            'body' => $this->ticket->subject,
            'url' => $this->ticketUrl,
            'ticket_id' => $this->ticket->id,
        ];
    }
}
