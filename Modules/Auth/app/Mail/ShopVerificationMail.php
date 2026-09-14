<?php

namespace Modules\Auth\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Setting;
use Modules\Shop\Models\Shop;

class ShopVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?Shop $shop;

    public function __construct(
        public User $user,
        public string $verificationUrl,
        ?Shop $shop = null,
    ) {
        $this->shop = $shop ?? $user->shop;
    }

    public function envelope(): Envelope
    {
        $siteTitle = Setting::getSiteTitle();

        return new Envelope(
            subject: 'দোকান ভেরিফিকেশন লিংক | Verify Your Shop Email - '.$siteTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'auth::emails.verify-shop',
        );
    }
}
