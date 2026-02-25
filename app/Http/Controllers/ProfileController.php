<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Normalize phone number to Indonesian format (e.g. 6285725681860)
        if (array_key_exists('no_hp', $data)) {
            $raw = trim($data['no_hp'] ?? '');
            // remove non-digit chars
            $digits = preg_replace('/\D+/', '', $raw);
            if ($digits === '') {
                $data['no_hp'] = null;
            } else {
                if (str_starts_with($digits, '0')) {
                    $data['no_hp'] = '62' . substr($digits, 1);
                } elseif (str_starts_with($digits, '62')) {
                    $data['no_hp'] = $digits;
                } elseif (str_starts_with($digits, '8')) {
                    // user entered without leading zero
                    $data['no_hp'] = '62' . $digits;
                } else {
                    // fallback: store as digits
                    $data['no_hp'] = $digits;
                }
            }
        }

        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display vendor profile form.
     */
    public function vendorEdit(Request $request): View
    {
        if ($request->user()->role !== 'VENDOR') abort(403);

        return view('vendor.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update vendor profile information.
     */
    public function vendorUpdate(ProfileUpdateRequest $request): RedirectResponse
    {
        if ($request->user()->role !== 'VENDOR') abort(403);

        $data = $request->validated();

        if (array_key_exists('no_hp', $data)) {
            $raw = trim($data['no_hp'] ?? '');
            $digits = preg_replace('/\D+/', '', $raw);
            if ($digits === '') {
                $data['no_hp'] = null;
            } else {
                if (str_starts_with($digits, '0')) {
                    $data['no_hp'] = '62' . substr($digits, 1);
                } elseif (str_starts_with($digits, '62')) {
                    $data['no_hp'] = $digits;
                } elseif (str_starts_with($digits, '8')) {
                    $data['no_hp'] = '62' . $digits;
                } else {
                    $data['no_hp'] = $digits;
                }
            }
        }

        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('vendor.profile.edit')->with('status', 'profile-updated');
    }
}
