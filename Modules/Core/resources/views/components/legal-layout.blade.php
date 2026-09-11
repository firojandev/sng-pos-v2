@props([
    'title' => '',
    'titleEn' => '',
    'subtitle' => '',
    'subtitleEn' => '',
    'active' => 'privacy',
    'badge' => 'আইনি নীতিমালা',
    'badgeEn' => 'Legal Policy',
    'lastUpdated' => '১১ সেপ্টেম্বর ২০২৬',
    'lastUpdatedEn' => 'September 11, 2026',
    'version' => 'v2.0',
    'toc' => [],
])

@php
    $cookieTheme = request()->cookie('theme');
    $cookieLang = request()->cookie('lang');
    $isDark = $cookieTheme === 'dark';
    $isEn = $cookieLang === 'en';

    $siteTitle = \Modules\Core\Models\Setting::getSiteTitle();
    $siteTitleBn = $siteTitle === 'SNGPOS' ? 'এসএনজিপস' : $siteTitle;
    $currentSiteTitle = $isEn ? $siteTitle : $siteTitleBn;
    $pageHeading = $isEn ? ($titleEn ?: $title) : $title;

    $isPublicMode = !auth()->check() || request()->has('public');
    $landingContent = \Modules\Core\Support\LandingPageContent::all();
    $phone = $landingContent['support_phone'] ?? '+880 1886 861430';
    $email = $landingContent['support_email'] ?? 'support@softngear.com';
    $address = $landingContent['office_address'] ?? 'Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi';
    $isRegistrationEnabled = \Modules\Core\Models\Setting::isRegistrationEnabled();
@endphp

@if (!$isPublicMode)
    {{-- AUTHENTICATED DASHBOARD MODE --}}
    <x-core::layout
        :title="$title"
        :title-en="$titleEn"
        :subtitle="$subtitle"
        :subtitle-en="$subtitleEn"
        :active="$active"
    >
        <style>
            .legal-dashboard-wrap {
                max-width: 1200px;
                margin: 0 auto;
            }
            .legal-meta-strip {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                padding: 14px 18px;
                margin-bottom: 20px;
                box-shadow: var(--shadow-sm);
            }
            .legal-grid {
                display: grid;
                grid-template-columns: 280px minmax(0, 1fr);
                gap: 24px;
                align-items: start;
            }
            .legal-toc-card {
                position: sticky;
                top: 80px;
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                padding: 16px;
                box-shadow: var(--shadow-sm);
                max-height: calc(100vh - 100px);
                overflow-y: auto;
            }
            .legal-toc-title {
                font-size: 13px;
                font-weight: 700;
                color: var(--ink-900);
                margin-bottom: 12px;
                padding-bottom: 8px;
                border-bottom: 1px solid var(--border);
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .legal-toc-list {
                list-style: none;
                margin: 0;
                padding: 0;
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            .legal-toc-link {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 7px 10px;
                border-radius: 8px;
                font-size: 12px;
                color: var(--ink-700);
                text-decoration: none;
                transition: all 0.15s ease;
                line-height: 1.35;
            }
            .legal-toc-link:hover {
                background: var(--paper);
                color: var(--teal-800);
            }
            .legal-toc-link.active {
                background: var(--teal-100);
                color: var(--teal-800);
                font-weight: 700;
            }
            .legal-section {
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                padding: 24px 26px;
                margin-bottom: 20px;
                box-shadow: var(--shadow-sm);
                scroll-margin-top: 90px;
                transition: border-color 0.2s ease;
            }
            .legal-section:hover {
                border-color: var(--teal-800);
            }
            .legal-section-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
                padding-bottom: 12px;
                border-bottom: 1px solid var(--border);
            }
            .legal-section-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: var(--teal-100);
                color: var(--teal-800);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .legal-section-title {
                font-size: 16px;
                font-weight: 700;
                color: var(--ink-900);
                margin: 0;
                line-height: 1.35;
            }
            .legal-prose {
                font-size: 13px;
                line-height: 1.7;
                color: var(--ink-700);
            }
            .legal-prose p {
                margin: 0 0 12px 0;
            }
            .legal-prose p:last-child {
                margin-bottom: 0;
            }
            .legal-prose ul, .legal-prose ol {
                margin: 0 0 12px 0;
                padding-left: 20px;
            }
            .legal-prose li {
                margin-bottom: 6px;
            }
            .legal-callout {
                background: var(--paper);
                border-left: 3.5px solid var(--teal-800);
                border-radius: 0 8px 8px 0;
                padding: 12px 16px;
                margin: 14px 0;
                font-size: 12.5px;
                color: var(--ink-700);
            }
            .legal-filter-box {
                margin-bottom: 18px;
            }
            @media (max-width: 900px) {
                .legal-grid {
                    grid-template-columns: 1fr;
                }
                .legal-toc-card {
                    display: none;
                }
            }
            @media print {
                .sidebar, .topbar, .app-footer, .legal-toc-card, .legal-filter-box, .legal-meta-strip .actions, .btn-wrap {
                    display: none !important;
                }
                .main { margin-left: 0 !important; width: 100% !important; }
                .content { padding: 0 !important; }
                .legal-section { border: none !important; box-shadow: none !important; padding: 12px 0 !important; page-break-inside: avoid; }
            }
        </style>

        <div class="legal-dashboard-wrap">
            {{-- Meta Strip --}}
            <div class="legal-meta-strip">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <x-core::badge color="teal" size="sm" rounded>
                        <span class="bn">{{ $badge }}</span>
                        <span class="en" style="display:none;">{{ $badgeEn }}</span>
                    </x-core::badge>
                    <span style="font-size: 12px; color: var(--ink-400);">•</span>
                    <span style="font-size: 12px; color: var(--ink-600);">
                        <span class="bn">সর্বশেষ সংস্করণ: <b>{{ $lastUpdated }}</b> ({{ $version }})</span>
                        <span class="en" style="display:none;">Last Revised: <b>{{ $lastUpdatedEn }}</b> ({{ $version }})</span>
                    </span>
                </div>

                <div class="actions" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    @if ($active === 'privacy')
                        <x-core::button
                            size="sm"
                            variant="secondary"
                            icon="file-text"
                            href="{{ route('terms') }}"
                        >
                            <span class="bn">ব্যবহারের শর্তাবলী</span>
                            <span class="en" style="display:none;">Terms & Conditions</span>
                        </x-core::button>
                    @else
                        <x-core::button
                            size="sm"
                            variant="secondary"
                            icon="shield"
                            href="{{ route('privacy-policy') }}"
                        >
                            <span class="bn">গোপনীয়তা নীতি</span>
                            <span class="en" style="display:none;">Privacy Policy</span>
                        </x-core::button>
                    @endif

                    <x-core::button
                        size="sm"
                        variant="secondary"
                        icon="printer"
                        onclick="window.print()"
                    >
                        <span class="bn">প্রিন্ট করুন</span>
                        <span class="en" style="display:none;">Print</span>
                    </x-core::button>

                    <x-core::button
                        size="sm"
                        variant="secondary"
                        icon="external-link"
                        href="{{ request()->fullUrlWithQuery(['public' => 1]) }}"
                        target="_blank"
                    >
                        <span class="bn">ফুলস্ক্রিন ভিউ</span>
                        <span class="en" style="display:none;">Public View</span>
                    </x-core::button>
                </div>
            </div>

            {{-- Main Layout Grid --}}
            <div class="legal-grid">
                {{-- Table of Contents Sidebar --}}
                <aside class="legal-toc-card">
                    <div class="legal-toc-title">
                        <x-core::icon name="list" size="14" />
                        <span class="bn">সূচিপত্র</span>
                        <span class="en" style="display:none;">Table of Contents</span>
                    </div>
                    <ul class="legal-toc-list">
                        @foreach ($toc as $item)
                            <li>
                                <a href="#{{ $item['id'] }}" class="legal-toc-link">
                                    <x-core::icon name="{{ $item['icon'] ?? 'file-text' }}" size="13" style="flex-shrink:0;" />
                                    <span class="bn">{{ $item['title_bn'] }}</span>
                                    <span class="en" style="display:none;">{{ $item['title_en'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </aside>

                {{-- Document Body --}}
                <main class="legal-content-pane">
                    {{-- Search Filter --}}
                    <div class="legal-filter-box">
                        <x-core::input
                            id="legalSearchInput"
                            size="sm"
                            icon="search"
                            placeholder="ধারা বা বিষয়বস্তু খুঁজুন (যেমন: ব্যাকআপ, রিফান্ড, পাসওয়ার্ড)..."
                            placeholder-en="Search policy clauses (e.g. backup, refund, password)..."
                            :no-margin="true"
                            clearable
                        />
                    </div>

                    <div id="legalNoResults" style="display:none; text-align:center; padding:40px 20px; background:var(--card); border:1px solid var(--border); border-radius:var(--radius);">
                        <x-core::icon name="search-x" size="28" style="color:var(--ink-400); margin-bottom:8px;" />
                        <div style="font-size:13.5px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">কোনো ধারা খুঁজে পাওয়া যায়নি</span>
                            <span class="en" style="display:none;">No matching policy clause found</span>
                        </div>
                        <div style="font-size:12px; color:var(--ink-500); margin-top:4px;">
                            <span class="bn">অন্য কোনো শব্দ দিয়ে পুনরায় অনুসন্ধান করুন।</span>
                            <span class="en" style="display:none;">Try searching with different keywords.</span>
                        </div>
                    </div>

                    {{ $slot }}
                </main>
            </div>
        </div>
    </x-core::layout>

@else
    {{-- STANDALONE / PUBLIC GUEST MODE --}}
    <!DOCTYPE html>
    <html lang="{{ $isEn ? 'en' : 'bn' }}" @if ($cookieTheme) data-theme="{{ $cookieTheme }}" @endif class="{{ $isEn ? 'lang-en' : '' }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $pageHeading }} · {{ $currentSiteTitle }}</title>
        <meta name="description" content="{{ $isEn ? $subtitleEn : $subtitle }}">

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
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.maateen.me/solaiman-lipi/font.css">

        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                background: var(--paper);
                color: var(--ink-700);
                font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif;
                margin: 0;
                padding: 0;
            }
            .pub-header {
                background: var(--card);
                border-bottom: 1px solid var(--border);
                position: sticky;
                top: 0;
                z-index: 40;
                backdrop-filter: blur(8px);
            }
            .pub-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
            }
            .pub-header-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                height: 64px;
            }
            .pub-brand {
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
                color: inherit;
            }
            .pub-brand-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: var(--primary);
                color: var(--primary-text);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 800;
                font-size: 16px;
            }
            .pub-brand-name {
                font-size: 17px;
                font-weight: 800;
                color: var(--ink-900);
                letter-spacing: -0.3px;
            }
            .pub-brand-tag {
                font-size: 10.5px;
                color: var(--ink-400);
                display: block;
                line-height: 1;
                margin-top: 1px;
            }
            .pub-nav {
                display: flex;
                align-items: center;
                gap: 20px;
                list-style: none;
                margin: 0;
                padding: 0;
            }
            .pub-nav a {
                color: var(--ink-600);
                text-decoration: none;
                font-size: 13px;
                font-weight: 600;
                transition: color 0.15s;
            }
            .pub-nav a:hover, .pub-nav a.active {
                color: var(--teal-800);
            }
            .pub-header-actions {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .pub-hero {
                background: var(--card);
                border-bottom: 1px solid var(--border);
                padding: 44px 0 36px;
                background-image: radial-gradient(circle at 10% 20%, rgba(15, 23, 42, .04), transparent 45%), radial-gradient(circle at 90% 80%, rgba(13, 148, 136, .05), transparent 45%);
            }
            .pub-hero-badge {
                display: inline-flex;
                margin-bottom: 12px;
            }
            .pub-hero-title {
                font-size: 28px;
                font-weight: 800;
                color: var(--ink-900);
                margin: 0 0 10px 0;
                line-height: 1.3;
            }
            .pub-hero-sub {
                font-size: 14.5px;
                color: var(--ink-600);
                max-width: 780px;
                margin: 0 0 20px 0;
                line-height: 1.6;
            }
            .pub-hero-meta {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 14px;
                padding-top: 16px;
                border-top: 1px solid var(--border);
                font-size: 12.5px;
                color: var(--ink-600);
            }
            .pub-body-wrap {
                padding: 32px 0 60px;
            }
            .pub-grid {
                display: grid;
                grid-template-columns: 280px minmax(0, 1fr);
                gap: 28px;
                align-items: start;
            }
            .pub-toc-card {
                position: sticky;
                top: 84px;
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                padding: 18px;
                box-shadow: var(--shadow-sm);
                max-height: calc(100vh - 110px);
                overflow-y: auto;
            }
            .pub-toc-title {
                font-size: 13.5px;
                font-weight: 700;
                color: var(--ink-900);
                margin-bottom: 14px;
                padding-bottom: 8px;
                border-bottom: 1px solid var(--border);
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .pub-toc-list {
                list-style: none;
                margin: 0;
                padding: 0;
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            .pub-toc-link {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 10px;
                border-radius: 8px;
                font-size: 12.5px;
                color: var(--ink-700);
                text-decoration: none;
                transition: all 0.15s;
                line-height: 1.35;
            }
            .pub-toc-link:hover {
                background: var(--paper);
                color: var(--teal-800);
            }
            .pub-toc-link.active {
                background: var(--teal-100);
                color: var(--teal-800);
                font-weight: 700;
            }
            .legal-section {
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                padding: 26px 28px;
                margin-bottom: 22px;
                box-shadow: var(--shadow-sm);
                scroll-margin-top: 90px;
                transition: border-color 0.2s;
            }
            .legal-section:hover {
                border-color: var(--teal-800);
            }
            .legal-section-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
                padding-bottom: 12px;
                border-bottom: 1px solid var(--border);
            }
            .legal-section-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: var(--teal-100);
                color: var(--teal-800);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .legal-section-title {
                font-size: 16.5px;
                font-weight: 700;
                color: var(--ink-900);
                margin: 0;
                line-height: 1.35;
            }
            .legal-prose {
                font-size: 13.5px;
                line-height: 1.75;
                color: var(--ink-700);
            }
            .legal-prose p {
                margin: 0 0 12px 0;
            }
            .legal-prose p:last-child {
                margin-bottom: 0;
            }
            .legal-prose ul, .legal-prose ol {
                margin: 0 0 12px 0;
                padding-left: 20px;
            }
            .legal-prose li {
                margin-bottom: 6px;
            }
            .legal-callout {
                background: var(--paper);
                border-left: 3.5px solid var(--teal-800);
                border-radius: 0 8px 8px 0;
                padding: 12px 16px;
                margin: 14px 0;
                font-size: 13px;
                color: var(--ink-700);
            }
            .pub-footer {
                background: var(--card);
                border-top: 1px solid var(--border);
                padding: 40px 0 24px;
            }
            .pub-footer-grid {
                display: grid;
                grid-template-columns: 2fr 1fr 1.5fr;
                gap: 32px;
                margin-bottom: 32px;
            }
            .pub-footer-col h4 {
                font-size: 13.5px;
                font-weight: 700;
                color: var(--ink-900);
                margin: 0 0 14px 0;
            }
            .pub-footer-links {
                list-style: none;
                margin: 0;
                padding: 0;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .pub-footer-links a {
                color: var(--ink-600);
                text-decoration: none;
                font-size: 12.5px;
                transition: color 0.15s;
            }
            .pub-footer-links a:hover {
                color: var(--teal-800);
            }
            .pub-footer-contact-item {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 12.5px;
                color: var(--ink-600);
                margin-bottom: 8px;
            }
            .pub-footer-bottom {
                padding-top: 20px;
                border-top: 1px solid var(--border);
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
                font-size: 12px;
                color: var(--ink-400);
            }
            @media (max-width: 900px) {
                .pub-grid { grid-template-columns: 1fr; }
                .pub-toc-card { display: none; }
                .pub-nav { display: none; }
                .pub-footer-grid { grid-template-columns: 1fr; gap: 24px; }
            }
            @media print {
                .pub-header, .pub-footer, .pub-toc-card, .actions, .pub-hero-meta .actions, .legal-filter-box {
                    display: none !important;
                }
                .pub-hero { padding: 10px 0 !important; border: none !important; background: transparent !important; }
                .pub-body-wrap { padding: 0 !important; }
                .legal-section { border: none !important; box-shadow: none !important; padding: 10px 0 !important; page-break-inside: avoid; }
            }
        </style>
    </head>
    <body>

        {{-- Top Navigation Bar --}}
        <header class="pub-header">
            <div class="pub-container">
                <div class="pub-header-inner">
                    <a href="{{ route('home') }}" class="pub-brand">
                        <div class="pub-brand-icon">
                            <x-core::icon name="shopping-bag" size="18" />
                        </div>
                        <div>
                            <span class="pub-brand-name">{{ $siteTitleBn }}</span>
                            <span class="pub-brand-tag">Cloud POS & ERP</span>
                        </div>
                    </a>

                    <ul class="pub-nav">
                        <li>
                            <a href="{{ route('home') }}">
                                <span class="bn">হোম</span>
                                <span class="en" style="display:none;">Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('home') }}#features">
                                <span class="bn">ফিচারসমূহ</span>
                                <span class="en" style="display:none;">Features</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('privacy-policy') }}" class="{{ $active === 'privacy' ? 'active' : '' }}">
                                <span class="bn">গোপনীয়তা নীতি</span>
                                <span class="en" style="display:none;">Privacy Policy</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('terms') }}" class="{{ $active === 'terms' ? 'active' : '' }}">
                                <span class="bn">ব্যবহারের শর্তাবলী</span>
                                <span class="en" style="display:none;">Terms</span>
                            </a>
                        </li>
                    </ul>

                    <div class="pub-header-actions">
                        <x-core::theme-switcher size="sm" :show-text="false" />
                        <x-core::lang-switcher size="sm" />

                        @if (auth()->check())
                            <x-core::button size="sm" color="primary" icon="layout-dashboard" href="{{ route('dashboard') }}">
                                <span class="bn">ড্যাশবোর্ড</span>
                                <span class="en" style="display:none;">Dashboard</span>
                            </x-core::button>
                        @else
                            <x-core::button size="sm" variant="secondary" href="{{ route('login') }}">
                                <span class="bn">লগইন</span>
                                <span class="en" style="display:none;">Sign In</span>
                            </x-core::button>
                            @if ($isRegistrationEnabled)
                                <x-core::button size="sm" color="primary" href="{{ route('register') }}">
                                    <span class="bn">রেজিস্ট্রেশন</span>
                                    <span class="en" style="display:none;">Register</span>
                                </x-core::button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </header>

        {{-- Hero Header --}}
        <section class="pub-hero">
            <div class="pub-container">
                <div class="pub-hero-badge">
                    <x-core::badge color="teal" size="sm" rounded>
                        <span class="bn">{{ $badge }}</span>
                        <span class="en" style="display:none;">{{ $badgeEn }}</span>
                    </x-core::badge>
                </div>

                <h1 class="pub-hero-title">
                    <span class="bn">{{ $title }}</span>
                    <span class="en" style="display:none;">{{ $titleEn }}</span>
                </h1>

                <p class="pub-hero-sub">
                    <span class="bn">{{ $subtitle }}</span>
                    <span class="en" style="display:none;">{{ $subtitleEn }}</span>
                </p>

                <div class="pub-hero-meta">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span>
                            <span class="bn">সর্বশেষ হালনাগাদ: <strong>{{ $lastUpdated }}</strong></span>
                            <span class="en" style="display:none;">Last Updated: <strong>{{ $lastUpdatedEn }}</strong></span>
                        </span>
                        <span>•</span>
                        <span>
                            <span class="bn">সংস্করণ: <strong>{{ $version }}</strong></span>
                            <span class="en" style="display:none;">Version: <strong>{{ $version }}</strong></span>
                        </span>
                    </div>

                    <div class="actions" style="display: flex; align-items: center; gap: 8px;">
                        @if ($active === 'privacy')
                            <x-core::button size="sm" variant="secondary" icon="file-text" href="{{ route('terms') }}">
                                <span class="bn">ব্যবহারের শর্তাবলী দেখুন</span>
                                <span class="en" style="display:none;">View Terms</span>
                            </x-core::button>
                        @else
                            <x-core::button size="sm" variant="secondary" icon="shield" href="{{ route('privacy-policy') }}">
                                <span class="bn">গোপনীয়তা নীতি দেখুন</span>
                                <span class="en" style="display:none;">View Privacy Policy</span>
                            </x-core::button>
                        @endif

                        <x-core::button size="sm" variant="secondary" icon="printer" onclick="window.print()">
                            <span class="bn">প্রিন্ট করুন</span>
                            <span class="en" style="display:none;">Print</span>
                        </x-core::button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Document Area --}}
        <section class="pub-body-wrap">
            <div class="pub-container">
                <div class="pub-grid">
                    {{-- Sticky Table of Contents --}}
                    <aside class="pub-toc-card">
                        <div class="pub-toc-title">
                            <x-core::icon name="list" size="14" />
                            <span class="bn">সূচিপত্র</span>
                            <span class="en" style="display:none;">Table of Contents</span>
                        </div>
                        <ul class="pub-toc-list">
                            @foreach ($toc as $item)
                                <li>
                                    <a href="#{{ $item['id'] }}" class="pub-toc-link">
                                        <x-core::icon name="{{ $item['icon'] ?? 'file-text' }}" size="13" style="flex-shrink:0;" />
                                        <span class="bn">{{ $item['title_bn'] }}</span>
                                        <span class="en" style="display:none;">{{ $item['title_en'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </aside>

                    {{-- Document Content Pane --}}
                    <main class="pub-content-pane">
                        <div class="legal-filter-box" style="margin-bottom: 20px;">
                            <x-core::input
                                id="legalSearchInput"
                                size="sm"
                                icon="search"
                                placeholder="ধারা বা বিষয়বস্তু খুঁজুন (যেমন: ব্যাকআপ, রিফান্ড, পাসওয়ার্ড)..."
                                placeholder-en="Search policy clauses (e.g. backup, refund, password)..."
                                :no-margin="true"
                                clearable
                            />
                        </div>

                        <div id="legalNoResults" style="display:none; text-align:center; padding:40px 20px; background:var(--card); border:1px solid var(--border); border-radius:var(--radius);">
                            <x-core::icon name="search-x" size="28" style="color:var(--ink-400); margin-bottom:8px;" />
                            <div style="font-size:13.5px; font-weight:700; color:var(--ink-900);">
                                <span class="bn">কোনো ধারা খুঁজে পাওয়া যায়নি</span>
                                <span class="en" style="display:none;">No matching policy clause found</span>
                            </div>
                            <div style="font-size:12px; color:var(--ink-500); margin-top:4px;">
                                <span class="bn">অন্য কোনো শব্দ দিয়ে পুনরায় অনুসন্ধান করুন।</span>
                                <span class="en" style="display:none;">Try searching with different keywords.</span>
                            </div>
                        </div>

                        {{ $slot }}
                    </main>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="pub-footer">
            <div class="pub-container">
                <div class="pub-footer-grid">
                    <div class="pub-footer-col">
                        <h4>{{ $siteTitleBn }} ({{ $siteTitle }})</h4>
                        <p style="font-size: 12.5px; color: var(--ink-600); line-height: 1.6; margin: 0 0 12px 0;">
                            <span class="bn">বাংলাদেশের আধুনিক ব্যবসায়ী, সুপারশপ ও রিটেইল স্টোরের জন্য সর্বাধিক দ্রুত, নির্ভুল এবং নির্ভরযোগ্য ক্লাউড পিওএস ও ইনভেন্টরি ম্যানেজমেন্ট সফটওয়্যার।</span>
                            <span class="en" style="display:none;">Bangladesh's most reliable and fastest Cloud POS & ERP software for modern retail and wholesale businesses.</span>
                        </p>
                        <div style="font-size: 12px; color: var(--ink-500);">
                            <span class="bn">কারিগরি সহযোগিতায়: <b>Softngear</b></span>
                            <span class="en" style="display:none;">Powered by: <b>Softngear</b></span>
                        </div>
                    </div>

                    <div class="pub-footer-col">
                        <h4>
                            <span class="bn">আইনি ও অন্যান্য লিংক</span>
                            <span class="en" style="display:none;">Legal & Quick Links</span>
                        </h4>
                        <ul class="pub-footer-links">
                            <li>
                                <a href="{{ route('home') }}">
                                    <span class="bn">মূল পাতা (Home)</span>
                                    <span class="en" style="display:none;">Home</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('privacy-policy') }}">
                                    <span class="bn">গোপনীয়তা নীতি (Privacy Policy)</span>
                                    <span class="en" style="display:none;">Privacy Policy</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('terms') }}">
                                    <span class="bn">ব্যবহারের শর্তাবলী (Terms & Conditions)</span>
                                    <span class="en" style="display:none;">Terms & Conditions</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('login') }}">
                                    <span class="bn">লগইন পোর্টাল (Login)</span>
                                    <span class="en" style="display:none;">Login</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="pub-footer-col">
                        <h4>
                            <span class="bn">যোগাযোগ ও সহায়তা</span>
                            <span class="en" style="display:none;">Support & Contact</span>
                        </h4>
                        <div class="pub-footer-contact-item">
                            <x-core::icon name="phone" size="14" style="color:var(--teal-800); flex-shrink:0;" />
                            <span>{{ $phone }}</span>
                        </div>
                        <div class="pub-footer-contact-item">
                            <x-core::icon name="mail" size="14" style="color:var(--teal-800); flex-shrink:0;" />
                            <span>{{ $email }}</span>
                        </div>
                        <div class="pub-footer-contact-item">
                            <x-core::icon name="map-pin" size="14" style="color:var(--teal-800); flex-shrink:0;" />
                            <span>{{ $address }}</span>
                        </div>
                    </div>
                </div>

                <div class="pub-footer-bottom">
                    <div>
                        &copy; {{ now()->year }} <b>{{ $siteTitleBn }}</b>.
                        <span class="bn">সর্বস্বত্ব সংরক্ষিত।</span>
                        <span class="en" style="display:none;">All rights reserved.</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <a href="{{ route('privacy-policy') }}" style="color: var(--ink-500); text-decoration: none;">
                            <span class="bn">গোপনীয়তা নীতি</span>
                            <span class="en" style="display:none;">Privacy</span>
                        </a>
                        <span>&middot;</span>
                        <a href="{{ route('terms') }}" style="color: var(--ink-500); text-decoration: none;">
                            <span class="bn">শর্তাবলী</span>
                            <span class="en" style="display:none;">Terms</span>
                        </a>
                        <span>&middot;</span>
                        <a href="{{ route('home') }}#faq" style="color: var(--ink-500); text-decoration: none;">
                            <span class="bn">সহায়তা</span>
                            <span class="en" style="display:none;">Support</span>
                        </a>
                    </div>
                </div>
            </div>
        </footer>

    </body>
    </html>
@endif

<script>
    $(function () {
        // Realtime Client-Side Search Filter across policy sections
        $('#legalSearchInput').on('input keyup', function () {
            const query = String($(this).val() || '').toLowerCase().trim();
            let matches = 0;

            if (!query) {
                $('.legal-section').show();
                $('.legal-toc-link, .pub-toc-link').show();
                $('#legalNoResults').hide();
                return;
            }

            $('.legal-section').each(function () {
                const sectionId = $(this).attr('id');
                const text = $(this).text().toLowerCase();

                if (text.indexOf(query) !== -1) {
                    $(this).show();
                    $('a[href="#' + sectionId + '"]').show();
                    matches++;
                } else {
                    $(this).hide();
                    $('a[href="#' + sectionId + '"]').hide();
                }
            });

            if (matches === 0) {
                $('#legalNoResults').show();
            } else {
                $('#legalNoResults').hide();
            }
        });

        // Active State Tracking for TOC Links
        $(window).on('scroll', function () {
            const scrollPos = $(window).scrollTop() + 110;

            $('.legal-section:visible').each(function () {
                const top = $(this).offset().top;
                const bottom = top + $(this).outerHeight();
                const id = $(this).attr('id');

                if (scrollPos >= top && scrollPos < bottom) {
                    $('.legal-toc-link, .pub-toc-link').removeClass('active');
                    $('a[href="#' + id + '"]').addClass('active');
                }
            });
        });

        // Smooth Scrolling on TOC click
        $(document).on('click', '.legal-toc-link, .pub-toc-link', function (e) {
            const targetId = $(this).attr('href');
            if (targetId && targetId.startsWith('#') && targetId.length > 1) {
                const target = $(targetId);
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 85
                    }, 250);
                }
            }
        });
    });
</script>
