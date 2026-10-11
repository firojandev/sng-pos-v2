<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Auth\Support\PasswordInvitation;

/**
 * A user an admin created sets their own password from the emailed link.
 */
class SetPasswordController extends Controller
{
    public function show(Request $request, string $token): View
    {
        return view('auth::set-password', [
            'token' => $token,
            'email' => (string) $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে (Password must be at least 8 characters)।',
            'password.confirmed' => 'পাসওয়ার্ড নিশ্চিতকরণ মিলছে না (Password confirmation does not match)।',
        ]);

        $status = Password::broker(PasswordInvitation::BROKER)->reset(
            [...$credentials, 'password_confirmation' => $request->input('password_confirmation')],
            function (User $user, string $password): void {
                // Opening the emailed link also proves the address is theirs.
                $user->forceFill([
                    'password' => Hash::make($password),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'remember_token' => Str::random(60),
                ])->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'লিংকটি সঠিক নয় বা মেয়াদ শেষ হয়ে গেছে; আপনার প্রতিষ্ঠানের এডমিনকে নতুন লিংক পাঠাতে বলুন (This link is invalid or has expired; ask your admin to send a new one)।',
            ]);
        }

        return redirect()->route('login')->with('status', 'পাসওয়ার্ড সেট হয়েছে, এখন লগইন করুন (Password set — you can sign in now)');
    }
}
