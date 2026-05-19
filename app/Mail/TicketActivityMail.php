<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketActivityMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data,
        public User $user,
    ) {}

    public function build()
    {
        return $this->subject($this->data['title'] ?? 'Notifikasi Tiket')
            ->markdown('emails.notifications.ticket_activity', [
                'data' => $this->data,
                'notifiable' => $this->user,
            ]);
    }
}
