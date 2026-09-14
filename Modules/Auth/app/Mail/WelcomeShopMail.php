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

class WelcomeShopMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Shop $shop,
    ) {}

    public function envelope(): Envelope
    {
        $siteTitle = Setting::getSiteTitle();

        return new Envelope(
            subject: 'অভিনন্দন! আপনার দোকান সফলভাবে চালু হয়েছে | Welcome to '.$siteTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'auth::emails.welcome-shop',
        );
    }
}
