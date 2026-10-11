<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পাসওয়ার্ড সেট করুন</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Hind Siliguri', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #334155;
            -webkit-text-size-adjust: 100%;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 30px 15px;
        }
        .email-container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .email-header {
            background-color: #0d9488;
            padding: 24px;
            text-align: center;
            color: #ffffff;
        }
        .email-header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .email-header p {
            margin: 6px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px 24px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .lead-text {
            font-size: 14.5px;
            color: #334155;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0d9488;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }
        .info-box p {
            margin: 4px 0;
            font-size: 13.5px;
        }
        .btn-container {
            text-align: center;
            margin: 28px 0;
        }
        .verify-btn {
            display: inline-block;
            background-color: #0d9488;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            font-size: 14.5px;
            font-weight: 700;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(13, 148, 136, 0.25);
        }
        .expiry-note {
            font-size: 12px;
            color: #64748b;
            text-align: center;
            margin-bottom: 20px;
        }
        .fallback-link {
            font-size: 11.5px;
            color: #64748b;
            word-break: break-all;
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 6px;
            border: 1px dashed #cbd5e1;
            margin-top: 15px;
        }
        .email-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 18px 24px;
            text-align: center;
            font-size: 11.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1>{{ \Modules\Core\Models\Setting::getSiteTitle() }}</h1>
                <p>আপনার লগইন অ্যাকাউন্ট তৈরি হয়েছে (Your login account is ready)</p>
            </div>

            <div class="email-body">
                <div class="greeting">আসসালামু আলাইকুম / প্রিয় {{ $user->name }},</div>

                <div class="lead-text">
                    {{ $organisation ? $organisation.'-এ' : '' }} আপনার জন্য একটি লগইন অ্যাকাউন্ট তৈরি করা হয়েছে। নিচের বোতামে ক্লিক করে নিজের পাসওয়ার্ড সেট করুন, তারপর লগইন করতে পারবেন।
                </div>

                <div class="info-box">
                    @if ($organisation)
                        <p><strong>প্রতিষ্ঠান (Organisation):</strong> {{ $organisation }}</p>
                    @endif
                    <p><strong>ইমেইল (Email):</strong> {{ $user->email }}</p>
                    <p><strong>মোবাইল (Phone):</strong> {{ $user->phone }}</p>
                </div>

                <div class="btn-container">
                    <a href="{{ $setupUrl }}" class="verify-btn" target="_blank">
                        পাসওয়ার্ড সেট করুন (Set Password)
                    </a>
                </div>

                <div class="expiry-note">
                    এই লিংকটি পরবর্তী {{ $expiryDays }} দিনের জন্য কার্যকর থাকবে (This link is valid for {{ $expiryDays }} days)।
                </div>

                <div class="fallback-link">
                    বোতামে সমস্যা হলে সরাসরি এই লিংকটি ব্রাউজারে কপি করে খুলুন:<br>
                    <a href="{{ $setupUrl }}" style="color: #0d9488;">{{ $setupUrl }}</a>
                </div>
            </div>

            <div class="email-footer">
                &copy; {{ date('Y') }} {{ \Modules\Core\Models\Setting::getSiteTitle() }}. সর্বস্বত্ব সংরক্ষিত।<br>
                আপনি যদি এই অ্যাকাউন্টের বিষয়ে না জানেন, তবে এই ইমেইলটি উপেক্ষা করতে পারেন।
            </div>
        </div>
    </div>
</body>
</html>
