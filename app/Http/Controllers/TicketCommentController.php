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
            $f = $request->file('attachment');
            $original = $f->getClientOriginalName();
            $ext = $f->getClientOriginalExtension();
            $name = pathinfo($original, PATHINFO_FILENAME);
            $sanitized = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
            try {
                $code = random_int(10000, 99999);
            } catch (\Throwable $e) {
                $code = mt_rand(10000, 99999);
            }
            $filename = $sanitized . '-' . $code . '.' . $ext;
            $path = $f->storeAs('attachments', $filename, 'public');
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

        // Jika yang menulis komentar adalah IT dan tiket masih OPEN,
        // ubah status menjadi ON_PROGRESS, set it_id bila belum, catat history dan kirim notifikasi history.
        try {
            $actor = $request->user();
            if ($actor && ($actor->role === 'IT') && $ticket->status === 'OPEN') {
                $ticket->status = 'ON_PROGRESS';
                $ticket->it_id = $ticket->it_id ?: $actor->id;
                $ticket->taken_at = $ticket->taken_at ?: now();
                $ticket->save();

                $h = \App\Models\TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $actor->id,
                    'action'    => 'taken',
                    'note'      => 'Tiket diambil oleh IT (komentar)',
                ]);

                // notify participants about history (reuse recipients logic)
                $recipients = collect([$ticket->user, $ticket->it, $ticket->vendor])
                    ->filter()
                    ->unique('id')
                    ->reject(fn($u) => $actor && $u->id === $actor->id);

                $url = route('ticket.show', $ticket->id) . '#h-' . $h->id;
                $payload = [
                    'ticket_id'  => $ticket->id,
                    'ticket_no'  => $ticket->nomor_tiket ?? ('#'.$ticket->id),
                    'kind'       => 'history',
                    'title'      => 'Tiket diambil oleh IT',
                    'body'       => 'Tiket sedang ditangani oleh ' . ($actor->name ?? 'IT'),
                    'url'        => $url,
                    'actor_id'   => $actor?->id,
                    'actor_name' => $actor?->name,
                    'history_id' => $h->id,
                    'action'     => $h->action,
                    'created_at' => now()->toIso8601String(),
                ];

                foreach ($recipients as $user) {
                    $user->notify(new TicketActivity($payload));
                }
            }
        } catch (\Throwable $e) {
            // ignore errors
        }

        return redirect()
            ->route('ticket.show', $ticket->id)
            ->with('success', 'Komentar berhasil ditambahkan');
    }

    
    

    
}
