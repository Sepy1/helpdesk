<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\TicketActivity;
use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DeliverTicketActivitySideEffects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId,
        public array $data
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user) {
            return;
        }

        try {
            Notification::sendNow($user, new TicketActivity($this->data), ['mail']);
        } catch (\Throwable $e) {
            Log::error('TicketActivity mail failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $tokens = DB::table('user_devices')
                ->where('user_id', $user->id)
                ->pluck('fcm_token')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($tokens === []) {
                return;
            }

            $title = $this->data['title'] ?? 'Notifikasi Tiket';
            $body = is_string($this->data['body'] ?? null) ? $this->data['body'] : '';
            $payload = [
                'type' => 'ticket',
                'ticket_id' => (string) ($this->data['ticket_id'] ?? ''),
                'url' => (string) ($this->data['url'] ?? url('/ticket/'.($this->data['ticket_id'] ?? ''))),
            ];

            foreach ($tokens as $token) {
                try {
                    FcmService::sendToToken($token, $title, $body, $payload);
                } catch (\Throwable $e) {
                    Log::error('FCM send error', [
                        'user_id' => $user->id,
                        'token' => $token,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('FCM general error', ['error' => $e->getMessage()]);
        }
    }
}
