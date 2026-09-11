@php
    $cookieTheme = request()->cookie('theme');
    $cookieLang = request()->cookie('lang');
    $isDark = $cookieTheme === 'dark';
    $isEn = $cookieLang === 'en';

    $user = auth()->user();
    $userName = $user?->name;
    $userEmail = $user?->email ?? $user?->username;
    $userRole = $user && method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : null;
    $shopName = $user?->shop?->name;

    $exceptionMsg = isset($exception) && $exception->getMessage() ? trim($exception->getMessage()) : null;
    $isGenericMsg =
        empty($exceptionMsg) ||
        in_array($exceptionMsg, ['This action is unauthorized.', '403 Forbidden', 'Unauthorized', 'Forbidden']);

    $siteTitle = $siteTitle ?? \Modules\Core\Models\Setting::getSiteTitle();
    $siteTitleBn = $siteTitleBn ?? ($siteTitle === 'SNGPOS' ? 'এসএনজিপস' : $siteTitle);
    $currentSiteTitle = $isEn ? $siteTitle : $siteTitleBn;
    $siteMark = mb_strtoupper(mb_substr($siteTitle, 0, 1));
    $siteMarkBn = $siteTitle === 'SNGPOS' ? 'ম' : $siteMark;
    $supportEmail =
        \Modules\Core\Models\Setting::get('support_email') ?: 'support@' . (request()->getHost() ?: 'SNGPOS.app');
@endphp
<!DOCTYPE html>
<html lang="{{ $isEn ? 'en' : 'bn' }}" @if ($cookieTheme) data-theme="{{ $cookieTheme }}" @endif
    class="{{ $isEn ? 'lang-en' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F172A">
    <title>{{ $isEn ? '403 · Access Denied · ' . $siteTitle : '৪০৩ · অ্যাক্সেস নিষিদ্ধ · ' . $siteTitleBn }}</title>

    <script>
        (function() {
            try {
                var t = localStorage.getItem('theme');
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
            if (window.$ && window.$.ajaxPrefilter) {
                window.$.ajaxPrefilter(function(options, originalOptions, xhr) {
                    var token = $('meta[name="csrf-token"]').attr('content');
                    if (token) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', token);
                    }
                });
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .error-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            background: var(--paper);
            background-image:
                radial-gradient(circle at 12% 15%, rgba(225, 29, 72, 0.05), transparent 40%),
                radial-gradient(circle at 88% 85%, rgba(15, 23, 42, 0.04), transparent 45%);
            position: relative;
            box-sizing: border-box;
        }

        .error-topbar {
            position: fixed;
            top: 20px;
            left: 24px;
            right: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 40;
            pointer-events: none;
        }

        .error-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            pointer-events: auto;
            background: var(--card);
            border: 1px solid var(--border);
            padding: 6px 14px;
            border-radius: 999px;
            box-shadow: var(--shadow-sm);
        }

        .error-brand-mark {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: var(--primary);
            color: var(--primary-text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 15px;
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Baloo Da 2', sans-serif;
        }

        .error-brand-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--ink-900);
            letter-spacing: -0.01em;
        }

        .error-controls {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 6px 14px;
            box-shadow: var(--shadow-sm);
            pointer-events: auto;
        }

        .error-ctrl-divider {
            width: 1px;
            height: 16px;
            background: var(--border);
        }

        .error-card {
            width: 100%;
            max-width: 560px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px 36px 32px;
            box-shadow: 0 20px 48px -20px rgba(15, 23, 42, 0.12), 0 1px 3px 0 rgba(15, 23, 42, 0.05);
            text-align: center;
            margin: auto 0;
            position: relative;
            z-index: 10;
        }

        .error-emblem-wrap {
            position: relative;
            width: 88px;
            height: 88px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-emblem-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: var(--red-100);
            opacity: 0.85;
            animation: emblem-pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .error-emblem-inner {
            position: relative;
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: var(--red-600);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 10px 24px -6px rgba(225, 29, 72, 0.4);
        }

        @keyframes emblem-pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.85;
            }

            50% {
                transform: scale(1.12);
                opacity: 0.45;
            }
        }

        .error-status-badge {
            margin-bottom: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .error-title {
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Baloo Da 2', 'Plus Jakarta Sans', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: var(--ink-900);
            line-height: 1.25;
            margin: 0 0 10px;
            letter-spacing: -0.02em;
        }

        .error-desc {
            font-size: 14px;
            color: var(--ink-600);
            line-height: 1.6;
            margin: 0 auto 24px;
            max-width: 460px;
        }

        .error-callout {
            background: var(--red-100);
            border: 1px solid var(--red-ic-bg);
            border-radius: 12px;
            padding: 12px 16px;
            margin: 0 0 20px;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: var(--red-ink);
            font-size: 13px;
            line-height: 1.45;
        }

        .error-callout .callout-icon {
            flex-shrink: 0;
            margin-top: 2px;
            color: var(--red-600);
        }

        .error-user-strip {
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px 16px;
            margin: 0 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            text-align: left;
        }

        .user-strip-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .user-strip-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--primary-text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .user-strip-text {
            min-width: 0;
        }

        .user-strip-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .user-strip-sub {
            font-size: 12px;
            color: var(--ink-400);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 2px;
        }

        .error-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .error-details-box {
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            text-align: left;
            margin-top: 20px;
            font-size: 12px;
            color: var(--ink-600);
        }

        .error-details-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            background: transparent;
            border: none;
            padding: 0;
            cursor: pointer;
            color: var(--ink-700);
            font-weight: 600;
            font-size: 12px;
            font-family: inherit;
        }

        .error-details-toggle:hover {
            color: var(--ink-900);
        }

        .error-details-content {
            display: none;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed var(--border);
        }

        .error-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 11px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            word-break: break-all;
        }

        .error-meta-label {
            color: var(--ink-400);
            margin-right: 8px;
            flex-shrink: 0;
        }

        .error-meta-val {
            color: var(--ink-700);
            text-align: right;
        }

        .error-footer-text {
            font-size: 12px;
            color: var(--ink-400);
            text-align: center;
            margin-top: 20px;
        }

        .error-footer-text a {
            color: var(--ink-600);
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color 0.15s ease;
        }

        .error-footer-text a:hover {
            color: var(--ink-900);
        }

        @media (max-width: 640px) {
            .error-card {
                padding: 32px 20px 24px;
                border-radius: 18px;
            }

            .error-topbar {
                top: 12px;
                left: 12px;
                right: 12px;
            }

            .error-brand-name {
                display: none;
            }

            .error-actions {
                flex-direction: column;
                width: 100%;
            }

            .error-actions>* {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <header class="error-topbar">
        <a href="{{ route('dashboard') }}" class="error-brand" title="হোমপেজ / Dashboard">
            <span class="error-brand-mark">{{ $isEn ? $siteMark : $siteMarkBn }}</span>
            <span class="error-brand-name">
                <span class="bn">{{ $siteTitleBn }}</span>
                <span class="en">{{ $siteTitle }}</span>
            </span>
        </a>

        <div class="error-controls">
            <x-core::theme-switcher size="xs" :show-text="false" />
            <span class="error-ctrl-divider"></span>
            <x-core::lang-switcher size="xs" />
        </div>
    </header>

    <main class="error-shell">
        <div class="error-card">
            <!-- Visual Shield & Lock Icon -->
            <div class="error-emblem-wrap" aria-hidden="true">
                <div class="error-emblem-ring"></div>
                <div class="error-emblem-inner">
                    <x-core::icon name="lock" size="xl" stroke-width="2.3" />
                </div>
            </div>

            <!-- Status Pill -->
            <div class="error-status-badge">
                <x-core::badge color="danger" variant="subtle" size="sm" :dot="true">
                    <span class="bn">ত্রুটি ৪০৩ · অ্যাক্সেস নিষিদ্ধ</span>
                    <span class="en">ERROR 403 · FORBIDDEN</span>
                </x-core::badge>
            </div>

            <!-- Bilingual Heading -->
            <h1 class="error-title">
                <span class="bn">অ্যাক্সেস অনুমোদিত নয়</span>
                <span class="en">Access Denied</span>
            </h1>

            <!-- Bilingual Subtitle / Explanation -->
            <p class="error-desc">
                <span class="bn">আপনার এই পেজ বা তথ্যে প্রবেশের প্রয়োজনীয় অনুমতি (Permission) নেই। এটি যদি একটি
                    অনিচ্ছাকৃত ত্রুটি মনে হয়, তবে আপনার শপ অ্যাডমিন বা ম্যানেজারের সাথে যোগাযোগ করুন।</span>
                <span class="en">You don't have the necessary permissions to access this page or resource. If you
                    believe this is a mistake, please reach out to your shop administrator or manager.</span>
            </p>

            <!-- Custom Exception Message if present -->
            @if (!$isGenericMsg && $exceptionMsg)
                <div class="error-callout" role="alert">
                    <span class="callout-icon">
                        <x-core::icon name="alert-triangle" size="sm" />
                    </span>
                    <div>
                        <strong>
                            <span class="bn">নিরাপত্তা বার্তা:</span>
                            <span class="en">Security Note:</span>
                        </strong>
                        <span>{{ $exceptionMsg }}</span>
                    </div>
                </div>
            @endif

            <!-- Active User Strip (if logged in) -->
            @auth
                <div class="error-user-strip">
                    <div class="user-strip-info">
                        <div class="user-strip-avatar" title="{{ $userName }}">
                            {{ mb_substr($userName ?? 'U', 0, 1) }}
                        </div>
                        <div class="user-strip-text">
                            <div class="user-strip-name">
                                <span>{{ $userName }}</span>
                                @if ($userRole)
                                    <x-core::badge color="blue" size="xs">{{ $userRole }}</x-core::badge>
                                @endif
                            </div>
                            <div class="user-strip-sub">
                                <span>{{ $userEmail }}</span>
                                @if ($shopName)
                                    <span> · {{ $shopName }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <x-core::button size="sm" variant="secondary" color="danger" type="submit" icon="logout"
                            title="লগআউট করে অন্য অ্যাকাউন্টে প্রবেশ করুন">
                            <span class="bn">লগআউট</span>
                            <span class="en">Switch</span>
                        </x-core::button>
                    </form>
                </div>
            @else
                <div class="error-user-strip" style="justify-content: center; text-align: center;">
                    <div class="user-strip-sub">
                        <span class="bn">আপনি বর্তমানে কোনো অ্যাকাউন্টে লগইন অবস্থায় নেই।</span>
                        <span class="en">You are not currently logged in to an account.</span>
                    </div>
                </div>
            @endauth

            <!-- Action Buttons -->
            <div class="error-actions">
                <x-core::button size="sm" color="primary" href="{{ route('dashboard') }}" icon="home">
                    <span class="bn">ড্যাশবোর্ডে ফিরে যান</span>
                    <span class="en">Back to Dashboard</span>
                </x-core::button>

                <x-core::button size="sm" variant="secondary" id="btn-back" icon="arrow-left">
                    <span class="bn">পূর্ববর্তী পেজ</span>
                    <span class="en">Go Back</span>
                </x-core::button>

                <x-core::button size="sm" variant="secondary" id="btn-reload" icon="refresh">
                    <span class="bn">পুনরায় চেষ্টা করুন</span>
                    <span class="en">Try Again</span>
                </x-core::button>
            </div>
        </div>

        <!-- Muted Footer Support Note -->
        <div class="error-footer-text">
            <span class="bn">সাহায্য প্রয়োজন? আপনার সিস্টেম অ্যাডমিনের সাথে যোগাযোগ করুন অথবা</span>
            <span class="en">Need assistance? Contact your system administrator or</span>
            <a href="mailto:{{ $supportEmail }}" target="_blank" rel="noopener">
                <span class="bn">সাপোর্টে লিখুন</span>
                <span class="en">contact support</span>
            </a>
        </div>
    </main>

    <script>
        $(function() {
            // Go back action
            $('#btn-back').on('click', function(e) {
                e.preventDefault();
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = "{{ route('dashboard') }}";
                }
            });

            // Reload action
            $('#btn-reload').on('click', function(e) {
                e.preventDefault();
                window.location.reload();
            });

            // Technical details accordion toggle
            $('#toggle-details').on('click', function() {
                var $content = $('#details-content');
                var $chevron = $('#details-chevron');
                var isExpanded = $(this).attr('aria-expanded') === 'true';

                $content.slideToggle(150);
                $(this).attr('aria-expanded', !isExpanded);
                $chevron.css('transform', !isExpanded ? 'rotate(180deg)' : 'none');
            });

            // Copy Diagnostic Info
            $('#btn-copy-ref').on('click', function() {
                    var diagInfo = [
                        'Error: HTTP 403 Forbidden',
                        'URL: ' + window.location.href,
                        'Method: {{ request()->method() }}',
                        'Path: /{{ ltrim(request()->path(), '/') }}',
                        'Time: {{ now()->toIso8601String() }}',
                        'IP: {{ request()->ip() }}',
                        @auth 'User: {{ $userName }} (ID: {{ auth()->id() }})',
                        'Role: {{ $userRole ?? 'None' }}',
                        @if ($shopName)
                            'Shop: {{ $shopName }}',
                        @endif
                    @endauth
                ].join('\n');

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(diagInfo).then(function() {
                        var $btn = $('#btn-copy-ref');
                        var originalHtml = $btn.html();
                        $btn.html(
                            `<x-core::icon name="check" size="xs" /> <span class="bn">কপি হয়েছে!</span><span class="en">Copied!</span>`
                        );
                        setTimeout(function() {
                            $btn.html(originalHtml);
                        }, 2000);
                    });
                }
            });
        });
    </script>

</body>

</html>
