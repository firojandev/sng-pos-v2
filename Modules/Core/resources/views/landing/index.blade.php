
@php
    $cookieLang = request()->cookie('lang', 'bn');
    $isEn = $cookieLang === 'en';
    $siteName = \Modules\Core\Models\Setting::get('site_title', config('app.name', 'MasterPOS'));
    $phone = \Modules\Core\Models\Setting::get('support_phone', '+880 1886 861430');
    $email = \Modules\Core\Models\Setting::get('support_email', 'support@softngear.com');
    $address = \Modules\Core\Models\Setting::get('office_address', 'Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi');
    $isLandingEnabled = \Modules\Core\Models\Setting::isLandingPageEnabled();
@endphp
<!DOCTYPE html>
<html lang="{{ $isEn ? 'en' : 'bn' }}" class="{{ $isEn ? 'lang-en' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName }} — বাংলাদেশের #১ ক্লাউড POS ও ইনভেন্টরি সফটওয়্যার</title>
    <meta name="description" content="খাতা-কলমে হিসাবের দিন শেষ। দোকানের বেচাকেনা, কাস্টমারের বাকি খাতা, লাইভ স্টক, ক্যাশবক্স ও লাভ-ক্ষতির পূর্ণাঙ্গ হিসাব — সবই MasterPOS-এ।">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.maateen.me/solaiman-lipi/font.css">

    <link rel="stylesheet" href="/css/landing.css">
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
                <span class="bn">সিস্টেম সেটিংসে পরিবর্তন করুন →</span>
                <span class="en">Change in System Settings →</span>
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
                    <span class="lp-brand-name">Master<span>POS</span></span>
                    <span class="lp-brand-tag">Cloud POS & ERP</span>
                </div>
            </a>

            <nav>
                <ul class="lp-nav-links">
                    <li><a href="#features" class="lp-nav-link"><span class="bn">ফিচারসমূহ</span><span class="en">Features</span></a></li>
                    <li><a href="#solutions" class="lp-nav-link"><span class="bn">কেন MasterPOS</span><span class="en">Why Us</span></a></li>
                    <li><a href="#simulator" class="lp-nav-link"><span class="bn">লাইভ ডেমো</span><span class="en">Demo Simulator</span></a></li>
                    <li><a href="#pricing" class="lp-nav-link"><span class="bn">প্রাইসিং</span><span class="en">Pricing</span></a></li>
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
                    <a href="{{ route('login') }}" class="lp-btn lp-btn-sm lp-btn-primary">
                        <span class="bn">ফ্রি ট্রায়াল</span>
                        <span class="en">Free Trial</span>
                    </a>
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
<div class="lp-drawer-overlay" id="drawerOverlay"></div>
<div class="lp-mobile-drawer" id="mobileDrawer">
    <div style="display:flex; align-items:center; justify-content:space-between;">
        <span class="lp-brand-name">Master<span>POS</span></span>
        <button type="button" id="drawerCloseBtn" style="color:var(--text-muted); font-size:24px;">&times;</button>
    </div>
    <div style="display:flex; flex-direction:column; gap:14px; margin-top:10px;">
        <a href="#features" class="lp-nav-link close-drawer"><span class="bn">ফিচারসমূহ</span><span class="en">Features</span></a>
        <a href="#solutions" class="lp-nav-link close-drawer"><span class="bn">কেন MasterPOS</span><span class="en">Why Us</span></a>
        <a href="#simulator" class="lp-nav-link close-drawer"><span class="bn">লাইভ ডেমো</span><span class="en">Demo Simulator</span></a>
        <a href="#pricing" class="lp-nav-link close-drawer"><span class="bn">প্রাইসিং</span><span class="en">Pricing</span></a>
        <a href="#reviews" class="lp-nav-link close-drawer"><span class="bn">রিভিউ</span><span class="en">Reviews</span></a>
        <a href="#faq" class="lp-nav-link close-drawer"><span class="bn">সাধারণ জিজ্ঞাসা</span><span class="en">FAQ</span></a>
    </div>
    <div style="margin-top:auto; display:flex; flex-direction:column; gap:12px;">
        @if ($user)
            <a href="{{ route('dashboard') }}" class="lp-btn lp-btn-md lp-btn-primary">
                <span class="bn">ড্যাশবোর্ডে প্রবেশ করুন</span>
                <span class="en">Go to Dashboard</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="lp-btn lp-btn-md lp-btn-secondary">
                <span class="bn">লগ ইন</span>
                <span class="en">Log In</span>
            </a>
            <a href="{{ route('login') }}" class="lp-btn lp-btn-md lp-btn-primary">
                <span class="bn">৭ দিনের ফ্রি ট্রায়াল</span>
                <span class="en">Start Free Trial</span>
            </a>
        @endif
    </div>
</div>
{{-- Hero Section --}}
<section class="lp-hero">
    <div class="ambient-glow" style="top:-100px; left:50%; transform:translateX(-50%);"></div>
    <div class="lp-container">
        <div class="lp-hero-grid">
            <div class="lp-hero-copy">
                <div class="lp-eyebrow">
                    <span class="lp-pulse-dot"></span>
                    <span class="bn">বাংলাদেশের #১ আধুনিক ও দ্রুততম POS সফটওয়্যার</span>
                    <span class="en">Bangladesh's #1 Modern & Fastest POS Software</span>
                </div>

                <h1 class="lp-hero-title">
                    <span class="bn">খাতা-কলমে হিসাবের দিন শেষ — <span class="hl">ব্যবসা চালান এক ক্লিকে</span></span>
                    <span class="en">Ditch Pen & Paper — <span class="hl">Run Your Store in 1 Click</span></span>
                </h1>

                <p class="lp-hero-sub">
                    <span class="bn">দোকানের বেচাকেনা, কাস্টমারের বাকি খাতা, লাইভ স্টক, ক্যাশবক্স ও লাভ-ক্ষতির পূর্ণাঙ্গ হিসাব — সবই হাতের মুঠোয়। ৭ দিন ফ্রি ট্রায়াল, কোনো বাধ্যবাধকতা নেই।</span>
                    <span class="en">Superfast billing, customer due ledger, live inventory, multi-counter cashbox, and real-time profit & loss — all in one unified cloud system.</span>
                </p>

                <div class="lp-hero-actions">
                    <a href="{{ route('login') }}" class="lp-btn lp-btn-lg lp-btn-primary">
                        <span class="bn">ফ্রি ট্রায়াল শুরু করুন — ৭ দিন ফ্রি</span>
                        <span class="en">Start Free Trial — 7 Days Free</span>
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>

                    <a href="#simulator" class="lp-btn lp-btn-lg lp-btn-secondary">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" stroke="none">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <span class="bn">লাইভ ডেমো দেখুন</span>
                        <span class="en">Try Interactive Demo</span>
                    </a>
                </div>

                <div class="lp-trust-badges">
                    <div class="lp-trust-badge">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span class="bn">কোনো সেটআপ ফি নেই</span>
                        <span class="en">No Setup Fees</span>
                    </div>
                    <div class="lp-trust-badge">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span class="bn">বারকোড ও থার্মাল প্রিন্ট</span>
                        <span class="en">Barcode & Thermal Ready</span>
                    </div>
                    <div class="lp-trust-badge">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span class="bn">বিকাশ/নগদ/কার্ড সাপোর্ট</span>
                        <span class="en">bKash/Nagad/Card Support</span>
                    </div>
                </div>
            </div>

            <div class="lp-hero-visual">
                {{-- Floating Badges --}}
                <div class="lp-floating-badge fb-1">
                    <div class="lp-fb-icon" style="background:var(--accent-green);">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div>
                        <div class="lp-fb-title"><span class="bn">নতুন বিক্রয় সম্পন্ন</span><span class="en">New Sale Completed</span></div>
                        <div class="lp-fb-sub"><span class="bn">ইনভয়েস #১০৮৪ — ৳৩,২৫০ (ক্যাশ)</span><span class="en">Invoice #1084 — ৳3,250 (Cash)</span></div>
                    </div>
                </div>

                <div class="lp-floating-badge fb-2">
                    <div class="lp-fb-icon" style="background:var(--brand-primary);">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div>
                        <div class="lp-fb-title"><span class="bn">বাকি আদায় হয়েছে</span><span class="en">Due Payment Received</span></div>
                        <div class="lp-fb-sub"><span class="bn">রফিকুল ইসলাম — ৳৫,০০০ (বিকাশ)</span><span class="en">Rafiqul Islam — ৳5,000 (bKash)</span></div>
                    </div>
                </div>

                <div class="lp-floating-badge fb-3">
                    <div class="lp-fb-icon" style="background:var(--accent-amber);">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    </div>
                    <div>
                        <div class="lp-fb-title"><span class="bn">লো-স্টক সতর্কতা</span><span class="en">Low Stock Alert</span></div>
                        <div class="lp-fb-sub"><span class="bn">প্যারাসিটামল ৫০০ — বাকি ৫টি</span><span class="en">Paracetamol 500mg — 5 left</span></div>
                    </div>
                </div>

                {{-- Window Frame --}}
                <div class="lp-window-frame">
                    <div class="lp-window-bar">
                        <div class="lp-window-dots">
                            <i style="background:#FF5F57;"></i>
                            <i style="background:#FEBC2E;"></i>
                            <i style="background:#28C840;"></i>
                        </div>
                        <div class="lp-window-url">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            <span>app.masterpos.com/pos</span>
                        </div>
                    </div>

                    <div class="lp-dash-mock">
                        <div class="lp-dash-sidebar">
                            <div class="icon-slot active"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg></div>
                            <div class="icon-slot"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v10"></path></svg></div>
                            <div class="icon-slot"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg></div>
                            <div class="icon-slot"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg></div>
                            <div class="icon-slot"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg></div>
                        </div>

                        <div class="lp-dash-content">
                            <div class="lp-dash-topbar">
                                <div class="lp-dash-title">
                                    <span class="bn">আজকের বিক্রয় ও সারাংশ</span>
                                    <span class="en">Today's Sales Summary</span>
                                    <small>Outlet: ধানমন্ডি শাখা (Main Counter)</small>
                                </div>
                                <span style="font-size:11px; background:rgba(16,185,129,0.15); color:var(--accent-green); padding:3px 8px; border-radius:12px; font-weight:600;">● Live POS</span>
                            </div>

                            <div class="lp-kpis">
                                <div class="lp-kpi-card">
                                    <div class="lp-kpi-label"><span class="lp-kpi-dot" style="background:#3B82F6;"></span>আজকের আয়</div>
                                    <div class="lp-kpi-val">৳১,২৪,৮০০</div>
                                    <div class="lp-kpi-chg">▲ ১৮.২% বৃদ্ধি</div>
                                </div>
                                <div class="lp-kpi-card">
                                    <div class="lp-kpi-label"><span class="lp-kpi-dot" style="background:#10B981;"></span>মোট অর্ডার</div>
                                    <div class="lp-kpi-val">৩৪২ টি</div>
                                    <div class="lp-kpi-chg">▲ ৯.৪% বৃদ্ধি</div>
                                </div>
                                <div class="lp-kpi-card">
                                    <div class="lp-kpi-label"><span class="lp-kpi-dot" style="background:#F59E0B;"></span>বাকি আদায়</div>
                                    <div class="lp-kpi-val">৳১৮,৫০০</div>
                                    <div class="lp-kpi-chg">৳০ বকেয়া</div>
                                </div>
                            </div>

                            <div class="lp-dash-body">
                                <div class="lp-chart-panel">
                                    <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text-dim);">
                                        <span>সাপ্তাহিক বিক্রয় ট্রেন্ড</span>
                                        <span style="color:var(--accent-green); font-weight:700;">+২৪% এই সপ্তাহে</span>
                                    </div>
                                    <div class="lp-chart-bars">
                                        <div class="lp-chart-bar" style="height:45%;"></div>
                                        <div class="lp-chart-bar" style="height:62%;"></div>
                                        <div class="lp-chart-bar" style="height:55%;"></div>
                                        <div class="lp-chart-bar" style="height:78%;"></div>
                                        <div class="lp-chart-bar" style="height:70%;"></div>
                                        <div class="lp-chart-bar" style="height:92%;"></div>
                                        <div class="lp-chart-bar active" style="height:100%;"></div>
                                    </div>
                                </div>

                                <div class="lp-quick-cart">
                                    <div style="font-size:11px; font-weight:700; color:#fff;">চলতি কার্ট</div>
                                    <div>
                                        <div class="lp-quick-item"><span>মিনিকেট চাল</span><span>৳১,৮৫০</span></div>
                                        <div class="lp-quick-item"><span>সয়াবিন ৫L</span><span>৳৮৬০</span></div>
                                        <div class="lp-quick-item"><span>চিনি ১kg</span><span>৳১৩০</span></div>
                                    </div>
                                    <div style="font-size:12px; font-weight:800; color:var(--accent-green); text-align:right;">মোট: ৳২,৮৪০</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Social Proof Strip --}}
<section class="lp-proof-strip">
    <div class="lp-container">
        <div class="lp-proof-grid">
            <div class="lp-proof-item">
                <div class="lp-proof-val">২,৫০০+</div>
                <div class="lp-proof-label"><span class="bn">সক্রিয় দোকান ও প্রতিষ্ঠান</span><span class="en">Active Merchants</span></div>
            </div>
            <div class="lp-proof-item">
                <div class="lp-proof-val">৳৫০M+</div>
                <div class="lp-proof-label"><span class="bn">প্রতি মাসে বিক্রয় হিসাব</span><span class="en">Monthly Sales Processed</span></div>
            </div>
            <div class="lp-proof-item">
                <div class="lp-proof-val">৬৪ জেলায়</div>
                <div class="lp-proof-label"><span class="bn">সারাদেশে বিশ্বস্ত সেবা</span><span class="en">Across All 64 Districts</span></div>
            </div>
            <div class="lp-proof-item">
                <div class="lp-proof-val">৯৯.৯%</div>
                <div class="lp-proof-label"><span class="bn">নিরবচ্ছিন্ন ক্লাউড আপটাইম</span><span class="en">Cloud System Uptime</span></div>
            </div>
        </div>
    </div>
</section>
{{-- Pain vs Solution Section (Bitcommerz Highlight) --}}
<section class="lp-section lp-section-dark" id="solutions">
    <div class="lp-container">
        <div class="lp-sec-head">
            <span class="lp-kicker">
                <span class="lp-pulse-dot"></span>
                <span class="bn">এই সমস্যাগুলো কি চেনা লাগছে?</span>
                <span class="en">Sound Familiar to Your Store?</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">খাতা-কলমে ব্যবসা হয় না — ঝামেলা বাড়ে</span>
                <span class="en">Manual Notebooks Kill Growth — Automate Today</span>
            </h2>
            <p class="lp-sec-sub">
                <span class="bn">ভুল হিসাব, হারিয়ে যাওয়া বাকি খাতা আর ক্যাশের গড়মিল আপনার প্রতিদিনের লাভ কমিয়ে দিচ্ছে। MasterPOS আপনার ব্যবসাকে এক ধাক্কায় স্মার্ট করে তোলে।</span>
                <span class="en">Calculation mistakes, misplaced due registers, and inventory shrinkage eat into your profits. MasterPOS eliminates cash leaks from day one.</span>
            </p>
        </div>

        <div class="lp-vs-grid">
            <div class="lp-vs-col pain">
                <h3>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <span class="bn">সনাতন খাতা-কলম ও সাধারণ পদ্ধতি</span>
                    <span class="en">Old Manual Paper Records</span>
                </h3>
                <div class="lp-vs-item">
                    <span class="lp-vs-item-ic"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span>
                    <span><span class="bn">বাকি খাতার পৃষ্ঠা ছিঁড়ে যায়, কার কাছে কত বাকি তা খুঁজতে দিন পার হয়</span><span class="en">Torn ledger pages, manual search takes forever when customers ask for due</span></span>
                </div>
                <div class="lp-vs-item">
                    <span class="lp-vs-item-ic"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span>
                    <span><span class="bn">দিনশেষে ক্যাশবক্স আর বিক্রয়ের হিসাব মিলাতে ঘণ্টার পর ঘণ্টা সময় নষ্ট</span><span class="en">Hours wasted every evening matching cash drawer with handwritten tallies</span></span>
                </div>
                <div class="lp-vs-item">
                    <span class="lp-vs-item-ic"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span>
                    <span><span class="bn">দোকানে কোন পণ্য কতটি আছে জানা থাকে না, কাস্টমার ফিরে যায়</span><span class="en">Zero live stock visibility leads to stockouts and losing repeat buyers</span></span>
                </div>
                <div class="lp-vs-item">
                    <span class="lp-vs-item-ic"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span>
                    <span><span class="bn">দোকানের বাইরে থাকলে কর্মচারীরা কী বিক্রি করছে জানার উপায় থাকে না</span><span class="en">No remote visibility on staff sales, discounts, or inventory movements</span></span>
                </div>
            </div>

            <div class="lp-vs-divider">
                <div class="lp-vs-arrow">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </div>
            </div>

            <div class="lp-vs-col sol">
                <h3>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span class="bn">MasterPOS আধুনিক ক্লাউড সফটওয়্যার</span>
                    <span class="en">Modern MasterPOS Cloud POS</span>
                </h3>
                <div class="lp-vs-item">
                    <span class="lp-vs-item-ic"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    <span><span class="bn">কাস্টমারের মোবাইল নম্বর চাপলেই ১ সেকেন্ডে পুরো বকেয়া হিস্ট্রি ও স্টেটমেন্ট</span><span class="en">Type phone number and see complete due history and instant ledger statement</span></span>
                </div>
                <div class="lp-vs-item">
                    <span class="lp-vs-item-ic"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    <span><span class="bn">অটোমেটিক ক্যাশবক্স ট্র্যাকিং — ক্যাশ-ইন, ক্যাশ-আউট ও ড্রয়ার ব্যালেন্স নিখুঁত</span><span class="en">Automated drawer audit — register opening, float, cash-in/out synced perfectly</span></span>
                </div>
                <div class="lp-vs-item">
                    <span class="lp-vs-item-ic"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    <span><span class="bn">রিয়েল-টাইম লাইভ ইনভেন্টরি ও লো-স্টক অ্যালার্ট — পণ্য শেষ হওয়ার আগেই নোটিফিকেশন</span><span class="en">Real-time stock alerts — restock before runouts happen automatically</span></span>
                </div>
                <div class="lp-vs-item">
                    <span class="lp-vs-item-ic"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    <span><span class="bn">মোবাইল, ল্যাপটপ বা ট্যাবলেট থেকেই যেকোনো জায়গা থেকে লাইভ ব্যবসা মনিটর</span><span class="en">Monitor sales, profit and stock from your smartphone anytime, anywhere</span></span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features Showcase --}}
<section class="lp-section" id="features">
    <div class="lp-container">
        <div class="lp-sec-head">
            <span class="lp-kicker">
                <span class="lp-pulse-dot"></span>
                <span class="bn">শক্তিশালী ফিচারসমূহ</span>
                <span class="en">Powerful Built-in Features</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">একটি সফটওয়্যারেই আপনার পুরো ব্যবসা</span>
                <span class="en">Everything You Need In One Single Suite</span>
            </h2>
            <p class="lp-sec-sub">
                <span class="bn">আলাদা কোনো সফটওয়্যার বা অ্যাপ কেনার প্রয়োজন নেই। বেচাকেনা থেকে শুরু করে মাল্টি-ব্রাঞ্চ পর্যন্ত সব এক প্ল্যাটফর্মে।</span>
                <span class="en">No separate add-on apps needed. From instant billing to multi-branch warehouses, it is completely unified.</span>
            </p>
        </div>

        <div class="lp-feats-grid">
            {{-- Feature 1 --}}
            <div class="lp-feat-card">
                <div class="lp-feat-head">
                    <div class="lp-feat-ic" style="background:linear-gradient(135deg,#2563EB,#0EA5E9);">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <span class="lp-feat-num">০১</span>
                </div>
                <h3 class="lp-feat-title"><span class="bn">কুইক সেল ও দ্রুততম POS</span><span class="en">Superfast Quick Sale POS</span></h3>
                <p class="lp-feat-desc">
                    <span class="bn">বারকোড স্ক্যানার, কিবোর্ড শর্টকাট বা টাচ স্ক্রিন দিয়ে মাত্র ৩ সেকেন্ডে ইনভয়েস তৈরি ও থার্মাল প্রিন্ট।</span>
                    <span class="en">Generate invoices in 3 seconds with barcode scanner, touchscreen or hotkeys. Print on 58mm/80mm thermal.</span>
                </p>
                <ul class="lp-feat-bullets">
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>বারকোড ও দ্রুত পণ্য সার্চ</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>ক্যাশ, বিকাশ, নগদ ও কার্ড পেমেন্ট</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>থার্মাল রসিদ ও ইনভয়েস প্রিন্ট</li>
                </ul>
            </div>

            {{-- Feature 2 --}}
            <div class="lp-feat-card">
                <div class="lp-feat-head">
                    <div class="lp-feat-ic" style="background:linear-gradient(135deg,#10B981,#059669);">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <span class="lp-feat-num">০২</span>
                </div>
                <h3 class="lp-feat-title"><span class="bn">ডিজিটাল বাকি খাতা ও কাস্টমার লেজার</span><span class="en">Customer Due Ledger</span></h3>
                <p class="lp-feat-desc">
                    <span class="bn">কোন গ্রাহকের কাছে কত টাকা বকেয়া আছে তার তারিখভিত্তিক হিসাব। বকেয়া আদায় এবং ব্যালেন্স স্টেটমেন্ট।</span>
                    <span class="en">Track receivables per customer, payment dates, partial settlements, and full statement history.</span>
                </p>
                <ul class="lp-feat-bullets">
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>গ্রাহকভিত্তিক পূর্ণাঙ্গ লেজার</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>বকেয়া পেমেন্ট মডাল ও রসিদ</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>সাপ্লায়ার দেনা-পাওনা ট্র্যাকিং</li>
                </ul>
            </div>

            {{-- Feature 3 --}}
            <div class="lp-feat-card">
                <div class="lp-feat-head">
                    <div class="lp-feat-ic" style="background:linear-gradient(135deg,#8B5CF6,#6D28D9);">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </div>
                    <span class="lp-feat-num">০৩</span>
                </div>
                <h3 class="lp-feat-title"><span class="bn">রিয়েল-টাইম স্টক ও ইনভেন্টরি</span><span class="en">Live Stock & Inventory</span></h3>
                <p class="lp-feat-desc">
                    <span class="bn">প্রতিটি পণ্য বিক্রির সাথে সাথে স্টক অটোমেটিক সমন্বয় হয়। ব্যাচ, মেয়াদোত্তীর্ণের তারিখ ও লো-স্টক সতর্কতা।</span>
                    <span class="en">Automated stock decrement upon sales. Support for batches, expiry tracking, and low-stock triggers.</span>
                </p>
                <ul class="lp-feat-bullets">
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>অটোমেটিক স্টক সমন্বয়</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>লো-স্টক ও এক্সপায়ারি অ্যালার্ট</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>ক্রয় রসিদ ও সাপ্লায়ার বিল</li>
                </ul>
            </div>

            {{-- Feature 4 --}}
            <div class="lp-feat-card">
                <div class="lp-feat-head">
                    <div class="lp-feat-ic" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                    </div>
                    <span class="lp-feat-num">০৪</span>
                </div>
                <h3 class="lp-feat-title"><span class="bn">ক্যাশবক্স ও দৈনিক ব্যালেন্স শিট</span><span class="en">Cashbox & Daily Balance</span></h3>
                <p class="lp-feat-desc">
                    <span class="bn">দোকানের ড্রয়ারে কত টাকা থাকার কথা, কত জমা ও খরচ হলো তার নিখুঁত দৈনিক অডিট শিট ও কাউন্টার ক্লোজিং।</span>
                    <span class="en">Full register drawer management: opening float, cash-in/out, bank transfer, and end-of-day closing.</span>
                </p>
                <ul class="lp-feat-bullets">
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>কাউন্টার ও ড্রয়ার ট্র্যাকিং</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>দৈনিক ক্যাশ-ইন ও ক্যাশ-আউট</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>ব্যাংক ও বিকাশ ফান্ড ট্রান্সফার</li>
                </ul>
            </div>

            {{-- Feature 5 --}}
            <div class="lp-feat-card">
                <div class="lp-feat-head">
                    <div class="lp-feat-ic" style="background:linear-gradient(135deg,#EC4899,#BE185D);">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    </div>
                    <span class="lp-feat-num">০৫</span>
                </div>
                <h3 class="lp-feat-title"><span class="bn">লাভ-ক্ষতি ও সঠিক ব্যবসায়িক রিপোর্ট</span><span class="en">Profit & Loss Reporting</span></h3>
                <p class="lp-feat-desc">
                    <span class="bn">প্রতিটি পণ্য ও ইনভয়েসে কত লাভ হলো, মাসিক খরচ কত এবং নিখুঁত লাভ-ক্ষতির বিশ্লেষণ এক নজরে দেখুন।</span>
                    <span class="en">Know exact margins per item, operating expenses, daily/monthly revenue, and tax/VAT calculations.</span>
                </p>
                <ul class="lp-feat-bullets">
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>আইটেমভিত্তিক লাভ ট্র্যাকিং</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>দৈনিক ও মাসিক সেলস রিপোর্ট</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>এক্সেল ও পিডিএফ ডাউনলোড</li>
                </ul>
            </div>

            {{-- Feature 6 --}}
            <div class="lp-feat-card">
                <div class="lp-feat-head">
                    <div class="lp-feat-ic" style="background:linear-gradient(135deg,#06B6D4,#0891B2);">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    </div>
                    <span class="lp-feat-num">০৬</span>
                </div>
                <h3 class="lp-feat-title"><span class="bn">মাল্টি-শাখা ও কেন্দ্রীয় গুদাম</span><span class="en">Multi-Branch & Warehouses</span></h3>
                <p class="lp-feat-desc">
                    <span class="bn">একাধিক শাখা এবং গুদামের জন্য আলাদা স্টক ট্র্যাকিং ও শাখা থেকে শাখায় পণ্য স্থানান্তরের সহজ সুবিধা।</span>
                    <span class="en">Manage multiple shop outlets, central warehouses, and transfer stock seamlessly across branches.</span>
                </p>
                <ul class="lp-feat-bullets">
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>একাধিক শপ ও আউটলেট সাপোর্ট</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>গুদাম থেকে শাখা স্টক ট্রান্সফার</li>
                    <li><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>শাখাভিত্তিক বিক্রয় ও রাজস্ব হিসাব</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Interactive Live POS Simulator --}}
<section class="lp-section lp-section-dark" id="simulator">
    <div class="lp-container">
        <div class="lp-sec-head">
            <span class="lp-kicker">
                <span class="lp-pulse-dot"></span>
                <span class="bn">নিজে একবার পরখ করে দেখুন</span>
                <span class="en">Interactive POS Simulator</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">কতটা দ্রুত ও সহজ? নিজে টেস্ট করুন!</span>
                <span class="en">Experience How Fast & Smooth It Feels</span>
            </h2>
            <p class="lp-sec-sub">
                <span class="bn">নিচের পণ্যগুলোতে ক্লিক করে কার্টে যোগ করুন এবং দেখুন কত সহজেই কুইক সেল সম্পন্ন হয়।</span>
                <span class="en">Click on demo products below to add them into the cart and see the billing calculation live!</span>
            </p>
        </div>

        <div class="lp-sim-wrapper">
            <div class="lp-sim-grid">
                {{-- Product Grid --}}
                <div>
                    <div style="font-size:14px; font-weight:700; color:#fff; margin-bottom:14px; display:flex; justify-content:space-between;">
                        <span><span class="bn">ডেমো ক্যাটালগ (ক্লিক করুন)</span><span class="en">Demo Products (Click to Add)</span></span>
                        <span style="font-size:12px; color:var(--brand-cyan);">পণ্য যোগ করতে ক্লিক করুন</span>
                    </div>

                    <div class="lp-sim-products">
                        <div class="lp-sim-prod-card" data-name="মিনিকেট চাল ২৫ কেজি" data-price="1850">
                            <div class="lp-sim-prod-info">
                                <h5>মিনিকেট চাল ২৫ কেজি</h5>
                                <span>৳১,৮৫০</span>
                            </div>
                            <span class="lp-sim-add-btn">+</span>
                        </div>

                        <div class="lp-sim-prod-card" data-name="রূপচাঁদা সয়াবিন ৫ লিটার" data-price="860">
                            <div class="lp-sim-prod-info">
                                <h5>রূপচাঁদা সয়াবিন ৫ লিটার</h5>
                                <span>৳৮৬০</span>
                            </div>
                            <span class="lp-sim-add-btn">+</span>
                        </div>

                        <div class="lp-sim-prod-card" data-name="সাদা চিনি ১ কেজি" data-price="130">
                            <div class="lp-sim-prod-info">
                                <h5>সাদা চিনি ১ কেজি</h5>
                                <span>৳১৩০</span>
                            </div>
                            <span class="lp-sim-add-btn">+</span>
                        </div>

                        <div class="lp-sim-prod-card" data-name="ডেটল অ্যান্টিসেপ্টিক ১০০ গ্রাম" data-price="65">
                            <div class="lp-sim-prod-info">
                                <h5>ডেটল সাবান ১০০ গ্রাম</h5>
                                <span>৳৬৫</span>
                            </div>
                            <span class="lp-sim-add-btn">+</span>
                        </div>
                    </div>
                </div>

                {{-- Live Receipt / Cart Drawer --}}
                <div class="lp-sim-receipt">
                    <div>
                        <div class="lp-sim-receipt-head">
                            <h4 style="color:#fff; font-size:16px; margin:0;">MasterPOS রসিদ</h4>
                            <p style="font-size:11px; color:var(--text-dim); margin:2px 0 0;">লাইভ বিক্রয়ের পূর্বরূপ</p>
                        </div>

                        <div class="lp-sim-cart-list" id="simCartList">
                            <div style="text-align:center; color:var(--text-dim); font-size:13px; padding-top:40px;">
                                <span class="bn">বামপাশের পণ্যতে ক্লিক করে কার্টে নিন</span>
                                <span class="en">Click left products to add to cart</span>
                            </div>
                        </div>
                    </div>

                    <div class="lp-sim-totals">
                        <div class="lp-sim-total-row" style="font-size:13px; font-weight:500; color:var(--text-muted);">
                            <span>সাবটোটাল:</span>
                            <span id="simSubtotal">৳০</span>
                        </div>
                        <div class="lp-sim-total-row" style="font-size:13px; font-weight:500; color:var(--text-muted);">
                            <span>ভ্যাট (০%):</span>
                            <span>৳০</span>
                        </div>
                        <div class="lp-sim-total-row" style="margin-top:6px; border-top:1px solid rgba(255,255,255,0.1); padding-top:8px;">
                            <span>সর্বমোট প্রদেয়:</span>
                            <span id="simGrandTotal" style="color:var(--accent-green); font-size:18px;">৳০</span>
                        </div>

                        <button type="button" class="lp-btn lp-btn-sm lp-btn-primary" id="simCompleteBtn" style="margin-top:12px; width:100%;">
                            <span class="bn">বিক্রয় সম্পন্ন ও প্রিন্ট করুন</span>
                            <span class="en">Complete Sale & Print</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
{{-- Industries / Verticals --}}
<section class="lp-section">
    <div class="lp-container">
        <div class="lp-sec-head">
            <span class="lp-kicker">
                <span class="lp-pulse-dot"></span>
                <span class="bn">ব্যবসার ধরণ</span>
                <span class="en">Business Verticals</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">যেকোনো রিটেইল ও হোলসেল ব্যবসার জন্য আদর্শ</span>
                <span class="en">Tailored for Every Retail & Wholesale Business</span>
            </h2>
            <p class="lp-sec-sub">
                <span class="bn">দোকানের আকার যেমনই হোক, MasterPOS আপনার ব্যবসার ধরন অনুযায়ী সম্পূর্ণ মানিয়ে নেয়।</span>
                <span class="en">Whether you run a single counter or a multi-branch chain, MasterPOS adapts to your needs.</span>
            </p>
        </div>

        <div class="lp-vert-grid">
            <div class="lp-vert-card">
                <div class="lp-vert-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg></div>
                <div>
                    <h4><span class="bn">গ্রোসারি ও সুপারশপ</span><span class="en">Grocery & Super Shop</span></h4>
                    <p><span class="bn">দ্রুত বারকোড স্ক্যানিং ও ওজন স্কেল সাপোর্ট</span><span class="en">High-speed barcode scanning & weight scale</span></p>
                </div>
            </div>

            <div class="lp-vert-card">
                <div class="lp-vert-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <div>
                    <h4><span class="bn">ফার্মেসি ও হেলথকেয়ার</span><span class="en">Pharmacy & Healthcare</span></h4>
                    <p><span class="bn">ওষুধের ব্যাচ ও মেয়াদোত্তীর্ণের নিখুঁত হিসাব</span><span class="en">Batch, rack, and drug expiry alerts</span></p>
                </div>
            </div>

            <div class="lp-vert-card">
                <div class="lp-vert-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.38 3.46L16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"></path></svg></div>
                <div>
                    <h4><span class="bn">পোশাক ও ফ্যাশন শপ</span><span class="en">Fashion & Boutique</span></h4>
                    <p><span class="bn">সাইজ, কালার ভ্যারিয়েন্ট ও ব্র্যান্ড ট্র্যাকিং</span><span class="en">Size, color variant & brand management</span></p>
                </div>
            </div>

            <div class="lp-vert-card">
                <div class="lp-vert-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg></div>
                <div>
                    <h4><span class="bn">ইলেকট্রনিক্স ও গ্যাজেট</span><span class="en">Electronics & Gadgets</span></h4>
                    <p><span class="bn">সিরিয়াল নম্বর ও ওয়ারেন্টি ইনভয়েস ট্র্যাকিং</span><span class="en">Serial number, IMEI & warranty tracking</span></p>
                </div>
            </div>

            <div class="lp-vert-card">
                <div class="lp-vert-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg></div>
                <div>
                    <h4><span class="bn">হার্ডওয়্যার ও স্যানিটারি</span><span class="en">Hardware & Sanitary</span></h4>
                    <p><span class="bn">বড় ক্যাটালগ ও ইউনিটভিত্তিক বিক্রয় হিসাব</span><span class="en">Large parts catalog & unit conversions</span></p>
                </div>
            </div>

            <div class="lp-vert-card">
                <div class="lp-vert-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg></div>
                <div>
                    <h4><span class="bn">পাইকারি ও ডিলারশিপ</span><span class="en">Wholesale & Distribution</span></h4>
                    <p><span class="bn">পাইকারি রেট, বকেয়া চালান ও পার্টনার লেজার</span><span class="en">Wholesale tiers, bulk delivery & credits</span></p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Pricing Section --}}
<section class="lp-section lp-section-dark" id="pricing">
    <div class="lp-container">
        <div class="lp-sec-head">
            <span class="lp-kicker">
                <span class="lp-pulse-dot"></span>
                <span class="bn">স্বচ্ছ প্যাকেজ ও মূল্য</span>
                <span class="en">Transparent Pricing</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">সাশ্রয়ী মূল্যে সেরা ক্লাউড POS সফটওয়্যার</span>
                <span class="en">Simple, Predictable Plans for Every Merchant</span>
            </h2>
            <p class="lp-sec-sub">
                <span class="bn">কোনো লুকানো চার্জ নেই। সব প্ল্যানেই ৭ দিন ফ্রি ট্রায়াল উপভোগ করুন।</span>
                <span class="en">No hidden setup fees. Enjoy 7-day full feature free trial on all plans.</span>
            </p>

            <div class="lp-pricing-toggle">
                <button type="button" class="lp-price-toggle-btn active" id="btnMonthly">
                    <span class="bn">মাসিক প্ল্যান</span>
                    <span class="en">Monthly</span>
                </button>
                <button type="button" class="lp-price-toggle-btn" id="btnYearly">
                    <span class="bn">বার্ষিক প্ল্যান</span>
                    <span class="en">Yearly</span>
                    <span class="lp-save-badge"><span class="bn">২০% সাশ্রয়</span><span class="en">Save 20%</span></span>
                </button>
            </div>
        </div>

        <div class="lp-pricing-grid">
            @forelse ($plans->take(3) as $plan)
                @php
                    $isFeatured = $plan->slug === 'standard' || $loop->iteration === 2;
                    $monthlyPrice = (float) $plan->price;
                    $yearlyPrice = round($monthlyPrice * 12 * 0.80);
                @endphp
                <div class="lp-price-card {{ $isFeatured ? 'featured' : '' }}">
                    @if ($isFeatured)
                        <span class="lp-popular-badge"><span class="bn">সবচেয়ে জনপ্রিয়</span><span class="en">Most Popular</span></span>
                    @endif

                    <div>
                        <h3 class="lp-price-name">{{ $plan->name }}</h3>
                        <p class="lp-price-desc">{{ $plan->description ?: 'ছোট ও মাঝারি ব্যবসার সম্পূর্ণ পরিচালনা সমাধান।' }}</p>

                        <div class="lp-price-amount">
                            <span class="currency">৳</span>
                            <span class="val plan-price-display" data-monthly="{{ (int)$monthlyPrice }}" data-yearly="{{ (int)$yearlyPrice }}">
                                {{ number_format($monthlyPrice, 0) }}
                            </span>
                            <span class="period plan-period-display">/ মাস</span>
                        </div>

                        <ul class="lp-price-features">
                            <li class="lp-price-feature-item">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>{{ $plan->max_users ? \Modules\Core\Support\BanglaNumber::toBn($plan->max_users) . ' জন ইউজার অ্যাক্সেস' : 'সীমাহীন ইউজার অ্যাক্সেস' }}</span>
                            </li>
                            <li class="lp-price-feature-item">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>{{ $plan->max_branches ? \Modules\Core\Support\BanglaNumber::toBn($plan->max_branches) . ' টি শাখা / আউটলেট' : 'একাধিক শাখা সাপোর্ট' }}</span>
                            </li>
                            <li class="lp-price-feature-item">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>{{ $plan->max_products ? \Modules\Core\Support\BanglaNumber::toBn($plan->max_products) . ' টি পণ্য যোগের সুযোগ' : 'সীমাহীন পণ্য ও বারকোড' }}</span>
                            </li>
                            <li class="lp-price-feature-item">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>কুইক সেল ও থার্মাল প্রিন্ট</span>
                            </li>
                            <li class="lp-price-feature-item">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>বাকি খাতা ও ক্যাশবক্স ব্যালেন্স</span>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('login') }}" class="lp-btn lp-btn-md {{ $isFeatured ? 'lp-btn-primary' : 'lp-btn-outline' }}" style="width:100%;">
                        <span class="bn">৭ দিন ফ্রি ট্রায়াল শুরু করুন</span>
                        <span class="en">Start 7-Day Free Trial</span>
                    </a>
                </div>
            @empty
                <div class="lp-price-card">
                    <div>
                        <h3 class="lp-price-name">বেসিক (Starter)</h3>
                        <p class="lp-price-desc">ছোট দোকান ও নতুন ব্যবসার জন্য উপযুক্ত।</p>
                        <div class="lp-price-amount"><span class="currency">৳</span><span class="val">৯৯৯</span><span class="period">/ মাস</span></div>
                        <ul class="lp-price-features">
                            <li class="lp-price-feature-item">✓ ২ জন ইউজার অ্যাক্সেস</li>
                            <li class="lp-price-feature-item">✓ ১০০টি পণ্য তালিকাভুক্তি</li>
                            <li class="lp-price-feature-item">✓ কুইক সেল ও থার্মাল প্রিন্ট</li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="lp-btn lp-btn-md lp-btn-outline">৭ দিন ফ্রি ট্রায়াল</a>
                </div>
            @endforelse
        </div>

        {{-- Enterprise Custom Banner --}}
        <div class="lp-enterprise-banner">
            <div>
                <h4 style="color:#fff; font-size:18px; font-weight:700; margin:0 0 4px;">
                    <span class="bn">বড় প্রতিষ্ঠান বা কাস্টম চাহিদা রয়েছে?</span>
                    <span class="en">Need Custom Enterprise Deployment?</span>
                </h4>
                <p style="color:var(--text-muted); font-size:14px; margin:0;">
                    <span class="bn">ডেডিকেটেড সার্ভার, কাস্টম এপিআই বা অন-প্রিমাইস সেটআপের জন্য আমাদের বিশেষজ্ঞ টিমের সাথে কথা বলুন।</span>
                    <span class="en">Talk to our product specialists for multi-store chains, ERP integration or custom infrastructure.</span>
                </p>
            </div>
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="lp-btn lp-btn-md lp-btn-primary">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                <span class="bn">সরাসরি কথা বলুন</span>
                <span class="en">Call Enterprise Sales</span>
            </a>
        </div>
    </div>
</section>
{{-- Reviews / Testimonials --}}
<section class="lp-section" id="reviews">
    <div class="lp-container">
        <div class="lp-sec-head">
            <span class="lp-kicker">
                <span class="lp-pulse-dot"></span>
                <span class="bn">ব্যবসায়ীদের মতামত</span>
                <span class="en">Merchant Reviews</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">মার্চেন্টরা কেন MasterPOS ভালোবাসেন</span>
                <span class="en">Loved by 2,500+ Store Owners</span>
            </h2>
            <p class="lp-sec-sub">
                <span class="bn">সরাসরি শুনুন সফল ব্যবসায়ীদের বাস্তব অভিজ্ঞতার কথা।</span>
                <span class="en">Real experiences from retailers and merchants across Bangladesh.</span>
            </p>
        </div>

        <div class="lp-reviews-grid">
            <div class="lp-review-card">
                <div>
                    <div class="lp-review-stars">★★★★★</div>
                    <p class="lp-review-quote">
                        <span class="bn">“আগে প্রতিদিন দোকানে বাকি হিসাব আর ক্যাশ মেলাতে মেলাতে রাত ১২টা বেজে যেত। MasterPOS নেওয়ার পর এক ক্লিকেই দৈনিক সব হিসাব রেডি হয়ে যায়!”</span>
                        <span class="en">“Balancing cash and customer due registers used to take till midnight. With MasterPOS, daily reconciliation takes just one single click!”</span>
                    </p>
                </div>
                <div class="lp-review-author">
                    <div class="lp-review-avatar">কা</div>
                    <div class="lp-review-meta">
                        <h5>কাজী রাজুয়ান</h5>
                        <span>প্রোপ্রাইটর, রাজু জেনারেল স্টোর · ঢাকা</span>
                    </div>
                </div>
            </div>

            <div class="lp-review-card">
                <div>
                    <div class="lp-review-stars">★★★★★</div>
                    <p class="lp-review-quote">
                        <span class="bn">“ফার্মেসিতে মেয়াদোত্তীর্ণ ওষুধ আর লো-স্টকের সমস্যা একদম দূর হয়ে গেছে। বারকোড স্ক্যান করেই পলকে রসিদ বের হয়।”</span>
                        <span class="en">“Medicine batch and expiry tracking completely transformed our pharmacy. Barcode billing is lightning fast!”</span>
                    </p>
                </div>
                <div class="lp-review-author">
                    <div class="lp-review-avatar" style="background:linear-gradient(135deg,#10B981,#059669);">ই</div>
                    <div class="lp-review-meta">
                        <h5>ইমরান হোসেন</h5>
                        <span>মালিক, নিউ লাইফ ফার্মেসি · চট্টগ্রাম</span>
                    </div>
                </div>
            </div>

            <div class="lp-review-card">
                <div>
                    <div class="lp-review-stars">★★★★★</div>
                    <p class="lp-review-quote">
                        <span class="bn">“আমার ৩টি আউটলেট। আগে ফোন করে করে স্টক ও বিক্রির খবর নিতে হতো। এখন মোবাইল থেকেই লাইভ দেখতে পারি কোন শাখায় কত বেচাকেনা হচ্ছে।”</span>
                        <span class="en">“Running 3 branches was exhausting over phone calls. Now I see live sales and transfer stock right from my smartphone!”</span>
                    </p>
                </div>
                <div class="lp-review-author">
                    <div class="lp-review-avatar" style="background:linear-gradient(135deg,#8B5CF6,#6D28D9);">তা</div>
                    <div class="lp-review-meta">
                        <h5>তানভীর রেজওয়ান</h5>
                        <span>ফাউন্ডার, স্টাইল কর্নার ফ্যাশন · সিলেট</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ Section --}}
<section class="lp-section lp-section-dark" id="faq">
    <div class="lp-container">
        <div class="lp-sec-head">
            <span class="lp-kicker">
                <span class="lp-pulse-dot"></span>
                <span class="bn">সাধারণ জিজ্ঞাসা</span>
                <span class="en">Frequently Asked Questions</span>
            </span>
            <h2 class="lp-sec-title">
                <span class="bn">আপনার প্রশ্নের উত্তর এখানে</span>
                <span class="en">Got Questions? We've Got Answers</span>
            </h2>
        </div>

        <div class="lp-faq-wrap">
            <div class="lp-faq-item">
                <div class="lp-faq-question">
                    <span><span class="bn">MasterPOS ব্যবহার করতে কি বিশেষ কম্পিউটার বা মেশিন লাগবে?</span><span class="en">Do I need special hardware to use MasterPOS?</span></span>
                    <svg class="lp-faq-chevron" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="lp-faq-answer">
                    <span class="bn">না, কোনো বিশেষ বা দামি মেশিনের প্রয়োজন নেই। আপনার সাধারণ ল্যাপটপ, ডেস্কটপ কম্পিউটার, ট্যাবলেট বা এমনকি স্মার্টফোন থেকেই ব্রাউজারের মাধ্যমে এটি খুব সহজে চালানো যায়। যেকোনো সাধারণ বারকোড স্ক্যানার এবং থার্মাল প্রিন্টার এতে সরাসরি কাজ করে।</span>
                    <span class="en">Not at all. You can run MasterPOS on any standard laptop, desktop PC, tablet, or smartphone through your browser. It supports all standard USB/Bluetooth barcode scanners and thermal receipt printers.</span>
                </div>
            </div>

            <div class="lp-faq-item">
                <div class="lp-faq-question">
                    <span><span class="bn">৭ দিনের ফ্রি ট্রায়াল কীভাবে কাজ করে?</span><span class="en">How does the 7-day free trial work?</span></span>
                    <svg class="lp-faq-chevron" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="lp-faq-answer">
                    <span class="bn">আপনি কোনো ক্রেডিট কার্ড ছাড়াই এক মিনিটে অ্যাকাউন্ট তৈরি করে সম্পূর্ণ ফিচার ৭ দিন ফ্রিতে ব্যবহার করে দেখতে পারবেন। সন্তুষ্ট হলে সাবস্ক্রিপশন চালু রাখবেন, অন্যথায় কোনো টাকা পরিশোধ করতে হবে না।</span>
                    <span class="en">Sign up in 1 minute with no credit card required. Get full unrestricted access for 7 days. If you like it, choose a plan — zero risk.</span>
                </div>
            </div>

            <div class="lp-faq-item">
                <div class="lp-faq-question">
                    <span><span class="bn">আমাদের আগের কাস্টমার ও বাকি খাতার ডেটা কীভাবে তুলব?</span><span class="en">Can we migrate existing customer and due records?</span></span>
                    <svg class="lp-faq-chevron" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="lp-faq-answer">
                    <span class="bn">আমাদের সহজ এক্সেল/সিএসভি আমদানি ফিচারের মাধ্যমে কয়েক মিনিটে হাজার হাজার পণ্য ও কাস্টমার ডেটা আপলোড করা যায়। তাছাড়া আমাদের সাপোর্ট টিম আপনাকে ডেটা এন্ট্রিতে সম্পূর্ণ সহায়তা প্রদান করে।</span>
                    <span class="en">You can import thousands of items, supplier lists, and customers via CSV/Excel in minutes. Our dedicated support team can also assist in onboarding.</span>
                </div>
            </div>

            <div class="lp-faq-item">
                <div class="lp-faq-question">
                    <span><span class="bn">আমাদের ডেটা কি শতভাগ সুরক্ষিত থাকবে?</span><span class="en">Is our business data secure and backed up?</span></span>
                    <svg class="lp-faq-chevron" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="lp-faq-answer">
                    <span class="bn">হ্যাঁ, আপনার প্রতিটি ডেটা ব্যাংক-গ্রেড এনক্রিপশনে সুরক্ষিত থাকে এবং প্রতিদিন ক্লাউডে অটোমেটিক ব্যাকআপ সংরক্ষণ করা হয়। তাই কম্পিউটার নষ্ট বা হারিয়ে গেলেও ডেটা কখনোই হারাবে না।</span>
                    <span class="en">Yes, all data is encrypted with SSL and backed up daily in secure cloud vaults. Even if your hardware fails, your business data is always safe.</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Final CTA --}}
<section class="lp-final-cta">
    <div class="lp-container">
        <div class="lp-final-box">
            <h2>
                <span class="bn">২,৫০০+ সফল ব্যবসায়ীর সাথে আজই আপনার দোকানকে স্মার্ট করুন</span>
                <span class="en">Join 2,500+ Thriving Merchants Across Bangladesh</span>
            </h2>
            <p>
                <span class="bn">আজই শুরু করুন ৭ দিনের ফ্রি ট্রায়াল। কোনো ক্রেডিট কার্ডের দরকার নেই — মিনিটেই আপনার দোকান লাইভ।</span>
                <span class="en">Get started with a 7-day free trial. Zero commitment, no setup charges.</span>
            </p>
            <div style="display:flex; gap:14px; flex-wrap:wrap; justify-content:center;">
                <a href="{{ route('login') }}" class="lp-btn lp-btn-lg lp-btn-primary">
                    <span class="bn">ফ্রি ট্রায়াল শুরু করুন</span>
                    <span class="en">Start Free Trial Now</span>
                </a>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="lp-btn lp-btn-lg lp-btn-secondary">
                    <span class="bn">কল করুন: {{ $phone }}</span>
                    <span class="en">Call: {{ $phone }}</span>
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
                        <span class="lp-brand-name">Master<span>POS</span></span>
                        <span class="lp-brand-tag">Cloud POS & ERP</span>
                    </div>
                </a>
                <p>
                    <span class="bn">বাংলাদেশের আধুনিক ব্যবসায়ী ও রিটেইল স্টোরের জন্য সর্বাধিক দ্রুত, নির্ভুল এবং নির্ভরযোগ্য ক্লাউড পিওএস সমাধান।</span>
                    <span class="en">Bangladesh's fastest, most accurate and trusted cloud POS & inventory solution for modern merchants.</span>
                </p>
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
                    <li><a href="#pricing"><span class="bn">প্রাইসিং প্ল্যান</span><span class="en">Pricing Plans</span></a></li>
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

{{-- Interactive POS Simulator & UI Scripts --}}
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
        $('.plan-period-display').text('/ বছর (২০% ছাড়)');
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
});
</script>

</body>
</html>
