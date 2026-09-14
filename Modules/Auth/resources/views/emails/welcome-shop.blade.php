<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>স্বাগতম! আপনার দোকান চালু হয়েছে</title>
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
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 13.5px;
        }
        .summary-row:last-child {
            border-bottom: none;
        }
        .steps-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .steps-title {
            font-weight: 700;
            font-size: 14px;
            color: #166534;
            margin-bottom: 8px;
        }
        .steps-list {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #15803d;
        }
        .steps-list li {
            margin-bottom: 4px;
        }
        .btn-container {
            text-align: center;
            margin: 28px 0;
        }
        .dashboard-btn {
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
                <p>দোকান সক্রিয়করণ সম্পন্ন</p>
            </div>

            <div class="email-body">
                <div class="greeting">অভিনন্দন {{ $user->name }}!</div>

                <div class="lead-text">
                    আপনার ইমেইল ঠিকানাটি সফলভাবে ভেরিফাই করা হয়েছে। আপনার দোকান <strong>{{ $shop->name }}</strong> এখন সম্পূর্ণ সক্রিয় এবং ব্যবহারের জন্য প্রস্তুত।
                </div>

                <div class="summary-card">
                    <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 13.5px;">
                        <tr>
                            <td style="color:#64748b;">দোকানের নাম:</td>
                            <td style="font-weight: 700; color:#0f172a; text-align:right;">{{ $shop->name }}</td>
                        </tr>
                        <tr>
                            <td style="color:#64748b;">স্টোর কোড:</td>
                            <td style="font-weight: 700; color:#0d9488; text-align:right;">{{ $shop->store_code }}</td>
                        </tr>
                        <tr>
                            <td style="color:#64748b;">ইউজারনেম / লগইন:</td>
                            <td style="font-weight: 600; color:#0f172a; text-align:right;">{{ $user->username ?? $user->phone }}</td>
                        </tr>
                        <tr>
                            <td style="color:#64748b;">প্যাকেজ:</td>
                            <td style="font-weight: 600; color:#16a34a; text-align:right;">আজীবন ফ্রি প্যাকেজ (Lifetime Free)</td>
                        </tr>
                    </table>
                </div>

                <div class="steps-box">
                    <div class="steps-title">পরবর্তী সহজ ধাপসমূহ:</div>
                    <ol class="steps-list">
                        <li><strong>পণ্য যুক্ত করুন:</strong> আপনার দোকানের ক্যাটাগরি ও পণ্যসমূহ যুক্ত করুন।</li>
                        <li><strong>ক্যাশবক্স চেক করুন:</strong> প্রতিদিনের ওপেনিং ক্যাশ ব্যালেন্স মিলিয়ে নিন।</li>
                        <li><strong>দ্রুত বিক্রি করুন (POS):</strong> কাস্টমারের কাছে দ্রুত ক্যাশ বা বাকিতে পণ্য বিক্রি শুরু করুন।</li>
                    </ol>
                </div>

                <div class="btn-container">
                    <a href="{{ route('dashboard') }}" class="dashboard-btn" target="_blank">
                        দোকান ড্যাশবোর্ডে প্রবেশ করুন &rarr;
                    </a>
                </div>
            </div>

            <div class="email-footer">
                &copy; {{ date('Y') }} {{ \Modules\Core\Models\Setting::getSiteTitle() }}. সর্বস্বত্ব সংরক্ষিত।<br>
                যে কোনো সহযোগিতায় আমাদের সাপোর্ট টিমের সাথে যোগাযোগ করুন।
            </div>
        </div>
    </div>
</body>
</html>
