<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketComment;

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

        TicketComment::create($data);

        return redirect()
            ->route('ticket.show', $ticket->id)
            ->with('success', 'Komentar berhasil ditambahkan');
    }

    
    

    
}
