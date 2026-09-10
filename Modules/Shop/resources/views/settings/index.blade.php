<x-core::layout
    title="দোকান সেটিংস"
    title-en="Shop Settings"
    subtitle="আপনার দোকান পরিচিতি, যোগাযোগের তথ্য, লোগো ও ইনভয়েস সেটিংস পরিচালনা করুন"
    subtitle-en="Manage your shop profile, contact info, logo and invoice settings"
    active="settings"
>
    @if (session('status'))
        <div style="background:var(--green-100); border:1px solid var(--green-ic-bg); color:var(--green-ink); border-radius:12px; padding:12px 18px; margin-bottom:20px; font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px;">
            <x-core::icon name="check-circle" size="18" />
            <span>{{ session('status') }}</span>
        </div>
    @endif
    @php
        $authUser = auth()->user();
    @endphp

    <div style="width:100%; max-width:1160px;">
        <div style="display:grid; grid-template-columns:310px 1fr; gap:22px; align-items:start;" class="shop-settings-grid">
            {{-- Left Column: Shop Summary & Plan Card --}}
            <div style="display:flex; flex-direction:column; gap:20px;">
                {{-- Shop Summary Card --}}
                <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card); text-align:center;">
                    <div style="position:relative; width:88px; height:88px; margin:0 auto 16px;">
                        <div id="leftLogoWrapper" style="width:88px; height:88px; border-radius:20px; overflow:hidden; display:flex; align-items:center; justify-content:center; box-shadow:var(--shadow-sm); border:2px solid var(--border); background:var(--paper);">
                            @if ($shop->logo_url)
                                <img id="leftLogoImg" src="{{ $shop->logo_url }}" alt="{{ $shop->name }}" onerror="this.style.display='none'; document.getElementById('leftLogoFallback').style.display='flex';" style="width:100%; height:100%; object-fit:contain; padding:6px; display:block;">
                                <div id="leftLogoFallback" style="display:none; width:100%; height:100%; background:linear-gradient(135deg, #0D9488 0%, #0891B2 100%); color:#ffffff; align-items:center; justify-content:center; font-family:'Noto Sans Bengali','SolaimanLipi','Baloo Da 2',sans-serif; font-weight:800; font-size:32px;">
                                    {{ mb_substr($shop->name ?? '?', 0, 1) }}
                                </div>
                            @else
                                <img id="leftLogoImg" src="" alt="{{ $shop->name }}" style="display:none; width:100%; height:100%; object-fit:contain; padding:6px;">
                                <div id="leftLogoFallback" style="display:flex; width:100%; height:100%; background:linear-gradient(135deg, #0D9488 0%, #0891B2 100%); color:#ffffff; align-items:center; justify-content:center; font-family:'Noto Sans Bengali','SolaimanLipi','Baloo Da 2',sans-serif; font-weight:800; font-size:32px;">
                                    {{ mb_substr($shop->name ?? '?', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <button type="button" id="leftLogoChangeBtn" style="position:absolute; bottom:-4px; right:-4px; width:28px; height:28px; border-radius:50%; background:var(--teal-800); color:#ffffff; border:2px solid var(--card); display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:var(--shadow-sm); transition:transform 0.15s ease;" title="লোগো পরিবর্তন করুন / Change Logo">
                            <x-core::icon name="camera" size="13" />
                        </button>
                    </div>

                    <div style="font-size:17px; font-weight:700; color:var(--ink-900); line-height:1.3;">
                        {{ $shop->name }}
                    </div>

                    <div style="display:flex; align-items:center; justify-content:center; gap:6px; margin-top:10px; flex-wrap:wrap;">
                        @if ($shop->store_code)
                            <x-core::badge color="teal" size="xs" variant="soft">#{{ $shop->store_code }}</x-core::badge>
                        @endif
                        <x-core::badge color="grey" size="xs" variant="outline">{{ $shop->slug }}</x-core::badge>
                        <x-core::badge
                            :color="$shop->status === 'active' ? 'green' : 'grey'"
                            size="xs"
                            :dot="true"
                            :label="$shop->status === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়'"
                            :label-en="$shop->status === 'active' ? 'Active' : 'Inactive'"
                        />
                    </div>

                    {{-- Metrics Grid --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:18px; text-align:left;">
                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:10px 12px;">
                            <div style="font-size:11px; color:var(--ink-500); font-weight:600;">
                                <span class="bn">ইউজার</span><span class="en" style="display:none;">Users</span>
                            </div>
                            <div style="font-size:16px; font-weight:800; color:var(--teal-800); margin-top:2px;">
                                {{ \Modules\Core\Support\BanglaNumber::toBn($stats['users_count'] ?? 0) }}
                            </div>
                        </div>

                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:10px 12px;">
                            <div style="font-size:11px; color:var(--ink-500); font-weight:600;">
                                <span class="bn">শাখা</span><span class="en" style="display:none;">Branches</span>
                            </div>
                            <div style="font-size:16px; font-weight:800; color:var(--teal-800); margin-top:2px;">
                                {{ \Modules\Core\Support\BanglaNumber::toBn($stats['branches_count'] ?? 0) }}
                            </div>
                        </div>

                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:10px 12px;">
                            <div style="font-size:11px; color:var(--ink-500); font-weight:600;">
                                <span class="bn">গুদাম</span><span class="en" style="display:none;">Warehouses</span>
                            </div>
                            <div style="font-size:16px; font-weight:800; color:var(--teal-800); margin-top:2px;">
                                {{ \Modules\Core\Support\BanglaNumber::toBn($stats['warehouses_count'] ?? 0) }}
                            </div>
                        </div>

                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:10px 12px;">
                            <div style="font-size:11px; color:var(--ink-500); font-weight:600;">
                                <span class="bn">মোট পণ্য</span><span class="en" style="display:none;">Products</span>
                            </div>
                            <div style="font-size:16px; font-weight:800; color:var(--teal-800); margin-top:2px;">
                                {{ \Modules\Core\Support\BanglaNumber::toBn($stats['products_count'] ?? 0) }}
                            </div>
                        </div>
                    </div>

                    {{-- Contact Meta Preview --}}
                    <div style="border-top:1px solid var(--border); margin:18px 0; padding-top:16px; text-align:left; display:flex; flex-direction:column; gap:12px;">
                        <div>
                            <div style="font-size:11px; font-weight:600; color:var(--ink-400); text-transform:uppercase; letter-spacing:0.5px;">
                                <span class="bn">মোবাইল নম্বর</span><span class="en" style="display:none;">Phone</span>
                            </div>
                            <div style="font-size:12.5px; font-weight:600; color:var(--ink-800); margin-top:2px;">
                                {{ $shop->phone ?: '—' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:11px; font-weight:600; color:var(--ink-400); text-transform:uppercase; letter-spacing:0.5px;">
                                <span class="bn">ইমেইল</span><span class="en" style="display:none;">Email</span>
                            </div>
                            <div style="font-size:12.5px; font-weight:600; color:var(--ink-800); margin-top:2px; word-break:break-all;">
                                {{ $shop->email ?: '—' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size:11px; font-weight:600; color:var(--ink-400); text-transform:uppercase; letter-spacing:0.5px;">
                                <span class="bn">নিবন্ধনের তারিখ</span><span class="en" style="display:none;">Created Date</span>
                            </div>
                            <div style="font-size:12.5px; font-weight:600; color:var(--ink-800); margin-top:2px;">
                                {{ $shop->created_at ? $shop->created_at->format('d M, Y') : '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Subscription Plan Card --}}
                @if ($subscription && $subscription->plan && ($authUser?->isSuperAdmin() || ($shop ?? $authUser?->shop)?->hasFeature('subscription')))
                    <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:18px; box-shadow:var(--shadow-card);">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <x-core::icon name="sparkles" size="17" style="color:var(--teal-800);" />
                                <span style="font-size:13.5px; font-weight:700; color:var(--ink-900);">
                                    <span class="bn">বর্তমান প্ল্যান</span><span class="en" style="display:none;">Active Plan</span>
                                </span>
                            </div>
                            @php $statusLabel = $subscription->statusLabel(); @endphp
                            <x-core::badge
                                :color="$subscription->isUsable() ? 'green' : 'red'"
                                size="xs"
                                :dot="true"
                                :label="$statusLabel['bn']"
                                :label-en="$statusLabel['en']"
                            />
                        </div>

                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:12px; margin-bottom:12px;">
                            <div style="font-size:14px; font-weight:800; color:var(--teal-900);">
                                {{ $subscription->plan->name }}
                            </div>
                            <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                                ৳{{ number_format($subscription->plan->price, 0) }} / {{ $subscription->plan->billing_interval?->value ?? 'মাস' }}
                            </div>
                        </div>

                        @if (Route::has('subscription.show'))
                            <x-core::button
                                as="a"
                                href="{{ route('subscription.show') }}"
                                variant="secondary"
                                size="sm"
                                icon="external-link"
                                style="width:100%; justify-content:center;"
                            >
                                <span class="bn">সাবস্ক্রিপশন বিবরণ দেখুন</span>
                                <span class="en" style="display:none;">View Subscription Details</span>
                            </x-core::button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Right Column: Shop Settings Form --}}
            <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card);">
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" id="shopSettingsForm">
                    @csrf
                    @method('PUT')

                    {{-- Section: Shop Logo Upload --}}
                    <div style="margin-bottom:24px; padding:18px; background:var(--paper); border:1px solid var(--border); border-radius:14px; display:flex; align-items:center; gap:18px; flex-wrap:wrap;">
                        <div style="width:74px; height:74px; border-radius:18px; overflow:hidden; border:2px solid var(--border); background:var(--card); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:var(--shadow-sm);">
                            @if ($shop->logo_url)
                                <img id="formLogoPreview" src="{{ $shop->logo_url }}" alt="{{ $shop->name }}" onerror="this.style.display='none'; document.getElementById('formLogoFallback').style.display='flex';" style="width:100%; height:100%; object-fit:contain; padding:4px; display:block;">
                                <div id="formLogoFallback" style="display:none; width:100%; height:100%; background:linear-gradient(135deg, #0D9488 0%, #0891B2 100%); color:#ffffff; align-items:center; justify-content:center; font-family:'Noto Sans Bengali','SolaimanLipi','Baloo Da 2',sans-serif; font-weight:800; font-size:26px;">
                                    {{ mb_substr($shop->name ?? '?', 0, 1) }}
                                </div>
                            @else
                                <img id="formLogoPreview" src="" alt="{{ $shop->name }}" style="display:none; width:100%; height:100%; object-fit:contain; padding:4px;">
                                <div id="formLogoFallback" style="display:flex; width:100%; height:100%; background:linear-gradient(135deg, #0D9488 0%, #0891B2 100%); color:#ffffff; align-items:center; justify-content:center; font-family:'Noto Sans Bengali','SolaimanLipi','Baloo Da 2',sans-serif; font-weight:800; font-size:26px;">
                                    {{ mb_substr($shop->name ?? '?', 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <div style="flex:1; min-width:230px;">
                            <div style="font-size:13.5px; font-weight:700; color:var(--ink-900); margin-bottom:2px;">
                                <span class="bn">দোকানের লোগো</span>
                                <span class="en" style="display:none;">Shop Logo</span>
                            </div>
                            <div style="font-size:11.5px; color:var(--ink-500); margin-bottom:10px;">
                                <span class="bn">JPG, PNG, WEBP, GIF বা SVG ফরম্যাট (সর্বোচ্চ 2MB) — ইনভয়েস ও রসিদে প্রদর্শিত হবে</span>
                                <span class="en" style="display:none;">JPG, PNG, WEBP, GIF or SVG (Max 2MB) — printed on customer invoices</span>
                            </div>

                            <input type="file" id="logoInput" name="logo" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" style="display:none;">
                            <input type="hidden" id="removeLogoInput" name="remove_logo" value="0">

                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                <x-core::button
                                    type="button"
                                    id="btnUploadLogo"
                                    variant="secondary"
                                    size="sm"
                                    icon="upload"
                                >
                                    <span class="bn">লোগো পরিবর্তন</span>
                                    <span class="en" style="display:none;">Change Logo</span>
                                </x-core::button>

                                <x-core::button
                                    type="button"
                                    id="btnRemoveLogo"
                                    color="danger"
                                    variant="soft"
                                    size="sm"
                                    icon="trash-2"
                                    style="{{ $shop->logo ? '' : 'display:none;' }}"
                                >
                                    <span class="bn">লোগো মুছুন</span>
                                    <span class="en" style="display:none;">Remove Logo</span>
                                </x-core::button>

                                <span id="logoFileBadge" style="font-size:11.5px; color:var(--teal-800); font-weight:600; display:none; background:var(--teal-100); border:1px solid var(--teal-200); padding:2px 8px; border-radius:6px;"></span>
                            </div>

                            @error('logo')
                                <div style="font-size:11.5px; color:var(--red-600); margin-top:6px; font-weight:500;">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    {{-- Section 1: Basic Information --}}
                    <div style="margin-bottom:18px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
                        <x-core::icon name="store" size="18" style="color:var(--teal-800);" />
                        <span style="font-size:14px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">দোকানের পরিচিতি ও সাধারণ তথ্য</span>
                            <span class="en" style="display:none;">Shop Identity & General Info</span>
                        </span>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:24px;">
                        <x-core::input
                            name="name"
                            label="দোকানের নাম"
                            label-en="Shop Name"
                            value="{{ old('name', $shop->name) }}"
                            placeholder="দোকানের নাম লিখুন"
                            placeholder-en="Enter shop name"
                            size="sm"
                            icon="store"
                            :required="true"
                        />

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <x-core::input
                                name="store_code_display"
                                label="দোকান কোড (সিস্টেম আইডি)"
                                label-en="Store Code (System ID)"
                                value="{{ $shop->store_code ? '#' . $shop->store_code : 'N/A' }}"
                                size="sm"
                                icon="hash"
                                :readonly="true"
                                helper="সিস্টেম নির্ধারিত ইউনিক কোড"
                                helper-en="System assigned unique code"
                            />

                            <x-core::input
                                name="slug_display"
                                label="দোকান স্লাগ (ইউনিক আইডি)"
                                label-en="Shop Slug (Unique ID)"
                                value="{{ $shop->slug }}"
                                size="sm"
                                icon="link"
                                :readonly="true"
                                helper="দোকানের ওয়েব শনাক্তকারী কী"
                                helper-en="Web identifier key"
                            />
                        </div>
                    </div>

                    {{-- Section 2: Contact Information --}}
                    <div style="margin-bottom:18px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
                        <x-core::icon name="phone" size="18" style="color:var(--teal-800);" />
                        <span style="font-size:14px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">যোগাযোগের বিবরণ</span>
                            <span class="en" style="display:none;">Contact Details</span>
                        </span>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:24px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <x-core::input
                                name="phone"
                                label="মোবাইল / ফোন নম্বর"
                                label-en="Phone Number"
                                value="{{ old('phone', $shop->phone) }}"
                                placeholder="যেমন: 017xxxxxxxx"
                                placeholder-en="e.g. 017xxxxxxxx"
                                size="sm"
                                icon="phone"
                            />

                            <x-core::input
                                name="email"
                                type="email"
                                label="অফিসিয়াল ইমেইল"
                                label-en="Official Email"
                                value="{{ old('email', $shop->email) }}"
                                placeholder="যেমন: contact@example.com"
                                placeholder-en="e.g. contact@example.com"
                                size="sm"
                                icon="mail"
                            />
                        </div>

                        <x-core::textarea
                            name="address"
                            label="দোকানের পূর্ণ ঠিকানা"
                            label-en="Shop Address"
                            value="{{ old('address', $shop->address) }}"
                            placeholder="যেমন: বাড়ি ১২, রোড ৪, সেক্টর ৭, উত্তরা, ঢাকা"
                            placeholder-en="e.g. House 12, Road 4, Sector 7, Uttara, Dhaka"
                            size="sm"
                            rows="3"
                            icon="map-pin"
                        />
                    </div>

                    {{-- Section 3: Invoice & Branding Settings --}}
                    <div style="margin-bottom:18px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
                        <x-core::icon name="file-text" size="18" style="color:var(--teal-800);" />
                        <span style="font-size:14px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">ইনভয়েস ও রসিদ সেটিংস</span>
                            <span class="en" style="display:none;">Invoice & Receipt Settings</span>
                        </span>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:26px;">
                        <div style="max-width:280px;">
                            <x-core::input
                                name="currency_symbol"
                                label="মুদ্রা প্রতীক"
                                label-en="Currency Symbol"
                                value="{{ old('currency_symbol', $shop->currency_symbol ?? '৳') }}"
                                placeholder="যেমন: ৳ বা $"
                                placeholder-en="e.g. ৳ or $"
                                size="sm"
                                icon="coins"
                                helper="রসিদ ও হিসাবে প্রদর্শিত মুদ্রা চিহ্ন"
                                helper-en="Symbol shown on receipts & accounts"
                            />
                        </div>

                        <x-core::textarea
                            name="invoice_footer"
                            label="ইনভয়েস ফুটার নোট / শর্তাবলী"
                            label-en="Invoice Footer Note / Terms"
                            value="{{ old('invoice_footer', $shop->invoice_footer) }}"
                            placeholder="যেমন: বিক্রীত পণ্য ৭ দিনের মধ্যে পরিবর্তনের সুযোগ রয়েছে। ক্যাশ মেমো সাথে রাখবেন। ধন্যবাদ!"
                            placeholder-en="e.g. Sold items can be exchanged within 7 days. Please bring cash memo. Thank you!"
                            size="sm"
                            rows="3"
                            icon="file-text"
                            helper="ইনভয়েস বা বিক্রয় রসিদের নিচে এই লেখাটি মুদ্রিত হবে"
                            helper-en="This note will be printed at the bottom of customer invoices"
                        />
                    </div>

                    {{-- Form Actions --}}
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; padding-top:16px; border-top:1px solid var(--border);">
                        <x-core::button
                            type="submit"
                            color="primary"
                            size="sm"
                            icon="save"
                        >
                            <span class="bn">সেটিংস সংরক্ষণ করুন</span>
                            <span class="en" style="display:none;">Save Shop Settings</span>
                        </x-core::button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                const $logoInput = $('#logoInput');
                const $removeLogoInput = $('#removeLogoInput');
                const $btnUploadLogo = $('#btnUploadLogo');
                const $btnRemoveLogo = $('#btnRemoveLogo');
                const $leftLogoChangeBtn = $('#leftLogoChangeBtn');
                const $formLogoPreview = $('#formLogoPreview');
                const $formLogoFallback = $('#formLogoFallback');
                const $leftLogoImg = $('#leftLogoImg');
                const $leftLogoFallback = $('#leftLogoFallback');
                const $logoFileBadge = $('#logoFileBadge');

                // Trigger file selector from buttons
                $btnUploadLogo.on('click', function () {
                    $logoInput.trigger('click');
                });

                $leftLogoChangeBtn.on('click', function () {
                    $logoInput.trigger('click');
                });

                // Handle file selection
                $logoInput.on('change', function () {
                    const file = this.files && this.files[0];
                    if (!file) {
                        return;
                    }

                    // Max 2MB (2 * 1024 * 1024 bytes)
                    if (file.size > 2 * 1024 * 1024) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'ফাইল সাইজ খুব বড়',
                                text: 'লোগোর সাইজ সর্বোচ্চ ২ মেগাবাইট (2MB) হতে পারবে।',
                                confirmButtonText: 'ঠিক আছে',
                            });
                        } else if (typeof toast === 'function') {
                            toast('লোগোর সাইজ সর্বোচ্চ ২ মেগাবাইট (2MB) হতে পারবে।', 'Max logo size is 2MB');
                        }
                        $logoInput.val('');
                        return;
                    }

                    const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
                    if (!allowedMimes.includes(file.type)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'অসমর্থিত ফরম্যাট',
                                text: 'শুধুমাত্র JPG, PNG, WEBP, GIF বা SVG ফরম্যাটের ছবি আপলোড করুন।',
                                confirmButtonText: 'ঠিক আছে',
                            });
                        } else if (typeof toast === 'function') {
                            toast('শুধুমাত্র JPG, PNG, WEBP, GIF বা SVG ফরম্যাটের ছবি আপলোড করুন।', 'Only JPG, PNG, WEBP, GIF or SVG files allowed');
                        }
                        $logoInput.val('');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const previewSrc = e.target.result;
                        $formLogoPreview.attr('src', previewSrc).show();
                        $formLogoFallback.hide();

                        $leftLogoImg.attr('src', previewSrc).show();
                        $leftLogoFallback.hide();

                        $removeLogoInput.val('0');
                        $btnRemoveLogo.show();
                        $logoFileBadge.text(file.name).show();
                    };
                    reader.readAsDataURL(file);
                });

                // Handle remove logo
                $btnRemoveLogo.on('click', function () {
                    $logoInput.val('');
                    $removeLogoInput.val('1');

                    $formLogoPreview.attr('src', '').hide();
                    $formLogoFallback.css('display', 'flex').show();

                    $leftLogoImg.attr('src', '').hide();
                    $leftLogoFallback.css('display', 'flex').show();

                    $logoFileBadge.hide().text('');
                    $btnRemoveLogo.hide();
                });
            });
        </script>
    @endpush
</x-core::layout>
