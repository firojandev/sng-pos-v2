<x-core::layout title="সিস্টেম সেটিংস ও ল্যান্ডিং পেজ কনটেন্ট" title-en="System Settings & Landing Page Content"
    subtitle="পাবলিক ল্যান্ডিং পেজের প্রতিটি সেকশন, টেক্সট, ফিচার, এফএকিউ ও কনটেন্ট পরিচালনা করুন"
    subtitle-en="Manage public landing page toggle, hero, features, FAQs, reviews, and dynamic content"
    active="system-settings">
    <div style="width:100%; padding-bottom:60px;">

        {{-- Top Notification on Update --}}
        @if (session('status'))
            <div
                style="margin-bottom:20px; padding:12px 18px; border-radius:10px; background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.3); color:var(--green-ink); display:flex; align-items:center; gap:10px; font-weight:600; font-size:14px;">
                <x-core::icon name="check-circle" size="20" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Header Cards with Live Toggles & Quick Links --}}
        <div
            style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:18px; margin-bottom:24px;">
            {{-- Card 1: Public Landing Page --}}
            <div
                style="background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 22px; box-shadow:var(--shadow-card); display:flex; flex-direction:column; justify-content:space-between; gap:16px;">
                <div style="display:flex; gap:14px; align-items:flex-start;">
                    <div
                        style="width:46px; height:46px; border-radius:12px; background:linear-gradient(135deg, rgba(37,99,235,0.15), rgba(14,165,233,0.2)); color:var(--brand-primary, #2563EB); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="globe" size="24" />
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <h3 style="margin:0; font-size:17px; font-weight:700; color:var(--ink-900);">
                                <span class="bn">পাবলিক ল্যান্ডিং পেজ</span>
                                <span class="en">Public Landing Page</span>
                            </h3>
                            <x-core::badge id="landingStatusBadge" :color="$settings['landing_page_enabled'] ? 'green' : 'grey'" size="sm" :dot="true"
                                :label="$settings['landing_page_enabled']
                                    ? 'চালু আছে (Active)'
                                    : 'বন্ধ আছে (Disabled)'" :label-en="$settings['landing_page_enabled'] ? 'Active' : 'Disabled'" />
                        </div>
                        <p style="margin:4px 0 0; font-size:12.5px; color:var(--ink-500); line-height:1.4;">
                            <span class="bn">চালু থাকলে সাইটের শুরুতে আধুনিক ল্যান্ডিং পেজ প্রদর্শিত হবে। বন্ধ থাকলে
                                সরাসরি লগইন পেজে চলে যাবে।</span>
                            <span class="en">When enabled, root URL (/) displays the marketing SaaS landing page.
                                When disabled, redirects to login.</span>
                        </p>
                    </div>
                </div>

                <div
                    style="display:flex; align-items:center; justify-content:space-between; padding-top:12px; border-top:1px dashed var(--border);">
                    <x-core::button variant="secondary" size="sm" icon="external-link" :href="route('landing')"
                        target="_blank">
                        <span class="bn">লাইভ প্রিভিউ দেখুন</span>
                        <span class="en">View Preview</span>
                    </x-core::button>

                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:12px; font-weight:600; color:var(--ink-600);">
                            <span class="bn">ল্যান্ডিং পেজ:</span>
                            <span class="en" style="display:none;">Landing Page:</span>
                        </span>
                        <x-core::toggle id="landing_page_toggle" name="landing_page_toggle" :checked="(bool) $settings['landing_page_enabled']"
                            size="md" color="primary" />
                    </div>
                </div>
            </div>

            {{-- Card 2: Online Shop Registration --}}
            <div
                style="background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 22px; box-shadow:var(--shadow-card); display:flex; flex-direction:column; justify-content:space-between; gap:16px;">
                <div style="display:flex; gap:14px; align-items:flex-start;">
                    <div
                        style="width:46px; height:46px; border-radius:12px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="user-plus" size="24" />
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <h3 style="margin:0; font-size:17px; font-weight:700; color:var(--ink-900);">
                                <span class="bn">অনলাইন দোকান রেজিস্ট্রেশন</span>
                                <span class="en">Online Shop Registration</span>
                            </h3>
                            <x-core::badge id="registrationStatusBadge" :color="$registrationEnabled ? 'green' : 'grey'" size="sm"
                                :dot="true" :label="$registrationEnabled ? 'চালু আছে (Active)' : 'বন্ধ আছে (Disabled)'" :label-en="$registrationEnabled ? 'Active' : 'Disabled'" />
                        </div>
                        <p style="margin:4px 0 0; font-size:12.5px; color:var(--ink-500); line-height:1.4;">
                            <span class="bn">চালু থাকলে নতুন গ্রাহক নিজে ৩-ধাপে রেজিস্ট্রেশন করে ফ্রি প্যাকেজ নিতে
                                পারবে। বন্ধ থাকলে রেজিস্ট্রেশন বন্ধ থাকবে।</span>
                            <span class="en">When enabled, visitors can register a new shop with the free package.
                                When disabled, registration is closed.</span>
                        </p>
                    </div>
                </div>

                <div
                    style="display:flex; align-items:center; justify-content:space-between; padding-top:12px; border-top:1px dashed var(--border);">
                    <x-core::button variant="secondary" size="sm" icon="external-link" :href="route('register')"
                        target="_blank">
                        <span class="bn">রেজিস্ট্রেশন পেজ</span>
                        <span class="en">Register Page</span>
                    </x-core::button>

                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:12px; font-weight:600; color:var(--ink-600);">
                            <span class="bn">রেজিস্ট্রেশন পারমিশন:</span>
                            <span class="en" style="display:none;">Registration:</span>
                        </span>
                        <x-core::toggle id="registration_toggle" name="registration_toggle" :checked="(bool) $registrationEnabled"
                            size="md" color="primary" />
                    </div>
                </div>
            </div>

            {{-- Card 3: Terms & Policy Footer Links --}}
            <div
                style="background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 22px; box-shadow:var(--shadow-card); display:flex; flex-direction:column; justify-content:space-between; gap:16px;">
                <div style="display:flex; gap:14px; align-items:flex-start;">
                    <div
                        style="width:46px; height:46px; border-radius:12px; background:linear-gradient(135deg, rgba(168,85,247,0.15), rgba(139,92,246,0.2)); color:#8b5cf6; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="file-text" size="24" />
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <h3 style="margin:0; font-size:17px; font-weight:700; color:var(--ink-900);">
                                <span class="bn">শর্তাবলী ও গোপনীয়তা নীতি</span>
                                <span class="en">Terms & Privacy Policy</span>
                            </h3>
                            <x-core::badge id="termsStatusBadge" :color="$termsAndPolicyEnabled ? 'green' : 'grey'" size="sm" :dot="true"
                                :label="$termsAndPolicyEnabled ? 'চালু আছে (Active)' : 'বন্ধ আছে (Disabled)'" :label-en="$termsAndPolicyEnabled ? 'Active' : 'Disabled'" />
                        </div>
                        <p style="margin:4px 0 0; font-size:12.5px; color:var(--ink-500); line-height:1.4;">
                            <span class="bn">চালু থাকলে সাইটের ফুটার, ল্যান্ডিং ও লগইন পেজে শর্তাবলী ও গোপনীয়তা নীতি প্রদর্শিত হবে এবং পেজগুলো সচল থাকবে। বন্ধ থাকলে পেজগুলোতে অ্যাক্সেস বন্ধ থাকবে এবং সাইটের কোথাও প্রদর্শিত হবে না।</span>
                            <span class="en">When enabled, Terms and Privacy Policy pages and links are accessible and visible across all footers. When disabled, pages and links are hidden everywhere.</span>
                        </p>
                    </div>
                </div>

                <div
                    style="display:flex; align-items:center; justify-content:flex-end; padding-top:12px; border-top:1px dashed var(--border);">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:12px; font-weight:600; color:var(--ink-600);">
                            <span class="bn">শর্তাবলী ও পলিসি:</span>
                            <span class="en" style="display:none;">Terms & Policy:</span>
                        </span>
                        <x-core::toggle id="terms_policy_toggle" name="terms_policy_toggle" :checked="(bool) $termsAndPolicyEnabled"
                            size="md" color="primary" />
                    </div>
                </div>
            </div>

            {{-- Card 4: Credit Text --}}
            <div
                style="background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 22px; box-shadow:var(--shadow-card); display:flex; flex-direction:column; justify-content:space-between; gap:16px;">
                <div style="display:flex; gap:14px; align-items:flex-start;">
                    <div
                        style="width:46px; height:46px; border-radius:12px; background:linear-gradient(135deg, rgba(245,158,11,0.15), rgba(234,138,12,0.2)); color:#d97706; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="type" size="24" />
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <h3 style="margin:0; font-size:17px; font-weight:700; color:var(--ink-900);">
                                <span class="bn">ক্রেডিট টেক্সট</span>
                                <span class="en">Credit Text</span>
                            </h3>
                            <x-core::badge id="creditStatusBadge" :color="$creditTextEnabled ? 'green' : 'grey'" size="sm" :dot="true"
                                :label="$creditTextEnabled ? 'চালু আছে (Active)' : 'বন্ধ আছে (Disabled)'" :label-en="$creditTextEnabled ? 'Active' : 'Disabled'" />
                        </div>
                        <p style="margin:4px 0 0; font-size:12.5px; color:var(--ink-500); line-height:1.4;">
                            <span class="bn">চালু থাকলে সকল পেজের ফুটারে ও PDF এক্সপোর্টের নিচে
                                ক্রেডিট টেক্সট প্রদর্শিত হবে।</span>
                            <span class="en">When enabled, credit text is shown in the footer and
                                bottom-right of PDF exports.</span>
                        </p>
                    </div>
                </div>

                <div id="creditTextInputWrap"
                    style="padding-top:12px; border-top:1px dashed var(--border); {{ $creditTextEnabled ? '' : 'opacity:0.5; pointer-events:none;' }}">
                    <x-core::input name="credit_text" label="ক্রেডিট টেক্সট (Credit Text)"
                        size="sm" :value="old('credit_text', $creditText)"
                        placeholder="Design and developed by SoftNGear" />
                </div>

                <div
                    style="display:flex; align-items:center; justify-content:space-between;">
                    <x-core::button color="primary" size="sm" type="button" icon="check" id="saveCreditTextBtn"
                        style="{{ $creditTextEnabled ? '' : 'opacity:0.5; pointer-events:none;' }}">
                        <span class="bn">সংরক্ষণ</span>
                        <span class="en">Save</span>
                    </x-core::button>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="font-size:12px; font-weight:600; color:var(--ink-600);">
                            <span class="bn">ক্রেডিট টেক্সট:</span>
                            <span class="en" style="display:none;">Credit Text:</span>
                        </span>
                        <x-core::toggle id="credit_text_toggle" name="credit_text_toggle" :checked="(bool) $creditTextEnabled"
                            size="md" color="primary" />
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-layout-grid">
            {{-- Left Navigation Sidebar: Sections Tabs --}}
            <aside class="settings-nav-sidebar">
                <div class="settings-nav-card">
                    <div class="settings-nav-header">
                        <div class="settings-nav-header-title">
                            <span class="bn">ল্যান্ডিং পেজ সেকশন</span>
                            <span class="en">Landing Sections</span>
                        </div>
                        <div class="settings-nav-header-sub">
                            <span class="bn">কনফিগারেশন নির্বাচন করুন</span>
                            <span class="en">Select configuration tab</span>
                        </div>
                    </div>
                    <nav class="settings-nav-list tabs-nav-bar" role="tablist">
                        <button type="button"
                            class="tab-btn {{ ($activeTab ?? 'general') === 'general' ? 'active' : '' }}"
                            data-tab="general" role="tab"
                            aria-selected="{{ ($activeTab ?? 'general') === 'general' ? 'true' : 'false' }}">
                            <x-core::icon name="settings" size="16" />
                            <span class="tab-label">
                                <span class="bn">সাধারণ ও যোগাযোগ</span>
                                <span class="en">General & SEO</span>
                            </span>
                            <x-core::icon name="chevron-right" size="14" class="tab-arrow" />
                        </button>
                        <button type="button" class="tab-btn {{ ($activeTab ?? '') === 'hero' ? 'active' : '' }}"
                            data-tab="hero" role="tab"
                            aria-selected="{{ ($activeTab ?? '') === 'hero' ? 'true' : 'false' }}">
                            <x-core::icon name="zap" size="16" />
                            <span class="tab-label">
                                <span class="bn">হিরো ব্যানার</span>
                                <span class="en">Hero Section</span>
                            </span>
                            <x-core::icon name="chevron-right" size="14" class="tab-arrow" />
                        </button>
                        <button type="button" class="tab-btn {{ ($activeTab ?? '') === 'stats' ? 'active' : '' }}"
                            data-tab="stats" role="tab"
                            aria-selected="{{ ($activeTab ?? '') === 'stats' ? 'true' : 'false' }}">
                            <x-core::icon name="bar-chart-2" size="16" />
                            <span class="tab-label">
                                <span class="bn">পরিসংখ্যান</span>
                                <span class="en">Proof & Stats</span>
                            </span>
                            <x-core::icon name="chevron-right" size="14" class="tab-arrow" />
                        </button>
                        <button type="button"
                            class="tab-btn {{ ($activeTab ?? '') === 'comparison' ? 'active' : '' }}"
                            data-tab="comparison" role="tab"
                            aria-selected="{{ ($activeTab ?? '') === 'comparison' ? 'true' : 'false' }}">
                            <x-core::icon name="columns" size="16" />
                            <span class="tab-label">
                                <span class="bn">খাতা বনাম পিওএস</span>
                                <span class="en">Problem vs Solution</span>
                            </span>
                            <x-core::icon name="chevron-right" size="14" class="tab-arrow" />
                        </button>
                        <button type="button" class="tab-btn {{ ($activeTab ?? '') === 'features' ? 'active' : '' }}"
                            data-tab="features" role="tab"
                            aria-selected="{{ ($activeTab ?? '') === 'features' ? 'true' : 'false' }}">
                            <x-core::icon name="layers" size="16" />
                            <span class="tab-label">
                                <span class="bn">কোর ফিচারসমূহ</span>
                                <span class="en">Features</span>
                            </span>
                            <x-core::icon name="chevron-right" size="14" class="tab-arrow" />
                        </button>
                        <button type="button"
                            class="tab-btn {{ ($activeTab ?? '') === 'verticals' ? 'active' : '' }}"
                            data-tab="verticals" role="tab"
                            aria-selected="{{ ($activeTab ?? '') === 'verticals' ? 'true' : 'false' }}">
                            <x-core::icon name="briefcase" size="16" />
                            <span class="tab-label">
                                <span class="bn">ব্যবসায়ের ধরন</span>
                                <span class="en">Verticals</span>
                            </span>
                            <x-core::icon name="chevron-right" size="14" class="tab-arrow" />
                        </button>
                        <button type="button" class="tab-btn {{ ($activeTab ?? '') === 'reviews' ? 'active' : '' }}"
                            data-tab="reviews" role="tab"
                            aria-selected="{{ ($activeTab ?? '') === 'reviews' ? 'true' : 'false' }}">
                            <x-core::icon name="message-square" size="16" />
                            <span class="tab-label">
                                <span class="bn">গ্রাহক রিভিউ</span>
                                <span class="en">Reviews</span>
                            </span>
                            <x-core::icon name="chevron-right" size="14" class="tab-arrow" />
                        </button>
                        <button type="button" class="tab-btn {{ ($activeTab ?? '') === 'faqs' ? 'active' : '' }}"
                            data-tab="faqs" role="tab"
                            aria-selected="{{ ($activeTab ?? '') === 'faqs' ? 'true' : 'false' }}">
                            <x-core::icon name="help-circle" size="16" />
                            <span class="tab-label">
                                <span class="bn">সাধারণ জিজ্ঞাসা</span>
                                <span class="en">FAQ</span>
                            </span>
                            <x-core::icon name="chevron-right" size="14" class="tab-arrow" />
                        </button>
                        <button type="button" class="tab-btn {{ ($activeTab ?? '') === 'cta' ? 'active' : '' }}"
                            data-tab="cta" role="tab"
                            aria-selected="{{ ($activeTab ?? '') === 'cta' ? 'true' : 'false' }}">
                            <x-core::icon name="send" size="16" />
                            <span class="tab-label">
                                <span class="bn">ব্যানার ও ফুটার</span>
                                <span class="en">CTA & Footer</span>
                            </span>
                            <x-core::icon name="chevron-right" size="14" class="tab-arrow" />
                        </button>
                    </nav>
                </div>
            </aside>

            {{-- Right Content Area: Settings Form --}}
            <div class="settings-content-pane">
                <form method="POST" action="{{ route('system-settings.update') }}" id="settingsForm" novalidate>
                    @csrf
                    <input type="hidden" name="active_tab" id="activeTabInput"
                        value="{{ $activeTab ?? 'general' }}">
                    <input type="hidden" name="landing_page_enabled" id="hiddenLandingEnabled"
                        value="{{ $settings['landing_page_enabled'] ? '1' : '0' }}">
                    <input type="hidden" name="registration_enabled" id="hiddenRegistrationEnabled"
                        value="{{ $registrationEnabled ? '1' : '0' }}">
                    <input type="hidden" name="show_terms_and_policy" id="hiddenTermsPolicyEnabled"
                        value="{{ $termsAndPolicyEnabled ? '1' : '0' }}">
                    <input type="hidden" name="show_credit_text" id="hiddenCreditTextEnabled"
                        value="{{ $creditTextEnabled ? '1' : '0' }}">

                    {{-- 1. General & SEO Tab --}}
                    <div class="tab-pane {{ ($activeTab ?? 'general') === 'general' ? 'active' : '' }}"
                        id="tab-general">
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <h4 class="settings-card-title">
                                    <span class="bn">ওয়েবসাইট পরিচিতি ও যোগাযোগের তথ্য</span>
                                    <span class="en">General Information & Contact Details</span>
                                </h4>
                                <p class="settings-card-desc">
                                    <span class="bn">ওয়েবসাইটের মূল নাম, সাপোর্ট হেল্পলাইন, অফিস ঠিকানা ও সার্চ
                                        ইঞ্জিন এসইও বিবরণ।</span>
                                    <span class="en">Main website branding, contact hotline, office address, and
                                        SEO search meta.</span>
                                </p>
                            </div>

                            <div class="grid-2">
                                <div>
                                    <x-core::input name="site_title" label="ওয়েবসাইট / সফটওয়্যারের নাম (Site Name)"
                                        size="sm" :value="old('site_title', $settings['site_title'])"
                                        placeholder="{{ config('app.name', 'SNGPOS') }}" />
                                </div>
                                <div>
                                    <x-core::input name="brand_tag" label="ব্র্যান্ড ট্যাগলাইন (Brand Tagline)"
                                        size="sm" :value="old('brand_tag', $settings['brand_tag'])" placeholder="Cloud POS & ERP" />
                                </div>
                                <div>
                                    <x-core::input name="support_phone"
                                        label="হেল্পলাইন / মোবাইল নম্বর (Support Phone)" size="sm"
                                        :value="old('support_phone', $settings['support_phone'])" placeholder="+880 1886 861430" />
                                </div>
                                <div>
                                    <x-core::input name="support_email" label="সাপোর্ট ইমেইল (Support Email)"
                                        size="sm" type="email" :value="old('support_email', $settings['support_email'])"
                                        placeholder="support@softngear.com" />
                                </div>
                            </div>

                            <div style="margin-top:16px;">
                                <x-core::input name="office_address" label="অফিসের ঠিকানা (Office Address)"
                                    size="sm" :value="old('office_address', $settings['office_address'])"
                                    placeholder="Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi" />
                            </div>

                            <div style="margin-top:16px;">
                                <x-core::textarea name="meta_description" label="এসইও মেটা বিবরণ (Meta Description)"
                                    size="sm" rows="2" :value="old('meta_description', $settings['meta_description'])"
                                    placeholder="বাংলাদেশের আধুনিক ও দ্রুততম ক্লাউড POS এবং ব্যবসা পরিচালনা সফটওয়্যার।" />
                            </div>

                            <div class="card-footer-action">
                                <x-core::button color="primary" size="sm" type="submit" icon="check">
                                    <span class="bn">সংরক্ষণ করুন</span>
                                    <span class="en">Save Changes</span>
                                </x-core::button>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Hero Section Tab --}}
                    <div class="tab-pane {{ ($activeTab ?? '') === 'hero' ? 'active' : '' }}" id="tab-hero">
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <h4 class="settings-card-title">
                                    <span class="bn">হিরো ব্যানার কনটেন্ট (Hero Section)</span>
                                    <span class="en">Hero Banner Section</span>
                                </h4>
                                <p class="settings-card-desc">
                                    <span class="bn">ওয়েবসাইটের প্রথম দৃশ্যমান ব্যানার, আকর্ষণীয় হেডলাইন ও
                                        কল-টু-অ্যাকশন বাটন।</span>
                                    <span class="en">Top visible hero section, value proposition headline, and
                                        primary buttons.</span>
                                </p>
                            </div>

                            <div class="grid-2">
                                <div>
                                    <x-core::input name="hero_badge_bn" label="হিরো ব্যাজ টেক্সট (বাংলা)"
                                        size="sm" :value="old('hero_badge_bn', $settings['hero_badge_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="hero_badge_en" label="Hero Badge Text (English)"
                                        size="sm" :value="old('hero_badge_en', $settings['hero_badge_en'])" />
                                </div>
                                <div>
                                    <x-core::input name="hero_title_bn" label="মূল হেডলাইন (বাংলা)" size="sm"
                                        :value="old('hero_title_bn', $settings['hero_title_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="hero_title_en" label="Main Headline (English)"
                                        size="sm" :value="old('hero_title_en', $settings['hero_title_en'])" />
                                </div>
                                <div>
                                    <x-core::input name="hero_title_gradient_bn" label="হাইলাইটেড রঙিন শব্দ (বাংলা)"
                                        size="sm" :value="old('hero_title_gradient_bn', $settings['hero_title_gradient_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="hero_title_gradient_en"
                                        label="Highlighted Gradient Word (English)" size="sm"
                                        :value="old('hero_title_gradient_en', $settings['hero_title_gradient_en'])" />
                                </div>
                            </div>

                            <div style="margin-top:16px;">
                                <x-core::textarea name="hero_subtitle_bn" label="সাব-টাইটেল / বিবরণ (বাংলা)"
                                    size="sm" rows="2" :value="old('hero_subtitle_bn', $settings['hero_subtitle_bn'])" />
                            </div>
                            <div style="margin-top:16px;">
                                <x-core::textarea name="hero_subtitle_en" label="Subtitle / Description (English)"
                                    size="sm" rows="2" :value="old('hero_subtitle_en', $settings['hero_subtitle_en'])" />
                            </div>

                            <div class="grid-2" style="margin-top:16px;">
                                <div>
                                    <x-core::input name="hero_btn_primary_text_bn" label="১ম বাটন টেক্সট (বাংলা)"
                                        size="sm" :value="old(
                                            'hero_btn_primary_text_bn',
                                            $settings['hero_btn_primary_text_bn'],
                                        )" />
                                </div>
                                <div>
                                    <x-core::input name="hero_btn_primary_text_en"
                                        label="Primary Button Text (English)" size="sm" :value="old(
                                            'hero_btn_primary_text_en',
                                            $settings['hero_btn_primary_text_en'],
                                        )" />
                                </div>
                                <div>
                                    <x-core::input name="hero_btn_primary_url" label="১ম বাটন লিংক / URL"
                                        size="sm" :value="old('hero_btn_primary_url', $settings['hero_btn_primary_url'])" placeholder="#simulator" />
                                </div>
                                <div>
                                    <x-core::input name="hero_active_users" label="সক্রিয় ব্যবসায়ী সংখ্যা ট্যাগ"
                                        size="sm" :value="old('hero_active_users', $settings['hero_active_users'])" placeholder="৫,০০০+ ব্যবসায়ী যুক্ত" />
                                </div>
                                <div>
                                    <x-core::input name="hero_btn_secondary_text_bn" label="২য় বাটন টেক্সট (বাংলা)"
                                        size="sm" :value="old(
                                            'hero_btn_secondary_text_bn',
                                            $settings['hero_btn_secondary_text_bn'],
                                        )" />
                                </div>
                                <div>
                                    <x-core::input name="hero_btn_secondary_text_en"
                                        label="Secondary Button Text (English)" size="sm" :value="old(
                                            'hero_btn_secondary_text_en',
                                            $settings['hero_btn_secondary_text_en'],
                                        )" />
                                </div>
                                <div>
                                    <x-core::input name="hero_btn_secondary_url" label="২য় বাটন লিংক / URL"
                                        size="sm" :value="old('hero_btn_secondary_url', $settings['hero_btn_secondary_url'])" placeholder="/login" />
                                </div>
                                <div>
                                    <x-core::input name="hero_trust_text_bn" label="হিরো ফুটনোট টেক্সট (বাংলা)"
                                        size="sm" :value="old('hero_trust_text_bn', $settings['hero_trust_text_bn'])" />
                                </div>
                            </div>

                            <div class="card-footer-action">
                                <x-core::button color="primary" size="sm" type="submit" icon="check">
                                    <span class="bn">সংরক্ষণ করুন</span>
                                    <span class="en">Save Changes</span>
                                </x-core::button>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Stats & Metrics Tab --}}
                    <div class="tab-pane {{ ($activeTab ?? '') === 'stats' ? 'active' : '' }}" id="tab-stats">
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <h4 class="settings-card-title">
                                    <span class="bn">পরিসংখ্যান ও বিশ্বাসযোগ্যতার প্রমাণ (Proof & Stats)</span>
                                    <span class="en">Key Metrics & Social Proof Bar</span>
                                </h4>
                                <p class="settings-card-desc">
                                    <span class="bn">হিরো ব্যানারের নিচে ৪টি মূল হাইলাইট ও পরিসংখ্যান।</span>
                                    <span class="en">The 4 prominent metrics displayed immediately below the hero
                                        banner.</span>
                                </p>
                            </div>

                            <div class="grid-2">
                                <div class="item-box">
                                    <h5
                                        style="margin-bottom:12px; color:var(--brand-primary, #2563EB); font-weight:700;">
                                        <span class="bn">কাউন্টার ১</span>
                                        <span class="en">Counter 1</span>
                                    </h5>
                                    <div class="grid-2">
                                        <x-core::input name="stat_1_number" label="মান / সংখ্যা (বাংলা)" size="sm"
                                            :value="old('stat_1_number', $settings['stat_1_number'])" placeholder="৯৯.৯%" />
                                        <x-core::input name="stat_1_number_en" label="Value / Number (English)" size="sm"
                                            :value="old('stat_1_number_en', $settings['stat_1_number_en'] ?? '')" placeholder="99.9%" />
                                    </div>
                                    <div class="grid-2" style="margin-top:10px;">
                                        <x-core::input name="stat_1_label_bn"
                                            label="লেবেল (বাংলা)" size="sm" :value="old('stat_1_label_bn', $settings['stat_1_label_bn'])" />
                                        <x-core::input name="stat_1_label_en"
                                            label="Label (English)" size="sm" :value="old('stat_1_label_en', $settings['stat_1_label_en'])" />
                                    </div>
                                </div>

                                <div class="item-box">
                                    <h5
                                        style="margin-bottom:12px; color:var(--brand-primary, #2563EB); font-weight:700;">
                                        <span class="bn">কাউন্টার ২</span>
                                        <span class="en">Counter 2</span>
                                    </h5>
                                    <div class="grid-2">
                                        <x-core::input name="stat_2_number" label="মান / সংখ্যা (বাংলা)" size="sm"
                                            :value="old('stat_2_number', $settings['stat_2_number'])" placeholder="৫০,০০০+" />
                                        <x-core::input name="stat_2_number_en" label="Value / Number (English)" size="sm"
                                            :value="old('stat_2_number_en', $settings['stat_2_number_en'] ?? '')" placeholder="50,000+" />
                                    </div>
                                    <div class="grid-2" style="margin-top:10px;">
                                        <x-core::input name="stat_2_label_bn"
                                            label="লেবেল (বাংলা)" size="sm" :value="old('stat_2_label_bn', $settings['stat_2_label_bn'])" />
                                        <x-core::input name="stat_2_label_en"
                                            label="Label (English)" size="sm" :value="old('stat_2_label_en', $settings['stat_2_label_en'])" />
                                    </div>
                                </div>

                                <div class="item-box">
                                    <h5
                                        style="margin-bottom:12px; color:var(--brand-primary, #2563EB); font-weight:700;">
                                        <span class="bn">কাউন্টার ৩</span>
                                        <span class="en">Counter 3</span>
                                    </h5>
                                    <div class="grid-2">
                                        <x-core::input name="stat_3_number" label="মান / সংখ্যা (বাংলা)" size="sm"
                                            :value="old('stat_3_number', $settings['stat_3_number'])" placeholder="৩ সেকেন্ড" />
                                        <x-core::input name="stat_3_number_en" label="Value / Number (English)" size="sm"
                                            :value="old('stat_3_number_en', $settings['stat_3_number_en'] ?? '')" placeholder="3s" />
                                    </div>
                                    <div class="grid-2" style="margin-top:10px;">
                                        <x-core::input name="stat_3_label_bn"
                                            label="লেবেল (বাংলা)" size="sm" :value="old('stat_3_label_bn', $settings['stat_3_label_bn'])" />
                                        <x-core::input name="stat_3_label_en"
                                            label="Label (English)" size="sm" :value="old('stat_3_label_en', $settings['stat_3_label_en'])" />
                                    </div>
                                </div>

                                <div class="item-box">
                                    <h5
                                        style="margin-bottom:12px; color:var(--brand-primary, #2563EB); font-weight:700;">
                                        <span class="bn">কাউন্টার ৪</span>
                                        <span class="en">Counter 4</span>
                                    </h5>
                                    <div class="grid-2">
                                        <x-core::input name="stat_4_number" label="মান / সংখ্যা (বাংলা)" size="sm"
                                            :value="old('stat_4_number', $settings['stat_4_number'])" placeholder="২৪/৭" />
                                        <x-core::input name="stat_4_number_en" label="Value / Number (English)" size="sm"
                                            :value="old('stat_4_number_en', $settings['stat_4_number_en'] ?? '')" placeholder="24/7" />
                                    </div>
                                    <div class="grid-2" style="margin-top:10px;">
                                        <x-core::input name="stat_4_label_bn"
                                            label="লেবেল (বাংলা)" size="sm" :value="old('stat_4_label_bn', $settings['stat_4_label_bn'])" />
                                        <x-core::input name="stat_4_label_en"
                                            label="Label (English)" size="sm" :value="old('stat_4_label_en', $settings['stat_4_label_en'])" />
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer-action">
                                <x-core::button color="primary" size="sm" type="submit" icon="check">
                                    <span class="bn">সংরক্ষণ করুন</span>
                                    <span class="en">Save Changes</span>
                                </x-core::button>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Problem vs Solution Tab --}}
                    <div class="tab-pane {{ ($activeTab ?? '') === 'comparison' ? 'active' : '' }}"
                        id="tab-comparison">
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <h4 class="settings-card-title">
                                    <span class="bn">সনাতন পদ্ধতি বনাম
                                        {{ $settings['site_title'] ?? 'সফটওয়্যার' }} (Comparison Matrix)</span>
                                    <span class="en">Traditional Method vs
                                        {{ $settings['site_title'] ?? 'Software' }}</span>
                                </h4>
                                <p class="settings-card-desc">
                                    <span class="bn">খাতা-কলমের সমস্যা এবং সফটওয়্যারের সমাধান তালিকা।</span>
                                    <span class="en">Pain points of traditional bookkeeping vs automated modern
                                        solutions.</span>
                                </p>
                            </div>

                            <div class="grid-2">
                                <div>
                                    <x-core::input name="vs_badge_bn" label="সেকশন ব্যাজ (বাংলা)" size="sm"
                                        :value="old('vs_badge_bn', $settings['vs_badge_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="vs_badge_en" label="Section Badge (English)" size="sm"
                                        :value="old('vs_badge_en', $settings['vs_badge_en'])" />
                                </div>
                                <div>
                                    <x-core::input name="vs_title_bn" label="সেকশন শিরোনাম (বাংলা)" size="sm"
                                        :value="old('vs_title_bn', $settings['vs_title_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="vs_title_en" label="Section Title (English)" size="sm"
                                        :value="old('vs_title_en', $settings['vs_title_en'])" />
                                </div>
                            </div>
                            <div style="margin-top:16px;">
                                <x-core::textarea name="vs_subtitle_bn" label="উপ-শিরোনাম / বিবরণ (বাংলা)"
                                    size="sm" rows="2" :value="old('vs_subtitle_bn', $settings['vs_subtitle_bn'])" />
                            </div>

                            <div class="grid-2" style="gap:20px; margin-top:24px;">
                                {{-- Pain Points --}}
                                <div class="item-box" style="border-left:4px solid #ef4444;">
                                    <h5 style="color:#ef4444; font-weight:700; margin-bottom:12px;">❌ সনাতন খাতা-কলমের
                                        সমস্যাসমূহ (Pain Points)</h5>
                                    <div id="painPointsWrap" style="display:flex; flex-direction:column; gap:12px;">
                                        @foreach ($settings['vs_pain_items'] as $i => $item)
                                            <div class="dynamic-row"
                                                style="background:var(--paper); padding:10px 14px; border-radius:8px; border:1px solid var(--border);">
                                                <x-core::input name="vs_pain_items[{{ $i }}][bn]"
                                                    label="সমস্যা {{ $i + 1 }} (বাংলা)" size="sm"
                                                    :value="$item['bn'] ?? ''" />
                                                <div style="margin-top:8px;">
                                                    <x-core::input name="vs_pain_items[{{ $i }}][en]"
                                                        label="Pain Point {{ $i + 1 }} (English)"
                                                        size="sm" :value="$item['en'] ?? ''" />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Solution Points --}}
                                <div class="item-box" style="border-left:4px solid #10b981;">
                                    <h5 style="color:#10b981; font-weight:700; margin-bottom:12px;">✅
                                        {{ $settings['site_title'] ?? 'সফটওয়্যার' }} সমাধান (Solutions)</h5>
                                    <div id="solutionPointsWrap"
                                        style="display:flex; flex-direction:column; gap:12px;">
                                        @foreach ($settings['vs_solution_items'] as $i => $item)
                                            <div class="dynamic-row"
                                                style="background:var(--paper); padding:10px 14px; border-radius:8px; border:1px solid var(--border);">
                                                <x-core::input name="vs_solution_items[{{ $i }}][bn]"
                                                    label="সমাধান {{ $i + 1 }} (বাংলা)" size="sm"
                                                    :value="$item['bn'] ?? ''" />
                                                <div style="margin-top:8px;">
                                                    <x-core::input name="vs_solution_items[{{ $i }}][en]"
                                                        label="Solution Point {{ $i + 1 }} (English)"
                                                        size="sm" :value="$item['en'] ?? ''" />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer-action">
                                <x-core::button color="primary" size="sm" type="submit" icon="check">
                                    <span class="bn">সংরক্ষণ করুন</span>
                                    <span class="en">Save Changes</span>
                                </x-core::button>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Core Features Tab --}}
                    <div class="tab-pane {{ ($activeTab ?? '') === 'features' ? 'active' : '' }}" id="tab-features">
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <h4 class="settings-card-title">
                                    <span class="bn">কোর ফিচারসমূহ (Core Modules)</span>
                                    <span class="en">Core Features & Modules</span>
                                </h4>
                                <p class="settings-card-desc">
                                    <span class="bn">ল্যান্ডিং পেজের ৬টি মূল ফিচার কার্ডের শিরোনাম, ব্যাজ এবং বিবরণ
                                        সম্পাদনা করুন।</span>
                                    <span class="en">Customize the 6 core feature showcase cards on the landing
                                        page.</span>
                                </p>
                            </div>

                            <div class="grid-2">
                                <div>
                                    <x-core::input name="features_badge_bn" label="সেকশন ব্যাজ (বাংলা)"
                                        size="sm" :value="old('features_badge_bn', $settings['features_badge_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="features_title_bn" label="সেকশন শিরোনাম (বাংলা)"
                                        size="sm" :value="old('features_title_bn', $settings['features_title_bn'])" />
                                </div>
                            </div>
                            <div style="margin-top:16px;">
                                <x-core::textarea name="features_subtitle_bn" label="উপ-শিরোনাম / বিবরণ (বাংলা)"
                                    size="sm" rows="2" :value="old('features_subtitle_bn', $settings['features_subtitle_bn'])" />
                            </div>

                            <div style="margin-top:24px; display:flex; flex-direction:column; gap:16px;">
                                @foreach ($settings['features_list'] as $i => $feat)
                                    <div class="item-box"
                                        style="border-left:4px solid var(--brand-primary, #2563EB);">
                                        <div
                                            style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                            <h5 style="margin:0; font-weight:700; color:var(--ink-900);">ফিচার
                                                #{{ $i + 1 }}: {{ $feat['title_bn'] ?? '' }}</h5>
                                            <x-core::badge color="blue" size="sm" :label="$feat['badge_bn'] ?? ''" />
                                        </div>
                                        <div class="grid-2">
                                            <x-core::input name="features_list[{{ $i }}][title_bn]"
                                                label="শিরোনাম (বাংলা)" size="sm" :value="$feat['title_bn'] ?? ''" />
                                            <x-core::input name="features_list[{{ $i }}][title_en]"
                                                label="Title (English)" size="sm" :value="$feat['title_en'] ?? ''" />
                                            <x-core::input name="features_list[{{ $i }}][badge_bn]"
                                                label="ব্যাজ ট্যাগ (বাংলা)" size="sm" :value="$feat['badge_bn'] ?? ''" />
                                            <x-core::input name="features_list[{{ $i }}][icon]"
                                                label="আইকন নাম (Lucide)" size="sm" :value="$feat['icon'] ?? 'zap'" />
                                        </div>
                                        <div style="margin-top:10px;">
                                            <x-core::textarea name="features_list[{{ $i }}][desc_bn]"
                                                label="বিবরণ (বাংলা)" size="sm" rows="2"
                                                :value="$feat['desc_bn'] ?? ''" />
                                        </div>
                                        <div style="margin-top:10px;">
                                            <x-core::textarea name="features_list[{{ $i }}][desc_en]"
                                                label="Description (English)" size="sm" rows="2"
                                                :value="$feat['desc_en'] ?? ''" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="card-footer-action">
                                <x-core::button color="primary" size="sm" type="submit" icon="check">
                                    <span class="bn">সংরক্ষণ করুন</span>
                                    <span class="en">Save Changes</span>
                                </x-core::button>
                            </div>
                        </div>
                    </div>

                    {{-- 6. Business Verticals Tab --}}
                    <div class="tab-pane {{ ($activeTab ?? '') === 'verticals' ? 'active' : '' }}"
                        id="tab-verticals">
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <h4 class="settings-card-title">
                                    <span class="bn">ব্যবসায়ের ধরন (Supported Industries)</span>
                                    <span class="en">Business Verticals & Industry Workflows</span>
                                </h4>
                                <p class="settings-card-desc">
                                    <span class="bn">যেসব ব্যবসা ক্যাটাগরিতে
                                        {{ $settings['site_title'] ?? 'সফটওয়্যার' }} উপযোগী (মুদি, ফ্যাশন, ফার্মেসি,
                                        রেস্টুরেন্ট ইত্যাদি)।</span>
                                    <span class="en">Industry cards showing custom workflows for retail
                                        verticals.</span>
                                </p>
                            </div>

                            <div class="grid-2">
                                <div>
                                    <x-core::input name="vert_badge_bn" label="সেকশন ব্যাজ (বাংলা)" size="sm"
                                        :value="old('vert_badge_bn', $settings['vert_badge_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="vert_badge_en" label="Section Badge (English)" size="sm"
                                        :value="old('vert_badge_en', $settings['vert_badge_en'] ?? '')" />
                                </div>
                                <div>
                                    <x-core::input name="vert_title_bn" label="সেকশন শিরোনাম (বাংলা)" size="sm"
                                        :value="old('vert_title_bn', $settings['vert_title_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="vert_title_en" label="Section Title (English)" size="sm"
                                        :value="old('vert_title_en', $settings['vert_title_en'] ?? '')" />
                                </div>
                            </div>
                            <div class="grid-2" style="margin-top:16px;">
                                <div>
                                    <x-core::textarea name="vert_subtitle_bn" label="উপ-শিরোনাম / বিবরণ (বাংলা)" size="sm" rows="2"
                                        :value="old('vert_subtitle_bn', $settings['vert_subtitle_bn'])" />
                                </div>
                                <div>
                                    <x-core::textarea name="vert_subtitle_en" label="Subtitle / Description (English)" size="sm" rows="2"
                                        :value="old('vert_subtitle_en', $settings['vert_subtitle_en'] ?? '')" />
                                </div>
                            </div>

                            <div class="grid-2" style="gap:16px; margin-top:24px;">
                                @foreach ($settings['verticals_list'] as $i => $vert)
                                    <div class="item-box">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                            <h5 style="margin:0; font-weight:700; color:var(--brand-primary, #2563EB);">
                                                <span class="bn">ব্যবসায়ের ধরন #{{ $i + 1 }}: {{ $vert['name_bn'] ?? '' }}</span>
                                                <span class="en">Vertical #{{ $i + 1 }}: {{ $vert['name_en'] ?? ($vert['name_bn'] ?? '') }}</span>
                                            </h5>
                                            @if(!empty($vert['tag_bn']) || !empty($vert['tag_en']))
                                                <x-core::badge color="blue" size="sm">
                                                    <span class="bn">{{ $vert['tag_bn'] ?? '' }}</span>
                                                    <span class="en">{{ $vert['tag_en'] ?? ($vert['tag_bn'] ?? '') }}</span>
                                                </x-core::badge>
                                            @endif
                                        </div>
                                        <div class="grid-2">
                                            <x-core::input name="verticals_list[{{ $i }}][name_bn]"
                                                label="নাম (বাংলা)" size="sm" :value="$vert['name_bn'] ?? ''" />
                                            <x-core::input name="verticals_list[{{ $i }}][name_en]"
                                                label="Name (English)" size="sm" :value="$vert['name_en'] ?? ''" />
                                            <x-core::input name="verticals_list[{{ $i }}][tag_bn]"
                                                label="ট্যাগ (বাংলা)" size="sm" :value="$vert['tag_bn'] ?? ''" />
                                            <x-core::input name="verticals_list[{{ $i }}][tag_en]"
                                                label="Tag (English)" size="sm" :value="$vert['tag_en'] ?? ''" />
                                        </div>
                                        <div style="margin-top:10px;">
                                            <x-core::input name="verticals_list[{{ $i }}][icon]"
                                                label="আইকন (Lucide)" size="sm" :value="$vert['icon'] ?? 'shopping-cart'" />
                                        </div>
                                        <div class="grid-2" style="margin-top:10px;">
                                            <div>
                                                <x-core::textarea name="verticals_list[{{ $i }}][desc_bn]"
                                                    label="বিবরণ (বাংলা)" size="sm" rows="2"
                                                    :value="$vert['desc_bn'] ?? ''" />
                                            </div>
                                            <div>
                                                <x-core::textarea name="verticals_list[{{ $i }}][desc_en]"
                                                    label="Description (English)" size="sm" rows="2"
                                                    :value="$vert['desc_en'] ?? ''" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="card-footer-action">
                                <x-core::button color="primary" size="sm" type="submit" icon="check">
                                    <span class="bn">সংরক্ষণ করুন</span>
                                    <span class="en">Save Changes</span>
                                </x-core::button>
                            </div>
                        </div>
                    </div>

                    {{-- 7. Reviews / Testimonials Tab --}}
                    <div class="tab-pane {{ ($activeTab ?? '') === 'reviews' ? 'active' : '' }}" id="tab-reviews">
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <h4 class="settings-card-title">
                                            <span class="bn">গ্রাহক রিভিউ ও টেস্টোমোনিয়াল (Customer
                                                Reviews)</span>
                                            <span class="en">Client Testimonials & Feedback</span>
                                        </h4>
                                        <p class="settings-card-desc">
                                            <span class="bn">ব্যবসায়ীদের মন্তব্য, রেটিং ও সফলতার বাস্তব গল্প
                                                পরিচালনা করুন।</span>
                                            <span class="en">Manage authentic testimonials, store names, and
                                                ratings.</span>
                                        </p>
                                    </div>
                                    <x-core::button size="sm" variant="secondary" type="button" icon="plus"
                                        id="addReviewBtn">
                                        <span class="bn">নতুন রিভিউ যোগ করুন</span>
                                        <span class="en">Add Review</span>
                                    </x-core::button>
                                </div>
                            </div>

                            <div class="grid-2">
                                <x-core::input name="reviews_badge_bn" label="সেকশন ব্যাজ (বাংলা)" size="sm"
                                    :value="old('reviews_badge_bn', $settings['reviews_badge_bn'])" />
                                <x-core::input name="reviews_title_bn" label="সেকশন শিরোনাম (বাংলা)" size="sm"
                                    :value="old('reviews_title_bn', $settings['reviews_title_bn'])" />
                            </div>

                            <div id="reviewsListWrap"
                                style="margin-top:24px; display:flex; flex-direction:column; gap:12px;">
                                @foreach ($settings['reviews_list'] as $i => $rev)
                                    @php
                                        $author = $rev['author'] ?? ($rev['author_bn'] ?? '');
                                        $shop = $rev['shop'] ?? ($rev['shop_bn'] ?? '');
                                        $revTitle =
                                            'রিভিউ #' .
                                            ($i + 1) .
                                            ($author ? ': ' . $author : '') .
                                            ($shop ? ' (' . $shop . ')' : '');
                                    @endphp
                                    <x-core::accordion :open="$loop->first" :title="$revTitle" icon="message-square"
                                        class="review-item" card>
                                        <x-slot:actions>
                                            <x-core::button size="sm" color="danger" variant="soft"
                                                type="button" icon="trash-2" class="delete-row-btn"
                                                title="মুছে ফেলুন" />
                                        </x-slot:actions>

                                        <div class="grid-2">
                                            <x-core::input name="reviews_list[{{ $i }}][author]"
                                                label="ব্যবসায়ীর নাম (বাংলা)" size="sm" :value="$rev['author'] ?? ($rev['author_bn'] ?? '')" />
                                            <x-core::input name="reviews_list[{{ $i }}][author_en]"
                                                label="Author Name (English)" size="sm" :value="$rev['author_en'] ?? ''" />
                                            <x-core::input name="reviews_list[{{ $i }}][shop]"
                                                label="প্রতিষ্ঠানের নাম (বাংলা)" size="sm"
                                                :value="$rev['shop'] ?? ($rev['shop_bn'] ?? '')" />
                                            <x-core::input name="reviews_list[{{ $i }}][shop_en]"
                                                label="Shop Name (English)" size="sm"
                                                :value="$rev['shop_en'] ?? ''" />
                                            <x-core::input name="reviews_list[{{ $i }}][city]"
                                                label="শহর / এলাকা (বাংলা)" size="sm" :value="$rev['city'] ?? ($rev['city_bn'] ?? '')" />
                                            <x-core::input name="reviews_list[{{ $i }}][city_en]"
                                                label="City / Area (English)" size="sm" :value="$rev['city_en'] ?? ''" />
                                            <x-core::input name="reviews_list[{{ $i }}][initials]"
                                                label="অবতার আদ্যক্ষর (বাংলা)" size="sm" placeholder="যেমন: র"
                                                :value="$rev['initials'] ?? ($rev['initials_bn'] ?? '')" />
                                            <x-core::input name="reviews_list[{{ $i }}][initials_en]"
                                                label="Avatar Initial (English)" size="sm" placeholder="e.g. R"
                                                :value="$rev['initials_en'] ?? ''" />
                                            <x-core::input name="reviews_list[{{ $i }}][rating]"
                                                label="রেটিং (1 - 5)" size="sm" type="number" min="1"
                                                max="5" :value="$rev['rating'] ?? 5" />
                                        </div>
                                        <div style="margin-top:12px;">
                                            <x-core::textarea name="reviews_list[{{ $i }}][quote_bn]"
                                                label="মন্তব্য / কোটেশন (বাংলা)" size="sm" rows="2"
                                                :value="$rev['quote_bn'] ?? ''" />
                                        </div>
                                        <div style="margin-top:12px;">
                                            <x-core::textarea name="reviews_list[{{ $i }}][quote_en]"
                                                label="Quote (English)" size="sm" rows="2"
                                                :value="$rev['quote_en'] ?? ''" />
                                        </div>
                                    </x-core::accordion>
                                @endforeach
                            </div>

                            <div class="card-footer-action">
                                <x-core::button color="primary" size="sm" type="submit" icon="check">
                                    <span class="bn">সংরক্ষণ করুন</span>
                                    <span class="en">Save Changes</span>
                                </x-core::button>
                            </div>
                        </div>
                    </div>

                    {{-- 8. FAQ Section Tab --}}
                    <div class="tab-pane {{ ($activeTab ?? '') === 'faqs' ? 'active' : '' }}" id="tab-faqs">
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <h4 class="settings-card-title">
                                            <span class="bn">সাধারণ জিজ্ঞাসা ও এফএকিউ (FAQ Section)</span>
                                            <span class="en">Frequently Asked Questions</span>
                                        </h4>
                                        <p class="settings-card-desc">
                                            <span class="bn">প্রায়শই জিজ্ঞাসিত প্রশ্ন ও উত্তর যুক্ত, সম্পাদনা বা
                                                বাতিল করুন।</span>
                                            <span class="en">Manage questions and detailed answers in the
                                                interactive accordion.</span>
                                        </p>
                                    </div>
                                    <x-core::button size="sm" variant="secondary" type="button" icon="plus"
                                        id="addFaqBtn">
                                        <span class="bn">নতুন প্রশ্ন যোগ করুন</span>
                                        <span class="en">Add FAQ</span>
                                    </x-core::button>
                                </div>
                            </div>

                            <div class="grid-2">
                                <x-core::input name="faq_badge_bn" label="সেকশন ব্যাজ (বাংলা)" size="sm"
                                    :value="old('faq_badge_bn', $settings['faq_badge_bn'])" />
                                <x-core::input name="faq_title_bn" label="সেকশন শিরোনাম (বাংলা)" size="sm"
                                    :value="old('faq_title_bn', $settings['faq_title_bn'])" />
                            </div>

                            <div id="faqsListWrap"
                                style="margin-top:24px; display:flex; flex-direction:column; gap:12px;">
                                @foreach ($settings['faqs_list'] as $i => $faq)
                                    @php
                                        $qBn = $faq['question_bn'] ?? '';
                                        $faqTitle =
                                            'প্রশ্ন #' .
                                            ($i + 1) .
                                            ($qBn ? ': ' . \Illuminate\Support\Str::limit($qBn, 65) : '');
                                    @endphp
                                    <x-core::accordion :open="$loop->first" :title="$faqTitle" icon="help-circle"
                                        class="faq-item" card>
                                        <x-slot:actions>
                                            <x-core::button size="sm" color="danger" variant="soft"
                                                type="button" icon="trash-2" class="delete-row-btn"
                                                title="মুছে ফেলুন" />
                                        </x-slot:actions>

                                        <div style="margin-bottom:12px;">
                                            <x-core::input name="faqs_list[{{ $i }}][question_bn]"
                                                label="প্রশ্ন (বাংলা)" size="sm" :value="$faq['question_bn'] ?? ''" />
                                        </div>
                                        <div style="margin-bottom:12px;">
                                            <x-core::input name="faqs_list[{{ $i }}][question_en]"
                                                label="Question (English)" size="sm" :value="$faq['question_en'] ?? ''" />
                                        </div>
                                        <div style="margin-bottom:12px;">
                                            <x-core::textarea name="faqs_list[{{ $i }}][answer_bn]"
                                                label="উত্তর (বাংলা)" size="sm" rows="3"
                                                :value="$faq['answer_bn'] ?? ''" />
                                        </div>
                                        <div>
                                            <x-core::textarea name="faqs_list[{{ $i }}][answer_en]"
                                                label="Answer (English)" size="sm" rows="3"
                                                :value="$faq['answer_en'] ?? ''" />
                                        </div>
                                    </x-core::accordion>
                                @endforeach
                            </div>

                            <div class="card-footer-action">
                                <x-core::button color="primary" size="sm" type="submit" icon="check">
                                    <span class="bn">সংরক্ষণ করুন</span>
                                    <span class="en">Save Changes</span>
                                </x-core::button>
                            </div>
                        </div>
                    </div>

                    {{-- 9. CTA & Footer Tab --}}
                    <div class="tab-pane {{ ($activeTab ?? '') === 'cta' ? 'active' : '' }}" id="tab-cta">
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <h4 class="settings-card-title">
                                    <span class="bn">চূড়ান্ত কল-টু-অ্যাকশন ও ফুটার (CTA & Social)</span>
                                    <span class="en">Final Call-to-Action & Footer Links</span>
                                </h4>
                                <p class="settings-card-desc">
                                    <span class="bn">পেজের শেষের রূপান্তর ব্যানার ও সোশ্যাল মিডিয়া
                                        লিংকসমূহ।</span>
                                    <span class="en">Bottom conversion banner, phone CTA, and social media
                                        handles.</span>
                                </p>
                            </div>

                            <div class="grid-2">
                                <div>
                                    <x-core::input name="cta_title_bn" label="ব্যানার শিরোনাম (বাংলা)" size="sm"
                                        :value="old('cta_title_bn', $settings['cta_title_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="cta_title_en" label="Banner Title (English)" size="sm"
                                        :value="old('cta_title_en', $settings['cta_title_en'])" />
                                </div>
                            </div>
                            <div style="margin-top:16px;">
                                <x-core::textarea name="cta_subtitle_bn" label="ব্যানার সাব-টাইটেল (বাংলা)"
                                    size="sm" rows="2" :value="old('cta_subtitle_bn', $settings['cta_subtitle_bn'])" />
                            </div>

                            <div class="grid-2" style="margin-top:16px;">
                                <div>
                                    <x-core::input name="cta_btn_text_bn" label="বাটন টেক্সট (বাংলা)" size="sm"
                                        :value="old('cta_btn_text_bn', $settings['cta_btn_text_bn'])" />
                                </div>
                                <div>
                                    <x-core::input name="cta_btn_url" label="বাটন লিংক / URL" size="sm"
                                        :value="old('cta_btn_url', $settings['cta_btn_url'])" />
                                </div>
                                <div>
                                    <x-core::input name="cta_phone_btn_text" label="কল বাটন টেক্সট" size="sm"
                                        :value="old('cta_phone_btn_text', $settings['cta_phone_btn_text'])" />
                                </div>
                                <div>
                                    <x-core::input name="social_whatsapp" label="WhatsApp লিংক" size="sm"
                                        :value="old('social_whatsapp', $settings['social_whatsapp'])" placeholder="https://wa.me/8801886861430" />
                                </div>
                                <div>
                                    <x-core::input name="social_facebook" label="Facebook পেজ লিংক" size="sm"
                                        :value="old('social_facebook', $settings['social_facebook'])" placeholder="https://facebook.com/..." />
                                </div>
                                <div>
                                    <x-core::input name="social_youtube" label="YouTube চ্যানেল লিংক" size="sm"
                                        :value="old('social_youtube', $settings['social_youtube'])" placeholder="https://youtube.com/..." />
                                </div>
                            </div>

                            <div style="margin-top:16px;">
                                <x-core::textarea name="footer_about_bn" label="ফুটার বিবরণ (বাংলা)" size="sm"
                                    rows="2" :value="old('footer_about_bn', $settings['footer_about_bn'])" />
                            </div>

                            <div class="card-footer-action">
                                <x-core::button color="primary" size="sm" type="submit" icon="check">
                                    <span class="bn">সংরক্ষণ করুন</span>
                                    <span class="en">Save Changes</span>
                                </x-core::button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .settings-layout-grid {
                display: grid;
                grid-template-columns: 280px 1fr;
                gap: 22px;
                align-items: start;
            }

            .settings-nav-sidebar {
                position: sticky;
                top: 20px;
                z-index: 10;
            }

            .settings-nav-card {
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: 14px;
                padding: 10px;
                box-shadow: var(--shadow-card);
            }

            .settings-nav-header {
                padding: 8px 12px 12px;
                margin-bottom: 6px;
                border-bottom: 1px solid var(--border);
            }

            .settings-nav-header-title {
                font-size: 13.5px;
                font-weight: 700;
                color: var(--ink-900);
                font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif;
            }

            .settings-nav-header-sub {
                font-size: 11.5px;
                color: var(--ink-500);
                margin-top: 2px;
                font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif;
            }

            .settings-nav-list {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .tab-btn {
                display: flex;
                align-items: center;
                gap: 10px;
                width: 100%;
                padding: 10px 12px;
                font-size: 13px;
                font-weight: 600;
                border-radius: 9px;
                border: 1px solid transparent;
                background: transparent;
                color: var(--ink-700);
                cursor: pointer;
                transition: all 0.15s ease;
                text-align: left;
                font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif;
                text-decoration: none;
                box-sizing: border-box;
            }

            .tab-btn .tab-label {
                flex: 1;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .tab-btn .tab-arrow {
                color: var(--ink-400);
                opacity: 0.4;
                transition: transform 0.15s ease, opacity 0.15s ease;
                flex-shrink: 0;
            }

            .tab-btn:hover {
                color: var(--ink-900);
                background: var(--paper);
                border-color: var(--border);
            }

            .tab-btn:hover .tab-arrow {
                opacity: 1;
                transform: translateX(2px);
                color: var(--ink-700);
            }

            .tab-btn.active {
                background: var(--teal-100);
                color: var(--teal-800) !important;
                border-color: var(--teal-600);
                box-shadow: var(--shadow-sm);
                font-weight: 700;
            }

            .tab-btn.active svg,
            .tab-btn.active .tab-label,
            .tab-btn.active .tab-arrow {
                color: var(--teal-800) !important;
                stroke: var(--teal-800);
                opacity: 1;
            }

            .settings-content-pane {
                min-width: 0;
                flex: 1;
            }

            .tab-pane {
                display: none;
            }

            .tab-pane.active {
                display: block;
                animation: fadeInTab 0.18s ease;
            }

            @keyframes fadeInTab {
                from {
                    opacity: 0;
                    transform: translateY(3px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .settings-card {
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: 14px;
                padding: 24px 28px;
                box-shadow: var(--shadow-card);
            }

            .settings-card-header {
                margin-bottom: 20px;
                padding-bottom: 16px;
                border-bottom: 1px solid var(--border);
            }

            .settings-card-title {
                margin: 0 0 6px;
                font-size: 16px;
                font-weight: 700;
                color: var(--ink-900);
                font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif;
            }

            .settings-card-desc {
                margin: 0;
                font-size: 13px;
                color: var(--ink-600);
            }

            .grid-2 {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            .item-box {
                background: var(--paper);
                border: 1px solid var(--border);
                border-radius: 10px;
                padding: 16px;
            }

            #reviewsListWrap .app-accordion,
            #faqsListWrap .app-accordion {
                margin-top: 0;
                border-radius: 12px;
                padding: 14px 18px;
            }

            #reviewsListWrap .app-accordion-header,
            #faqsListWrap .app-accordion-header {
                padding: 2px 0;
            }

            #reviewsListWrap .app-accordion-title,
            #faqsListWrap .app-accordion-title {
                font-weight: 700;
                font-size: 14px;
                color: var(--ink-900);
                font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Plus Jakarta Sans', sans-serif;
            }

            #reviewsListWrap .app-accordion-icon svg,
            #faqsListWrap .app-accordion-icon svg,
            #reviewsListWrap .app-accordion-icon .app-icon,
            #faqsListWrap .app-accordion-icon .app-icon {
                width: 16px !important;
                height: 16px !important;
                max-width: 16px !important;
                max-height: 16px !important;
            }

            #reviewsListWrap .app-accordion-toggle-icon svg,
            #faqsListWrap .app-accordion-toggle-icon svg,
            #reviewsListWrap .app-accordion-toggle-icon .app-icon,
            #faqsListWrap .app-accordion-toggle-icon .app-icon {
                width: 16px !important;
                height: 16px !important;
                max-width: 16px !important;
                max-height: 16px !important;
            }

            .card-footer-action {
                margin-top: 24px;
                padding-top: 18px;
                border-top: 1px solid var(--border);
                display: flex;
                justify-content: flex-end;
            }

            @media (max-width: 992px) {
                .settings-layout-grid {
                    grid-template-columns: 1fr;
                }

                .settings-nav-sidebar {
                    position: static;
                }

                .settings-nav-list {
                    flex-direction: row;
                    overflow-x: auto;
                    padding-bottom: 6px;
                    scrollbar-width: thin;
                }

                .settings-nav-list::-webkit-scrollbar {
                    height: 4px;
                }

                .settings-nav-list::-webkit-scrollbar-thumb {
                    background: var(--border);
                    border-radius: 4px;
                }

                .tab-btn {
                    width: auto;
                    white-space: nowrap;
                    flex-shrink: 0;
                }

                .tab-btn .tab-arrow {
                    display: none;
                }
            }

            @media (max-width: 768px) {
                .grid-2 {
                    grid-template-columns: 1fr;
                }

                .settings-card {
                    padding: 18px 16px;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(function() {
                // 1. Tab switching
                $('.tab-btn').on('click', function() {
                    const target = $(this).data('tab');
                    if (!target) return;

                    $('.tab-btn').removeClass('active').attr('aria-selected', 'false');
                    $(this).addClass('active').attr('aria-selected', 'true');

                    $('.tab-pane').removeClass('active');
                    $('#tab-' + target).addClass('active');

                    $('#activeTabInput').val(target);

                    if (history.replaceState) {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', target);
                        window.history.replaceState({}, '', url);
                    }
                });

                // 2. Tab initialization from query or hash
                const urlParams = new URLSearchParams(window.location.search);
                const queryTab = urlParams.get('tab');
                const hashTab = window.location.hash.replace('#', '');
                const targetTab = queryTab || hashTab;
                if (targetTab && $('#tab-' + targetTab).length) {
                    $('.tab-btn[data-tab="' + targetTab + '"]').trigger('click');
                }

                // 3. AJAX Landing Toggle
                $('#landing_page_toggle').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $('#hiddenLandingEnabled').val(isChecked ? '1' : '0');

                    $.ajax({
                        url: "{{ route('system-settings.toggle-landing') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            state: isChecked ? 1 : 0
                        },
                        success: function(res) {
                            if (res.success) {
                                if (window.toast) {
                                    window.toast(res.message, res.message);
                                }
                                const badge = $('#landingStatusBadge');
                                if (res.enabled) {
                                    badge.removeClass('badge-grey').addClass('badge-green');
                                    badge.find('.bn').text('চালু আছে (Active)');
                                    badge.find('.en').text('Active');
                                } else {
                                    badge.removeClass('badge-green').addClass('badge-grey');
                                    badge.find('.bn').text('বন্ধ আছে (Disabled)');
                                    badge.find('.en').text('Disabled');
                                }
                            }
                        },
                        error: function() {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ত্রুটি',
                                    text: 'সেটিংস পরিবর্তন করতে ব্যর্থ হয়েছে।'
                                });
                            }
                        }
                    });
                });

                // 3b. AJAX Registration Toggle
                $('#registration_toggle').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $('#hiddenRegistrationEnabled').val(isChecked ? '1' : '0');

                    $.ajax({
                        url: "{{ route('system-settings.toggle-registration') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            state: isChecked ? 1 : 0
                        },
                        success: function(res) {
                            if (res.success) {
                                if (window.toast) {
                                    window.toast(res.message, res.message);
                                }
                                const badge = $('#registrationStatusBadge');
                                if (res.enabled) {
                                    badge.removeClass('badge-grey').addClass('badge-green');
                                    badge.find('.bn').text('চালু আছে (Active)');
                                    badge.find('.en').text('Active');
                                } else {
                                    badge.removeClass('badge-green').addClass('badge-grey');
                                    badge.find('.bn').text('বন্ধ আছে (Disabled)');
                                    badge.find('.en').text('Disabled');
                                }
                            }
                        },
                        error: function() {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ত্রুটি',
                                    text: 'রেজিস্ট্রেশন সেটিংস পরিবর্তন করতে ব্যর্থ হয়েছে।'
                                });
                            }
                        }
                    });
                });

                // 3c. AJAX Terms & Policy Toggle
                $('#terms_policy_toggle').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $('#hiddenTermsPolicyEnabled').val(isChecked ? '1' : '0');

                    $.ajax({
                        url: "{{ route('system-settings.toggle-terms-policy') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            state: isChecked ? 1 : 0
                        },
                        success: function(res) {
                            if (res.success) {
                                if (window.toast) {
                                    window.toast(res.message, res.message);
                                }
                                const badge = $('#termsStatusBadge');
                                if (res.enabled) {
                                    badge.removeClass('badge-grey').addClass('badge-green');
                                    badge.find('.bn').text('চালু আছে (Active)');
                                    badge.find('.en').text('Active');
                                } else {
                                    badge.removeClass('badge-green').addClass('badge-grey');
                                    badge.find('.bn').text('বন্ধ আছে (Disabled)');
                                    badge.find('.en').text('Disabled');
                                }
                            }
                        },
                        error: function() {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ত্রুটি',
                                    text: 'সেটিংস পরিবর্তন করতে ব্যর্থ হয়েছে।'
                                });
                            }
                        }
                    });
                });

                // 3d. AJAX Credit Text Toggle
                $('#credit_text_toggle').on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $('#hiddenCreditTextEnabled').val(isChecked ? '1' : '0');

                    // Toggle input & save button enabled state
                    if (isChecked) {
                        $('#creditTextInputWrap').css({ opacity: 1, 'pointer-events': 'auto' });
                        $('#saveCreditTextBtn').css({ opacity: 1, 'pointer-events': 'auto' });
                    } else {
                        $('#creditTextInputWrap').css({ opacity: 0.5, 'pointer-events': 'none' });
                        $('#saveCreditTextBtn').css({ opacity: 0.5, 'pointer-events': 'none' });
                    }

                    $.ajax({
                        url: "{{ route('system-settings.toggle-credit-text') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            state: isChecked ? 1 : 0
                        },
                        success: function(res) {
                            if (res.success) {
                                if (window.toast) {
                                    window.toast(res.message, res.message);
                                }
                                const badge = $('#creditStatusBadge');
                                if (res.enabled) {
                                    badge.removeClass('badge-grey').addClass('badge-green');
                                    badge.find('.bn').text('চালু আছে (Active)');
                                    badge.find('.en').text('Active');
                                } else {
                                    badge.removeClass('badge-green').addClass('badge-grey');
                                    badge.find('.bn').text('বন্ধ আছে (Disabled)');
                                    badge.find('.en').text('Disabled');
                                }
                            }
                        },
                        error: function() {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ত্রুটি',
                                    text: 'ক্রেডিট টেক্সট সেটিংস পরিবর্তন করতে ব্যর্থ হয়েছে।'
                                });
                            }
                        }
                    });
                });

                // 3e. Save Credit Text via AJAX
                $('#saveCreditTextBtn').on('click', function() {
                    const creditText = $('input[name="credit_text"]').val();

                    $.ajax({
                        url: "{{ route('system-settings.update') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            credit_text: creditText
                        },
                        success: function() {
                            if (window.toast) {
                                window.toast(
                                    'ক্রেডিট টেক্সট সংরক্ষণ করা হয়েছে (Credit text saved)',
                                    'ক্রেডিট টেক্সট সংরক্ষণ করা হয়েছে (Credit text saved)'
                                );
                            }
                        },
                        error: function() {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ত্রুটি',
                                    text: 'ক্রেডিট টেক্সট সংরক্ষণ করতে ব্যর্থ হয়েছে।'
                                });
                            }
                        }
                    });
                });

                // 4. Dynamic FAQ Add (Accordion)
                $('#addFaqBtn').on('click', function() {
                    const count = $('#faqsListWrap .faq-item').length;
                    const num = count + 1;
                    const html = `
                    <div class="app-accordion feature-box is-open active app-accordion-card accordion-teal faq-item" data-accordion style="animation:fadeInTab 0.2s ease;">
                        <div class="app-accordion-header feature-box-toggle" data-accordion-trigger>
                            <div class="app-accordion-title-wrap">
                                <div class="app-accordion-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                                        <line x1="12" x2="12.01" y1="17" y2="17"/>
                                    </svg>
                                </div>
                                <div class="app-accordion-text">
                                    <span class="app-accordion-title">নতুন প্রশ্ন #${num}</span>
                                </div>
                            </div>
                            <div class="app-accordion-actions">
                                <button class="btn btn-soft btn-soft-red btn-sm delete-row-btn" type="button" title="মুছে ফেলুন">
                                    <span class="btn-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon">
                                            <path d="M3 6h18"/>
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            <line x1="10" x2="10" y1="11" y2="17"/>
                                            <line x1="14" x2="14" y1="11" y2="17"/>
                                        </svg>
                                    </span>
                                </button>
                                <span class="app-accordion-toggle-icon toggle-icon" data-accordion-icon>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon">
                                        <path d="m6 9 6 6 6-6"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="app-accordion-body" data-accordion-content>
                            <div style="margin-bottom:12px;">
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">প্রশ্ন (বাংলা)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="faqs_list[${count}][question_bn]" class="form-control form-control-outline form-control-sm" placeholder="প্রশ্ন লিখুন" />
                                    </div>
                                </div>
                            </div>
                            <div style="margin-bottom:12px;">
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">Question (English)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="faqs_list[${count}][question_en]" class="form-control form-control-outline form-control-sm" placeholder="Enter question in English" />
                                    </div>
                                </div>
                            </div>
                            <div style="margin-bottom:12px;">
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">উত্তর (বাংলা)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <textarea name="faqs_list[${count}][answer_bn]" rows="3" class="form-control form-textarea form-control-outline form-control-sm" placeholder="বিস্তারিত উত্তর লিখুন"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">Answer (English)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <textarea name="faqs_list[${count}][answer_en]" rows="3" class="form-control form-textarea form-control-outline form-control-sm" placeholder="Enter detailed answer"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    $('#faqsListWrap').append(html);
                });

                // 5. Dynamic Review Add (Accordion)
                $('#addReviewBtn').on('click', function() {
                    const count = $('#reviewsListWrap .review-item').length;
                    const num = count + 1;
                    const html = `
                    <div class="app-accordion feature-box is-open active app-accordion-card accordion-teal review-item" data-accordion style="animation:fadeInTab 0.2s ease;">
                        <div class="app-accordion-header feature-box-toggle" data-accordion-trigger>
                            <div class="app-accordion-title-wrap">
                                <div class="app-accordion-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                </div>
                                <div class="app-accordion-text">
                                    <span class="app-accordion-title">নতুন রিভিউ #${num}</span>
                                </div>
                            </div>
                            <div class="app-accordion-actions">
                                <button class="btn btn-soft btn-soft-red btn-sm delete-row-btn" type="button" title="মুছে ফেলুন">
                                    <span class="btn-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon">
                                            <path d="M3 6h18"/>
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            <line x1="10" x2="10" y1="11" y2="17"/>
                                            <line x1="14" x2="14" y1="11" y2="17"/>
                                        </svg>
                                    </span>
                                </button>
                                <span class="app-accordion-toggle-icon toggle-icon" data-accordion-icon>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon">
                                        <path d="m6 9 6 6 6-6"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="app-accordion-body" data-accordion-content>
                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">ব্যবসায়ীর নাম (বাংলা)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="reviews_list[${count}][author]" class="form-control form-control-outline form-control-sm" placeholder="যেমনঃ মোঃ রফিকুল ইসলাম" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">Author Name (English)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="reviews_list[${count}][author_en]" class="form-control form-control-outline form-control-sm" placeholder="e.g. Md. Rafiqul Islam" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">প্রতিষ্ঠানের নাম (বাংলা)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="reviews_list[${count}][shop]" class="form-control form-control-outline form-control-sm" placeholder="যেমনঃ আল-মদিনা স্টোর" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">Shop Name (English)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="reviews_list[${count}][shop_en]" class="form-control form-control-outline form-control-sm" placeholder="e.g. Al-Madina Departmental Store" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">শহর / এলাকা (বাংলা)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="reviews_list[${count}][city]" class="form-control form-control-outline form-control-sm" placeholder="যেমনঃ রাজশাহী" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">City / Area (English)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="reviews_list[${count}][city_en]" class="form-control form-control-outline form-control-sm" placeholder="e.g. Rajshahi" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">অবতার আদ্যক্ষর (বাংলা)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="reviews_list[${count}][initials]" class="form-control form-control-outline form-control-sm" placeholder="যেমনঃ র" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">Avatar Initial (English)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="text" name="reviews_list[${count}][initials_en]" class="form-control form-control-outline form-control-sm" placeholder="e.g. R" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">রেটিং (1 - 5)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <input type="number" min="1" max="5" name="reviews_list[${count}][rating]" value="5" class="form-control form-control-outline form-control-sm" />
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">মন্তব্য / কোটেশন (বাংলা)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <textarea name="reviews_list[${count}][quote_bn]" rows="2" class="form-control form-textarea form-control-outline form-control-sm" placeholder="গ্রাহকের মন্তব্য লিখুন"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                <div class="form-group">
                                    <label class="form-label form-label-sm" style="color:var(--ink-700);">Quote (English)</label>
                                    <div class="form-input-group form-input-group-sm">
                                        <textarea name="reviews_list[${count}][quote_en]" rows="2" class="form-control form-textarea form-control-outline form-control-sm" placeholder="Customer quote in English"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    $('#reviewsListWrap').append(html);
                });

                // 6. Live Accordion Header Title Updates
                $(document).on('input', '.review-item input[name$="[author]"], .review-item input[name$="[shop]"]',
                    function() {
                        const $item = $(this).closest('.review-item');
                        const idx = $item.index() + 1;
                        const author = $item.find('input[name$="[author]"]').val().trim();
                        const shop = $item.find('input[name$="[shop]"]').val().trim();
                        let title = 'রিভিউ #' + idx;
                        if (author) title += ': ' + author;
                        if (shop) title += ' (' + shop + ')';
                        $item.find('.app-accordion-title').text(title);
                    });

                $(document).on('input', '.faq-item input[name*="[question_bn]"]', function() {
                    const $item = $(this).closest('.faq-item');
                    const idx = $item.index() + 1;
                    const q = $(this).val().trim();
                    let title = 'প্রশ্ন #' + idx;
                    if (q) title += ': ' + (q.length > 65 ? q.substring(0, 65) + '...' : q);
                    $item.find('.app-accordion-title').text(title);
                });

                // 7. Delete Row Handler
                $(document).on('click', '.delete-row-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const $item = $(this).closest('.review-item, .faq-item, .item-box, [data-accordion]');
                    $item.slideUp(150, function() {
                        $item.remove();
                        // Re-index Review Titles
                        $('#reviewsListWrap .review-item').each(function(idx) {
                            const author = $(this).find('input[name$="[author]"]').val()
                            ?.trim() || '';
                            const shop = $(this).find('input[name$="[shop]"]').val()?.trim() ||
                                '';
                            let text = 'রিভিউ #' + (idx + 1);
                            if (author) text += ': ' + author;
                            if (shop) text += ' (' + shop + ')';
                            $(this).find('.app-accordion-title').text(text);
                        });
                        // Re-index FAQ Titles
                        $('#faqsListWrap .faq-item').each(function(idx) {
                            const q = $(this).find('input[name*="[question_bn]"]').val()
                            ?.trim() || '';
                            let text = 'প্রশ্ন #' + (idx + 1);
                            if (q) text += ': ' + (q.length > 65 ? q.substring(0, 65) + '...' :
                                q);
                            $(this).find('.app-accordion-title').text(text);
                        });
                    });
                });
            });
        </script>
    @endpush
</x-core::layout>
