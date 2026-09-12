<x-core::auth-layout
    title="নতুন দোকান রেজিস্টার"
    title-en="Register Shop"
    card-title="নতুন দোকান রেজিস্টার করুন"
    card-title-en="Register Your Shop"
    card-subtitle="সহজে ৩টি ধাপে আপনার অ্যাকাউন্ট, দোকান এবং ফ্রি প্যাকেজ চালু করুন"
    card-subtitle-en="Quickly set up your account, shop, and free package in 3 simple steps"
    max-width="660px"
    :show-theme-switcher="false"
    default-theme="dark"
>
    <style>
        .stepper-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            margin-bottom: 24px;
            padding: 0 8px;
        }
        .stepper-line {
            position: absolute;
            top: 18px;
            left: 36px;
            right: 36px;
            height: 2px;
            background: var(--paper-line);
            z-index: 1;
        }
        .stepper-line-fill {
            position: absolute;
            top: 18px;
            left: 36px;
            height: 2px;
            background: var(--teal-800);
            z-index: 2;
            transition: width 0.3s ease;
            width: 0%;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 3;
            cursor: pointer;
        }
        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            background: var(--card);
            border: 2px solid var(--border);
            color: var(--ink-500);
            transition: all 0.25s ease;
        }
        .step-item.active .step-circle {
            border-color: var(--teal-800);
            background: var(--teal-800);
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.18);
        }
        .step-item.completed .step-circle {
            border-color: var(--teal-800);
            background: var(--teal-100);
            color: var(--teal-800);
        }
        .step-label {
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
            color: var(--ink-500);
            transition: color 0.25s ease;
        }
        .step-item.active .step-label {
            color: var(--teal-800);
            font-weight: 700;
        }
        .step-item.completed .step-label {
            color: var(--ink-700);
        }
        .step-pane {
            display: none;
            animation: fadeIn 0.25s ease forwards;
        }
        .step-pane.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .step-footer-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            gap: 12px;
        }
        .free-plan-card {
            background: var(--paper);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            margin-top: 16px;
            transition: border-color 0.2s;
        }
        .free-plan-card:hover {
            border-color: var(--teal-800);
        }
        .plan-feature-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--ink-700);
            margin-bottom: 6px;
        }
        /* In register wizard, let the grid handle all inter-field vertical gaps uniformly */
        #register-wizard-form .form-group {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .availability-status {
            font-size: 11.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .availability-status:empty {
            display: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .availability-status:not(:empty) {
            margin-top: 4px;
        }
        .text-valid {
            color: var(--green-600, #10b981);
        }
        .text-invalid {
            color: var(--red-600, #ef4444);
        }
        .client-error-message {
            color: var(--red-600, #ef4444);
            font-size: 11.5px;
            font-weight: 600;
            margin-top: 4px;
            display: none;
        }

        /* Responsive Form Grids */
        .reg-grid {
            display: grid;
            gap: 14px;
        }
        .reg-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .reg-grid-slug {
            grid-template-columns: 1.25fr 0.75fr;
        }
        .reg-col-full {
            grid-column: 1 / -1;
        }
        .reg-field-item {
            display: flex;
            flex-direction: column;
            width: 100%;
        }
        .free-plan-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .reg-plan-features-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
            margin-top: 10px;
        }

        /* Responsive Breakpoint for Mobile & Tablet */
        @media (max-width: 640px) {
            .stepper-header {
                margin-bottom: 18px;
                padding: 0 4px;
            }
            .stepper-line,
            .stepper-line-fill {
                left: 20px;
                right: 20px;
                top: 16px;
            }
            .step-circle {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }
            .step-label {
                font-size: 11px;
                margin-top: 4px;
            }
            .reg-grid {
                gap: 14px;
            }
            .reg-grid-2,
            .reg-grid-slug {
                grid-template-columns: 1fr;
            }
            .reg-plan-features-grid {
                grid-template-columns: 1fr;
                gap: 5px;
            }
            .step-footer-actions {
                flex-direction: column-reverse;
                align-items: stretch;
                gap: 10px;
                margin-top: 20px;
                padding-top: 14px;
            }
            .step-footer-actions > * {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
            .step-footer-actions a {
                justify-content: center;
                padding: 6px 0;
            }
            .free-plan-card {
                padding: 12px;
            }
        }
    </style>

    {{-- Stepper Progress Bar --}}
    <div class="stepper-header">
        <div class="stepper-line"></div>
        <div class="stepper-line-fill" id="stepper-fill"></div>

        <div class="step-item active" id="step-nav-1" data-step="1">
            <div class="step-circle" id="step-circle-1">১</div>
            <div class="step-label">
                <span class="bn">আপনার তথ্য</span>
                <span class="en" style="display:none;">Your Info</span>
            </div>
        </div>

        <div class="step-item" id="step-nav-2" data-step="2">
            <div class="step-circle" id="step-circle-2">২</div>
            <div class="step-label">
                <span class="bn">দোকানের বিবরণ</span>
                <span class="en" style="display:none;">Shop Details</span>
            </div>
        </div>

        <div class="step-item" id="step-nav-3" data-step="3">
            <div class="step-circle" id="step-circle-3">৩</div>
            <div class="step-label">
                <span class="bn">সেটআপ ও প্ল্যান</span>
                <span class="en" style="display:none;">Setup & Plan</span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="auth-error">
            <x-core::icon name="alert-triangle" size="14" />
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" id="register-wizard-form" autocomplete="off">
        @csrf

        {{-- ======================================================== --}}
        {{-- STEP 1: OWNER ACCOUNT INFORMATION                         --}}
        {{-- ======================================================== --}}
        <div class="step-pane active" id="step-pane-1">
            <div style="font-size:13px; font-weight:700; color:var(--ink-900); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <x-core::icon name="user" size="16" style="color:var(--teal-800);" />
                <span class="bn">ধাপ ১: দোকান মালিকের লগইন ও ব্যক্তিগত তথ্য</span>
                <span class="en" style="display:none;">Step 1: Shop Owner Credentials & Personal Info</span>
            </div>

            <div class="reg-grid reg-grid-2">
                <div class="reg-col-full reg-field-item">
                    <x-core::input
                        name="name"
                        id="reg-name"
                        label="আপনার পুরো নাম"
                        label-en="Full Name"
                        placeholder="যেমন: মোঃ রফিকুল ইসলাম"
                        placeholder-en="e.g. Rafiqul Islam"
                        icon="user"
                        size="sm"
                        :value="old('name')"
                        required
                    />
                    <div class="client-error-message" id="err-reg-name">নাম প্রদান করা আবশ্যক।</div>
                </div>

                <div class="reg-field-item">
                    <x-core::input
                        type="tel"
                        name="phone"
                        id="reg-phone"
                        label="মোবাইল নম্বর (লগইনে ব্যবহৃত হবে)"
                        label-en="Mobile Number (Used for Login)"
                        placeholder="017xxxxxxxx"
                        placeholder-en="017xxxxxxxx"
                        icon="phone"
                        size="sm"
                        :value="old('phone')"
                        required
                    />
                    <div id="phone-feedback" class="availability-status"></div>
                    <div class="client-error-message" id="err-reg-phone">সঠিক মোবাইল নম্বর প্রদান করা আবশ্যক।</div>
                </div>

                <div class="reg-field-item">
                    <x-core::input
                        type="email"
                        name="email"
                        id="reg-email"
                        label="ইমেইল ঠিকানা (ঐচ্ছিক)"
                        label-en="Email Address (Optional)"
                        placeholder="user@example.com"
                        placeholder-en="user@example.com"
                        icon="mail"
                        size="sm"
                        :value="old('email')"
                    />
                    <div id="email-feedback" class="availability-status"></div>
                </div>

                <div class="reg-col-full reg-field-item">
                    <x-core::input
                        name="username"
                        id="reg-username"
                        label="ইউজারনেম (ইংরেজি)"
                        label-en="Username"
                        placeholder="যেমন: rafiq_pos"
                        placeholder-en="e.g. rafiq_pos"
                        icon="at-sign"
                        size="sm"
                        :value="old('username')"
                    />
                    <div id="username-feedback" class="availability-status"></div>
                </div>

                <div class="reg-field-item">
                    <x-core::input
                        type="password"
                        name="password"
                        id="reg-password"
                        label="পাসওয়ার্ড"
                        label-en="Password"
                        placeholder="কমপক্ষে ৬ অক্ষর"
                        placeholder-en="Min 6 characters"
                        icon="lock"
                        size="sm"
                        password-toggle
                        required
                    />
                    <div class="client-error-message" id="err-reg-password">পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।</div>
                </div>

                <div class="reg-field-item">
                    <x-core::input
                        type="password"
                        name="password_confirmation"
                        id="reg-password-confirmation"
                        label="পাসওয়ার্ড নিশ্চিত করুন"
                        label-en="Confirm Password"
                        placeholder="পাসওয়ার্ডটি পুনরায় লিখুন"
                        placeholder-en="Re-type password"
                        icon="lock"
                        size="sm"
                        password-toggle
                        required
                    />
                    <div class="client-error-message" id="err-reg-password-confirmation">পাসওয়ার্ড নিশ্চিতকরণ মিলছে না।</div>
                </div>
            </div>

            <div class="step-footer-actions">
                <a href="{{ route('login') }}" style="font-size:12.5px; color:var(--ink-600); text-decoration:none; display:flex; align-items:center; gap:6px;">
                    <x-core::icon name="arrow-left" size="14" />
                    <span class="bn">লগইন পেজে যান</span>
                    <span class="en" style="display:none;">Back to Login</span>
                </a>

                <x-core::button
                    type="button"
                    id="btn-step-1-next"
                    color="primary"
                    size="sm"
                    iconRight="arrow-right"
                >
                    <span class="bn">পরবর্তী ধাপ (দোকানের বিবরণ)</span>
                    <span class="en" style="display:none;">Next Step (Shop Details)</span>
                </x-core::button>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- STEP 2: SHOP INFORMATION                                 --}}
        {{-- ======================================================== --}}
        <div class="step-pane" id="step-pane-2">
            <div style="font-size:13px; font-weight:700; color:var(--ink-900); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <x-core::icon name="shopping-bag" size="16" style="color:var(--teal-800);" />
                <span class="bn">ধাপ ২: আপনার দোকানের প্রাথমিক বিবরণ</span>
                <span class="en" style="display:none;">Step 2: Your Shop Information</span>
            </div>

            <div class="reg-grid reg-grid-2">
                <div class="reg-col-full reg-field-item">
                    <x-core::input
                        name="shop_name"
                        id="reg-shop-name"
                        label="দোকানের নাম"
                        label-en="Shop Name"
                        placeholder="যেমন: রফিক জেনারেল স্টোর"
                        placeholder-en="e.g. Rafiq General Store"
                        icon="shopping-bag"
                        size="sm"
                        :value="old('shop_name')"
                        required
                    />
                    <div class="client-error-message" id="err-reg-shop-name">দোকানের নাম প্রদান করা আবশ্যক।</div>
                </div>

                <div class="reg-col-full reg-grid reg-grid-slug" style="padding:0;">
                    <div class="reg-field-item">
                        <x-core::input
                            name="shop_slug"
                            id="reg-shop-slug"
                            label="দোকানের URL / স্লাগ"
                            label-en="Store Slug (URL)"
                            placeholder="যেমন: rafiq-general-store"
                            placeholder-en="e.g. rafiq-general-store"
                            icon="globe"
                            size="sm"
                            :value="old('shop_slug')"
                            required
                        />
                        <div id="slug-feedback" class="availability-status"></div>
                        <div class="client-error-message" id="err-reg-shop-slug">দোকানের স্লাগ আবশ্যক ও শুধুমাত্র ইংরেজি অক্ষর, সংখ্যা এবং হাইফেন প্রযোজ্য।</div>
                    </div>

                    <div class="reg-field-item">
                        <x-core::input
                            name="currency_symbol"
                            id="reg-currency"
                            label="মুদ্রার প্রতীক"
                            label-en="Currency Symbol"
                            placeholder="৳"
                            placeholder-en="৳"
                            size="sm"
                            :value="old('currency_symbol', '৳')"
                        />
                    </div>
                </div>

                <div class="reg-field-item">
                    <x-core::input
                        type="tel"
                        name="shop_phone"
                        id="reg-shop-phone"
                        label="দোকানের হেল্পলাইন / ফোন"
                        label-en="Shop Helpline / Phone"
                        placeholder="017xxxxxxxx"
                        placeholder-en="017xxxxxxxx"
                        icon="phone"
                        size="sm"
                        :value="old('shop_phone')"
                    />
                    <div style="font-size:11px; color:var(--ink-500); margin-top:4px;">খালি রাখলে আপনার ব্যক্তিগত ফোন নম্বরটি ব্যবহৃত হবে</div>
                </div>

                <div class="reg-field-item">
                    <x-core::input
                        name="shop_address"
                        id="reg-shop-address"
                        label="দোকানের ঠিকানা / অবস্থান"
                        label-en="Shop Address"
                        placeholder="যেমন: মিরপুর-১০, ঢাকা"
                        placeholder-en="e.g. Mirpur-10, Dhaka"
                        icon="map-pin"
                        size="sm"
                        :value="old('shop_address')"
                    />
                </div>
            </div>

            <div class="step-footer-actions">
                <x-core::button
                    type="button"
                    id="btn-step-2-back"
                    variant="soft"
                    color="secondary"
                    size="sm"
                    icon="arrow-left"
                >
                    <span class="bn">পূর্ববর্তী ধাপ</span>
                    <span class="en" style="display:none;">Previous Step</span>
                </x-core::button>

                <x-core::button
                    type="button"
                    id="btn-step-2-next"
                    color="primary"
                    size="sm"
                    icon="arrow-right"
                >
                    <span class="bn">পরবর্তী ধাপ (সেটআপ ও প্ল্যান)</span>
                    <span class="en" style="display:none;">Next Step (Setup & Plan)</span>
                </x-core::button>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- STEP 3: INITIAL SETUP & FREE PACKAGE                     --}}
        {{-- ======================================================== --}}
        <div class="step-pane" id="step-pane-3">
            <div style="font-size:13px; font-weight:700; color:var(--ink-900); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <x-core::icon name="settings" size="16" style="color:var(--teal-800);" />
                <span class="bn">ধাপ ৩: প্রাথমিক শাখা, গুদাম ও ফ্রি প্যাকেজ কনফার্মেশন</span>
                <span class="en" style="display:none;">Step 3: Initial Branch, Warehouse & Free Package Confirmation</span>
            </div>

            <div class="reg-grid reg-grid-2">
                <div class="reg-field-item">
                    <x-core::input
                        name="branch_name"
                        id="reg-branch-name"
                        label="প্রধান শাখার নাম"
                        label-en="Main Branch Name"
                        placeholder="প্রধান শাখা"
                        placeholder-en="Main Branch"
                        icon="home"
                        size="sm"
                        :value="old('branch_name', 'প্রধান শাখা')"
                    />
                </div>

                <div class="reg-field-item">
                    <x-core::input
                        name="warehouse_name"
                        id="reg-warehouse-name"
                        label="প্রধান গুদামের নাম"
                        label-en="Main Warehouse Name"
                        placeholder="প্রধান গুদাম"
                        placeholder-en="Main Warehouse"
                        icon="archive"
                        size="sm"
                        :value="old('warehouse_name', 'প্রধান গুদাম')"
                    />
                </div>

                <div class="reg-col-full reg-field-item">
                    <x-core::input
                        type="number"
                        name="opening_cash_balance"
                        id="reg-opening-cash"
                        label="প্রারম্ভিক ক্যাশ ব্যালেন্স (ক্যাশ ড্রয়ারে থাকা টাকা, ঐচ্ছিক)"
                        label-en="Opening Cash Balance (Optional)"
                        placeholder="0.00"
                        placeholder-en="0.00"
                        icon="dollar-sign"
                        size="sm"
                        step="0.01"
                        min="0"
                        :value="old('opening_cash_balance', '0')"
                    />
                    <div style="font-size:11px; color:var(--ink-500); margin-top:4px;">ক্যাশ ড্রয়ারে শুরুতে কোনো নগদ টাকা থাকলে তা লিখুন, অন্যথায় ০ রাখতে পারেন।</div>
                </div>
            </div>

            {{-- Free Package Card --}}
            <div class="free-plan-card">
                <div class="free-plan-card-header">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                            <x-core::icon name="gift" size="18" />
                        </div>
                        <div>
                            <div style="font-size:14px; font-weight:700; color:var(--ink-900);">
                                {{ $freePlan?->name ?? 'ফ্রি প্ল্যান (Free Plan)' }}
                            </div>
                            <div style="font-size:11px; color:var(--ink-500);">
                                আজীবন বিনামূল্যে ব্যবহারের সুযোগ (Lifetime Free)
                            </div>
                        </div>
                    </div>
                    <x-core::badge color="green" size="sm" rounded>
                        ৳০ / আজীবন
                    </x-core::badge>
                </div>

                <div class="reg-plan-features-grid">
                    <div class="plan-feature-row">
                        <x-core::icon name="check" size="14" style="color:var(--green-600, #10b981);" />
                        <span>১ জন ইউজার / এডমিন</span>
                    </div>
                    <div class="plan-feature-row">
                        <x-core::icon name="check" size="14" style="color:var(--green-600, #10b981);" />
                        <span>১টি শাখা ও ১টি গুদাম</span>
                    </div>
                    <div class="plan-feature-row">
                        <x-core::icon name="check" size="14" style="color:var(--green-600, #10b981);" />
                        <span>১০০ টি পণ্য ক্যাটালগ</span>
                    </div>
                    <div class="plan-feature-row">
                        <x-core::icon name="check" size="14" style="color:var(--green-600, #10b981);" />
                        <span>দ্রুত বেচা (POS) ও ক্রয় রসিদ</span>
                    </div>
                    <div class="plan-feature-row">
                        <x-core::icon name="check" size="14" style="color:var(--green-600, #10b981);" />
                        <span>স্টক ট্র্যাকিং ও ক্যাশবক্স</span>
                    </div>
                    <div class="plan-feature-row">
                        <x-core::icon name="check" size="14" style="color:var(--green-600, #10b981);" />
                        <span>গ্রাহক ও বাকি খাতা</span>
                    </div>
                </div>

                <div style="margin-top:12px; padding-top:10px; border-top:1px dashed var(--border); font-size:11.5px; color:var(--ink-500); display:flex; align-items:center; gap:6px;">
                    <x-core::icon name="info" size="14" style="color:var(--teal-800);" />
                    <span>দোকান তৈরির সাথে সাথে ফ্রি প্যাকেজটি তাৎক্ষণিকভাবে সক্রিয় হয়ে যাবে। পরবর্তীতে যে কোনো সময় আপগ্রেড করতে পারবেন।</span>
                </div>
            </div>

            <div class="step-footer-actions">
                <x-core::button
                    type="button"
                    id="btn-step-3-back"
                    variant="soft"
                    color="secondary"
                    size="sm"
                    icon="arrow-left"
                >
                    <span class="bn">পূর্ববর্তী ধাপ</span>
                    <span class="en" style="display:none;">Previous Step</span>
                </x-core::button>

                <x-core::button
                    type="submit"
                    id="btn-submit-registration"
                    color="primary"
                    size="sm"
                    icon="check-circle"
                >
                    <span class="bn">রেজিস্ট্রেশন ও দোকান চালু করুন</span>
                    <span class="en" style="display:none;">Launch Shop & Complete</span>
                </x-core::button>
            </div>
        </div>
    </form>

    <div style="margin-top:30px; text-align:center; font-size:12.5px; color:var(--ink-600);">
        <span class="bn">ইতিমধ্যে একটি দোকান অ্যাকাউন্ট রয়েছে?</span>
        <span class="en" style="display:none;">Already have an account?</span>
        <a href="{{ route('login') }}" style="color:var(--teal-800); font-weight:700; text-decoration:none; margin-left:4px;">
            <span class="bn">এখানে লগইন করুন</span>
            <span class="en" style="display:none;">Sign in here</span>
        </a>
    </div>

    @push('scripts')
    <script>
    $(function () {
        let currentStep = 1;
        let isSlugAvailable = true;
        let isPhoneAvailable = true;
        let slugCheckTimer = null;
        let phoneCheckTimer = null;

        function updateStepperUI(step) {
            $('.step-pane').removeClass('active');
            $('#step-pane-' + step).addClass('active');

            $('.step-item').removeClass('active completed');
            for (let i = 1; i <= 3; i++) {
                if (i < step) {
                    $('#step-nav-' + i).addClass('completed');
                    $('#step-circle-' + i).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>');
                } else if (i === step) {
                    $('#step-nav-' + i).addClass('active');
                    const bengaliDigits = ['১', '২', '৩'];
                    $('#step-circle-' + i).text(bengaliDigits[i - 1]);
                } else {
                    const bengaliDigits = ['১', '২', '৩'];
                    $('#step-circle-' + i).text(bengaliDigits[i - 1]);
                }
            }

            const fillWidths = { 1: '0%', 2: '50%', 3: '100%' };
            $('#stepper-fill').css('width', fillWidths[step] || '0%');
            currentStep = step;
        }

        // Slugify helper
        function slugify(text) {
            return text.toString().toLowerCase().trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        }

        // Auto-generate slug and populate shop phone
        $(document).on('input', '#reg-shop-name', function () {
            const name = $(this).val();
            const currentSlug = $('#reg-shop-slug').val();
            if (!currentSlug || $(this).data('auto-slug') !== false) {
                const generated = slugify(name);
                if (generated) {
                    $('#reg-shop-slug').val(generated).trigger('change');
                }
            }
        });

        $(document).on('input', '#reg-shop-slug', function () {
            $(this).data('auto-slug', false);
        });

        $(document).on('input', '#reg-phone', function () {
            const phone = $(this).val();
            if (!$('#reg-shop-phone').val() || $('#reg-shop-phone').data('auto-fill') !== false) {
                $('#reg-shop-phone').val(phone);
            }
        });

        $(document).on('input', '#reg-shop-phone', function () {
            $(this).data('auto-fill', false);
        });

        // Real-time phone check
        $(document).on('input change', '#reg-phone', function () {
            clearTimeout(phoneCheckTimer);
            const phone = String($(this).val() || '').trim();
            if (phone.length < 6) {
                $('#phone-feedback').empty();
                isPhoneAvailable = true;
                return;
            }

            phoneCheckTimer = setTimeout(function () {
                $.get('{{ route("register.check-availability") }}', { phone: phone }, function (res) {
                    if (res.phone_available) {
                        isPhoneAvailable = true;
                        $('#phone-feedback').html('<span class="text-valid">✓ মোবাইল নম্বরটি ব্যবহারযোগ্য</span>');
                    } else {
                        isPhoneAvailable = false;
                        $('#phone-feedback').html('<span class="text-invalid">✕ এই মোবাইল নম্বরটি ইতিমধ্যে নিবন্ধিত</span>');
                    }
                });
            }, 300);
        });

        // Real-time slug check
        $(document).on('input change', '#reg-shop-slug', function () {
            clearTimeout(slugCheckTimer);
            const slug = String($(this).val() || '').trim();
            if (!slug) {
                $('#slug-feedback').empty();
                isSlugAvailable = false;
                return;
            }

            slugCheckTimer = setTimeout(function () {
                $.get('{{ route("register.check-availability") }}', { slug: slug }, function (res) {
                    if (res.slug_available) {
                        isSlugAvailable = true;
                        $('#slug-feedback').html('<span class="text-valid">✓ URL / স্লাগটি খালি আছে</span>');
                    } else {
                        isSlugAvailable = false;
                        $('#slug-feedback').html('<span class="text-invalid">✕ এই স্লাগটি ইতিমধ্যে অন্য দোকানে ব্যবহৃত</span>');
                    }
                });
            }, 300);
        });

        // Validate Step 1
        function validateStep1() {
            let isValid = true;
            $('.client-error-message').hide();

            const name = String($('#reg-name').val() || '').trim();
            const phone = String($('#reg-phone').val() || '').trim();
            const password = $('#reg-password').val();
            const passwordConfirm = $('#reg-password-confirmation').val();

            if (!name) {
                $('#err-reg-name').show();
                isValid = false;
            }
            if (!phone || phone.length < 6 || !isPhoneAvailable) {
                $('#err-reg-phone').show();
                isValid = false;
            }
            if (!password || password.length < 6) {
                $('#err-reg-password').show();
                isValid = false;
            }
            if (password !== passwordConfirm) {
                $('#err-reg-password-confirmation').show();
                isValid = false;
            }

            return isValid;
        }

        // Validate Step 2
        function validateStep2() {
            let isValid = true;
            $('.client-error-message').hide();

            const shopName = String($('#reg-shop-name').val() || '').trim();
            const shopSlug = String($('#reg-shop-slug').val() || '').trim();

            if (!shopName) {
                $('#err-reg-shop-name').show();
                isValid = false;
            }
            if (!shopSlug || !isSlugAvailable) {
                $('#err-reg-shop-slug').show();
                isValid = false;
            }

            return isValid;
        }

        // Navigation Handlers
        $(document).on('click', '#btn-step-1-next', function () {
            if (validateStep1()) {
                updateStepperUI(2);
            }
        });

        $(document).on('click', '#btn-step-2-back', function () {
            updateStepperUI(1);
        });

        $(document).on('click', '#btn-step-2-next', function () {
            if (validateStep2()) {
                updateStepperUI(3);
            }
        });

        $(document).on('click', '#btn-step-3-back', function () {
            updateStepperUI(2);
        });

        // Header Stepper Click
        $(document).on('click', '.step-item', function () {
            const targetStep = parseInt($(this).data('step'), 10);
            if (targetStep < currentStep) {
                updateStepperUI(targetStep);
            } else if (targetStep === 2 && currentStep === 1) {
                if (validateStep1()) updateStepperUI(2);
            } else if (targetStep === 3 && currentStep === 2) {
                if (validateStep2()) updateStepperUI(3);
            }
        });

        // Form Submit Handler
        $(document).on('submit', '#register-wizard-form', function (e) {
            if (!validateStep1()) {
                e.preventDefault();
                updateStepperUI(1);
                return false;
            }
            if (!validateStep2()) {
                e.preventDefault();
                updateStepperUI(2);
                return false;
            }

            const $btn = $('#btn-submit-registration');
            $btn.prop('disabled', true);
            $btn.find('.bn').text('দোকান তৈরি হচ্ছে...');
            $btn.find('.en').text('Creating Shop...');
        });

        // Auto jump to step with error on server-side validation failure
        @if ($errors->has('shop_name') || $errors->has('shop_slug') || $errors->has('shop_phone') || $errors->has('shop_address'))
            updateStepperUI(2);
        @elseif ($errors->has('branch_name') || $errors->has('warehouse_name') || $errors->has('opening_cash_balance'))
            updateStepperUI(3);
        @else
            updateStepperUI(1);
        @endif
    });
    </script>
    @endpush
</x-core::auth-layout>
