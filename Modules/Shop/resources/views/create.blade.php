<x-core::layout
    title="নতুন দোকান তৈরি"
    title-en="Create Shop"
    subtitle="নতুন দোকান, প্রথম এডমিন অ্যাকাউন্ট ও ফিচার অনুমতি কনফিগার করুন"
    subtitle-en="Create a new shop, assign initial shop admin, and configure active modules"
    active="shops"
>
    @php
        $selectedFeatures = (array) old('features', array_keys($features));
        $featureIcons = [
            'sales' => 'shopping-cart',
            'purchase' => 'truck',
            'cashbox' => 'wallet',
            'quick-sale' => 'sparkles',
            'stock' => 'box',
            'products' => 'tag',
            'branches' => 'building',
            'customers' => 'users',
            'suppliers' => 'truck',
            'income' => 'trending-up',
            'expense' => 'trending-down',
            'tax' => 'percent',
            'reports' => 'file-text',
            'audit' => 'shield',
            'employees' => 'user',
            'users' => 'lock',
        ];
    @endphp

    <style>
    .shop-form-grid {
        display: grid;
        grid-template-columns: 1.18fr 0.82fr;
        gap: 20px;
        align-items: start;
        width: 100%;
    }
    .input-status-valid {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
    }
    .input-status-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.18) !important;
    }
    .availability-feedback {
        font-size: 11.5px;
        font-weight: 600;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
        min-height: 18px;
    }
    @media (max-width: 1024px) {
        .shop-form-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <div class="panel" style="margin-top:0; width:100%;">
        <div class="panel-head" style="padding:16px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <x-core::icon name="plus" size="18" />
                </div>
                <div>
                    <div class="panel-title bn" style="font-size:16px;">নতুন দোকান তৈরি করুন</div>
                    <div class="panel-title en" style="display:none; font-size:16px;">Create New Shop</div>
                    <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                        <span class="bn">দোকানের মৌলিক তথ্য, প্রথম এডমিনের লগইন এবং সক্রিয় ফিচার নির্ধারণ করুন</span>
                        <span class="en" style="display:none;">Set basic shop info, first admin credentials, and accessible features</span>
                    </div>
                </div>
            </div>

            <x-core::button
                as="a"
                href="{{ route('shops.index') }}"
                variant="soft"
                color="secondary"
                size="sm"
                icon="arrow-left"
            >
                <span class="bn">দোকান তালিকায় ফিরে যান</span>
                <span class="en" style="display:none;">Back to Shop List</span>
            </x-core::button>
        </div>

        <div class="panel-body" style="padding:22px;">
            <form method="POST" action="{{ route('shops.store') }}">
                @csrf

                <div class="shop-form-grid">
                    {{-- Left Column: Shop Details, Admin Info & Feature Toggles --}}
                    <div style="display:flex; flex-direction:column; gap:20px;">
                        {{-- Card 1: Shop Basic Information --}}
                        <div class="panel" style="margin-top:0;">
                            <div class="panel-head" style="padding:14px 18px;">
                                <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:15px;">
                                    <x-core::icon name="shopping-bag" size="18" style="color:var(--teal-800);" />
                                    <span class="bn">দোকানের প্রাথমিক বিবরণ</span>
                                    <span class="en" style="display:none;">Shop Information</span>
                                </div>
                            </div>
                            <div class="panel-body" style="padding:18px;">
                                <div style="margin-bottom:12px;">
                                    <x-core::input
                                        name="name"
                                        id="shop-name-input"
                                        label="দোকানের নাম"
                                        label-en="Shop Name"
                                        icon="shopping-bag"
                                        placeholder="যেমন: রহিম জেনারেল স্টোর"
                                        :value="old('name')"
                                        required
                                    />
                                </div>

                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                                    <div>
                                        <x-core::input
                                            name="slug"
                                            id="shop-slug-input"
                                            label="স্লাগ (URL আইডেন্টিফায়ার)"
                                            label-en="Slug (URL)"
                                            icon="globe"
                                            placeholder="যেমন: rahim-general-store"
                                            :value="old('slug')"
                                            required
                                        />
                                        <div id="slug-availability-msg" class="availability-feedback"></div>
                                    </div>
                                    <div>
                                        <x-core::input
                                            name="store_code"
                                            id="shop-store-code-input"
                                            label="দোকান কোড (Shop Code)"
                                            label-en="Shop Code"
                                            icon="tag"
                                            placeholder="যেমন: shop-001, shop-002"
                                            :value="old('store_code', $nextStoreCode ?? 'shop-')"
                                        />
                                        <div id="store-code-availability-msg" class="availability-feedback"></div>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                                    <div>
                                        <x-core::input
                                            name="phone"
                                            id="shop-phone-input"
                                            label="ফোন / মোবাইল নম্বর"
                                            label-en="Phone Number"
                                            icon="phone"
                                            placeholder="017xxxxxxxx"
                                            :value="old('phone')"
                                        />
                                    </div>
                                    <div>
                                        <x-core::form-group name="status" label="দোকানের অবস্থা" label-en="Shop Status" icon="check-circle" required>
                                            <select name="status" id="shop-status-select" class="form-control form-select" required>
                                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                                    সক্রিয় (Active)
                                                </option>
                                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                                    নিষ্ক্রিয় (Inactive)
                                                </option>
                                            </select>
                                        </x-core::form-group>
                                    </div>
                                </div>

                                <div>
                                    <x-core::textarea
                                        name="address"
                                        id="shop-address-input"
                                        label="দোকানের ঠিকানা"
                                        label-en="Shop Address"
                                        icon="map-pin"
                                        placeholder="দোকানের পূর্ণাঙ্গ ঠিকানা লিখুন..."
                                        rows="2"
                                        :value="old('address')"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: First Admin Information --}}
                        <div class="panel" style="margin-top:0;">
                            <div class="panel-head" style="padding:14px 18px;">
                                <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:15px;">
                                    <x-core::icon name="user-check" size="18" style="color:var(--teal-800);" />
                                    <span class="bn">প্রথম এডমিন অ্যাকাউন্ট তৈরি</span>
                                    <span class="en" style="display:none;">First Admin Account</span>
                                </div>
                            </div>
                            <div class="panel-body" style="padding:18px;">
                                <div class="helper" style="background:var(--gold-100); color:var(--ink-800); border:1px solid var(--border); margin-top:0; margin-bottom:14px; padding:10px 14px; border-radius:8px; font-size:12.5px;">
                                    <span class="bn">এই এডমিন ইউজার দিয়ে দোকানদার তার POS সিস্টেমে লগইন করে সকল কার্যক্রম পরিচালনা করবেন।</span>
                                    <span class="en" style="display:none;">This admin account will be used by the shop owner to log into and manage the shop.</span>
                                </div>

                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                                    <div>
                                        <x-core::input
                                            name="admin_name"
                                            id="shop-admin-name-input"
                                            label="এডমিনের নাম"
                                            label-en="Admin Name"
                                            icon="user"
                                            placeholder="যেমন: মোঃ রহিম উল্লাহ"
                                            :value="old('admin_name')"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <x-core::input
                                            type="email"
                                            name="admin_email"
                                            id="shop-admin-email-input"
                                            label="এডমিনের ইমেইল"
                                            label-en="Admin Email"
                                            icon="mail"
                                            placeholder="admin@example.com"
                                            :value="old('admin_email')"
                                            required
                                        />
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                                    <div>
                                        <x-core::input
                                            type="password"
                                            password-toggle
                                            name="admin_password"
                                            id="shop-admin-password-input"
                                            label="পাসওয়ার্ড"
                                            label-en="Password"
                                            icon="lock"
                                            placeholder="কমপক্ষে ৮ অক্ষর"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <x-core::input
                                            type="password"
                                            password-toggle
                                            name="admin_password_confirmation"
                                            id="shop-admin-password-confirm-input"
                                            label="পাসওয়ার্ড নিশ্চিত করুন"
                                            label-en="Confirm Password"
                                            icon="lock"
                                            placeholder="পাসওয়ার্ড পুনরায় দিন"
                                            required
                                        />
                                    </div>
                                </div>

                                <div>
                                    <x-core::form-group name="admin_role" label="এডমিন রোল নির্ধারণ করুন" label-en="Assign Role" icon="shield" required>
                                        <select name="admin_role" id="shop-admin-role-select" class="form-control form-select" required>
                                            <option value="" disabled {{ old('admin_role') ? '' : 'selected' }}>-- রোল নির্বাচন করুন --</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}" {{ old('admin_role') === $role->name ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </x-core::form-group>
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: Active Modules & Features Selection --}}
                        <div class="panel" style="margin-top:0;">
                            <div class="panel-head" style="padding:14px 18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                                <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:15px;">
                                    <x-core::icon name="shield" size="18" style="color:var(--teal-800);" />
                                    <span class="bn">এই দোকানের জন্য সক্রিয় মডিউল ও ফিচার</span>
                                    <span class="en" style="display:none;">Active Modules & Features</span>
                                    <x-core::badge id="selected-features-count" color="teal" size="xs">
                                        {{ count($selectedFeatures) }} টি নির্বাচিত
                                    </x-core::badge>
                                </div>

                                <div style="display:flex; align-items:center; gap:6px;">
                                    <x-core::button
                                        type="button"
                                        variant="soft"
                                        color="teal"
                                        size="xs"
                                        id="btn-select-all-features"
                                        icon="check"
                                    >
                                        <span class="bn">সবগুলো নির্বাচন</span>
                                        <span class="en" style="display:none;">Select All</span>
                                    </x-core::button>

                                    <x-core::button
                                        type="button"
                                        variant="soft"
                                        color="secondary"
                                        size="xs"
                                        id="btn-deselect-all-features"
                                        icon="x"
                                    >
                                        <span class="bn">সব বাতিল</span>
                                        <span class="en" style="display:none;">Deselect All</span>
                                    </x-core::button>
                                </div>
                            </div>
                            <div class="panel-body" style="padding:18px;">
                                <div style="font-size:12px; color:var(--ink-500); margin-bottom:12px;">
                                    <span class="bn">শুধু নির্বাচিত মডিউলগুলো এই দোকানের এডমিন সাইডবারে দেখতে ও ব্যবহার করতে পারবে।</span>
                                    <span class="en" style="display:none;">Only checked modules will be visible and usable in this shop's dashboard.</span>
                                </div>

                                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:10px;">
                                    @foreach ($features as $key => $labels)
                                        @php
                                            $isChecked = in_array($key, $selectedFeatures);
                                            $iconName = $featureIcons[$key] ?? 'check-circle';
                                        @endphp
                                        <label
                                            class="form-radio-card feature-card {{ $isChecked ? 'active' : '' }}"
                                            style="padding:10px 12px; gap:10px; border-radius:10px;"
                                        >
                                            <input
                                                type="checkbox"
                                                name="features[]"
                                                value="{{ $key }}"
                                                class="feature-checkbox"
                                                data-feature-key="{{ $key }}"
                                                data-feature-name-bn="{{ $labels['bn'] }}"
                                                data-feature-name-en="{{ $labels['en'] }}"
                                                {{ $isChecked ? 'checked' : '' }}
                                            />
                                            <span class="card-icon" style="width:30px; height:30px; border-radius:8px;">
                                                <x-core::icon :name="$iconName" size="15" />
                                            </span>
                                            <span class="card-content">
                                                <span class="card-title" style="font-size:12.5px;">
                                                    <span class="bn">{{ $labels['bn'] }}</span>
                                                    <span class="en" style="display:none;">{{ $labels['en'] }}</span>
                                                </span>
                                                <span class="card-desc" style="font-size:11px; font-family:monospace; color:var(--ink-400);">
                                                    {{ $key }}
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('features')
                                    <div class="form-error" style="margin-top:10px;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Live Shop Overview & Submission Actions --}}
                    <div style="display:flex; flex-direction:column; gap:20px;">
                        {{-- Card 4: Live Interactive Shop Preview Card --}}
                        <div class="panel" style="margin-top:0; border:1px solid var(--border); box-shadow:var(--shadow-card);">
                            <div class="panel-head" style="padding:12px 18px; background:var(--paper); border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                                <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--ink-800);">
                                    <x-core::icon name="eye" size="16" style="color:var(--teal-800);" />
                                    <span class="bn">লাইভ দোকান প্রিভিউ (Live Preview)</span>
                                    <span class="en" style="display:none;">Live Shop Preview</span>
                                </div>
                                <x-core::badge
                                    id="preview-status-badge"
                                    :color="old('status', 'active') === 'active' ? 'green' : 'grey'"
                                    size="xs"
                                    :dot="true"
                                    :label="old('status', 'active') === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়'"
                                    :label-en="old('status', 'active') === 'active' ? 'Active' : 'Inactive'"
                                />
                            </div>
                            <div class="panel-body" style="padding:18px;">
                                <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:14px;">
                                    <div style="width:42px; height:42px; border-radius:10px; background:var(--teal-50); color:var(--teal-700); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        <x-core::icon name="shopping-bag" size="20" />
                                    </div>
                                    <div style="min-width:0; flex:1;">
                                        <div id="preview-shop-name" style="font-weight:800; font-size:16.5px; color:var(--ink-900); word-break:break-word;">
                                            {{ old('name') ?: 'দোকানের নাম' }}
                                        </div>
                                        <div style="display:flex; align-items:center; gap:5px; margin-top:3px; flex-wrap:wrap;">
                                            <span id="preview-shop-slug" style="font-size:11.5px; color:var(--ink-600); font-family:monospace; background:var(--ink-50); padding:1px 6px; border-radius:4px; border:1px solid var(--border);">
                                                {{ old('slug') ?: 'shop-slug' }}
                                            </span>
                                            <span id="preview-shop-store-code" style="font-size:11.5px; color:var(--teal-800); font-weight:700; font-family:monospace; background:var(--teal-50); padding:1px 6px; border-radius:4px; border:1px solid var(--teal-200);">
                                                #{{ old('store_code') ?: 'STORE-CODE' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div style="background:var(--paper); border-radius:8px; border:1px solid var(--border); padding:10px 12px; margin-bottom:14px; font-size:12px; display:flex; flex-direction:column; gap:6px;">
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <x-core::icon name="phone" size="13" style="color:var(--ink-500);" />
                                        <span id="preview-shop-phone" style="font-family:monospace; color:var(--ink-700);">
                                            {{ old('phone') ?: 'মোবাইল নম্বর দেওয়া হয়নি' }}
                                        </span>
                                    </div>
                                    <div style="display:flex; align-items:baseline; gap:6px;">
                                        <x-core::icon name="map-pin" size="13" style="color:var(--ink-500);" />
                                        <span id="preview-shop-address" style="color:var(--ink-600); word-break:break-word;">
                                            {{ old('address') ?: 'ঠিকানা দেওয়া হয়নি' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Admin Preview section --}}
                                <div style="margin-bottom:14px; padding-top:12px; border-top:1px dashed var(--border);">
                                    <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px;">
                                        <span class="bn">দায়িত্বপ্রাপ্ত এডমিন</span>
                                        <span class="en" style="display:none;">Designated Admin</span>
                                    </div>
                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                                        <div style="display:flex; align-items:center; gap:8px; min-width:0;">
                                            <div style="width:28px; height:28px; border-radius:50%; background:var(--ink-100); color:var(--ink-700); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0;">
                                                <x-core::icon name="user" size="14" />
                                            </div>
                                            <div style="min-width:0;">
                                                <div id="preview-admin-name" style="font-weight:700; font-size:13px; color:var(--ink-800); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                    {{ old('admin_name') ?: 'এডমিনের নাম' }}
                                                </div>
                                                <div id="preview-admin-email" style="font-size:11px; color:var(--ink-500); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                    {{ old('admin_email') ?: 'admin@example.com' }}
                                                </div>
                                            </div>
                                        </div>
                                        <x-core::badge id="preview-admin-role" color="teal" size="xs">
                                            {{ old('admin_role') ?: 'Role' }}
                                        </x-core::badge>
                                    </div>
                                </div>

                                {{-- Feature tags preview --}}
                                <div style="font-size:11.5px; font-weight:700; color:var(--ink-700); margin-bottom:6px;">
                                    <span class="bn">সক্রিয় মডিউলসমূহ:</span>
                                    <span class="en" style="display:none;">Active Modules:</span>
                                </div>
                                <div id="preview-feature-tags" style="display:flex; flex-wrap:wrap; gap:5px; min-height:28px;">
                                    <!-- Populated via jQuery -->
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions Card --}}
                        <div style="display:flex; align-items:center; gap:10px; padding:14px 18px; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm);">
                            <x-core::button
                                as="a"
                                href="{{ route('shops.index') }}"
                                variant="outline"
                                color="secondary"
                                size="md"
                                icon="arrow-left"
                                style="flex:1; justify-content:center;"
                            >
                                <span class="bn">বাতিল</span>
                                <span class="en" style="display:none;">Cancel</span>
                            </x-core::button>

                            <x-core::button
                                type="submit"
                                variant="solid"
                                color="gold"
                                size="md"
                                icon="save"
                                id="btn-submit-shop"
                                style="flex:1.4; justify-content:center;"
                            >
                                <span class="bn">দোকান সংরক্ষণ করুন</span>
                                <span class="en" style="display:none;">Create Shop</span>
                            </x-core::button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        function initShopCreateInteractions() {
            if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                setTimeout(initShopCreateInteractions, 30);
                return;
            }

            var $ = window.jQuery;
            var availabilityUrl = "{{ route('shops.check-availability') }}";
            var checkTimer = null;

            var $slugInput = $('#shop-slug-input');
            var $storeCodeInput = $('#shop-store-code-input');
            var $slugMsg = $('#slug-availability-msg');
            var $storeCodeMsg = $('#store-code-availability-msg');

            var slugManuallyEdited = $slugInput.length && $slugInput.val().trim() !== '';
            var storeCodeManuallyEdited = $storeCodeInput.length && $storeCodeInput.val().trim() !== '';

            // 1. AJAX Check Availability Function with jQuery
            function performAvailabilityCheck() {
                var slugVal = $slugInput.val().trim();
                var storeCodeVal = $storeCodeInput.val().trim();

                if (!slugVal && !storeCodeVal) {
                    $slugInput.removeClass('input-status-valid input-status-invalid');
                    $storeCodeInput.removeClass('input-status-valid input-status-invalid');
                    $slugMsg.empty();
                    $storeCodeMsg.empty();
                    return;
                }

                $.ajax({
                    url: availabilityUrl,
                    type: 'GET',
                    data: {
                        slug: slugVal,
                        store_code: storeCodeVal
                    },
                    dataType: 'json',
                    success: function (res) {
                        // Slug validation state
                        if (slugVal) {
                            if (res.slug_available) {
                                $slugInput.removeClass('input-status-invalid').addClass('input-status-valid');
                                $slugMsg.html(
                                    '<span style="color:#059669; display:inline-flex; align-items:center; gap:4px;">' +
                                    '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                                    '<span class="bn">স্লাগটি উপলব্ধ (Available)</span>' +
                                    '</span>'
                                );
                            } else {
                                $slugInput.removeClass('input-status-valid').addClass('input-status-invalid');
                                $slugMsg.html(
                                    '<span style="color:#dc2626; display:inline-flex; align-items:center; gap:4px;">' +
                                    '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
                                    '<span class="bn">এই স্লাগটি ইতিমধ্যে ব্যবহৃত হয়েছে (Already taken)</span>' +
                                    '</span>'
                                );
                            }
                        } else {
                            $slugInput.removeClass('input-status-valid input-status-invalid');
                            $slugMsg.empty();
                        }

                        // Store Code validation state
                        if (storeCodeVal) {
                            if (res.store_code_available) {
                                $storeCodeInput.removeClass('input-status-invalid').addClass('input-status-valid');
                                $storeCodeMsg.html(
                                    '<span style="color:#059669; display:inline-flex; align-items:center; gap:4px;">' +
                                    '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                                    '<span class="bn">স্টোর কোড উপলব্ধ (Available)</span>' +
                                    '</span>'
                                );
                            } else {
                                $storeCodeInput.removeClass('input-status-valid').addClass('input-status-invalid');
                                $storeCodeMsg.html(
                                    '<span style="color:#dc2626; display:inline-flex; align-items:center; gap:4px;">' +
                                    '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
                                    '<span class="bn">এই কোডটি ইতিমধ্যে ব্যবহৃত হয়েছে (Already taken)</span>' +
                                    '</span>'
                                );
                            }
                        } else {
                            $storeCodeInput.removeClass('input-status-valid input-status-invalid');
                            $storeCodeMsg.empty();
                        }
                    }
                });
            }

            function scheduleAvailabilityCheck() {
                clearTimeout(checkTimer);
                checkTimer = setTimeout(performAvailabilityCheck, 280);
            }

            // 2. Feature tags live update and counter
            function updateFeaturesPreview() {
                var $checked = $('input[name="features[]"]:checked');
                $('#selected-features-count').text($checked.length + ' টি নির্বাচিত');

                var $tagsContainer = $('#preview-feature-tags');
                $tagsContainer.empty();

                if ($checked.length === 0) {
                    $tagsContainer.html('<span style="font-size:11px; color:var(--ink-400);">কোনো ফিচার নির্বাচিত নেই</span>');
                    return;
                }

                $checked.each(function () {
                    var nameBn = $(this).data('feature-name-bn') || $(this).val();
                    $tagsContainer.append(
                        '<span class="badge b-teal badge-teal badge-xs" style="padding:2px 7px;">' + nameBn + '</span>'
                    );
                });
            }

            // Feature checkbox changes
            $(document).on('change', 'input[name="features[]"]', function () {
                var isChecked = $(this).is(':checked');
                $(this).closest('.feature-card').toggleClass('active', isChecked);
                updateFeaturesPreview();
            });

            // Select All Features
            $(document).on('click', '#btn-select-all-features', function (e) {
                e.preventDefault();
                $('input[name="features[]"]').prop('checked', true);
                $('.feature-card').addClass('active');
                updateFeaturesPreview();
            });

            // Deselect All Features
            $(document).on('click', '#btn-deselect-all-features', function (e) {
                e.preventDefault();
                $('input[name="features[]"]').prop('checked', false);
                $('.feature-card').removeClass('active');
                updateFeaturesPreview();
            });

            // 3. Slug and Store Code Auto-generation from Shop Name
            $(document).on('input', '#shop-slug-input', function () {
                slugManuallyEdited = true;
                $('#preview-shop-slug').text($(this).val() || 'shop-slug');
                scheduleAvailabilityCheck();
            });

            $(document).on('input', '#shop-store-code-input', function () {
                storeCodeManuallyEdited = true;
                $('#preview-shop-store-code').text('#' + ($(this).val() || 'STORE-CODE'));
                scheduleAvailabilityCheck();
            });

            $(document).on('input', '#shop-name-input', function () {
                var val = $(this).val();
                $('#preview-shop-name').text(val || 'দোকানের নাম');

                if (!slugManuallyEdited && $slugInput.length) {
                    var slug = val.toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                    $slugInput.val(slug);
                    $('#preview-shop-slug').text(slug || 'shop-slug');
                }

                scheduleAvailabilityCheck();
            });

            // 4. Live Phone & Address Updates
            $(document).on('input', '#shop-phone-input', function () {
                $('#preview-shop-phone').text($(this).val() || 'মোবাইল নম্বর দেওয়া হয়নি');
            });

            $(document).on('input', '#shop-address-input', function () {
                $('#preview-shop-address').text($(this).val() || 'ঠিকানা দেওয়া হয়নি');
            });

            // 5. Live Admin Info Updates
            $(document).on('input', '#shop-admin-name-input', function () {
                $('#preview-admin-name').text($(this).val() || 'এডমিনের নাম');
            });

            $(document).on('input', '#shop-admin-email-input', function () {
                $('#preview-admin-email').text($(this).val() || 'admin@example.com');
            });

            $(document).on('change', '#shop-admin-role-select', function () {
                $('#preview-admin-role').text($(this).val() || 'Role');
            });

            // 6. Live Status Updates
            $(document).on('change', '#shop-status-select', function () {
                var isActive = $(this).val() === 'active';
                var $badge = $('#preview-status-badge');
                $badge.removeClass('b-green b-grey badge-green badge-grey')
                    .addClass(isActive ? 'b-green badge-green' : 'b-grey badge-grey');
                if ($badge.find('.bn').length) {
                    $badge.find('.bn').text(isActive ? 'সক্রিয়' : 'নিষ্ক্রিয়');
                    $badge.find('.en').text(isActive ? 'Active' : 'Inactive');
                } else {
                    $badge.text(isActive ? 'সক্রিয়' : 'নিষ্ক্রিয়');
                }
            });

            // Run initial sync
            function syncInitialValues() {
                var nameVal = $('#shop-name-input').val();
                if (nameVal) $('#preview-shop-name').text(nameVal);

                var slugVal = $('#shop-slug-input').val();
                if (slugVal) $('#preview-shop-slug').text(slugVal);

                var codeVal = $('#shop-store-code-input').val();
                if (codeVal) $('#preview-shop-store-code').text('#' + codeVal);

                var phoneVal = $('#shop-phone-input').val();
                if (phoneVal) $('#preview-shop-phone').text(phoneVal);

                var addressVal = $('#shop-address-input').val();
                if (addressVal) $('#preview-shop-address').text(addressVal);

                var adminNameVal = $('#shop-admin-name-input').val();
                if (adminNameVal) $('#preview-admin-name').text(adminNameVal);

                var adminEmailVal = $('#shop-admin-email-input').val();
                if (adminEmailVal) $('#preview-admin-email').text(adminEmailVal);

                var adminRoleVal = $('#shop-admin-role-select').val();
                if (adminRoleVal) $('#preview-admin-role').text(adminRoleVal);

                updateFeaturesPreview();

                if (slugVal || codeVal) {
                    performAvailabilityCheck();
                }
            }

            syncInitialValues();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initShopCreateInteractions);
        } else {
            initShopCreateInteractions();
        }
    })();
    </script>
    @endpush
</x-core::layout>
