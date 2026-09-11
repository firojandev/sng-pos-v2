@props([
    'title' => 'লগইন',
    'titleEn' => 'Login',
    'cardTitle' => null,
    'cardTitleEn' => null,
    'cardSubtitle' => 'আপনার হিসাব পরিচালনা করতে লগইন করুন',
    'cardSubtitleEn' => 'Sign in to manage your business account',
    'mark' => null,
    'maxWidth' => '400px',
    'showThemeSwitcher' => true,
    'defaultTheme' => null,
])

@php
    $cookieTheme = request()->cookie('theme');
    $cookieLang = request()->cookie('lang');
    $effectiveTheme = $cookieTheme ?: $defaultTheme;
    $isDark = $effectiveTheme === 'dark';
    $isEn = $cookieLang === 'en';

    $siteTitle = $siteTitle ?? \Modules\Core\Models\Setting::getSiteTitle();
    $siteTitleBn = $siteTitleBn ?? ($siteTitle === 'SNGPOS' ? 'এসএনজিপস' : $siteTitle);
    $currentSiteTitle = $isEn ? $siteTitle : $siteTitleBn;
    $pageHeading = $isEn ? ($titleEn ?: $title) : $title;

    $cardTitle = $cardTitle ?? $siteTitleBn . '-এ লগইন করুন';
    $cardTitleEn = $cardTitleEn ?? 'Sign in to ' . $siteTitle;
    $mark =
        $mark ??
        ($isEn
            ? mb_strtoupper(mb_substr($siteTitle, 0, 1))
            : ($siteTitle === 'SNGPOS'
                ? 'ম'
                : mb_strtoupper(mb_substr($siteTitle, 0, 1))));
@endphp

<!DOCTYPE html>
<html lang="{{ $isEn ? 'en' : 'bn' }}" @if ($effectiveTheme) data-theme="{{ $effectiveTheme }}" @endif
    class="{{ $isEn ? 'lang-en' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F172A">
    <title>{{ $pageHeading ? $pageHeading . ' · ' : '' }}{{ $currentSiteTitle }}</title>

    <script>
        (function() {
            try {
                var defaultTheme = @json($defaultTheme);
                var t = defaultTheme || localStorage.getItem('theme');
                if (t === 'light' || t === 'dark') {
                    document.documentElement.setAttribute('data-theme', t);
                    if (!document.cookie.includes('theme=' + t)) {
                        document.cookie = "theme=" + t + ";path=/;max-age=31536000;SameSite=Lax";
                    }
                }
                var l = localStorage.getItem('lang');
                if (l === 'en' || l === 'bn') {
                    if (l === 'en') {
                        document.documentElement.classList.add('lang-en');
                    } else {
                        document.documentElement.classList.remove('lang-en');
                    }
                    if (!document.cookie.includes('lang=' + l)) {
                        document.cookie = "lang=" + l + ";path=/;max-age=31536000;SameSite=Lax";
                    }
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Baloo+Da+2:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.maateen.me/solaiman-lipi/font.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        (function() {
            if (window.$) {
                if (!window.$.trim) {
                    window.$.trim = function(str) {
                        return str == null ? '' : (str + '').trim();
                    };
                }
                if (window.$.ajaxPrefilter) {
                    window.$.ajaxPrefilter(function(options, originalOptions, xhr) {
                        var token = $('meta[name="csrf-token"]').attr('content');
                        if (token) {
                            xhr.setRequestHeader('X-CSRF-TOKEN', token);
                        }
                    });
                }
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .auth-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: var(--paper);
            background-image: radial-gradient(circle at 15% 15%, rgba(15, 23, 42, .06), transparent 45%), radial-gradient(circle at 85% 85%, rgba(241, 245, 249, .15), transparent 45%);
        }

        .auth-footer a:hover {
            color: var(--teal-800) !important;
            text-decoration: underline !important;
        }

        .auth-card {
            width: 100%;
            max-width: 400px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 32px 30px;
            box-shadow: 0 24px 48px -24px rgba(15, 23, 42, .18);
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
            box-shadow: 0 8px 24px -6px rgba(15, 23, 42, .35);
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
            box-shadow: 0 4px 16px -4px rgba(0, 0, 0, .12);
        }

        .auth-action-divider {
            width: 1px;
            height: 16px;
            background: var(--border);
        }

        @media (max-width: 640px) {
            .auth-shell {
                padding: 16px 12px 32px;
                justify-content: flex-start;
                min-height: 100vh;
            }
            .auth-actions {
                position: relative;
                top: auto;
                right: auto;
                margin: 0 auto 16px auto;
                padding: 5px 12px;
                gap: 10px;
            }
            .auth-card {
                padding: 22px 16px;
                border-radius: 14px;
            }
            .auth-title {
                font-size: 18px;
            }
            .auth-sub {
                font-size: 12.5px;
                margin-bottom: 18px;
            }
        }
    </style>
</head>

<body>

    <div class="auth-actions">
        @if ($showThemeSwitcher)
            <x-core::theme-switcher />
            <div class="auth-action-divider"></div>
        @endif

        <x-core::lang-switcher />
    </div>

    <div class="auth-shell">
        <div class="auth-card" style="max-width: {{ $maxWidth }};">
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
                    <x-core::icon name="alert-triangle" size="14" />
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="auth-status">
                    <x-core::icon name="info" size="14" />
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{ $slot }}
        </div>

        <footer class="auth-footer" style="margin-top: 24px; text-align: center; font-size: 11.5px; color: var(--ink-400); display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap;">
            <span>&copy; {{ now()->year }} {{ $siteTitleBn }}</span>
            <span>&middot;</span>
            <a href="{{ route('privacy-policy') }}" style="color: var(--ink-600); text-decoration: none; transition: color 0.15s;">
                <span class="bn">গোপনীয়তা নীতি</span>
                <span class="en" style="display:none;">Privacy Policy</span>
            </a>
            <span>&middot;</span>
            <a href="{{ route('terms') }}" style="color: var(--ink-600); text-decoration: none; transition: color 0.15s;">
                <span class="bn">ব্যবহারের শর্তাবলী</span>
                <span class="en" style="display:none;">Terms & Conditions</span>
            </a>
            <span>&middot;</span>
            <a href="{{ route('home') }}" style="color: var(--ink-600); text-decoration: none; transition: color 0.15s;">
                <span class="bn">মূল পাতা</span>
                <span class="en" style="display:none;">Home</span>
            </a>
        </footer>
    </div>

    <div class="toast" id="toast"></div>

    @if (session('status') || session('success') || session('error'))
        <script>
            (function() {
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
