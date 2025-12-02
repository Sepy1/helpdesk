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
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // Expected keys in $data:
        // 'ticket_id','ticket_no','kind' => 'comment'|'history', 'title','body','url','actor_id','actor_name','comment_id'|'history_id','created_at'
        return $this->data;
    }
}
