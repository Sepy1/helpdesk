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

        UserDevice::updateOrCreate(
            ['fcm_token' => $request->token],
            [
                'user_id' => auth()->id(),
            ]
        );

        return response()->json(['success' => true]);
    }
}