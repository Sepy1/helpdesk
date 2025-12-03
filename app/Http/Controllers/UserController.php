<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        if (! $user) abort(401);
        return view('profile', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        if (! $user) abort(401);

        $data = $request->validate([
            'current_password' => ['required'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.'])->withInput();
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
