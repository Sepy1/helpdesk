<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserDevice;

class FcmController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        $user = auth()->user();
        if (! $user || ! $user->android_notifications_enabled) {
            return response()->json(['success' => false, 'message' => 'Android notifications disabled'], 403);
        }

        UserDevice::updateOrCreate(
            ['fcm_token' => $request->token],
            [
                'user_id' => $user->id,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        $user = auth()->user();
        if (! $user) {
            return response()->json(['success' => false], 401);
        }

        UserDevice::where('user_id', $user->id)
            ->where('fcm_token', $request->token)
            ->delete();

        return response()->json(['success' => true]);
    }
}
