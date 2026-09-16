<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Modules\Auth\Mail\NewShopAdminNotificationMail;
use Modules\Auth\Mail\WelcomeShopMail;

class VerificationController extends Controller
{
    /**
     * Show the email verification notice page.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $email = $user?->email ?? session('registered_email');

        return view('auth::verify-email', [
            'email' => $email,
        ]);
    }

    /**
     * Mark the user's email address as verified.
     */
    public function verify(Request $request, int|string $id, string $hash): RedirectResponse|Response|View
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'ভেরিফিকেশন লিংকটি অবৈধ বা পরিবর্তন করা হয়েছে।');
        }

        // If user has already verified their email, this link has already been used and is expired
        if ($user->hasVerifiedEmail()) {
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->view('auth::verify-expired', [
                'reason' => 'already_used',
                'email' => $user->email,
            ]);
        }

        // Mark email as verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        $shop = $user->shop;

        // 1. Send Welcome Email to the Shop Owner
        if ($shop && ! empty($user->email)) {
            try {
                Mail::to($user->email)->send(new WelcomeShopMail($user, $shop));
            } catch (\Throwable $e) {
                report($e);
            }

            // 2. Notify the Admin (Super Admin) for new shop registration
            try {
                $adminEmails = User::getSuperAdminEmails();

                if (! empty($adminEmails)) {
                    Mail::to($adminEmails)->send(new NewShopAdminNotificationMail($shop, $user));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Ensure user is logged out (do not auto-login, require manual login with credentials)
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('verification.success')->with([
            'verified_email' => $user->email,
        ]);
    }

    /**
     * Show the email verification successful page.
     */
    public function success(Request $request): View
    {
        $email = session('verified_email');

        return view('auth::verify-success', [
            'email' => $email,
        ]);
    }

    /**
     * Resend the email verification notification.
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user && session('registered_user_id')) {
            $user = User::find(session('registered_user_id'));
        }

        if (! $user) {
            return redirect()->route('login')->with('error', 'অনুগ্রহ করে প্রথমে লগইন করুন।');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'নতুন ভেরিফিকেশন লিংক আপনার ইমেইল ('.$user->email.') ঠিকানায় পাঠানো হয়েছে।');
    }
}
