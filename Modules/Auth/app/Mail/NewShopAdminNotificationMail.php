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

class NewShopAdminNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Shop $shop,
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        $siteTitle = Setting::getSiteTitle();

        return new Envelope(
            subject: 'নতুন দোকান রেজিস্ট্রেশন ও ভেরিফিকেশন: '.$this->shop->name.' | '.$siteTitle.' Admin Alert',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'auth::emails.admin-new-shop',
        );
    }
}
