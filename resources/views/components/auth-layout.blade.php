@props([
    'title' => 'লগইন',
    'titleEn' => 'Login',
    'cardTitle' => 'মাস্টারপস-এ লগইন করুন',
    'cardTitleEn' => 'Sign in to MasterPOS',
    'cardSubtitle' => 'আপনার হিসাব পরিচালনা করতে লগইন করুন',
    'cardSubtitleEn' => 'Sign in to manage your business account',
    'mark' => 'ম',
])

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F172A">
    <title>{{ $title ? $title . ' · ' : '' }}মাস্টারপস</title>

    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t === 'light' || t === 'dark') {
                    document.documentElement.setAttribute('data-theme', t);
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Baloo+Da+2:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.maateen.me/solaiman-lipi/font.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: var(--paper);
            background-image: radial-gradient(circle at 15% 15%, rgba(15,23,42,.06), transparent 45%), radial-gradient(circle at 85% 85%, rgba(241,245,249,.15), transparent 45%);
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 32px 30px;
            box-shadow: 0 24px 48px -24px rgba(15,23,42,.18);
        }
        .auth-mark {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Baloo Da 2', sans-serif;
            font-weight: 800;
            color: var(--primary-text);
            font-size: 22px;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px -6px rgba(15,23,42,.35);
        }
        .auth-title {
            display: block;
            width: 100%;
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Baloo Da 2', sans-serif;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            margin: 0 0 6px 0;
            line-height: 1.3;
        }
        .auth-sub {
            display: block;
            width: 100%;
            font-size: 13px;
            color: var(--ink-600);
            text-align: center;
            margin: 0 0 22px 0;
            line-height: 1.4;
        }
        .auth-error {
            background: var(--red-100);
            color: var(--red-600);
            font-size: 12px;
            font-weight: 600;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .auth-status {
            background: var(--teal-100);
            color: var(--teal-800);
            font-size: 12px;
            font-weight: 600;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .auth-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 14px;
        }
        .auth-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 50;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 6px 14px;
            box-shadow: 0 4px 16px -4px rgba(0,0,0,.12);
        }
        .auth-action-divider {
            width: 1px;
            height: 16px;
            background: var(--border);
        }
    </style>
</head>
<body>

<div class="auth-actions">
    <x-theme-switcher />

    <div class="auth-action-divider"></div>

    <x-lang-switcher />
</div>

<div class="auth-shell">
    <div class="auth-card">
        @if ($mark)
            <div class="auth-mark">{{ $mark }}</div>
        @endif

        @if ($cardTitle || $cardTitleEn)
            <h1 class="auth-title">
                @if ($cardTitle)
                    <span class="bn">{{ $cardTitle }}</span>
                @endif
                @if ($cardTitleEn)
                    <span class="en">{{ $cardTitleEn }}</span>
                @endif
            </h1>
        @endif

        @if ($cardSubtitle || $cardSubtitleEn)
            <p class="auth-sub">
                @if ($cardSubtitle)
                    <span class="bn">{{ $cardSubtitle }}</span>
                @endif
                @if ($cardSubtitleEn)
                    <span class="en">{{ $cardSubtitleEn }}</span>
                @endif
            </p>
        @endif

        @if (session('error'))
            <div class="auth-error">
                <x-icon name="alert-triangle" size="14" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('status'))
            <div class="auth-status">
                <x-icon name="info" size="14" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>

<div class="toast" id="toast"></div>

@if (session('status') || session('success') || session('error'))
    <script>
        (function () {
            function showToasts() {
                if (typeof window.toast !== 'function') {
                    setTimeout(showToasts, 30);
                    return;
                }
                @if (session('status'))
                    toast(@json(session('status')), @json(session('status')));
                @endif
                @if (session('success'))
                    toast(@json(session('success')), @json(session('success')));
                @endif
                @if (session('error'))
                    toast(@json(session('error')), @json(session('error')));
                @endif
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showToasts);
            } else {
                showToasts();
            }
        })();
    </script>
@endif

@stack('scripts')
</body>
</html>
