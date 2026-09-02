<x-core::layout
    title="দোকান সম্পাদনা"
    title-en="Edit Shop"
    subtitle="দোকানের তথ্য, সাবস্ক্রিপশন প্যাকেজ ও এডমিন অ্যাকাউন্ট পরিচালনা করুন"
    subtitle-en="Manage shop details, active subscription plan, and shop administrators"
    active="shops"
>
    @php
        $selectedFeatures = (array) old('features', $shop->enabled_features ?? []);
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
    .shop-edit-grid {
        display: grid;
        grid-template-columns: 1.25fr 0.75fr;
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
        .shop-edit-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <div class="panel" style="margin-top:0; width:100%;">
        <div class="panel-head" style="padding:16px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; border-radius:10px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <x-core::icon name="shopping-bag" size="20" />
                </div>
                <div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <span class="panel-title bn" style="font-size:16px;">{{ $shop->name }}</span>
                        <span class="panel-title en" style="display:none; font-size:16px;">{{ $shop->name }}</span>
                        <x-core::badge color="grey" size="xs" variant="outline">{{ $shop->slug }}</x-core::badge>
                        @if ($shop->store_code)
                            <x-core::badge color="teal" size="xs" variant="soft">#{{ $shop->store_code }}</x-core::badge>
                        @endif
                        <x-core::badge
                            :color="$shop->status === 'active' ? 'green' : 'grey'"
                            size="xs"
                            :dot="true"
                            :label="$shop->status === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়'"
                            :label-en="$shop->status === 'active' ? 'Active' : 'Inactive'"
                        />
                    </div>
                    <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                        <span class="bn">নিবন্ধনের তারিখ: {{ $shop->created_at ? $shop->created_at->format('d M, Y (h:i A)') : '—' }}</span>
                        <span class="en" style="display:none;">Created: {{ $shop->created_at ? $shop->created_at->format('d M, Y (h:i A)') : '—' }}</span>
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
            <div class="shop-edit-grid">
                {{-- Left Column: Forms for Details, Subscription & Admins --}}
                <div style="display:flex; flex-direction:column; gap:22px;">

                    {{-- Form 1: Shop Basic Info & Feature Access --}}
                    <div class="panel" style="margin-top:0;">
                        <div class="panel-head" style="padding:14px 18px;">
                            <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:15px;">
                                <x-core::icon name="edit" size="18" style="color:var(--teal-800);" />
                                <span class="bn">দোকানের বিবরণ ও সক্রিয় ফিচার</span>
                                <span class="en" style="display:none;">Shop Details & Feature Modules</span>
                            </div>
                        </div>
                        <div class="panel-body" style="padding:18px;">
                            <form method="POST" action="{{ route('shops.update', $shop) }}">
                                @csrf
                                @method('PUT')

                                <div style="margin-bottom:12px;">
                                    <x-core::input
                                        name="name"
                                        id="edit-shop-name-input"
                                        label="দোকানের নাম"
                                        label-en="Shop Name"
                                        icon="shopping-bag"
                                        :value="old('name', $shop->name)"
                                        required
                                    />
                                </div>

                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                                    <div>
                                        <x-core::input
                                            name="slug"
                                            id="edit-shop-slug-input"
                                            label="স্লাগ (URL আইডেন্টিফায়ার)"
                                            label-en="Slug (URL)"
                                            icon="globe"
                                            :value="old('slug', $shop->slug)"
                                            required
                                        />
                                        <div id="edit-slug-availability-msg" class="availability-feedback"></div>
                                    </div>
                                    <div>
                                        <x-core::input
                                            name="store_code"
                                            id="edit-shop-store-code-input"
                                            label="স্টোর কোড (Store Code)"
                                            label-en="Store Code"
                                            icon="tag"
                                            placeholder="যেমন: shop-001, shop-002"
                                            :value="old('store_code', $shop->store_code)"
                                        />
                                        <div id="edit-store-code-availability-msg" class="availability-feedback"></div>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                                    <div>
                                        <x-core::input
                                            name="phone"
                                            id="edit-shop-phone-input"
                                            label="ফোন / মোবাইল নম্বর"
                                            label-en="Phone Number"
                                            icon="phone"
                                            placeholder="017xxxxxxxx"
                                            :value="old('phone', $shop->phone)"
                                        />
                                    </div>
                                    <div>
                                        <x-core::form-group name="status" label="দোকানের অবস্থা" label-en="Shop Status" icon="check-circle" required>
                                            <select name="status" id="edit-shop-status-select" class="form-control form-select" required>
                                                <option value="active" {{ old('status', $shop->status) === 'active' ? 'selected' : '' }}>
                                                    সক্রিয় (Active)
                                                </option>
                                                <option value="inactive" {{ old('status', $shop->status) === 'inactive' ? 'selected' : '' }}>
                                                    নিষ্ক্রিয় (Inactive)
                                                </option>
                                            </select>
                                        </x-core::form-group>
                                    </div>
                                </div>

                                <div style="margin-bottom:18px;">
                                    <x-core::textarea
                                        name="address"
                                        id="edit-shop-address-input"
                                        label="দোকানের ঠিকানা"
                                        label-en="Shop Address"
                                        icon="map-pin"
                                        rows="2"
                                        :value="old('address', $shop->address)"
                                    />
                                </div>

                                {{-- Features Section inside Form 1 --}}
                                <div style="border-top:1px solid var(--border); padding-top:16px; margin-bottom:18px;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-weight:700; font-size:13.5px; color:var(--ink-800);">
                                                <span class="bn">সক্রিয় মডিউল ও ফিচার</span>
                                                <span class="en" style="display:none;">Active Modules & Features</span>
                                            </span>
                                            <x-core::badge id="edit-selected-features-count" color="teal" size="xs">
                                                {{ count($selectedFeatures) }} টি নির্বাচিত
                                            </x-core::badge>
                                        </div>

                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <x-core::button
                                                type="button"
                                                variant="soft"
                                                color="teal"
                                                size="xs"
                                                id="btn-edit-select-all-features"
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
                                                id="btn-edit-deselect-all-features"
                                                icon="x"
                                            >
                                                <span class="bn">সব বাতিল</span>
                                                <span class="en" style="display:none;">Deselect All</span>
                                            </x-core::button>
                                        </div>
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
                                                    class="edit-feature-checkbox"
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

                                <div style="display:flex; justify-content:flex-end;">
                                    <x-core::button
                                        type="submit"
                                        variant="solid"
                                        color="gold"
                                        size="md"
                                        icon="save"
                                    >
                                        <span class="bn">দোকানের তথ্য হালনাগাদ করুন</span>
                                        <span class="en" style="display:none;">Update Shop Details</span>
                                    </x-core::button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Form 2: Subscription Management --}}
                    <div class="panel" style="margin-top:0;">
                        <div class="panel-head" style="padding:14px 18px; display:flex; align-items:center; justify-content:space-between;">
                            <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:15px;">
                                <x-core::icon name="sparkles" size="18" style="color:var(--teal-800);" />
                                <span class="bn">সাবস্ক্রিপশন প্যাকেজ ও মেয়াদ</span>
                                <span class="en" style="display:none;">Subscription & Billing</span>
                            </div>

                            @if ($subscription && $subscription->plan)
                                <x-core::badge color="teal" size="xs" variant="soft">
                                    {{ $subscription->plan->name }} ({{ $subscription->statusLabel()['bn'] }})
                                </x-core::badge>
                            @endif
                        </div>
                        <div class="panel-body" style="padding:18px;">
                            <form method="POST" action="{{ route('shops.subscription.update', $shop) }}">
                                @csrf
                                @method('PUT')

                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                                    <div>
                                        <x-core::form-group name="plan_id" label="সাবস্ক্রিপশন প্ল্যান" label-en="Subscription Plan" icon="tag" required>
                                            <select name="plan_id" class="form-control form-select" required>
                                                <option value="" disabled {{ $subscription?->plan_id ? '' : 'selected' }}>-- প্ল্যান নির্বাচন করুন --</option>
                                                @foreach ($plans as $plan)
                                                    <option value="{{ $plan->id }}" {{ (string) old('plan_id', $subscription->plan_id ?? '') === (string) $plan->id ? 'selected' : '' }}>
                                                        {{ $plan->name }} (৳{{ number_format($plan->price, 0) }}/{{ $plan->billing_cycle === 'yearly' ? 'বছর' : 'মাস' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </x-core::form-group>
                                    </div>
                                    <div>
                                        <x-core::form-group name="status" label="সাবস্ক্রিপশন অবস্থা" label-en="Subscription Status" icon="check-circle" required>
                                            <select name="status" class="form-control form-select" required>
                                                @foreach (\Modules\Shop\Models\Subscription::statusLabels() as $key => $label)
                                                    <option value="{{ $key }}" {{ old('status', $subscription->status ?? 'active') === $key ? 'selected' : '' }}>
                                                        {{ $label['bn'] }} ({{ $label['en'] }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </x-core::form-group>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:16px;">
                                    <div>
                                        <x-core::input
                                            type="date"
                                            name="trial_ends_at"
                                            label="ট্রায়াল সমাপ্তির তারিখ"
                                            label-en="Trial Ends At"
                                            icon="calendar"
                                            :value="old('trial_ends_at', optional($subscription->trial_ends_at ?? null)->format('Y-m-d'))"
                                        />
                                    </div>
                                    <div>
                                        <x-core::input
                                            type="date"
                                            name="current_period_start"
                                            label="বর্তমান মেয়াদ শুরু"
                                            label-en="Period Start Date"
                                            icon="calendar"
                                            :value="old('current_period_start', optional($subscription->current_period_start ?? null)->format('Y-m-d'))"
                                        />
                                    </div>
                                    <div>
                                        <x-core::input
                                            type="date"
                                            name="current_period_end"
                                            label="বর্তমান মেয়াদ শেষ"
                                            label-en="Period End Date"
                                            icon="calendar"
                                            :value="old('current_period_end', optional($subscription->current_period_end ?? null)->format('Y-m-d'))"
                                        />
                                    </div>
                                </div>

                                <div style="display:flex; justify-content:flex-end;">
                                    <x-core::button
                                        type="submit"
                                        variant="solid"
                                        color="teal"
                                        size="md"
                                        icon="refresh"
                                    >
                                        <span class="bn">সাবস্ক্রিপশন হালনাগাদ করুন</span>
                                        <span class="en" style="display:none;">Update Subscription</span>
                                    </x-core::button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Form 3: Shop Admins List & Add New Admin --}}
                    <div class="panel" style="margin-top:0;">
                        <div class="panel-head" style="padding:14px 18px; display:flex; align-items:center; justify-content:space-between;">
                            <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:15px;">
                                <x-core::icon name="users" size="18" style="color:var(--teal-800);" />
                                <span class="bn">দোকানের এডমিনগণ ({{ count($admins) }})</span>
                                <span class="en" style="display:none;">Shop Admins ({{ count($admins) }})</span>
                            </div>
                        </div>
                        <div class="panel-body" style="padding:18px;">
                            {{-- Existing Admins Table --}}
                            <div class="table-container table-teal" style="margin-bottom:20px;">
                                <div class="table-responsive">
                                    <table class="app-table">
                                        <thead>
                                            <tr>
                                                <th class="bn">এডমিনের নাম</th>
                                                <th class="bn">ইমেইল</th>
                                                <th class="bn">রোল / পদবী</th>
                                                <th class="table-cell-right"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($admins as $admin)
                                                <tr>
                                                    <td class="cell-main">
                                                        <div style="display:flex; align-items:center; gap:8px;">
                                                            <div style="width:26px; height:26px; border-radius:50%; background:var(--teal-50); color:var(--teal-800); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11px;">
                                                                {{ mb_substr($admin->name, 0, 1) }}
                                                            </div>
                                                            <span style="font-weight:600; color:var(--ink-900);">{{ $admin->name }}</span>
                                                        </div>
                                                    </td>
                                                    <td style="font-family:monospace; font-size:12px; color:var(--ink-700);">
                                                        {{ $admin->email }}
                                                    </td>
                                                    <td>
                                                        @foreach ($admin->roles as $role)
                                                            <x-core::badge color="teal" size="xs" variant="soft">{{ $role->name }}</x-core::badge>
                                                        @endforeach
                                                        @if ($admin->roles->isEmpty())
                                                            <span style="color:var(--ink-400);">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="table-cell-right">
                                                        <form
                                                            method="POST"
                                                            action="{{ route('shops.admins.destroy', [$shop, $admin]) }}"
                                                            onsubmit="return confirm('এই এডমিনকে মুছে ফেলতে চান? / Are you sure you want to delete this admin?');"
                                                        >
                                                            @csrf
                                                            @method('DELETE')
                                                            <x-core::button
                                                                type="submit"
                                                                variant="soft"
                                                                color="red"
                                                                size="xs"
                                                                icon="trash-2"
                                                                icon-only
                                                                title="মুছুন / Delete"
                                                            />
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" style="text-align:center; padding:20px; color:var(--ink-400);">
                                                        কোনো এডমিন যোগ করা নেই
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Add New Admin Box --}}
                            <div class="panel" style="margin-top:0; border:1px solid var(--border); box-shadow:none; background:var(--paper);">
                                <div class="panel-head" style="padding:10px 14px; background:var(--card); border-bottom:1px solid var(--border);">
                                    <div class="panel-title" style="font-size:13.5px; display:flex; align-items:center; gap:6px;">
                                        <x-core::icon name="user-plus" size="16" style="color:var(--teal-800);" />
                                        <span class="bn">নতুন এডমিন যোগ করুন</span>
                                        <span class="en" style="display:none;">Add New Admin</span>
                                    </div>
                                </div>
                                <div class="panel-body" style="padding:16px;">
                                    <form method="POST" action="{{ route('shops.admins.store', $shop) }}">
                                        @csrf

                                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px; margin-bottom:10px;">
                                            <div>
                                                <x-core::input
                                                    name="name"
                                                    label="নাম"
                                                    label-en="Name"
                                                    icon="user"
                                                    placeholder="এডমিনের নাম"
                                                    required
                                                />
                                            </div>
                                            <div>
                                                <x-core::input
                                                    type="email"
                                                    name="email"
                                                    label="ইমেইল"
                                                    label-en="Email"
                                                    icon="mail"
                                                    placeholder="admin@example.com"
                                                    required
                                                />
                                            </div>
                                        </div>

                                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:10px; margin-bottom:10px;">
                                            <div>
                                                <x-core::input
                                                    type="password"
                                                    password-toggle
                                                    name="password"
                                                    label="পাসওয়ার্ড"
                                                    label-en="Password"
                                                    icon="lock"
                                                    placeholder="পাসওয়ার্ড"
                                                    required
                                                />
                                            </div>
                                            <div>
                                                <x-core::input
                                                    type="password"
                                                    password-toggle
                                                    name="password_confirmation"
                                                    label="পাসওয়ার্ড নিশ্চিত করুন"
                                                    label-en="Confirm Password"
                                                    icon="lock"
                                                    placeholder="পুনরায় লিখুন"
                                                    required
                                                />
                                            </div>
                                            <div>
                                                <x-core::form-group name="role" label="রোল" label-en="Role" icon="shield" required>
                                                    <select name="role" class="form-control form-select" required>
                                                        <option value="" disabled selected>-- রোল নির্বাচন --</option>
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </x-core::form-group>
                                            </div>
                                        </div>

                                        <div style="display:flex; justify-content:flex-end;">
                                            <x-core::button
                                                type="submit"
                                                variant="solid"
                                                color="teal"
                                                size="sm"
                                                icon="user-plus"
                                            >
                                                <span class="bn">এডমিন যোগ করুন</span>
                                                <span class="en" style="display:none;">Add Admin</span>
                                            </x-core::button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Shop Summary Overview Card --}}
                <div style="display:flex; flex-direction:column; gap:20px;">
                    <div class="panel" style="margin-top:0; border:1px solid var(--border); box-shadow:var(--shadow-card);">
                        <div class="panel-head" style="padding:12px 18px; background:var(--paper); border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                            <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--ink-800);">
                                <x-core::icon name="eye" size="16" style="color:var(--teal-800);" />
                                <span class="bn">দোকানের সংক্ষিপ্ত বিবরণ</span>
                                <span class="en" style="display:none;">Shop Overview</span>
                            </div>
                            <x-core::badge
                                id="edit-preview-status-badge"
                                :color="$shop->status === 'active' ? 'green' : 'grey'"
                                size="xs"
                                :dot="true"
                                :label="$shop->status === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়'"
                                :label-en="$shop->status === 'active' ? 'Active' : 'Inactive'"
                            />
                        </div>
                        <div class="panel-body" style="padding:18px;">
                            <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:14px;">
                                <div style="width:42px; height:42px; border-radius:10px; background:var(--teal-50); color:var(--teal-700); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <x-core::icon name="shopping-bag" size="20" />
                                </div>
                                <div style="min-width:0; flex:1;">
                                    <div id="edit-preview-shop-name" style="font-weight:800; font-size:16.5px; color:var(--ink-900); word-break:break-word;">
                                        {{ $shop->name }}
                                    </div>
                                    <div style="display:flex; align-items:center; gap:5px; margin-top:3px; flex-wrap:wrap;">
                                        <span id="edit-preview-shop-slug" style="font-size:11.5px; color:var(--ink-600); font-family:monospace; background:var(--ink-50); padding:1px 6px; border-radius:4px; border:1px solid var(--border);">
                                            {{ $shop->slug }}
                                        </span>
                                        <span id="edit-preview-shop-store-code" style="font-size:11.5px; color:var(--teal-800); font-weight:700; font-family:monospace; background:var(--teal-50); padding:1px 6px; border-radius:4px; border:1px solid var(--teal-200);">
                                            #{{ $shop->store_code ?: 'STORE-CODE' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div style="background:var(--paper); border-radius:8px; border:1px solid var(--border); padding:10px 12px; margin-bottom:14px; font-size:12px; display:flex; flex-direction:column; gap:6px;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <x-core::icon name="phone" size="13" style="color:var(--ink-500);" />
                                    <span id="edit-preview-shop-phone" style="font-family:monospace; color:var(--ink-700);">
                                        {{ $shop->phone ?: 'মোবাইল নম্বর দেওয়া হয়নি' }}
                                    </span>
                                </div>
                                <div style="display:flex; align-items:baseline; gap:6px;">
                                    <x-core::icon name="map-pin" size="13" style="color:var(--ink-500);" />
                                    <span id="edit-preview-shop-address" style="color:var(--ink-600); word-break:break-word;">
                                        {{ $shop->address ?: 'ঠিকানা দেওয়া হয়নি' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Active Subscription Info box --}}
                            <div style="background:var(--teal-50); border:1px solid var(--teal-100); border-radius:8px; padding:12px; margin-bottom:14px;">
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                                    <div style="font-size:11px; font-weight:700; color:var(--teal-900); text-transform:uppercase; letter-spacing:0.5px;">
                                        <span class="bn">বর্তমান সাবস্ক্রিপশন</span>
                                        <span class="en" style="display:none;">Current Plan</span>
                                    </div>
                                    @if ($subscription)
                                        <x-core::badge color="teal" size="xs">
                                            {{ $subscription->statusLabel()['bn'] }}
                                        </x-core::badge>
                                    @endif
                                </div>
                                @if ($subscription && $subscription->plan)
                                    <div style="font-size:15px; font-weight:800; color:var(--teal-900);">
                                        {{ $subscription->plan->name }}
                                        <span style="font-size:12px; font-weight:600; color:var(--teal-700);">
                                            (৳{{ number_format($subscription->plan->price, 0) }}/{{ $subscription->plan->billing_cycle === 'yearly' ? 'বছর' : 'মাস' }})
                                        </span>
                                    </div>
                                    <div style="font-size:11px; color:var(--teal-800); margin-top:4px;">
                                        @if ($subscription->ends_at)
                                            মেয়াদ শেষ: {{ $subscription->ends_at->format('d M, Y') }}
                                        @elseif ($subscription->trial_ends_at)
                                            ট্রায়াল শেষ: {{ $subscription->trial_ends_at->format('d M, Y') }}
                                        @endif
                                    </div>
                                @else
                                    <div style="font-size:12.5px; color:var(--ink-500);">
                                        কোনো সক্রিয় সাবস্ক্রিপশন নেই
                                    </div>
                                @endif
                            </div>

                            {{-- Feature tags preview --}}
                            <div style="font-size:11.5px; font-weight:700; color:var(--ink-700); margin-bottom:6px;">
                                <span class="bn">সক্রিয় মডিউলসমূহ:</span>
                                <span class="en" style="display:none;">Active Modules:</span>
                            </div>
                            <div id="edit-preview-feature-tags" style="display:flex; flex-wrap:wrap; gap:5px; min-height:28px;">
                                <!-- Populated via jQuery -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        function initShopEditInteractions() {
            if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                setTimeout(initShopEditInteractions, 30);
                return;
            }

            var $ = window.jQuery;
            var availabilityUrl = "{{ route('shops.check-availability') }}";
            var shopId = "{{ $shop->id }}";
            var checkTimer = null;

            var $slugInput = $('#edit-shop-slug-input');
            var $storeCodeInput = $('#edit-shop-store-code-input');
            var $slugMsg = $('#edit-slug-availability-msg');
            var $storeCodeMsg = $('#edit-store-code-availability-msg');

            // 1. AJAX Check Availability Function for Edit
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
                        store_code: storeCodeVal,
                        ignore_id: shopId
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
                var $checked = $('input.edit-feature-checkbox:checked');
                $('#edit-selected-features-count').text($checked.length + ' টি নির্বাচিত');

                var $tagsContainer = $('#edit-preview-feature-tags');
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
            $(document).on('change', 'input.edit-feature-checkbox', function () {
                var isChecked = $(this).is(':checked');
                $(this).closest('.feature-card').toggleClass('active', isChecked);
                updateFeaturesPreview();
            });

            // Select All Features
            $(document).on('click', '#btn-edit-select-all-features', function (e) {
                e.preventDefault();
                $('input.edit-feature-checkbox').prop('checked', true);
                $('.feature-card').addClass('active');
                updateFeaturesPreview();
            });

            // Deselect All Features
            $(document).on('click', '#btn-edit-deselect-all-features', function (e) {
                e.preventDefault();
                $('input.edit-feature-checkbox').prop('checked', false);
                $('.feature-card').removeClass('active');
                updateFeaturesPreview();
            });

            // 3. Live updates for shop details in right card
            $(document).on('input', '#edit-shop-name-input', function () {
                $('#edit-preview-shop-name').text($(this).val() || 'দোকানের নাম');
            });

            $(document).on('input', '#edit-shop-slug-input', function () {
                $('#edit-preview-shop-slug').text($(this).val() || 'shop-slug');
                scheduleAvailabilityCheck();
            });

            $(document).on('input', '#edit-shop-store-code-input', function () {
                $('#edit-preview-shop-store-code').text('#' + ($(this).val() || 'STORE-CODE'));
                scheduleAvailabilityCheck();
            });

            $(document).on('input', '#edit-shop-phone-input', function () {
                $('#edit-preview-shop-phone').text($(this).val() || 'মোবাইল নম্বর দেওয়া হয়নি');
            });

            $(document).on('input', '#edit-shop-address-input', function () {
                $('#edit-preview-shop-address').text($(this).val() || 'ঠিকানা দেওয়া হয়নি');
            });

            $(document).on('change', '#edit-shop-status-select', function () {
                var isActive = $(this).val() === 'active';
                var $badge = $('#edit-preview-status-badge');
                $badge.removeClass('b-green b-grey badge-green badge-grey')
                    .addClass(isActive ? 'b-green badge-green' : 'b-grey badge-grey');
                if ($badge.find('.bn').length) {
                    $badge.find('.bn').text(isActive ? 'সক্রিয়' : 'নিষ্ক্রিয়');
                    $badge.find('.en').text(isActive ? 'Active' : 'Inactive');
                } else {
                    $badge.text(isActive ? 'সক্রিয়' : 'নিষ্ক্রিয়');
                }
            });

            updateFeaturesPreview();

            if ($slugInput.val() || $storeCodeInput.val()) {
                performAvailabilityCheck();
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initShopEditInteractions);
        } else {
            initShopEditInteractions();
        }
    })();
    </script>
    @endpush
</x-core::layout>
