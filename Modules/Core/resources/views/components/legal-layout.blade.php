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
            .legal-scroll-top {
                position: fixed;
                bottom: 28px;
                right: 28px;
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background: var(--teal-600, #0D9488);
                color: #ffffff;
                border: 1px solid rgba(45, 212, 191, 0.4);
                box-shadow: 0 4px 18px rgba(13, 148, 136, 0.35), 0 2px 6px rgba(0, 0, 0, 0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 1030;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(16px) scale(0.9);
                transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                            transform 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                            visibility 0.25s ease,
                            background 0.2s ease,
                            box-shadow 0.2s ease;
            }
            .legal-scroll-top.visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateY(0) scale(1);
            }
            .legal-scroll-top:hover {
                transform: translateY(-3px) scale(1.05);
                box-shadow: 0 8px 24px rgba(13, 148, 136, 0.5), 0 4px 10px rgba(0, 0, 0, 0.3);
                background: var(--teal-700, #0F766E);
                color: #ffffff;
            }
            .legal-scroll-top:active {
                transform: translateY(0) scale(0.96);
            }
            .legal-scroll-top svg {
                display: block;
                transition: transform 0.2s ease;
            }
            .legal-scroll-top:hover svg {
                transform: translateY(-2px);
            }
            @media (max-width: 768px) {
                .legal-scroll-top {
                    bottom: 20px;
                    right: 20px;
                    width: 40px;
                    height: 40px;
                }
            }
            @media print {
                .sidebar, .topbar, .app-footer, .legal-toc-card, .legal-filter-box, .legal-meta-strip .actions, .btn-wrap, .legal-scroll-top {
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

        {{-- Floating Scroll to Top Button --}}
        <button type="button" class="legal-scroll-top" aria-label="Scroll to top" title="উপরে যান / Back to top">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
        </button>
    </x-core::layout>

@else
    {{-- STANDALONE / PUBLIC GUEST MODE (Matches Landing Page Theme, Header & Footer) --}}
    @php
        $content = $landingContent;
        $siteName = $content['site_title'] ?? $siteTitle;
        $user = auth()->user();
        $plans = \Modules\Shop\Models\Plan::active()->get();
    @endphp
    <!DOCTYPE html>
    <html lang="{{ $isEn ? 'en' : 'bn' }}" data-theme="dark" class="{{ $isEn ? 'lang-en' : '' }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $pageHeading }} · {{ $siteName }}</title>
        <meta name="description" content="{{ $isEn ? $subtitleEn : $subtitle }}">

        <script>
            (function() {
                try {
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

        <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ file_exists(public_path('css/landing.css')) ? filemtime(public_path('css/landing.css')) : time() }}">
        @if (file_exists(public_path('css/landing.css')))
            <style>
                {!! file_get_contents(public_path('css/landing.css')) !!}
            </style>
        @endif
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            html, body {
                background: var(--bg-dark, #061014) !important;
                color: var(--text-main, #F1F5F9);
                font-family: var(--font-bengali, 'Noto Sans Bengali', 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif);
                margin: 0;
                padding: 0;
                scroll-behavior: smooth;
            }

            .pub-hero {
                background: radial-gradient(circle at 50% 20%, rgba(13, 148, 136, 0.22) 0%, var(--bg-dark, #061014) 75%);
                border-bottom: 1px solid var(--border-dark, rgba(255, 255, 255, 0.08));
                padding: 120px 0 40px;
                position: relative;
            }
            .pub-hero-badge {
                display: inline-flex;
                margin-bottom: 14px;
            }
            .pub-hero-title {
                font-size: 32px;
                font-weight: 800;
                color: #ffffff;
                margin: 0 0 12px 0;
                line-height: 1.3;
                letter-spacing: -0.01em;
            }
            .pub-hero-sub {
                font-size: 15.5px;
                color: var(--text-muted, #94A3B8);
                max-width: 780px;
                margin: 0 0 24px 0;
                line-height: 1.65;
            }
            .pub-hero-meta {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 16px;
                padding-top: 18px;
                border-top: 1px solid var(--border-dark, rgba(255, 255, 255, 0.08));
                font-size: 13px;
                color: var(--text-muted, #94A3B8);
            }
            .pub-hero-meta strong {
                color: #F1F5F9;
            }
            .pub-hero .actions .btn-secondary {
                background: rgba(255, 255, 255, 0.08) !important;
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
                color: #FFFFFF !important;
                backdrop-filter: blur(8px);
            }
            .pub-hero .actions .btn-secondary:hover {
                background: rgba(255, 255, 255, 0.14) !important;
                border-color: rgba(20, 184, 166, 0.45) !important;
                color: #FFFFFF !important;
            }

            .pub-body-wrap {
                background: var(--bg-dark, #061014);
                padding: 40px 0 80px;
            }
            .pub-grid {
                display: grid;
                grid-template-columns: 280px minmax(0, 1fr);
                gap: 28px;
                align-items: start;
            }
            .pub-toc-card {
                position: sticky;
                top: 90px;
                background: var(--bg-card, #0E1F26);
                border: 1px solid var(--border-dark, rgba(255, 255, 255, 0.08));
                border-radius: 14px;
                padding: 18px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
                max-height: calc(100vh - 120px);
                overflow-y: auto;
            }
            .pub-toc-title {
                font-size: 13.5px;
                font-weight: 700;
                color: #ffffff;
                margin-bottom: 14px;
                padding-bottom: 8px;
                border-bottom: 1px solid var(--border-dark, rgba(255, 255, 255, 0.08));
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
                color: var(--text-muted, #94A3B8);
                text-decoration: none;
                transition: all 0.15s;
                line-height: 1.35;
            }
            .pub-toc-link:hover {
                background: rgba(255, 255, 255, 0.05);
                color: var(--brand-cyan, #2DD4BF);
            }
            .pub-toc-link.active {
                background: rgba(13, 148, 136, 0.18);
                color: var(--brand-cyan, #2DD4BF);
                font-weight: 700;
                border-left: 2px solid var(--brand-light, #14B8A6);
            }
            .legal-filter-box {
                margin-bottom: 22px;
            }
            .legal-filter-box .form-input,
            .legal-filter-box input {
                background: var(--bg-card, #0E1F26) !important;
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
                color: #ffffff !important;
                border-radius: 10px !important;
            }
            .legal-filter-box .form-input::placeholder,
            .legal-filter-box input::placeholder {
                color: var(--text-dim, #64748B) !important;
            }
            .legal-filter-box .form-input:focus,
            .legal-filter-box input:focus {
                border-color: var(--brand-light, #14B8A6) !important;
                box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.2) !important;
            }
            .legal-filter-box .input-icon {
                color: var(--text-muted, #94A3B8) !important;
            }
            #legalNoResults {
                background: var(--bg-card, #0E1F26) !important;
                border: 1px solid var(--border-dark, rgba(255, 255, 255, 0.08)) !important;
                border-radius: 14px !important;
            }
            #legalNoResults [style*="color:var(--ink-900)"] {
                color: #ffffff !important;
            }
            #legalNoResults [style*="color:var(--ink-500)"] {
                color: var(--text-muted, #94A3B8) !important;
            }
            .legal-section {
                background: var(--bg-card, #0E1F26);
                border: 1px solid var(--border-dark, rgba(255, 255, 255, 0.08));
                border-radius: 14px;
                padding: 26px 28px;
                margin-bottom: 22px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
                scroll-margin-top: 90px;
                transition: border-color 0.2s;
            }
            .legal-section:hover {
                border-color: rgba(20, 184, 166, 0.35);
            }
            .legal-section-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
                padding-bottom: 12px;
                border-bottom: 1px solid var(--border-dark, rgba(255, 255, 255, 0.08));
            }
            .legal-section-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: rgba(13, 148, 136, 0.18);
                color: var(--brand-cyan, #2DD4BF);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 13px;
                flex-shrink: 0;
            }
            .legal-section-title {
                font-size: 16.5px;
                font-weight: 700;
                color: #ffffff;
                margin: 0;
                line-height: 1.35;
            }
            .legal-prose {
                font-size: 13.5px;
                line-height: 1.75;
                color: #CBD5E1;
            }
            .legal-prose p {
                margin: 0 0 12px 0;
            }
            .legal-prose p:last-child {
                margin-bottom: 0;
            }
            .legal-prose strong, .legal-prose b {
                color: #ffffff;
            }
            .legal-prose ul, .legal-prose ol {
                margin: 0 0 12px 0;
                padding-left: 20px;
            }
            .legal-prose li {
                margin-bottom: 6px;
            }
            .legal-callout {
                background: var(--bg-dark-alt, #0A171D);
                border-left: 3.5px solid var(--brand-light, #14B8A6);
                border-radius: 0 8px 8px 0;
                padding: 12px 16px;
                margin: 14px 0;
                font-size: 13px;
                color: #E2E8F0;
                border-top: 1px solid rgba(255, 255, 255, 0.04);
                border-right: 1px solid rgba(255, 255, 255, 0.04);
                border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            }
            .legal-callout strong, .legal-callout b {
                color: var(--brand-cyan, #2DD4BF);
            }

            @media (max-width: 900px) {
                .pub-hero { padding: 100px 0 32px; }
                .pub-grid { grid-template-columns: 1fr; }
                .pub-toc-card { display: none; }
            }
            @media print {
                .lp-header, .mobile-drawer, .drawer-overlay, .lp-footer, .pub-toc-card, .actions, .pub-hero-meta .actions, .legal-filter-box, .lp-scroll-top {
                    display: none !important;
                }
                .pub-hero { padding: 10px 0 !important; border: none !important; background: transparent !important; }
                .pub-body-wrap { padding: 0 !important; }
                .legal-section { border: none !important; box-shadow: none !important; padding: 10px 0 !important; page-break-inside: avoid; }
            }
        </style>
    </head>
    <body>

        {{-- Landing Page Header --}}
        <header class="lp-header" id="lpHeader">
            <div class="lp-container">
                <div class="lp-header-inner">
                    <a href="{{ route('home') }}" class="lp-brand">
                        <div class="lp-brand-icon">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                        </div>
                        <div class="lp-brand-text">
                            <span class="lp-brand-name">{{ $siteName }}</span>
                            <span class="lp-brand-tag">{{ $content['brand_tag'] ?? 'Cloud POS & ERP' }}</span>
                        </div>
                    </a>

                    <nav>
                        <ul class="lp-nav-links">
                            <li><a href="{{ route('home') }}" class="lp-nav-link"><span class="bn">মূল পাতা</span><span class="en">Home</span></a></li>
                            <li><a href="{{ route('home') }}#features" class="lp-nav-link"><span class="bn">ফিচারসমূহ</span><span class="en">Features</span></a></li>
                            <li><a href="{{ route('home') }}#solutions" class="lp-nav-link"><span class="bn">কেন {{ $siteName }}</span><span class="en">Why Us</span></a></li>
                            <li><a href="{{ route('home') }}#simulator" class="lp-nav-link"><span class="bn">লাইভ ডেমো</span><span class="en">Demo Simulator</span></a></li>
                            @if ($plans && $plans->count())
                                <li><a href="{{ route('home') }}#pricing" class="lp-nav-link"><span class="bn">প্রাইসিং</span><span class="en">Pricing</span></a></li>
                            @endif
                            <li><a href="{{ route('privacy-policy') }}" class="lp-nav-link {{ $active === 'privacy' ? 'active' : '' }}" style="{{ $active === 'privacy' ? 'color:var(--brand-cyan); font-weight:700;' : '' }}"><span class="bn">গোপনীয়তা নীতি</span><span class="en">Privacy Policy</span></a></li>
                            <li><a href="{{ route('terms') }}" class="lp-nav-link {{ $active === 'terms' ? 'active' : '' }}" style="{{ $active === 'terms' ? 'color:var(--brand-cyan); font-weight:700;' : '' }}"><span class="bn">শর্তাবলী</span><span class="en">Terms</span></a></li>
                        </ul>
                    </nav>

                    <div class="lp-header-actions">
                        <x-core::lang-switcher size="sm" id="landingLangToggle" />

                        @if ($user)
                            <a href="{{ route('dashboard') }}" class="lp-btn lp-btn-sm lp-btn-primary">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                                <span class="bn">ড্যাশবোর্ড</span>
                                <span class="en">Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="lp-nav-login">
                                <span class="bn">লগ ইন</span>
                                <span class="en">Log In</span>
                            </a>
                            @if ($isRegistrationEnabled)
                                <a href="{{ route('register') }}" class="lp-btn lp-btn-sm lp-btn-primary">
                                    <span class="bn">রেজিস্ট্রেশন করুন</span>
                                    <span class="en">Registration</span>
                                </a>
                            @endif
                        @endif

                        <button type="button" class="lp-burger" id="lpBurger" aria-label="Open menu">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        {{-- Mobile Drawer --}}
        <div class="drawer-overlay" id="drawerOverlay"></div>
        <div class="mobile-drawer" id="mobileDrawer">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
                <span class="lp-brand-name">{{ $siteName }}</span>
                <button type="button" id="drawerCloseBtn" style="color:#fff; font-size:20px;">✕</button>
            </div>
            <ul style="list-style:none; display:flex; flex-direction:column; gap:16px;">
                <li><a href="{{ route('home') }}" class="close-drawer"><span class="bn">মূল পাতা</span><span class="en">Home</span></a></li>
                <li><a href="{{ route('home') }}#features" class="close-drawer"><span class="bn">ফিচারসমূহ</span><span class="en">Features</span></a></li>
                <li><a href="{{ route('home') }}#solutions" class="close-drawer"><span class="bn">কেন {{ $siteName }}</span><span class="en">Why Us</span></a></li>
                <li><a href="{{ route('home') }}#simulator" class="close-drawer"><span class="bn">লাইভ ডেমো</span><span class="en">Demo</span></a></li>
                @if ($plans && $plans->count())
                    <li><a href="{{ route('home') }}#pricing" class="close-drawer"><span class="bn">প্রাইসিং</span><span class="en">Pricing</span></a></li>
                @endif
                <li><a href="{{ route('privacy-policy') }}" class="close-drawer"><span class="bn">গোপনীয়তা নীতি</span><span class="en">Privacy Policy</span></a></li>
                <li><a href="{{ route('terms') }}" class="close-drawer"><span class="bn">শর্তাবলী</span><span class="en">Terms</span></a></li>
            </ul>
            <div style="margin-top:32px; display:flex; flex-direction:column; gap:12px;">
                @if ($user)
                    <a href="{{ route('dashboard') }}" class="lp-btn lp-btn-md lp-btn-primary" style="width:100%;">
                        <span class="bn">ড্যাশবোর্ড</span>
                        <span class="en">Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="lp-btn lp-btn-md lp-btn-secondary" style="width:100%;">
                        <span class="bn">লগ ইন</span>
                        <span class="en">Log In</span>
                    </a>
                    @if ($isRegistrationEnabled)
                        <a href="{{ route('register') }}" class="lp-btn lp-btn-md lp-btn-primary" style="width:100%;">
                            <span class="bn">রেজিস্ট্রেশন করুন</span>
                            <span class="en">Registration</span>
                        </a>
                    @endif
                @endif
                <div style="display:flex; justify-content:center; padding-top:14px; margin-top:4px; border-top:1px solid var(--border-dark);">
                    <x-core::lang-switcher size="sm" id="landingLangToggleMobile" />
                </div>
            </div>
        </div>

        {{-- Hero Header --}}
        <section class="pub-hero">
            <div class="lp-container">
                <div class="pub-hero-badge">
                    <x-core::badge color="teal" size="sm" rounded>
                        <span class="bn">{{ $badge }}</span>
                        <span class="en">{{ $badgeEn }}</span>
                    </x-core::badge>
                </div>

                <h1 class="pub-hero-title">
                    <span class="bn">{{ $title }}</span>
                    <span class="en">{{ $titleEn }}</span>
                </h1>

                <p class="pub-hero-sub">
                    <span class="bn">{{ $subtitle }}</span>
                    <span class="en">{{ $subtitleEn }}</span>
                </p>

                <div class="pub-hero-meta">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span>
                            <span class="bn">সর্বশেষ হালনাগাদ: <strong>{{ $lastUpdated }}</strong></span>
                            <span class="en">Last Updated: <strong>{{ $lastUpdatedEn }}</strong></span>
                        </span>
                        <span>•</span>
                        <span>
                            <span class="bn">সংস্করণ: <strong>{{ $version }}</strong></span>
                            <span class="en">Version: <strong>{{ $version }}</strong></span>
                        </span>
                    </div>

                    <div class="actions" style="display: flex; align-items: center; gap: 8px;">
                        @if ($active === 'privacy')
                            <x-core::button size="sm" variant="secondary" icon="file-text" href="{{ route('terms') }}">
                                <span class="bn">ব্যবহারের শর্তাবলী দেখুন</span>
                                <span class="en">View Terms</span>
                            </x-core::button>
                        @else
                            <x-core::button size="sm" variant="secondary" icon="shield" href="{{ route('privacy-policy') }}">
                                <span class="bn">গোপনীয়তা নীতি দেখুন</span>
                                <span class="en">View Privacy Policy</span>
                            </x-core::button>
                        @endif

                        <x-core::button size="sm" variant="secondary" icon="printer" onclick="window.print()">
                            <span class="bn">প্রিন্ট করুন</span>
                            <span class="en">Print</span>
                        </x-core::button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Document Area --}}
        <section class="pub-body-wrap">
            <div class="lp-container">
                <div class="pub-grid">
                    {{-- Sticky Table of Contents --}}
                    <aside class="pub-toc-card">
                        <div class="pub-toc-title">
                            <x-core::icon name="list" size="14" />
                            <span class="bn">সূচিপত্র</span>
                            <span class="en">Table of Contents</span>
                        </div>
                        <ul class="pub-toc-list">
                            @foreach ($toc as $item)
                                <li>
                                    <a href="#{{ $item['id'] }}" class="pub-toc-link">
                                        <x-core::icon name="{{ $item['icon'] ?? 'file-text' }}" size="13" style="flex-shrink:0;" />
                                        <span class="bn">{{ $item['title_bn'] }}</span>
                                        <span class="en">{{ $item['title_en'] }}</span>
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
                                <span class="en">No matching policy clause found</span>
                            </div>
                            <div style="font-size:12px; color:var(--ink-500); margin-top:4px;">
                                <span class="bn">অন্য কোনো শব্দ দিয়ে পুনরায় অনুসন্ধান করুন।</span>
                                <span class="en">Try searching with different keywords.</span>
                            </div>
                        </div>

                        {{ $slot }}
                    </main>
                </div>
            </div>
        </section>

        {{-- Landing Page Footer --}}
        <footer class="lp-footer">
            <div class="lp-container">
                <div class="lp-footer-top">
                    <div class="lp-footer-brand-col">
                        <a href="{{ route('home') }}" class="lp-brand">
                            <div class="lp-brand-icon">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="3" width="20" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                            </div>
                            <div class="lp-brand-text">
                                <span class="lp-brand-name">{{ $siteName }}</span>
                                <span class="lp-brand-tag">{{ $content['brand_tag'] ?? 'Cloud POS & ERP' }}</span>
                            </div>
                        </a>
                        <p>
                            <span class="bn">{{ $content['footer_about_bn'] ?? 'বাংলাদেশের আধুনিক ব্যবসায়ী ও রিটেইল স্টোরের জন্য সর্বাধিক দ্রুত, নির্ভুল এবং নির্ভরযোগ্য ক্লাউড পিওএস সমাধান।' }}</span>
                            <span class="en">{{ $content['footer_about_en'] ?? "Bangladesh's fastest, most accurate and trusted cloud POS & inventory solution for modern merchants." }}</span>
                        </p>
                        @if (!empty($content['social_whatsapp']) || !empty($content['social_facebook']) || !empty($content['social_youtube']))
                            <div style="display:flex; gap:12px; margin-top:8px;">
                                @if (!empty($content['social_whatsapp']))
                                    <a href="{{ $content['social_whatsapp'] }}" target="_blank" style="color:var(--brand-cyan);" title="WhatsApp">
                                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                    </a>
                                @endif
                                @if (!empty($content['social_facebook']))
                                    <a href="{{ $content['social_facebook'] }}" target="_blank" style="color:var(--brand-cyan);" title="Facebook">
                                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                                    </a>
                                @endif
                                @if (!empty($content['social_youtube']))
                                    <a href="{{ $content['social_youtube'] }}" target="_blank" style="color:var(--brand-cyan);" title="YouTube">
                                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="lp-footer-col">
                        <h4><span class="bn">ফিচারসমূহ</span><span class="en">Features</span></h4>
                        <ul class="lp-footer-links">
                            <li><a href="{{ route('home') }}#features"><span class="bn">কুইক সেল পিওএস</span><span class="en">Quick Sale POS</span></a></li>
                            <li><a href="{{ route('home') }}#features"><span class="bn">ডিজিটাল বাকি খাতা</span><span class="en">Due Ledger</span></a></li>
                            <li><a href="{{ route('home') }}#features"><span class="bn">লাইভ ইনভেন্টরি</span><span class="en">Live Inventory</span></a></li>
                            <li><a href="{{ route('home') }}#features"><span class="bn">ক্যাশবক্স অডিট</span><span class="en">Cashbox Audit</span></a></li>
                            <li><a href="{{ route('home') }}#features"><span class="bn">মাল্টি-আউটলেট</span><span class="en">Multi-Outlet</span></a></li>
                        </ul>
                    </div>

                    <div class="lp-footer-col">
                        <h4><span class="bn">কোম্পানি</span><span class="en">Company</span></h4>
                        <ul class="lp-footer-links">
                            <li><a href="{{ route('home') }}#solutions"><span class="bn">আমাদের সুবিধা</span><span class="en">Why Choose Us</span></a></li>
                            @if ($plans && $plans->count())
                                <li><a href="{{ route('home') }}#pricing"><span class="bn">প্রাইসিং প্ল্যান</span><span class="en">Pricing Plans</span></a></li>
                            @endif
                            <li><a href="{{ route('home') }}#reviews"><span class="bn">রিভিউ ও মতামত</span><span class="en">Reviews</span></a></li>
                            <li><a href="{{ route('home') }}#faq"><span class="bn">সাধারণ জিজ্ঞাসা</span><span class="en">FAQ</span></a></li>
                            <li><a href="{{ route('privacy-policy') }}"><span class="bn">গোপনীয়তা নীতি</span><span class="en">Privacy Policy</span></a></li>
                            <li><a href="{{ route('terms') }}"><span class="bn">ব্যবহারের শর্তাবলী</span><span class="en">Terms & Conditions</span></a></li>
                            <li><a href="{{ route('login') }}"><span class="bn">লগ ইন পোর্টাল</span><span class="en">Login Portal</span></a></li>
                        </ul>
                    </div>

                    <div class="lp-footer-col">
                        <h4><span class="bn">যোগাযোগ</span><span class="en">Contact</span></h4>
                        <div class="lp-footer-contact-item">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--brand-cyan); flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span>{{ $address }}</span>
                        </div>
                        <div class="lp-footer-contact-item">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--brand-cyan); flex-shrink:0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <span>{{ $phone }}</span>
                        </div>
                        <div class="lp-footer-contact-item">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--brand-cyan); flex-shrink:0;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <span>{{ $email }}</span>
                        </div>
                    </div>
                </div>

                <div class="lp-footer-bottom">
                    <span>
                        © {{ date('Y') }} {{ $siteName }}. সর্বস্বত্ব সংরক্ষিত (All rights reserved).
                        &middot; <a href="{{ route('privacy-policy') }}" style="color:var(--text-dim, #94a3b8); text-decoration:none;"><span class="bn">গোপনীয়তা নীতি</span><span class="en">Privacy Policy</span></a>
                        &middot; <a href="{{ route('terms') }}" style="color:var(--text-dim, #94a3b8); text-decoration:none;"><span class="bn">শর্তাবলী</span><span class="en">Terms</span></a>
                    </span>
                    <button type="button" class="lp-back-top" id="backToTopBtn" aria-label="Back to top" title="উপরে যান / Back to top">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
                    </button>
                </div>
            </div>
        </footer>

        {{-- Floating Scroll to Top Button --}}
        <button type="button" class="lp-scroll-top" id="lpScrollTop" aria-label="Scroll to top" title="উপরে যান / Back to top">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
        </button>

    </body>
    </html>
@endif

<script>
    $(function () {
        // Sticky Header for landing header in public mode
        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 30) {
                $('#lpHeader').addClass('scrolled');
            } else {
                $('#lpHeader').removeClass('scrolled');
            }
        });

        // Mobile Drawer
        $('#lpBurger').on('click', function () {
            $('#mobileDrawer').addClass('open');
            $('#drawerOverlay').addClass('open');
        });

        $('#drawerCloseBtn, #drawerOverlay, .close-drawer').on('click', function () {
            $('#mobileDrawer').removeClass('open');
            $('#drawerOverlay').removeClass('open');
        });

        // Language Switcher (Segmented Switcher)
        function setLandingLanguage(lang) {
            const isEn = lang === 'en';
            $('html, body').toggleClass('lang-en', isEn);
            $('#landingLangToggle, #landingLangToggleMobile, .lang-segmented-switcher .segmented-switch-input').prop('checked', isEn);
            $('.lang-segmented-switcher .switch-opt-bn').toggleClass('active', !isEn);
            $('.lang-segmented-switcher .switch-opt-en').toggleClass('active', isEn);

            try {
                localStorage.setItem('lang', lang);
                document.cookie = "lang=" + lang + ";path=/;max-age=31536000;SameSite=Lax";
            } catch (e) {}
        }

        $(document).on('change', '.lang-segmented-switcher .segmented-switch-input', function () {
            setLandingLanguage($(this).is(':checked') ? 'en' : 'bn');
        });

        $(document).on('click', '[data-action="set-lang-bn"], .lang-segmented-switcher .switch-opt-bn', function (e) {
            if ($(e.target).is('input')) return;
            setLandingLanguage('bn');
        });

        $(document).on('click', '[data-action="set-lang-en"], .lang-segmented-switcher .switch-opt-en', function (e) {
            if ($(e.target).is('input')) return;
            setLandingLanguage('en');
        });

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

        // Floating Scroll to Top (Supports both .legal-scroll-top & #lpScrollTop / #backToTopBtn)
        const $scrollTopBtn = $('#lpScrollTop, .legal-scroll-top');
        function checkLegalScroll() {
            if ($(window).scrollTop() > 300) {
                $scrollTopBtn.addClass('visible');
            } else {
                $scrollTopBtn.removeClass('visible');
            }
        }
        $(window).on('scroll', checkLegalScroll);
        checkLegalScroll();

        $(document).on('click', '#lpScrollTop, #backToTopBtn, .legal-scroll-top', function (e) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    });
</script>
