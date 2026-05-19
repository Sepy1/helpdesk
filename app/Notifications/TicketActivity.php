<?php

namespace App\Notifications;

use App\Jobs\DeliverTicketActivitySideEffects;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketActivity extends Notification
{
    use Queueable;

    public function __construct(
        public array $data
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $d = $this->data;

        return (new MailMessage)
            ->subject($d['title'] ?? 'Notifikasi Tiket')
            ->markdown('emails.notifications.ticket_activity', [
                'data' => $d,
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        DeliverTicketActivitySideEffects::dispatch($notifiable->id, $this->data)->afterResponse();

        return $this->data;
    }
}
