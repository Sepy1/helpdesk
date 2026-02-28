<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FcmService;

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

        return (new MailMessage)
            ->subject($d['title'] ?? 'Notifikasi Tiket')
            ->markdown('emails.notifications.ticket_activity', [
                'data' => $d,
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        // Expected keys in $data:
        // 'ticket_id','ticket_no','kind' => 'comment'|'history', 'title','body','url','actor_id','actor_name','comment_id'|'history_id','created_at'
        // send push notifications (FCM) to user's registered devices (if any)
        try {
    $tokens = DB::table('user_devices')
        ->where('user_id', $notifiable->id)
        ->pluck('fcm_token')
        ->filter()
        ->unique()
        ->values()
        ->all();

    if (!empty($tokens)) {

        $title = $this->data['title'] ?? 'Notifikasi Tiket';
        $body  = is_string($this->data['body']) ? $this->data['body'] : '';

        $payload = [
            'type'       => 'ticket',
            'ticket_id'  => (string) ($this->data['ticket_id'] ?? ''),
            'url'        => (string) ($this->data['url'] ?? url('/ticket/'.$this->data['ticket_id'])),
        ];

        foreach ($tokens as $token) {
            try {

                $res = FcmService::sendToToken(
                    $token,
                    $title,
                    $body,
                    $payload
                );

                Log::info('FCM sent', [
                    'user_id' => $notifiable->id,
                    'token'   => $token,
                    'result'  => $res
                ]);

            } catch (\Throwable $e) {

                Log::error('FCM send error', [
                    'user_id' => $notifiable->id,
                    'token'   => $token,
                    'error'   => $e->getMessage()
                ]);
            }
        }
    }
} catch (\Throwable $e) {
    Log::error('FCM general error', ['error' => $e->getMessage()]);
}

        return $this->data;
    }
}
