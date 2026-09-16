@php
    use Modules\Core\Support\BanglaNumber;

    $shop = $sale->shop ?? auth()->user()?->shop ?? \Modules\Shop\Models\Shop::first();
    $customer = $sale->customer;
    $publicUrl = $sale->public_url;
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বিক্রয় ইনভয়েস #{{ $sale->invoice_no }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: 'Noto Sans Bengali', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .email-header {
            background: #0f172a;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .email-header p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #94a3b8;
        }
        .email-body {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .intro-text {
            font-size: 14px;
            color: #475569;
            margin-bottom: 24px;
        }
        .invoice-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13.5px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #64748b;
        }
        .info-value {
            font-weight: 600;
            color: #0f172a;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn-view-invoice {
            display: inline-block;
            background: #2563eb;
            color: #ffffff !important;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 6px;
        }
        .fallback-link {
            font-size: 12px;
            color: #64748b;
            text-align: center;
            word-break: break-all;
            margin-top: 16px;
        }
        .email-footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- Header --}}
        <div class="email-header">
            <h1>{{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}</h1>
            @if (!empty($shop?->address))
                <p>{{ $shop->address }}</p>
            @endif
            @if (!empty($shop?->phone))
                <p>মোবাইল: {{ $shop->phone }}</p>
            @endif
        </div>

        {{-- Body --}}
        <div class="email-body">
            <div class="greeting">
                প্রিয় {{ $customer->name ?? 'সম্মানিত গ্রাহক' }},
            </div>
            <div class="intro-text">
                আমাদের সাথে কেনাকাটা করার জন্য ধন্যবাদ। আপনার বিক্রয় ইনভয়েসটি প্রস্তুত করা হয়েছে। নিচে ইনভয়েসের সারসংক্ষেপ ও বিস্তারিত দেখার লিংক দেওয়া হলো:
            </div>

            {{-- Invoice Summary Table --}}
            <table style="width:100%; border-collapse:collapse; margin-bottom:24px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;">
                <tr>
                    <td style="padding:10px 16px; color:#64748b; font-size:13px; border-bottom:1px dashed #e2e8f0;">ইনভয়েস নম্বর:</td>
                    <td style="padding:10px 16px; text-align:right; font-weight:700; font-size:13px; color:#0f172a; border-bottom:1px dashed #e2e8f0;">#{{ $sale->invoice_no }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 16px; color:#64748b; font-size:13px; border-bottom:1px dashed #e2e8f0;">তারিখ:</td>
                    <td style="padding:10px 16px; text-align:right; font-weight:600; font-size:13px; color:#0f172a; border-bottom:1px dashed #e2e8f0;">{{ $sale->created_at ? $sale->created_at->format('d/m/Y h:i A') : '' }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 16px; color:#64748b; font-size:13px; border-bottom:1px dashed #e2e8f0;">মোট পরিমাণ:</td>
                    <td style="padding:10px 16px; text-align:right; font-weight:700; font-size:14px; color:#0f172a; border-bottom:1px dashed #e2e8f0;">৳ {{ BanglaNumber::toBnMoney($sale->total) }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 16px; color:#64748b; font-size:13px; border-bottom:1px dashed #e2e8f0;">পরিশোধ:</td>
                    <td style="padding:10px 16px; text-align:right; font-weight:600; font-size:13px; color:#15803d; border-bottom:1px dashed #e2e8f0;">৳ {{ BanglaNumber::toBnMoney($sale->paid_amount) }}</td>
                </tr>
                @if ((float) $sale->due_amount > 0)
                    <tr>
                        <td style="padding:10px 16px; color:#64748b; font-size:13px;">বর্তমান বাকি:</td>
                        <td style="padding:10px 16px; text-align:right; font-weight:700; font-size:14px; color:#b91c1c;">৳ {{ BanglaNumber::toBnMoney($sale->due_amount) }}</td>
                    </tr>
                @endif
            </table>

            {{-- CTA Button --}}
            <div style="text-align:center; margin:32px 0;">
                <a href="{{ $publicUrl }}" style="background:#2563eb; color:#ffffff; padding:12px 30px; font-size:14px; font-weight:700; text-decoration:none; border-radius:6px; display:inline-block;">
                    অনলাইন ইনভয়েস দেখুন ও প্রিন্ট করুন
                </a>
            </div>

            <div class="fallback-link">
                লিংকটি কাজ না করলে সরাসরি এই লিংকটি ব্রাউজারে পেস্ট করুন:<br>
                <a href="{{ $publicUrl }}" style="color:#2563eb;">{{ $publicUrl }}</a>
            </div>
        </div>

        {{-- Footer --}}
        <div class="email-footer">
            <p style="margin:0;">ধন্যবাদান্তে,<br><strong>{{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}</strong></p>
            <p style="margin:6px 0 0; font-size:11px; color:#94a3b8;">এটি একটি স্বয়ংক্রিয়ভাবে প্রেরিত ইমেইল।</p>
        </div>
    </div>
</body>
</html>
