<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $replierName,
        public ?string $ticketUrl = null
    ) {
        $this->ticketUrl = $ticketUrl ?? url('/app/inbox/' . $ticket->id);
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Nouvelle réponse – Ticket #' . $this->ticket->id . ' : ' . $this->ticket->subject)
            ->line($this->replierName . ' a répondu au ticket #' . $this->ticket->id . '.')
            ->action('Voir le ticket', $this->ticketUrl);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_reply',
            'title' => 'Réponse sur le ticket #' . $this->ticket->id,
            'body' => $this->replierName . ' : ' . $this->ticket->subject,
            'url' => $this->ticketUrl,
            'ticket_id' => $this->ticket->id,
        ];
    }
}
