@php
    $cookieLang = request()->cookie('lang', 'bn');
    $isEn = $cookieLang === 'en';
    $content = $content ?? \Modules\Core\Support\LandingPageContent::all();
    $siteName = $content['site_title'] ?? config('app.name', 'MasterPOS');
    $phone = $content['support_phone'] ?? '+880 1886 861430';
    $email = $content['support_email'] ?? 'support@softngear.com';
    $address = $content['office_address'] ?? 'Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi';
    $isLandingEnabled = \Modules\Core\Models\Setting::isLandingPageEnabled();
@endphp
<!DOCTYPE html>
<html lang="{{ $isEn ? 'en' : 'bn' }}" class="{{ $isEn ? 'lang-en' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName }} — {{ $content['hero_title_bn'] ?? 'বাংলাদেশের #১ ক্লাউড POS ও ইনভেন্টরি সফটওয়্যার' }}</title>
    <meta name="description" content="{{ $content['meta_description'] ?? 'খাতা-কলমে হিসাবের দিন শেষ। দোকানের বেচাকেনা, কাস্টমারের বাকি খাতা, লাইভ স্টক, ক্যাশবক্স ও লাভ-ক্ষতির পূর্ণাঙ্গ হিসাব — সবই MasterPOS-এ।' }}">

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
</head>
<body>

@if ($isPreview || (auth()->check() && auth()->user()->isSuperAdmin()))
    <div class="lp-admin-bar">
        <span>
            @if ($isPreview)
                <span class="bn">⚠️ <strong>প্রিভিউ মোড:</strong> ল্যান্ডিং পেজ প্রিভিউ দেখছেন।</span>
                <span class="en">⚠️ <strong>Preview Mode:</strong> You are viewing landing page in preview mode.</span>
            @endif
            <span class="bn">ল্যান্ডিং পেজ বর্তমান স্ট্যাটাস:</span>
            <span class="en">Current Status:</span>
            <strong>{{ $isLandingEnabled ? 'সক্রিয় (Enabled)' : 'নিষ্ক্রিয় (Disabled)' }}</strong>
        </span>
        @if (auth()->check() && auth()->user()->isSuperAdmin())
            <a href="{{ route('system-settings.index') }}">
                <span class="bn">সুপার এডমিন সেটিংস পরিবর্তন করুন →</span>
                <span class="en">Edit in Super Admin Dashboard →</span>
            </a>
        @endif
    </div>
@endif

{{-- Header --}}
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
                    <li><a href="#features" class="lp-nav-link"><span class="bn">ফিচারসমূহ</span><span class="en">Features</span></a></li>
                    <li><a href="#solutions" class="lp-nav-link"><span class="bn">কেন MasterPOS</span><span class="en">Why Us</span></a></li>
                    <li><a href="#simulator" class="lp-nav-link"><span class="bn">লাইভ ডেমো</span><span class="en">Demo Simulator</span></a></li>
                    @if ($plans && $plans->count())
                        <li><a href="#pricing" class="lp-nav-link"><span class="bn">প্রাইসিং</span><span class="en">Pricing</span></a></li>
                    @endif
                    <li><a href="#reviews" class="lp-nav-link"><span class="bn">রিভিউ</span><span class="en">Reviews</span></a></li>
                    <li><a href="#faq" class="lp-nav-link"><span class="bn">সাধারণ জিজ্ঞাসা</span><span class="en">FAQ</span></a></li>
                </ul>
            </nav>

            <div class="lp-header-actions">
                <button type="button" class="lp-lang-btn" id="langToggleBtn" title="Toggle Language">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    <span class="bn">EN</span>
                    <span class="en">বাং</span>
                </button>

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
        <li><a href="#features" class="close-drawer"><span class="bn">ফিচারসমূহ</span><span class="en">Features</span></a></li>
        <li><a href="#solutions" class="close-drawer"><span class="bn">কেন MasterPOS</span><span class="en">Why Us</span></a></li>
        <li><a href="#simulator" class="close-drawer"><span class="bn">লাইভ ডেমো</span><span class="en">Demo</span></a></li>
        @if ($plans && $plans->count())
            <li><a href="#pricing" class="close-drawer"><span class="bn">প্রাইসিং</span><span class="en">Pricing</span></a></li>
        @endif
        <li><a href="#reviews" class="close-drawer"><span class="bn">রিভিউ</span><span class="en">Reviews</span></a></li>
        <li><a href="#faq" class="close-drawer"><span class="bn">সাধারণ জিজ্ঞাসা</span><span class="en">FAQ</span></a></li>
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
    </div>
</div>

{{-- Hero Section --}}
<section class="lp-hero">
    <div class="ambient-glow" style="top:-100px; left:50%; transform:translateX(-50%);"></div>

    <div class="lp-container">
        <div class="lp-hero-grid">
            <div class="lp-hero-copy">
                <div class="lp-badge-glow">
                    <span class="lp-badge-dot"></span>
                    <span class="bn">{{ $content['hero_badge_bn'] ?? '⚡ বাংলাদেশের #১ ক্লাউড POS সফটওয়্যার' }}</span>
                    <span class="en">{{ $content['hero_badge_en'] ?? "⚡ Bangladesh's #1 Cloud POS Software" }}</span>
                </div>

                <h1 class="lp-hero-title">
                    <span class="bn">
                        {{ $content['hero_title_bn'] ?? 'ব্যবসা পরিচালনার স্মার্ট সমাধান' }}
                        <span class="lp-hero-gradient-text">{{ $content['hero_title_gradient_bn'] ?? 'সহজ ও দ্রুততম POS' }}</span>
                    </span>
                    <span class="en">
                        {{ $content['hero_title_en'] ?? 'Smart Solution to Run Your Business' }}
                        <span class="lp-hero-gradient-text">{{ $content['hero_title_gradient_en'] ?? 'Fastest Cloud POS' }}</span>
                    </span>
                </h1>

                <p class="lp-hero-subtitle">
                    <span class="bn">{{ $content['hero_subtitle_bn'] ?? 'খাতা-কলমে হিসাবের ঝামেলা ভুলে যান। সেলস কাউন্টার, বাকির খাতা, ইনভেন্টরি স্টক ও লাভ-ক্ষতির লাইভ হিসাব এখন এক ক্লিকেই।' }}</span>
                    <span class="en">{{ $content['hero_subtitle_en'] ?? 'Say goodbye to paper ledger chaos. Instant sales counter, due ledger, live stock inventory, and profit & loss reports — all in one click.' }}</span>
                </p>

                <div class="lp-hero-actions">
                    <a href="{{ $content['hero_btn_primary_url'] ?? '#simulator' }}" class="lp-btn lp-btn-lg lp-btn-primary">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <span class="bn">{{ $content['hero_btn_primary_text_bn'] ?? 'সরাসরি ব্যবহার দেখুন' }}</span>
                        <span class="en">{{ $content['hero_btn_primary_text_en'] ?? 'Explore Demo' }}</span>
                    </a>

                    @if ($isRegistrationEnabled)
                        <a href="{{ route('register') }}" class="lp-btn lp-btn-lg lp-btn-secondary">
                            <span class="bn">{{ $content['hero_btn_secondary_text_bn'] ?? 'রেজিস্ট্রেশন করুন' }}</span>
                            <span class="en">{{ $content['hero_btn_secondary_text_en'] ?? 'Registration' }}</span>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="lp-btn lp-btn-lg lp-btn-secondary">
                            <span class="bn">লগ ইন</span>
                            <span class="en">Log In</span>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    @endif
                </div>

                <div class="lp-trust-badges">
                    <div class="lp-trust-users">
                        <div class="lp-user-avatars">
                            <span class="lp-user-avatar" style="background:#3b82f6;">র</span>
                            <span class="lp-user-avatar" style="background:#10b981;">স</span>
                            <span class="lp-user-avatar" style="background:#f59e0b;">আ</span>
                            <span class="lp-user-avatar" style="background:#8b5cf6;">ত</span>
                        </div>
                        <span class="lp-trust-text">
                            <strong>{{ $content['hero_active_users'] ?? '৫,০০০+ ব্যবসায়ী যুক্ত' }}</strong>
                        </span>
                    </div>
                    <div style="height:20px; width:1px; background:var(--border-dark);"></div>
                    <span class="lp-trust-text">
                        <span class="bn">{{ $content['hero_trust_text_bn'] ?? 'ক্রেডিট কার্ডের প্রয়োজন নেই • ২ মিনিটে সেটআপ • ২৪/৭ ব্যাকআপ' }}</span>
                        <span class="en">{{ $content['hero_trust_text_en'] ?? 'No Credit Card Needed • 2-Min Setup • 24/7 Cloud Backup' }}</span>
                    </span>
                </div>
            </div>

            {{-- Mockup Visual with Floating Pills --}}
            <div class="lp-hero-visual">
                <div class="lp-mockup-wrapper">
                    <div class="lp-mockup-header">
                        <div class="lp-mockup-dot red"></div>
                        <div class="lp-mockup-dot yellow"></div>
                        <div class="lp-mockup-dot green"></div>
                        <div class="lp-mockup-search">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <span>app.masterpos.com/pos/counter</span>
                        </div>
                    </div>

                    <div class="lp-dash-mock">
                        <div class="lp-dash-sidebar">
                            <div style="height:12px; width:60%; background:rgba(255,255,255,0.15); border-radius:4px; margin-bottom:12px;"></div>
                            <div style="height:8px; width:80%; background:rgba(255,255,255,0.06); border-radius:4px; margin-bottom:8px;"></div>
                            <div style="height:8px; width:70%; background:rgba(255,255,255,0.06); border-radius:4px; margin-bottom:8px;"></div>
                            <div style="height:8px; width:85%; background:rgba(255,255,255,0.06); border-radius:4px;"></div>
                        </div>

                        <div class="lp-dash-body">
                            <div class="lp-dash-stat-row">
                                <div class="lp-dash-stat-card">
                                    <span>দৈনিক বিক্রয় (Daily Sales)</span>
                                    <h4>৳৮৫,৪২০</h4>
                                </div>
                                <div class="lp-dash-stat-card">
                                    <span>বর্তমান স্টক (Current Stock)</span>
                                    <h4>১,৪৫০ টি</h4>
                                </div>
                                <div class="lp-dash-stat-card">
                                    <span>মোট আদায় (Collected)</span>
                                    <h4>৯৮.৫%</h4>
                                </div>
                            </div>

                            <div style="margin-top:14px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:10px; padding:12px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:11px; color:var(--text-dim);">
                                    <span>সর্বশেষ লেনদেন (Live Counter Invoices)</span>
                                    <span style="color:var(--accent-green);">● লাইভ সিঙ্ক</span>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:6px;">
                                    <div style="display:flex; justify-content:space-between; font-size:12px; padding:4px 0; border-bottom:1px solid rgba(255,255,255,0.03);">
                                        <span>#INV-2026-9041 • ক্যাশ বিক্রয়</span>
                                        <strong style="color:#fff;">৳১,৮৫০</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; font-size:12px; padding:4px 0; border-bottom:1px solid rgba(255,255,255,0.03);">
                                        <span>#INV-2026-9040 • বিকাশ পেমেন্ট</span>
                                        <strong style="color:#fff;">৳৩,৪০০</strong>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; font-size:12px; padding:4px 0;">
                                        <span>#INV-2026-9039 • বাকি আদায় SMS</span>
                                        <strong style="color:#fff;">৳৫,০০০</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating KPI Pills --}}
                    <div class="lp-floating-badge badge-top-right">
                        <div class="lp-float-icon" style="background:rgba(16,185,129,0.15); color:var(--accent-green);">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <h6>বিক্রয় সম্পন্ন! (Sale Success)</h6>
                            <span>মাত্র ২.৮ সেকেন্ডে প্রিন্ট</span>
                        </div>
                    </div>

                    <div class="lp-floating-badge badge-bottom-left">
                        <div class="lp-float-icon" style="background:rgba(59,130,246,0.15); color:var(--brand-cyan);">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path></svg>
                        </div>
                        <div>
                            <h6>বাকি কালেকশন SMS</h6>
                            <span>৳২,৫০০ পরিশোধ হয়েছে</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Social Proof / Stats Strip --}}
<section class="lp-proof-strip">
    <div class="lp-container">
        <div class="lp-proof-grid">
            <div class="lp-proof-item">
                <h3>{{ $content['stat_1_number'] ?? '৯৯.৯%' }}</h3>
                <p>
                    <span class="bn">{{ $content['stat_1_label_bn'] ?? 'সিস্টেম আপটাইম গ্যারান্টি' }}</span>
                    <span class="en">{{ $content['stat_1_label_en'] ?? 'System Uptime Guarantee' }}</span>
                </p>
            </div>
            <div class="lp-proof-item">
                <h3>{{ $content['stat_2_number'] ?? '৫০,০০০+' }}</h3>
                <p>
                    <span class="bn">{{ $content['stat_2_label_bn'] ?? 'প্রতিদিনের সফল লেনদেন' }}</span>
                    <span class="en">{{ $content['stat_2_label_en'] ?? 'Daily Successful Invoices' }}</span>
                </p>
            </div>
            <div class="lp-proof-item">
                <h3>{{ $content['stat_3_number'] ?? '৩ সেকেন্ড' }}</h3>
                <p>
                    <span class="bn">{{ $content['stat_3_label_bn'] ?? 'দ্রুততম ক্যাশ মেমো প্রিন্ট' }}</span>
                    <span class="en">{{ $content['stat_3_label_en'] ?? 'Fastest Invoice Print' }}</span>
                </p>
            </div>
            <div class="lp-proof-item">
                <h3>{{ $content['stat_4_number'] ?? '২৪/৭' }}</h3>
                <p>
                    <span class="bn">{{ $content['stat_4_label_bn'] ?? 'গ্রাহক সহায়তা ও ব্যাকআপ' }}</span>
                    <span class="en">{{ $content['stat_4_label_en'] ?? 'Customer Support & Backup' }}</span>
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Problem vs Solution --}}
<section class="lp-vs-section" id="solutions">
    <div class="lp-container">
        <div class="lp-sec-header">
            <span class="lp-sec-badge">
                <span class="bn">{{ $content['vs_badge_bn'] ?? 'তুলনামূলক বিশ্লেষণ' }}</span>
                <span class="en">{{ $content['vs_badge_en'] ?? 'Direct Comparison' }}</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">{{ $content['vs_title_bn'] ?? 'সনাতন পদ্ধতি বনাম MasterPOS' }}</span>
                <span class="en">{{ $content['vs_title_en'] ?? 'Traditional Method vs MasterPOS' }}</span>
            </h2>
            <p class="lp-sec-subtitle">
                <span class="bn">{{ $content['vs_subtitle_bn'] ?? 'কেন শত শত ব্যবসায়ী তাদের খাতা-কলমের হিসাব ছেড়ে ক্লাউড সিস্টেমে স্থানান্তর হচ্ছেন?' }}</span>
                <span class="en">{{ $content['vs_subtitle_en'] ?? 'Why hundreds of smart retail merchants are moving from pen-and-paper to cloud software?' }}</span>
            </p>
        </div>

        <div class="lp-vs-grid">
            <div class="lp-vs-card vs-pain">
                <div class="lp-vs-head">
                    <div class="lp-vs-icon">✕</div>
                    <h4>
                        <span class="bn">সনাতন খাতা-কলমের হিসাব</span>
                        <span class="en">Traditional Paper Ledger</span>
                    </h4>
                </div>
                <ul class="lp-vs-list">
                    @foreach (($content['vs_pain_items'] ?? []) as $pain)
                        <li>
                            <span class="lp-vs-bullet">✕</span>
                            <span>
                                <span class="bn">{{ $pain['bn'] ?? '' }}</span>
                                <span class="en">{{ $pain['en'] ?? '' }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lp-vs-divider">
                <span>VS</span>
            </div>

            <div class="lp-vs-card vs-gain">
                <div class="lp-vs-head">
                    <div class="lp-vs-icon">✓</div>
                    <h4>
                        <span class="bn">MasterPOS স্মার্ট অটোমেশন</span>
                        <span class="en">MasterPOS Smart Automation</span>
                    </h4>
                </div>
                <ul class="lp-vs-list">
                    @foreach (($content['vs_solution_items'] ?? []) as $sol)
                        <li>
                            <span class="lp-vs-bullet">✓</span>
                            <span>
                                <span class="bn">{{ $sol['bn'] ?? '' }}</span>
                                <span class="en">{{ $sol['en'] ?? '' }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Feature Showcase --}}
<section class="lp-feats-section" id="features">
    <div class="lp-container">
        <div class="lp-sec-header">
            <span class="lp-sec-badge">
                <span class="bn">{{ $content['features_badge_bn'] ?? 'শক্তিশালী ফিচারসমূহ' }}</span>
                <span class="en">{{ $content['features_badge_en'] ?? 'Powerful Core Modules' }}</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">{{ $content['features_title_bn'] ?? 'ব্যবসার প্রতিটি ধাপ নিয়ন্ত্রণের পূর্ণাঙ্গ টুলস' }}</span>
                <span class="en">{{ $content['features_title_en'] ?? 'Complete Toolkit to Control Every Aspect of Your Store' }}</span>
            </h2>
            <p class="lp-sec-subtitle">
                <span class="bn">{{ $content['features_subtitle_bn'] ?? 'সহজ ইন্টারফেস, যাতে কম্পিউটার না জানা যেকেউ ৫ মিনিটে শিখতে পারে।' }}</span>
                <span class="en">{{ $content['features_subtitle_en'] ?? 'Intuitive interface that anyone can master in under 5 minutes without prior IT skills.' }}</span>
            </p>
        </div>

        <div class="lp-feats-grid">
            @foreach (($content['features_list'] ?? []) as $feat)
                <div class="lp-feat-card">
                    <div class="lp-feat-top">
                        <div class="lp-feat-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <span class="lp-feat-pill">
                            <span class="bn">{{ $feat['badge_bn'] ?? '' }}</span>
                            <span class="en">{{ $feat['badge_en'] ?? '' }}</span>
                        </span>
                    </div>
                    <h3>
                        <span class="bn">{{ $feat['title_bn'] ?? '' }}</span>
                        <span class="en">{{ $feat['title_en'] ?? '' }}</span>
                    </h3>
                    <p>
                        <span class="bn">{{ $feat['desc_bn'] ?? '' }}</span>
                        <span class="en">{{ $feat['desc_en'] ?? '' }}</span>
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Interactive POS Simulator --}}
<section class="lp-sim-section" id="simulator">
    <div class="lp-container">
        <div class="lp-sec-header">
            <span class="lp-sec-badge">
                <span class="bn">{{ $content['sim_badge_bn'] ?? 'লাইভ ডেমো এক্সপেরিয়েন্স' }}</span>
                <span class="en">{{ $content['sim_badge_en'] ?? 'Interactive Demo' }}</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">{{ $content['sim_title_bn'] ?? 'নিজে টেস্ট করে দেখুন কীভাবে POS কাজ করে' }}</span>
                <span class="en">{{ $content['sim_title_en'] ?? 'Test Drive The POS Counter Yourself' }}</span>
            </h2>
            <p class="lp-sec-subtitle">
                <span class="bn">{{ $content['sim_subtitle_bn'] ?? 'যেকোনো পণ্যে ক্লিক করুন, কার্ট আপডেট হবে এবং মাত্র ৩ সেকেন্ডে মেমো তৈরি হবে।' }}</span>
                <span class="en">{{ $content['sim_subtitle_en'] ?? 'Click any product on the left to add it to cart and complete an invoice in seconds.' }}</span>
            </p>
        </div>

        <div class="lp-sim-container">
            <div class="lp-sim-grid">
                <div class="lp-sim-prods">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                        <span style="font-size:13px; font-weight:600; color:var(--text-muted);"><span class="bn">পণ্য নির্বাচন করুন (ক্লিক করুন)</span><span class="en">Select Products</span></span>
                        <span style="font-size:11.5px; color:var(--brand-cyan);">● বারকোড রেডি</span>
                    </div>

                    <div class="lp-sim-prod-grid">
                        <div class="lp-sim-prod-card" data-name="প্রাণ গুঁড়া দুধ ৫০০ গ্রাম" data-price="420">
                            <h5>প্রাণ গুঁড়া দুধ ৫০০ গ্রাম</h5>
                            <span>৳৪২০</span>
                        </div>
                        <div class="lp-sim-prod-card" data-name="রূপচাঁদা সয়াবিন তেল ৫ লিটার" data-price="890">
                            <h5>রূপচাঁদা সয়াবিন তেল ৫ লিটার</h5>
                            <span>৳৮৯০</span>
                        </div>
                        <div class="lp-sim-prod-card" data-name="মিনিকেট প্রিমিয়াম চাল ২৫ কেজি" data-price="1850">
                            <h5>মিনিকেট প্রিমিয়াম চাল ২৫ কেজি</h5>
                            <span>৳১,৮৫০</span>
                        </div>
                        <div class="lp-sim-prod-card" data-name="নেসক্যাফে ক্লাসিক কফি ৫০ গ্রাম" data-price="320">
                            <h5>নেসক্যাফে ক্লাসিক কফি ৫০ গ্রাম</h5>
                            <span>৳৩২০</span>
                        </div>
                        <div class="lp-sim-prod-card" data-name="সার্ফ এক্সেল ডিটারজেন্ট ১ কেজি" data-price="260">
                            <h5>সার্ফ এক্সেল ডিটারজেন্ট ১ কেজি</h5>
                            <span>৳২৬০</span>
                        </div>
                        <div class="lp-sim-prod-card" data-name="ডোভ শ্যাম্পু ৩৪০ মিলি" data-price="450">
                            <h5>ডোভ শ্যাম্পু ৩৪০ মিলি</h5>
                            <span>৳৪৫০</span>
                        </div>
                    </div>
                </div>

                <div class="lp-sim-cart">
                    <div class="lp-sim-cart-head">
                        <h4>
                            <span class="bn">লাইভ সেলস কার্ট</span>
                            <span class="en">Live Sales Cart</span>
                        </h4>
                        <span style="font-size:12px; color:var(--text-dim);">#INV-DEMO</span>
                    </div>

                    <div class="lp-sim-cart-list" id="simCartList">
                        <div style="text-align:center; color:var(--text-dim); font-size:13px; padding-top:40px;">
                            <span class="bn">বামপাশের পণ্যতে ক্লিক করে কার্টে নিন</span>
                            <span class="en">Click demo products to add to cart</span>
                        </div>
                    </div>

                    <div class="lp-sim-cart-foot">
                        <div class="lp-sim-cart-row" style="font-size:13px; color:var(--text-muted);">
                            <span>সাবটোটাল</span>
                            <span id="simSubtotal">৳০</span>
                        </div>
                        <div class="lp-sim-cart-row total">
                            <span>মোট প্রদেয়</span>
                            <span id="simGrandTotal">৳০</span>
                        </div>
                        <button type="button" class="lp-btn lp-btn-primary lp-btn-md" id="simCompleteBtn" style="width:100%; margin-top:14px;">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span class="bn">বিল তৈরি করুন (Complete Sale)</span>
                            <span class="en">Complete Sale</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Business Verticals --}}
<section class="lp-vert-section">
    <div class="lp-container">
        <div class="lp-sec-header">
            <span class="lp-sec-badge">
                <span class="bn">{{ $content['vert_badge_bn'] ?? 'যেকোনো ধরনের ব্যবসা' }}</span>
                <span class="en">{{ $content['vert_badge_en'] ?? 'Any Industry' }}</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">{{ $content['vert_title_bn'] ?? 'আপনার ব্যবসার জন্য বিশেষভাবে কাস্টমাইজড' }}</span>
                <span class="en">{{ $content['vert_title_en'] ?? 'Tailored Specifically For Your Business Type' }}</span>
            </h2>
            <p class="lp-sec-subtitle">
                <span class="bn">{{ $content['vert_subtitle_bn'] ?? 'মুদি দোকান থেকে ডিপার্টমেন্টাল স্টোর, ফার্মেসি থেকে ফ্যাশন আউটলেট — সবার জন্য উপযোগী।' }}</span>
                <span class="en">{{ $content['vert_subtitle_en'] ?? 'From retail grocers to pharmacies and fashion outlets — ready for your workflow.' }}</span>
            </p>
        </div>

        <div class="lp-vert-grid">
            @foreach (($content['verticals_list'] ?? []) as $vert)
                <div class="lp-vert-card">
                    <div class="lp-vert-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </div>
                    <h3>
                        <span class="bn">{{ $vert['name_bn'] ?? '' }}</span>
                        <span class="en">{{ $vert['name_en'] ?? '' }}</span>
                    </h3>
                    <p>
                        <span class="bn">{{ $vert['desc_bn'] ?? '' }}</span>
                        <span class="en">{{ $vert['desc_en'] ?? '' }}</span>
                    </p>
                    <span class="lp-vert-tag">
                        <span class="bn">{{ $vert['tag_bn'] ?? '' }}</span>
                        <span class="en">{{ $vert['tag_en'] ?? '' }}</span>
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Dynamic Pricing Plans --}}
@if ($plans && $plans->count())
<section class="lp-pricing-section" id="pricing">
    <div class="lp-container">
        <div class="lp-sec-header">
            <span class="lp-sec-badge">
                <span class="bn">{{ $content['pricing_badge_bn'] ?? 'সাশ্রয়ী প্যাকেজ' }}</span>
                <span class="en">{{ $content['pricing_badge_en'] ?? 'Affordable Pricing' }}</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">{{ $content['pricing_title_bn'] ?? 'ব্যবসার আকার অনুযায়ী সেরা প্ল্যান বেছে নিন' }}</span>
                <span class="en">{{ $content['pricing_title_en'] ?? 'Choose The Perfect Plan For Your Store' }}</span>
            </h2>
            <p class="lp-sec-subtitle">
                <span class="bn">{{ $content['pricing_subtitle_bn'] ?? 'কোনো গোপন চার্জ নেই। যেকোনো সময় আপগ্রেড বা বাতিল করার স্বাধীনতা।' }}</span>
                <span class="en">{{ $content['pricing_subtitle_en'] ?? 'No hidden fees. Freedom to upgrade, downgrade, or cancel at any time.' }}</span>
            </p>

            <div class="lp-pricing-toggle">
                <button type="button" class="lp-price-btn active" id="btnMonthly"><span class="bn">মাসিক প্ল্যান</span><span class="en">Monthly</span></button>
                <button type="button" class="lp-price-btn" id="btnYearly">
                    <span class="bn">বাৎসরিক প্ল্যান</span><span class="en">Yearly</span>
                    <span class="lp-price-save">{{ $content['pricing_annual_discount_bn'] ?? '২০% ছাড়' }}</span>
                </button>
            </div>
        </div>

        <div class="lp-pricing-grid">
            @foreach ($plans as $plan)
                @php
                    $isPopular = (bool) ($plan->is_popular ?? false);
                    $monthlyPrice = (float) $plan->price;
                    $yearlyPrice = round($monthlyPrice * 12 * 0.80);
                    $featuresCount = $plan->features ? $plan->features->count() : 0;
                    $initialLimit = 6;
                    $hasMore = $featuresCount > $initialLimit;
                    $moreCount = $hasMore ? $featuresCount - $initialLimit : 0;

                    $planRawName = $plan->name ?? '';
                    if (preg_match('/^(.*?)\s*\((.*?)\)$/u', $planRawName, $pm)) {
                        $planNameBn = trim($pm[1]);
                        $planNameEn = trim($pm[2]);
                    } else {
                        $planNameBn = $planRawName;
                        $planNameEn = $planRawName;
                    }

                    $popularLabelRaw = $plan->popular_label ?? '';
                    if (!empty($popularLabelRaw)) {
                        if (preg_match('/^(.*?)\s*\((.*?)\)$/u', $popularLabelRaw, $plm)) {
                            $popularLabelBn = trim($plm[1]);
                            $popularLabelEn = trim($plm[2]);
                        } else {
                            $popularLabelBn = $popularLabelRaw;
                            $popularLabelEn = $popularLabelRaw;
                        }
                    } else {
                        $popularLabelBn = 'সর্বাধিক জনপ্রিয়';
                        $popularLabelEn = 'Most Popular';
                    }
                @endphp
                <div class="lp-plan-card {{ $isPopular ? 'popular' : '' }}" data-plan-slug="{{ $plan->slug }}">
                    @if ($isPopular)
                        <div class="lp-plan-tag"><span class="bn">{{ $popularLabelBn }}</span><span class="en">{{ $popularLabelEn }}</span></div>
                    @endif

                    <h3 class="lp-plan-name">
                        <span class="bn">{{ $planNameBn }}</span>
                        <span class="en">{{ $planNameEn }}</span>
                    </h3>
                    <p class="lp-plan-desc">{{ $plan->description ?? 'খুচরা ও ছোট দোকানের দ্রুত বেচাকেনার আদর্শ প্যাকেজ।' }}</p>

                    <div class="lp-plan-price">
                        <span class="currency">৳</span>
                        <span class="amount plan-price-display" data-monthly="{{ $monthlyPrice }}" data-yearly="{{ $yearlyPrice }}">{{ number_format($monthlyPrice) }}</span>
                        <span class="period plan-period-display">/ মাস</span>
                    </div>

                    {{-- Plan Quota Limitations --}}
                    <div class="lp-plan-quotas">
                        <div class="lp-quota-item" title="ইউজার লিমিট / User Limit">
                            <span class="lp-quota-icon"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span>
                            <div class="lp-quota-text">
                                <span class="lp-quota-val">{{ $plan->max_users ? \Modules\Core\Support\BanglaNumber::toBn($plan->max_users) : 'আনলিমিটেড' }}</span>
                                <span class="lp-quota-label"><span class="bn">ইউজার</span><span class="en">Users</span></span>
                            </div>
                        </div>
                        <div class="lp-quota-item" title="শাখা লিমিট / Branch Limit">
                            <span class="lp-quota-icon"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path><path d="M10 6h4"></path><path d="M10 10h4"></path><path d="M10 14h4"></path><path d="M10 18h4"></path></svg></span>
                            <div class="lp-quota-text">
                                <span class="lp-quota-val">{{ $plan->max_branches ? \Modules\Core\Support\BanglaNumber::toBn($plan->max_branches) : 'আনলিমিটেড' }}</span>
                                <span class="lp-quota-label"><span class="bn">শাখা</span><span class="en">Branches</span></span>
                            </div>
                        </div>
                        <div class="lp-quota-item" title="গুদাম লিমিট / Warehouse Limit">
                            <span class="lp-quota-icon"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"></path><path d="M6 18h12"></path><path d="M6 14h12"></path></svg></span>
                            <div class="lp-quota-text">
                                <span class="lp-quota-val">{{ $plan->max_warehouses ? \Modules\Core\Support\BanglaNumber::toBn($plan->max_warehouses) : 'আনলিমিটেড' }}</span>
                                <span class="lp-quota-label"><span class="bn">গুদাম</span><span class="en">Warehouses</span></span>
                            </div>
                        </div>
                        <div class="lp-quota-item" title="পণ্য লিমিট / Product Limit">
                            <span class="lp-quota-icon"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg></span>
                            <div class="lp-quota-text">
                                <span class="lp-quota-val">{{ $plan->max_products ? \Modules\Core\Support\BanglaNumber::toBn(number_format($plan->max_products)) : 'আনলিমিটেড' }}</span>
                                <span class="lp-quota-label"><span class="bn">পণ্য</span><span class="en">Products</span></span>
                            </div>
                        </div>
                    </div>

                    <ul class="lp-plan-features">
                        @if ($plan->features && $plan->features->count())
                            @foreach ($plan->features as $feat)
                                @php
                                    $rawName = $feat->name ?? '';
                                    if (preg_match('/^(.*?)\s*\((.*?)\)$/u', $rawName, $m)) {
                                        $featBn = trim($m[1]);
                                        $featEn = trim($m[2]);
                                    } else {
                                        $featBn = $rawName;
                                        $featEn = $rawName;
                                    }
                                @endphp
                                <li class="{{ $loop->index >= $initialLimit ? 'lp-plan-feature-extra is-hidden' : '' }}">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>
                                        <span class="bn">{{ $featBn }}</span>
                                        <span class="en">{{ $featEn }}</span>
                                    </span>
                                </li>
                            @endforeach
                        @else
                            <li>
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>
                                    <span class="bn">আনলিমিটেড প্রোডাক্ট ও ইনভয়েস</span>
                                    <span class="en">Unlimited Products & Invoices</span>
                                </span>
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>
                                    <span class="bn">বাকির খাতা ও অটো SMS অ্যালার্ট</span>
                                    <span class="en">Due Ledger & Automated SMS</span>
                                </span>
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>
                                    <span class="bn">২৪/৭ ডেডিকেটেড ফোন ও চ্যাট সাপোর্ট</span>
                                    <span class="en">24/7 Dedicated Support</span>
                                </span>
                            </li>
                        @endif
                    </ul>

                    @if ($hasMore)
                        <div class="lp-plan-features-footer">
                            <button type="button" class="lp-plan-features-toggle" aria-expanded="false">
                                <span class="toggle-text-more">
                                    <span class="bn">+ আরও {{ \Modules\Core\Support\BanglaNumber::toBn($moreCount) }}টি ফিচার দেখুন</span>
                                    <span class="en">+ Show {{ $moreCount }} more features</span>
                                </span>
                                <span class="toggle-text-less">
                                    <span class="bn">কম ফিচার দেখুন</span>
                                    <span class="en">Show fewer features</span>
                                </span>
                                <svg class="toggle-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($plans->count() > 1)
            @php
                // Extract all unique features across all active plans
                $allPlanFeatures = collect();
                foreach ($plans as $p) {
                    if ($p->features) {
                        foreach ($p->features as $f) {
                            if (!$allPlanFeatures->has($f->slug)) {
                                $allPlanFeatures->put($f->slug, $f->name);
                            }
                        }
                    }
                }
            @endphp

            {{-- Toggle Button for Full Plan Comparison Table --}}
            <div class="lp-compare-toggle-wrap">
                <button type="button" class="lp-compare-toggle-btn" id="btnToggleCompareTable" aria-expanded="false">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 3h5v5"></path><path d="M4 20L21 3"></path><path d="M21 16v5h-5"></path><path d="M15 15l6 6"></path><path d="M4 4l5 5"></path></svg>
                    <span class="btn-compare-text-open">
                        <span class="bn">সকল প্যাকেজের ফিচার ও লিমিট বিস্তারিত তুলনা করুন</span>
                        <span class="en">Compare All Plan Features & Limitations</span>
                    </span>
                    <span class="btn-compare-text-close" style="display:none;">
                        <span class="bn">তুলনা টেবিল বন্ধ করুন</span>
                        <span class="en">Close Comparison Table</span>
                    </span>
                    <svg class="compare-toggle-arrow" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
            </div>

            {{-- Full Comparison Table Container --}}
            <div class="lp-compare-wrapper" id="lpCompareWrapper" style="display:none;">
                <div class="lp-compare-card">
                    <div class="lp-compare-card-head">
                        <div>
                            <h3 class="lp-compare-title">
                                <span class="bn">প্যাকেজ ভিত্তিক বিস্তারিত তুলনা (Side-by-Side Comparison)</span>
                                <span class="en">Side-by-Side Plan Comparison</span>
                            </h3>
                            <p class="lp-compare-sub">
                                <span class="bn">আপনার ব্যবসার প্রয়োজন অনুযায়ী প্রতিটি প্যাকেজের রিসোর্স লিমিট ও অন্তর্ভুক্ত মডিউলগুলো মিলিয়ে দেখুন।</span>
                                <span class="en">Review resource limits and included modules to choose the best fit for your business.</span>
                            </p>
                        </div>
                    </div>

                    <div class="lp-compare-table-responsive">
                        <table class="lp-compare-table">
                            <thead>
                                <tr>
                                    <th class="lp-th-feature">
                                        <span class="bn">ফিচার / লিমিট বিবরণ</span>
                                        <span class="en">Features & Limits</span>
                                    </th>
                                    @foreach ($plans as $p)
                                        @php
                                            $pName = $p->name ?? '';
                                            if (preg_match('/^(.*?)\s*\((.*?)\)$/u', $pName, $matchP)) {
                                                $pBn = trim($matchP[1]);
                                                $pEn = trim($matchP[2]);
                                            } else {
                                                $pBn = $pName;
                                                $pEn = $pName;
                                            }
                                            $mPrice = (float) $p->price;
                                            $yPrice = round($mPrice * 12 * 0.80);
                                        @endphp
                                        <th class="lp-th-plan {{ $p->is_popular ? 'highlight-col' : '' }}">
                                            @if ($p->is_popular)
                                                <span class="lp-compare-tag">
                                                    {{ $p->popular_label ?: 'জনপ্রিয় (Popular)' }}
                                                </span>
                                            @endif
                                            <div class="lp-compare-plan-name">
                                                <span class="bn">{{ $pBn }}</span>
                                                <span class="en">{{ $pEn }}</span>
                                            </div>
                                            <div class="lp-compare-plan-price">
                                                ৳<span class="plan-price-display" data-monthly="{{ $mPrice }}" data-yearly="{{ $yPrice }}">{{ number_format($mPrice) }}</span>
                                                <small class="plan-period-display">/ মাস</small>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Group: Resource Quotas & Limits --}}
                                <tr class="lp-tr-category">
                                    <td colspan="{{ $plans->count() + 1 }}">
                                        <span class="bn">রিসোর্স কোটা ও সীমাবদ্ধতা</span>
                                        <span class="en">Resource Quotas and Limits</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="lp-td-label">
                                        <div class="lp-feat-label-wrap">
                                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                            <span class="bn">সর্বোচ্চ ব্যবহারকারী (Users)</span>
                                            <span class="en">Max Users</span>
                                        </div>
                                    </td>
                                    @foreach ($plans as $p)
                                        <td class="lp-td-val {{ $p->is_popular ? 'highlight-col' : '' }}">
                                            @if ($p->max_users)
                                                <span class="lp-badge-limit">{{ \Modules\Core\Support\BanglaNumber::toBn($p->max_users) }} জন</span>
                                            @else
                                                <span class="lp-badge-unlimited"><span class="bn">আনলিমিটেড</span><span class="en">Unlimited</span></span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="lp-td-label">
                                        <div class="lp-feat-label-wrap">
                                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path><path d="M10 6h4"></path><path d="M10 10h4"></path><path d="M10 14h4"></path><path d="M10 18h4"></path></svg>
                                            <span class="bn">সর্বোচ্চ আউটলেট / শাখা (Branches)</span>
                                            <span class="en">Max Branches</span>
                                        </div>
                                    </td>
                                    @foreach ($plans as $p)
                                        <td class="lp-td-val {{ $p->is_popular ? 'highlight-col' : '' }}">
                                            @if ($p->max_branches)
                                                <span class="lp-badge-limit">{{ \Modules\Core\Support\BanglaNumber::toBn($p->max_branches) }} টি</span>
                                            @else
                                                <span class="lp-badge-unlimited"><span class="bn">আনলিমিটেড</span><span class="en">Unlimited</span></span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="lp-td-label">
                                        <div class="lp-feat-label-wrap">
                                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"></path><path d="M6 18h12"></path><path d="M6 14h12"></path></svg>
                                            <span class="bn">সর্বোচ্চ গুদাম (Warehouses)</span>
                                            <span class="en">Max Warehouses</span>
                                        </div>
                                    </td>
                                    @foreach ($plans as $p)
                                        <td class="lp-td-val {{ $p->is_popular ? 'highlight-col' : '' }}">
                                            @if ($p->max_warehouses)
                                                <span class="lp-badge-limit">{{ \Modules\Core\Support\BanglaNumber::toBn($p->max_warehouses) }} টি</span>
                                            @else
                                                <span class="lp-badge-unlimited"><span class="bn">আনলিমিটেড</span><span class="en">Unlimited</span></span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="lp-td-label">
                                        <div class="lp-feat-label-wrap">
                                            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
                                            <span class="bn">সর্বোচ্চ পণ্য সংখ্যা (Products)</span>
                                            <span class="en">Max Products</span>
                                        </div>
                                    </td>
                                    @foreach ($plans as $p)
                                        <td class="lp-td-val {{ $p->is_popular ? 'highlight-col' : '' }}">
                                            @if ($p->max_products)
                                                <span class="lp-badge-limit">{{ \Modules\Core\Support\BanglaNumber::toBn(number_format($p->max_products)) }} টি</span>
                                            @else
                                                <span class="lp-badge-unlimited"><span class="bn">আনলিমিটেড</span><span class="en">Unlimited</span></span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- Group: System Features & Modules --}}
                                @if ($allPlanFeatures->isNotEmpty())
                                    <tr class="lp-tr-category">
                                        <td colspan="{{ $plans->count() + 1 }}">
                                            <span class="bn">সিস্টেম ফিচার ও মডিউলসমূহ</span>
                                            <span class="en">System Features and Modules</span>
                                        </td>
                                    </tr>
                                    @foreach ($allPlanFeatures as $fSlug => $fName)
                                        @php
                                            if (preg_match('/^(.*?)\s*\((.*?)\)$/u', $fName, $mFeat)) {
                                                $fBn = trim($mFeat[1]);
                                                $fEn = trim($mFeat[2]);
                                            } else {
                                                $fBn = $fName;
                                                $fEn = $fName;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="lp-td-label">
                                                <div class="lp-feat-label-wrap">
                                                    <span class="bn">{{ $fBn }}</span>
                                                    <span class="en">{{ $fEn }}</span>
                                                </div>
                                            </td>
                                            @foreach ($plans as $p)
                                                @php
                                                    $hasFeature = $p->features && $p->features->contains('slug', $fSlug);
                                                @endphp
                                                <td class="lp-td-val {{ $p->is_popular ? 'highlight-col' : '' }}">
                                                    @if ($hasFeature)
                                                        <span class="lp-feat-yes" title="অন্তর্ভুক্ত / Included">
                                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.6"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                        </span>
                                                    @else
                                                        <span class="lp-feat-no" title="অন্তর্ভুক্ত নেই / Not Included">
                                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                        </span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endif

{{-- Customer Reviews --}}
<section class="lp-reviews-section" id="reviews">
    <div class="lp-container">
        <div class="lp-sec-header">
            <span class="lp-sec-badge">
                <span class="bn">{{ $content['reviews_badge_bn'] ?? 'গ্রাহক সন্তুষ্টি' }}</span>
                <span class="en">{{ $content['reviews_badge_en'] ?? 'Client Testimonials' }}</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">{{ $content['reviews_title_bn'] ?? 'সফল ব্যবসায়ীদের বাস্তব অভিজ্ঞতা' }}</span>
                <span class="en">{{ $content['reviews_title_en'] ?? 'Loved By Retail Shop Owners Across Bangladesh' }}</span>
            </h2>
            <p class="lp-sec-subtitle">
                <span class="bn">{{ $content['reviews_subtitle_bn'] ?? 'দেখুন কীভাবে MasterPOS তাদের দোকানের পরিচালন খরচ কমিয়েছে ও মুনাফা বাড়িয়েছে।' }}</span>
                <span class="en">{{ $content['reviews_subtitle_en'] ?? 'See how MasterPOS reduced operational errors and maximized profit for our clients.' }}</span>
            </p>
        </div>

        <div class="lp-reviews-grid">
            @foreach (($content['reviews_list'] ?? []) as $rev)
                <div class="lp-review-card">
                    <div class="lp-review-stars">
                        @for ($s = 0; $s < ($rev['rating'] ?? 5); $s++)
                            ★
                        @endfor
                    </div>
                    <p class="lp-review-quote">
                        <span class="bn">“{{ $rev['quote_bn'] ?? '' }}”</span>
                        <span class="en">“{{ $rev['quote_en'] ?? '' }}”</span>
                    </p>
                    <div class="lp-review-author">
                        <div class="lp-review-avatar">
                            {{ mb_substr($rev['author'] ?? 'ম', 0, 1) }}
                        </div>
                        <div class="lp-review-meta">
                            <h5>{{ $rev['author'] ?? '' }}</h5>
                            <span>{{ $rev['shop'] ?? '' }} • {{ $rev['city'] ?? '' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ Section --}}
<section class="lp-faq-section" id="faq">
    <div class="lp-container">
        <div class="lp-sec-header">
            <span class="lp-sec-badge">
                <span class="bn">{{ $content['faq_badge_bn'] ?? 'সাধারণ জিজ্ঞাসা' }}</span>
                <span class="en">{{ $content['faq_badge_en'] ?? 'Got Questions?' }}</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">{{ $content['faq_title_bn'] ?? 'প্রায়শই জিজ্ঞাসিত প্রশ্নাবলি' }}</span>
                <span class="en">{{ $content['faq_title_en'] ?? 'Frequently Asked Questions' }}</span>
            </h2>
            <p class="lp-sec-subtitle">
                <span class="bn">{{ $content['faq_subtitle_bn'] ?? 'আপনার মনে থাকা যেকোনো প্রশ্নের উত্তর এখানে পেয়ে যাবেন।' }}</span>
                <span class="en">{{ $content['faq_subtitle_en'] ?? 'Find answers to commonly asked questions about our software and setup.' }}</span>
            </p>
        </div>

        <div class="lp-faq-wrap">
            @foreach (($content['faqs_list'] ?? []) as $i => $faq)
                <div class="lp-faq-item {{ $i === 0 ? 'active' : '' }}">
                    <div class="lp-faq-question">
                        <span>
                            <span class="bn">{{ $faq['question_bn'] ?? '' }}</span>
                            <span class="en">{{ $faq['question_en'] ?? '' }}</span>
                        </span>
                        <svg class="lp-faq-chevron" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="lp-faq-answer" style="{{ $i === 0 ? 'display:block;' : '' }}">
                        <span class="bn">{{ $faq['answer_bn'] ?? '' }}</span>
                        <span class="en">{{ $faq['answer_en'] ?? '' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Final Conversion CTA --}}
<section class="lp-final-cta">
    <div class="lp-container">
        <div class="lp-final-box">
            <h2>
                <span class="bn">{{ $content['cta_title_bn'] ?? 'আজই আপনার দোকানের হিসাব ডিজিটাল করুন' }}</span>
                <span class="en">{{ $content['cta_title_en'] ?? 'Modernize Your Store Operations Today' }}</span>
            </h2>
            <p>
                <span class="bn">{{ $content['cta_subtitle_bn'] ?? 'মাত্র ২ মিনিটে অ্যাকাউন্ট খুলে শুরু করুন আপনার নতুন দোকান ও ফ্রি প্যাকেজ। কোনো ক্রেডিট কার্ড বা অগ্রিম পেমেন্টের প্রয়োজন নেই।' }}</span>
                <span class="en">{{ $content['cta_subtitle_en'] ?? 'Get started in 2 minutes with our free package. No credit card or upfront deposit required.' }}</span>
            </p>
            <div style="display:flex; gap:16px; flex-wrap:wrap; justify-content:center;">
                @if ($isRegistrationEnabled)
                    <a href="{{ route('register') }}" class="lp-btn lp-btn-lg lp-btn-primary">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span class="bn">{{ $content['cta_btn_text_bn'] ?? 'রেজিস্ট্রেশন করুন' }}</span>
                        <span class="en">{{ $content['cta_btn_text_en'] ?? 'Registration' }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="lp-btn lp-btn-lg lp-btn-primary">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span class="bn">লগইন করুন</span>
                        <span class="en">Login</span>
                    </a>
                @endif
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="lp-btn lp-btn-lg lp-btn-secondary">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <span>{{ $content['cta_phone_btn_text'] ?? $phone }}</span>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Footer --}}
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
                    <li><a href="#features"><span class="bn">কুইক সেল পিওএস</span><span class="en">Quick Sale POS</span></a></li>
                    <li><a href="#features"><span class="bn">ডিজিটাল বাকি খাতা</span><span class="en">Due Ledger</span></a></li>
                    <li><a href="#features"><span class="bn">লাইভ ইনভেন্টরি</span><span class="en">Live Inventory</span></a></li>
                    <li><a href="#features"><span class="bn">ক্যাশবক্স অডিট</span><span class="en">Cashbox Audit</span></a></li>
                    <li><a href="#features"><span class="bn">মাল্টি-আউটলেট</span><span class="en">Multi-Outlet</span></a></li>
                </ul>
            </div>

            <div class="lp-footer-col">
                <h4><span class="bn">কোম্পানি</span><span class="en">Company</span></h4>
                <ul class="lp-footer-links">
                    <li><a href="#solutions"><span class="bn">আমাদের সুবিধা</span><span class="en">Why Choose Us</span></a></li>
                    @if ($plans && $plans->count())
                        <li><a href="#pricing"><span class="bn">প্রাইসিং প্ল্যান</span><span class="en">Pricing Plans</span></a></li>
                    @endif
                    <li><a href="#reviews"><span class="bn">রিভিউ ও মতামত</span><span class="en">Reviews</span></a></li>
                    <li><a href="#faq"><span class="bn">সাধারণ জিজ্ঞাসা</span><span class="en">FAQ</span></a></li>
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
            <span>© {{ date('Y') }} {{ $siteName }}. সর্বস্বত্ব সংরক্ষিত (All rights reserved).</span>
            <button type="button" class="lp-back-top" id="backToTopBtn" aria-label="Back to top">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg>
            </button>
        </div>
    </div>
</footer>

{{-- Scripts --}}
<script>
$(function () {
    // 1. Sticky Header
    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 30) {
            $('#lpHeader').addClass('scrolled');
        } else {
            $('#lpHeader').removeClass('scrolled');
        }
    });

    // 2. Mobile Drawer
    $('#lpBurger').on('click', function () {
        $('#mobileDrawer').addClass('open');
        $('#drawerOverlay').addClass('open');
    });

    $('#drawerCloseBtn, #drawerOverlay, .close-drawer').on('click', function () {
        $('#mobileDrawer').removeClass('open');
        $('#drawerOverlay').removeClass('open');
    });

    // 3. Language Switcher (Cookie + localStorage)
    $('#langToggleBtn').on('click', function () {
        const isCurrentlyEn = $('html').hasClass('lang-en');
        const newLang = isCurrentlyEn ? 'bn' : 'en';

        $('html, body').toggleClass('lang-en', newLang === 'en');

        try {
            localStorage.setItem('lang', newLang);
            document.cookie = "lang=" + newLang + ";path=/;max-age=31536000;SameSite=Lax";
        } catch (e) {}
    });

    // 4. Smooth Anchor Scroll
    $('a[href^="#"]').on('click', function (e) {
        const targetId = $(this).attr('href');
        if (targetId && targetId !== '#') {
            const targetEl = $(targetId);
            if (targetEl.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: targetEl.offset().top - 80
                }, 400);
            }
        }
    });

    // 5. Back to Top Button
    $('#backToTopBtn').on('click', function () {
        $('html, body').animate({ scrollTop: 0 }, 400);
    });

    // 6. Pricing Toggle (Monthly vs Yearly)
    $('#btnMonthly').on('click', function () {
        $('#btnMonthly').addClass('active');
        $('#btnYearly').removeClass('active');
        $('.plan-price-display').each(function () {
            const monthlyVal = $(this).data('monthly');
            $(this).text(Number(monthlyVal).toLocaleString('en-US'));
        });
        $('.plan-period-display').text('/ মাস');
    });

    $('#btnYearly').on('click', function () {
        $('#btnYearly').addClass('active');
        $('#btnMonthly').removeClass('active');
        $('.plan-price-display').each(function () {
            const yearlyVal = $(this).data('yearly');
            $(this).text(Number(yearlyVal).toLocaleString('en-US'));
        });
        $('.plan-period-display').text('/ বছর ({{ $content["pricing_annual_discount_bn"] ?? "২০% ছাড়" }})');
    });

    // 7. FAQ Accordion
    $('.lp-faq-question').on('click', function () {
        const parent = $(this).closest('.lp-faq-item');
        const answer = parent.find('.lp-faq-answer');
        const isOpen = parent.hasClass('active');

        $('.lp-faq-item').removeClass('active').find('.lp-faq-answer').slideUp(200);

        if (!isOpen) {
            parent.addClass('active');
            answer.slideDown(200);
        }
    });

    // 8. Interactive POS Simulator Logic
    let cart = [];

    $('.lp-sim-prod-card').on('click', function () {
        const name = $(this).data('name');
        const price = parseFloat($(this).data('price'));

        const existing = cart.find(item => item.name === name);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({ name: name, price: price, qty: 1 });
        }

        renderSimCart();
    });

    function renderSimCart() {
        const list = $('#simCartList');
        if (cart.length === 0) {
            list.html('<div style="text-align:center; color:var(--text-dim); font-size:13px; padding-top:40px;">বামপাশের পণ্যতে ক্লিক করে কার্টে নিন</div>');
            $('#simSubtotal').text('৳০');
            $('#simGrandTotal').text('৳০');
            return;
        }

        let html = '';
        let subtotal = 0;

        cart.forEach((item, index) => {
            const rowTotal = item.price * item.qty;
            subtotal += rowTotal;
            html += `
                <div class="lp-sim-cart-row">
                    <span>${item.name} × ${item.qty}</span>
                    <span style="color:#fff; font-weight:600;">৳${rowTotal.toLocaleString()}</span>
                </div>
            `;
        });

        list.html(html);
        $('#simSubtotal').text('৳' + subtotal.toLocaleString());
        $('#simGrandTotal').text('৳' + subtotal.toLocaleString());
    }

    $('#simCompleteBtn').on('click', function () {
        if (cart.length === 0) {
            alert('অনুগ্রহ করে প্রথমে বামপাশের পণ্যতে ক্লিক করে কার্টে যোগ করুন!');
            return;
        }

        const total = $('#simGrandTotal').text();
        alert('🎉 বিক্রয় সফল হয়েছে! মোট সংগৃহীত: ' + total + '\n\nMasterPOS-এ এভাবে মাত্র ৩ সেকেন্ডে প্রতিটি বিক্রয় সম্পন্ন করা যায়।');
        cart = [];
        renderSimCart();
    });

    // 9. Pricing Plan Features Show More / Show Less Toggle
    $(document).on('click', '.lp-plan-features-toggle', function () {
        const $btn = $(this);
        const $card = $btn.closest('.lp-plan-card');
        const $extraItems = $card.find('.lp-plan-features li.lp-plan-feature-extra');
        const isExpanded = $btn.hasClass('expanded');

        if (isExpanded) {
            $extraItems.slideUp(200, function () {
                $(this).addClass('is-hidden').removeAttr('style');
            });
            $btn.removeClass('expanded').attr('aria-expanded', 'false');
        } else {
            $extraItems.removeClass('is-hidden').hide().slideDown({
                duration: 200,
                start: function () {
                    $(this).css('display', 'flex');
                },
                complete: function () {
                    $(this).css('display', 'flex');
                }
            });
            $btn.addClass('expanded').attr('aria-expanded', 'true');
        }
    });

    // 10. Plan Full Comparison Table Toggle
    $(document).on('click', '#btnToggleCompareTable', function () {
        const $btn = $(this);
        const $wrapper = $('#lpCompareWrapper');
        const isVisible = $wrapper.is(':visible');

        if (isVisible) {
            $wrapper.slideUp(250);
            $btn.removeClass('active').attr('aria-expanded', 'false');
        } else {
            $wrapper.slideDown(300, function () {
                $('html, body').animate({
                    scrollTop: $wrapper.offset().top - 80
                }, 300);
            });
            $btn.addClass('active').attr('aria-expanded', 'true');
        }
    });
});
</script>

</body>
</html>
