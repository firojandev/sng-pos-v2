<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন &middot; মাস্টারপস</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .auth-shell{min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; background:var(--paper);
            background-image:radial-gradient(circle at 15% 15%, rgba(211,161,62,.10), transparent 45%), radial-gradient(circle at 85% 85%, rgba(14,92,74,.10), transparent 45%);}
        .auth-card{width:100%; max-width:400px; background:var(--card); border:1px solid var(--border); border-radius:18px; padding:32px 30px; box-shadow:0 24px 48px -24px rgba(11,76,62,.25);}
        .auth-mark{width:52px; height:52px; border-radius:14px; background:var(--gold-500); display:flex; align-items:center; justify-content:center; font-family:'Baloo Da 2',sans-serif; font-weight:800; color:var(--teal-900); font-size:22px; margin:0 auto 16px;}
        .auth-title{font-family:'Baloo Da 2',sans-serif; font-size:20px; font-weight:700; text-align:center;}
        .auth-sub{font-size:12.5px; color:var(--ink-600); text-align:center; margin-top:4px; margin-bottom:22px;}
        .auth-error{background:var(--red-100); color:var(--red-600); font-size:12px; font-weight:600; padding:10px 12px; border-radius:10px; margin-bottom:14px;}
        .auth-row{display:flex; align-items:center; justify-content:space-between; margin-top:4px;}
        .auth-row label{display:flex; align-items:center; gap:6px; font-size:12px; color:var(--ink-600); font-weight:600;}
        .auth-lang{position:fixed; top:20px; right:20px;}
    </style>
</head>
<body>

<div class="langswitch auth-lang">
    <button id="btn-bn" class="active" onclick="setLang('bn')">বাং</button>
    <button id="btn-en" onclick="setLang('en')">EN</button>
</div>

<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-mark">ম</div>
        <div class="auth-title bn">মাস্টারপস-এ লগইন করুন</div>
        <div class="auth-title en" style="display:none;">Sign in to MasterPOS</div>
        <div class="auth-sub bn">আপনার হিসাব পরিচালনা করতে লগইন করুন</div>
        <div class="auth-sub en" style="display:none;">Sign in to manage your business account</div>

        @if ($errors->any())
            <div class="auth-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="field" style="margin-top:0;">
                <label class="bn">ইমেইল</label><label class="en" style="display:none;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
            </div>
            <div class="field">
                <label class="bn">পাসওয়ার্ড</label><label class="en" style="display:none;">Password</label>
                <input type="password" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
            </div>
            <div class="auth-row">
                <label>
                    <input type="checkbox" name="remember" style="width:auto;">
                    <span class="bn">মনে রাখুন</span><span class="en">Remember me</span>
                </label>
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%; justify-content:center; margin-top:20px; padding:12px 0; font-size:13.5px;">
                <span class="bn">লগইন করুন</span><span class="en">Sign In</span>
            </button>
        </form>
    </div>
</div>

</body>
</html>
