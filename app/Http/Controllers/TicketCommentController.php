<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Support\TicketActivityNotifier;

class TicketCommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'body' => 'nullable|string|max:65535',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $bodyRaw = (string) $request->input('body', '');
        if (trim($bodyRaw) === '' && ! $request->hasFile('attachment')) {
            return back()
                ->withErrors(['body' => 'Tulis pesan atau tempel/lampirkan gambar.'])
                ->withInput();
        }

        $data = [
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => trim($bodyRaw) === '' ? '' : $bodyRaw,
        ];

        if ($request->hasFile('attachment')) {
            $f = $request->file('attachment');
            $ext = strtolower($f->getClientOriginalExtension());
            if ($ext === '' || $ext === 'tmp' || strlen($ext) > 8) {
                $ext = match ($f->getMimeType()) {
                    'image/jpeg', 'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    'application/pdf' => 'pdf',
                    default => 'bin',
                };
            }
            try {
                $code = random_int(10000, 99999);
            } catch (\Throwable $e) {
                $code = mt_rand(10000, 99999);
            }
            $filename = 'comment-img-' . $code . '-' . uniqid('', true) . '.' . $ext;
            $path = $f->storeAs('attachments', $filename, 'public');
            $data['attachment'] = $path;
        }

        $comment = TicketComment::create($data);

        try {
            $actor = $request->user();
            $ticket->loadMissing(['user', 'it', 'vendor']);
            $recipients = collect([$ticket->user, $ticket->it, $ticket->vendor])
                ->filter()
                ->unique('id')
                ->reject(fn ($u) => $actor && $u->id === $actor->id);

            $autoTaken = $actor
                && ($actor->role === 'IT')
                && $ticket->status === 'OPEN';

            $url = route('ticket.show', $ticket->id) . '#c-' . $comment->id;
            $commentBody = (trim((string) $comment->body) !== '')
                ? (string) str($comment->body)->limit(140)
                : '[Gambar]';

            $payload = [
                'ticket_id' => $ticket->id,
                'ticket_no' => $ticket->nomor_tiket ?? ('#'.$ticket->id),
                'kind' => 'comment',
                'title' => $autoTaken
                    ? 'Komentar baru & tiket diambil IT'
                    : 'Komentar baru pada tiket ' . ($ticket->nomor_tiket ?? $ticket->id),
                'body' => $autoTaken
                    ? $commentBody . ' — Tiket sedang ditangani oleh ' . ($actor->name ?? 'IT')
                    : $commentBody,
                'url' => $url,
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'comment_id' => $comment->id,
                'created_at' => now()->toIso8601String(),
            ];

            foreach ($recipients as $user) {
                TicketActivityNotifier::notify($user, $payload);
            }

            if ($autoTaken) {
                $ticket->status = 'ON_PROGRESS';
                $ticket->it_id = $ticket->it_id ?: $actor->id;
                $ticket->taken_at = $ticket->taken_at ?: now();
                $ticket->save();

                \App\Models\TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $actor->id,
                    'action' => 'taken',
                    'note' => 'Tiket diambil oleh IT (komentar)',
                ]);
            }
        } catch (\Throwable $e) {
            // ignore notification errors
        }

        return redirect()
            ->route('ticket.show', $ticket->id)
            ->with('success', 'Komentar berhasil ditambahkan');
    }
}
