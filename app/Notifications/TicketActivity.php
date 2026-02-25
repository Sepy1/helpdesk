<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TicketActivity extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $data
    ) {}

    public function via(object $notifiable): array
    {
        // send both database notification and email
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $d = $this->data;

        $mail = (new MailMessage)
            ->subject($d['title'] ?? 'Notifikasi Tiket')
            ->line($d['body'] ?? '')
            ->action('Lihat Tiket', $d['url'] ?? url('/'));

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        // Expected keys in $data:
        // 'ticket_id','ticket_no','kind' => 'comment'|'history', 'title','body','url','actor_id','actor_name','comment_id'|'history_id','created_at'
        return $this->data;
    }
}
