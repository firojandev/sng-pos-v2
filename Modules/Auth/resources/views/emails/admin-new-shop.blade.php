<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নতুন দোকান রেজিস্ট্রেশন নোটিফিকেশন</title>
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
            background-color: #1e293b;
            padding: 24px;
            text-align: center;
            color: #ffffff;
            border-bottom: 3px solid #0d9488;
        }
        .email-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .email-header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .email-body {
            padding: 28px 24px;
            line-height: 1.6;
        }
        .alert-badge {
            display: inline-block;
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            margin-bottom: 14px;
        }
        .title {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            background-color: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }
        .details-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #e2e8f0;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .label-col {
            color: #64748b;
            width: 40%;
            font-weight: 500;
        }
        .value-col {
            color: #0f172a;
            font-weight: 600;
        }
        .btn-container {
            text-align: center;
            margin: 24px 0 10px;
        }
        .admin-btn {
            display: inline-block;
            background-color: #1e293b;
            color: #ffffff !important;
            text-decoration: none;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px;
        }
        .email-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
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
                <h1>{{ \Modules\Core\Models\Setting::getSiteTitle() }} &middot; সিস্টেম এডমিন নোটিফিকেশন</h1>
                <p>নতুন দোকান রেজিস্ট্রেশন ও ভেরিফিকেশন অ্যালার্ট</p>
            </div>

            <div class="email-body">
                <span class="alert-badge">✓ ইমেইল ভেরিফাইড ও সক্রিয়</span>
                <div class="title">একটি নতুন দোকান সফলভাবে নিবন্ধিত ও ভেরিফাই করা হয়েছে</div>

                <table class="details-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label-col">দোকানের নাম:</td>
                        <td class="value-col">{{ $shop->name }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">স্টোর কোড:</td>
                        <td class="value-col" style="color:#0d9488;">{{ $shop->store_code }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">স্লাগ / URL:</td>
                        <td class="value-col">{{ $shop->slug }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">মালিকের নাম:</td>
                        <td class="value-col">{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">মোবাইল নম্বর:</td>
                        <td class="value-col">{{ $user->phone }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">ইমেইল:</td>
                        <td class="value-col">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">ইউজারনেম:</td>
                        <td class="value-col">{{ $user->username ?? 'প্রযোজ্য নয়' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">ঠিকানা:</td>
                        <td class="value-col">{{ $shop->address ?? 'প্রযোজ্য নয়' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">সময়:</td>
                        <td class="value-col">{{ now()->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>

                <div class="btn-container">
                    <a href="{{ url('/shops') }}" class="admin-btn" target="_blank">
                        এডমিন প্যানেলে দোকান তালিকা দেখুন &rarr;
                    </a>
                </div>
            </div>

            <div class="email-footer">
                এই বার্তাটি স্বয়ংক্রিয়ভাবে প্রেরিত হয়েছে। {{ \Modules\Core\Models\Setting::getSiteTitle() }} সিস্টেম নোটিফিকেশন।
            </div>
        </div>
    </div>
</body>
</html>
