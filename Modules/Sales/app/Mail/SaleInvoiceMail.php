<?php

namespace Modules\Sales\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\Shop;

class SaleInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Sale $sale,
        public string $recipientEmail,
    ) {}

    public function envelope(): Envelope
    {
        $shop = $this->sale->shop ?? auth()->user()?->shop ?? Shop::first();
        $shopName = $shop?->name ?? 'ব্যবসা প্রতিষ্ঠান';

        return new Envelope(
            subject: "বিক্রয় ইনভয়েস #{$this->sale->invoice_no} - {$shopName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'sales::emails.invoice',
        );
    }
}
