<?php

namespace Modules\Auth\Support;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Mail\SetPasswordMail;
use Throwable;

/**
 * Emails a user an admin created a link to set their own password, so the
 * admin never has to choose or pass on a password.
 */
class PasswordInvitation
{
    public const BROKER = 'invitations';

    /**
     * Whether the email was sent (or queued). A mail failure is logged, not
     * thrown: the login exists and the link can be sent again.
     */
    public static function send(User $user, ?string $organisation = null, bool $queued = false): bool
    {
        if (! $user->email) {
            return false;
        }

        $url = route('password.setup', [
            'token' => Password::broker(self::BROKER)->createToken($user),
            'email' => $user->email,
        ]);

        $mail = new SetPasswordMail($user, $url, $organisation, self::expiryDays());

        try {
            $queued ? Mail::to($user->email)->queue($mail) : Mail::to($user->email)->send($mail);
        } catch (Throwable $exception) {
            Log::warning('Set-password email could not be sent', ['user_id' => $user->id, 'error' => $exception->getMessage()]);

            return false;
        }

        return true;
    }

    public static function expiryDays(): int
    {
        return (int) ceil(config('auth.passwords.'.self::BROKER.'.expire', 4320) / 1440);
    }
}
