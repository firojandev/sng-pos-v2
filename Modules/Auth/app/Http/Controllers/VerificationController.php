<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, int|string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'ভেরিফিকেশন লিংকটি অবৈধ বা পরিবর্তন করা হয়েছে।');
        }

        if (! $user->hasVerifiedEmail()) {
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
        }

        // Auto login user if not authenticated
        if (! Auth::check() || Auth::id() !== $user->id) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        if ($user->shop_id) {
            session(['current_shop_id' => $user->shop_id]);
        }

        return redirect()->route('dashboard')->with('status', 'অভিনন্দন! আপনার ইমেইল সফলভাবে ভেরিফাই করা হয়েছে। দোকান ড্যাশবোর্ডে স্বাগতম।');
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
