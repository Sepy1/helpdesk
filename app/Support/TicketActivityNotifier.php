<?php

namespace App\Support;

use App\Jobs\DeliverTicketActivitySideEffects;
use App\Models\User;
use App\Notifications\TicketActivity;

class TicketActivityNotifier
{
    public static function notify(User $user, array $payload): void
    {
        $user->notify(new TicketActivity($payload));
        DeliverTicketActivitySideEffects::dispatch($user->id, $payload);
    }
}
