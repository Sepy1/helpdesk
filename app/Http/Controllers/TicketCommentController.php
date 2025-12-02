<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Notifications\TicketActivity;

class TicketCommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'body' => 'required|string',
            'attachment' => 'nullable|file|max:5048',
        ]);

        $data = [
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
            $data['attachment'] = $path;
        }

        $comment = TicketComment::create($data);

        // Notify participants (owner, IT, vendor) except the actor
        try {
            $actor = $request->user();
            $ticket->loadMissing(['user','it','vendor']);
            $recipients = collect([$ticket->user, $ticket->it, $ticket->vendor])
                ->filter()
                ->unique('id')
                ->reject(fn($u) => $actor && $u->id === $actor->id);

            $url = route('ticket.show', $ticket->id) . '#c-' . $comment->id;
            $payload = [
                'ticket_id'  => $ticket->id,
                'ticket_no'  => $ticket->nomor_tiket ?? ('#'.$ticket->id),
                'kind'       => 'comment',
                'title'      => 'Komentar baru pada tiket ' . ($ticket->nomor_tiket ?? $ticket->id),
                'body'       => str($comment->body)->limit(140),
                'url'        => $url,
                'actor_id'   => $actor?->id,
                'actor_name' => $actor?->name,
                'comment_id' => $comment->id,
                'created_at' => now()->toIso8601String(),
            ];
            foreach ($recipients as $user) {
                $user->notify(new TicketActivity($payload));
            }
        } catch (\Throwable $e) {
            // ignore notification errors
        }

        return redirect()
            ->route('ticket.show', $ticket->id)
            ->with('success', 'Komentar berhasil ditambahkan');
    }

    
    

    
}
