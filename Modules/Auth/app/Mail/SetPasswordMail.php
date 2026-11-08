<?php

namespace Modules\Auth\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Setting;

class SetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $setupUrl,
        public ?string $organisation = null,
        public int $expiryDays = 3,
    ) {}

    public function envelope(): Envelope
    {
        $siteTitle = Setting::getSiteTitle();

        return new Envelope(
            subject: 'আপনার পাসওয়ার্ড সেট করুন | Set Your Password - '.$siteTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'auth::emails.set-password',
        );
    }
}
