<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $unread = $user->unreadNotifications()->count();
        $latest = $user->notifications()->latest()->limit(15)->get()->map(function($n){
            $d = $n->data ?? [];
            return [
                'id' => $n->id,
                'read_at' => $n->read_at,
                'created_at' => optional($n->created_at)->toIso8601String(),
                'title' => $d['title'] ?? 'Aktivitas Tiket',
                'body' => $d['body'] ?? null,
                'url' => $d['url'] ?? null,
                'ticket_id' => $d['ticket_id'] ?? null,
                'ticket_no' => $d['ticket_no'] ?? null,
                'kind' => $d['kind'] ?? null,
                'actor_name' => $d['actor_name'] ?? null,
            ];
        });
        return response()->json(['unread' => $unread, 'items' => $latest]);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }

    public function markOne(Request $request, string $id)
    {
        $user = $request->user();
        $n = $user->notifications()->where('id', $id)->first();
        if(!$n){ return response()->json(['ok'=>false], 404); }
        if($n->read_at === null){ $n->markAsRead(); }
        return response()->json(['ok' => true]);
    }
}
