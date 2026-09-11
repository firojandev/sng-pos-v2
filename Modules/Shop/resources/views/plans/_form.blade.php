@php
    $selectedFeatures = (array) old('features', $plan->relationLoaded('features') ? $plan->features->pluck('slug')->all() : []);
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
        'report-sales' => 'file-text',
        'report-purchase' => 'file-text',
        'report-stock' => 'file-text',
        'report-products' => 'file-text',
        'report-profit-loss' => 'file-text',
        'report-income' => 'file-text',
        'report-expense' => 'file-text',
        'audit' => 'shield',
        'employees' => 'user',
        'users' => 'lock',
    ];
@endphp

<style>
.plan-form-grid {
    display: grid;
    grid-template-columns: 1.18fr 0.82fr;
    gap: 20px;
    align-items: start;
    width: 100%;
}
@media (max-width: 1024px) {
    .plan-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="plan-form-grid">
    {{-- Left Column: General Info & Included Features --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        {{-- Card 1: Basic Plan Information & Pricing --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head" style="padding:14px 18px;">
                <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:15px;">
                    <x-core::icon name="sparkles" size="18" style="color:var(--teal-800);" />
                    <span class="bn">প্রাথমিক বিবরণ ও মূল্য নির্ধারণ</span>
                    <span class="en" style="display:none;">Basic Information & Pricing</span>
                </div>
            </div>
            <div class="panel-body" style="padding:18px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:12px;">
                    <div>
                        <x-core::input
                            name="name"
                            id="plan-name-input"
                            label="প্ল্যানের নাম"
                            label-en="Plan Name"
                            icon="tag"
                            placeholder="যেমন: Professional, Enterprise"
                            :value="old('name', $plan->name)"
                            required
                        />
                    </div>
                    <div>
                        <x-core::input
                            name="slug"
                            id="plan-slug-input"
                            label="স্লাগ (URL আইডেন্টিফায়ার)"
                            label-en="Slug"
                            icon="globe"
                            placeholder="যেমন: professional"
                            :value="old('slug', $plan->slug)"
                            required
                        />
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px; margin-bottom:12px;">
                    <div>
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            name="price"
                            id="plan-price-input"
                            label="প্ল্যানের মূল্য"
                            label-en="Plan Price"
                            icon="cash"
                            prefix="৳"
                            :value="old('price', $plan->price ?? 0)"
                            required
                        />
                    </div>
                    <div>
                        <x-core::form-group name="billing_cycle" label="বিলিং সাইকেল" label-en="Billing Cycle" icon="refresh" required>
                            <select name="billing_cycle" id="plan-billing-cycle-input" class="form-control form-select" required>
                                <option value="monthly" {{ old('billing_cycle', $plan->billing_cycle ?? 'monthly') === 'monthly' ? 'selected' : '' }}>
                                    মাসিক (Monthly)
                                </option>
                                <option value="yearly" {{ old('billing_cycle', $plan->billing_cycle ?? 'monthly') === 'yearly' ? 'selected' : '' }}>
                                    বাৎসরিক (Yearly)
                                </option>
                            </select>
                        </x-core::form-group>
                    </div>
                    <div>
                        <x-core::form-group name="status" label="প্ল্যান অবস্থা" label-en="Plan Status" icon="check-circle" required>
                            <select name="status" id="plan-status-input" class="form-control form-select" required>
                                <option value="active" {{ old('status', $plan->status ?? 'active') === 'active' ? 'selected' : '' }}>
                                    সক্রিয় (Active)
                                </option>
                                <option value="inactive" {{ old('status', $plan->status ?? 'active') === 'inactive' ? 'selected' : '' }}>
                                    নিষ্ক্রিয় (Inactive)
                                </option>
                            </select>
                        </x-core::form-group>
                    </div>
                </div>

                {{-- Plan Order & Popular Badge Settings --}}
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; align-items:flex-start; padding-top:12px; border-top:1px dashed var(--border);">
                    <div>
                        <x-core::input
                            type="number"
                            name="sort_order"
                            id="plan-sort-order-input"
                            label="ডিসপ্লে ক্রম (Display Order)"
                            label-en="Display Order"
                            icon="hash"
                            placeholder="যেমন: 1, 2, 3..."
                            min="0"
                            :value="old('sort_order', $plan->sort_order ?? 0)"
                            helper="কম ক্রমের প্ল্যান আগে প্রদর্শিত হবে (১, ২, ৩...)"
                            helper-en="Lower numbers appear first"
                        />
                    </div>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <div>
                            <label class="form-label" style="margin-bottom:6px; display:block;">
                                <span class="bn">জনপ্রিয় প্ল্যান ট্যাগ (Popular Badge)</span>
                                <span class="en" style="display:none;">Popular Plan Badge</span>
                            </label>
                            <input type="hidden" name="is_popular" value="0">
                            <label class="form-toggle-wrap form-toggle-sm form-primary" style="margin-top:2px; cursor:pointer;">
                                <input
                                    type="checkbox"
                                    name="is_popular"
                                    id="plan-popular-input"
                                    value="1"
                                    {{ old('is_popular', $plan->is_popular ?? false) ? 'checked' : '' }}
                                />
                                <span class="form-toggle-track">
                                    <span class="form-toggle-thumb"></span>
                                </span>
                                <span class="form-toggle-label">
                                    <span class="bn">জনপ্রিয় প্ল্যান হিসেবে মার্ক করুন</span>
                                    <span class="en" style="display:none;">Mark as Popular Plan</span>
                                </span>
                            </label>
                        </div>
                        <div id="popular-label-container" style="{{ old('is_popular', $plan->is_popular ?? false) ? '' : 'display:none;' }}">
                            <x-core::input
                                name="popular_label"
                                id="plan-popular-label-input"
                                label="জনপ্রিয় লেবেল টেক্সট (ঐচ্ছিক)"
                                label-en="Popular Label (Optional)"
                                icon="star"
                                placeholder="যেমন: সেরা অফার (Best Value) অথবা সর্বাধিক জনপ্রিয়"
                                :value="old('popular_label', $plan->popular_label)"
                                helper="খালি রাখলে ডিফল্ট 'সর্বাধিক জনপ্রিয় (Most Popular)' প্রদর্শিত হবে"
                                helper-en="Leave empty to use default 'Most Popular'"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Included Features & Modules Selection --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head" style="padding:14px 18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:15px;">
                    <x-core::icon name="shield" size="18" style="color:var(--teal-800);" />
                    <span class="bn">অন্তর্ভুক্ত ফিচার ও মডিউলসমূহ</span>
                    <span class="en" style="display:none;">Included Features & Modules</span>
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
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:10px;">
                    @foreach ($features as $key => $labels)
                        @continue(str_starts_with($key, 'report-'))
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

                <div style="font-weight:700; font-size:12.5px; color:var(--ink-700); margin:16px 0 10px; padding-top:14px; border-top:1px solid var(--border);">
                    <span class="bn">রিপোর্ট মডিউল</span>
                    <span class="en" style="display:none;">Report Modules</span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:10px;">
                    @foreach ($features as $key => $labels)
                        @continue(! str_starts_with($key, 'report-'))
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

    {{-- Right Column: Quotas, Limits & Live Preview Card --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        {{-- Card 3: Resource Quotas & Limits --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head" style="padding:14px 18px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:15px;">
                    <x-core::icon name="box" size="18" style="color:var(--teal-800);" />
                    <span class="bn">রিসোর্স কোটা ও সীমাবদ্ধতা</span>
                    <span class="en" style="display:none;">Resource Quotas & Limits</span>
                </div>
                <x-core::badge color="grey" size="xs" label="খালি রাখলে সীমাহীন (∞)" label-en="Blank for unlimited (∞)" />
            </div>
            <div class="panel-body" style="padding:18px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px;">
                    <div>
                        <x-core::input
                            type="number"
                            min="1"
                            name="max_users"
                            id="plan-users-input"
                            label="সর্বোচ্চ ইউজার"
                            label-en="Max Users"
                            icon="users"
                            placeholder="সীমাহীন (Unlimited)"
                            :value="old('max_users', $plan->max_users)"
                        />
                    </div>
                    <div>
                        <x-core::input
                            type="number"
                            min="1"
                            name="max_branches"
                            id="plan-branches-input"
                            label="সর্বোচ্চ শাখা"
                            label-en="Max Branches"
                            icon="building"
                            placeholder="সীমাহীন (Unlimited)"
                            :value="old('max_branches', $plan->max_branches)"
                        />
                    </div>
                    <div>
                        <x-core::input
                            type="number"
                            min="1"
                            name="max_warehouses"
                            id="plan-warehouses-input"
                            label="সর্বোচ্চ গুদাম"
                            label-en="Max Warehouses"
                            icon="warehouse"
                            placeholder="সীমাহীন (Unlimited)"
                            :value="old('max_warehouses', $plan->max_warehouses)"
                        />
                    </div>
                    <div>
                        <x-core::input
                            type="number"
                            min="1"
                            name="max_products"
                            id="plan-products-input"
                            label="সর্বোচ্চ পণ্য সংখ্যা"
                            label-en="Max Products"
                            icon="box"
                            placeholder="সীমাহীন (Unlimited)"
                            :value="old('max_products', $plan->max_products)"
                        />
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Live Interactive Plan Preview Card --}}
        <div class="panel" style="margin-top:0; border:1px solid var(--border); box-shadow:var(--shadow-card);">
            <div class="panel-head" style="padding:12px 18px; background:var(--paper); border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div class="panel-title" style="display:flex; align-items:center; gap:8px; font-size:13.5px; color:var(--ink-800);">
                    <x-core::icon name="eye" size="16" style="color:var(--teal-800);" />
                    <span class="bn">লাইভ প্ল্যান প্রিভিউ (Live Preview)</span>
                    <span class="en" style="display:none;">Live Plan Preview</span>
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <x-core::badge
                        id="preview-popular-badge"
                        color="gold"
                        size="xs"
                        variant="soft"
                        icon="star"
                        :label="old('popular_label', $plan->popular_label) ?: 'সর্বাধিক জনপ্রিয়'"
                        :label-en="old('popular_label', $plan->popular_label) ?: 'Popular'"
                        style="{{ old('is_popular', $plan->is_popular ?? false) ? '' : 'display:none;' }}"
                    />
                    <x-core::badge
                        id="preview-status-badge"
                        :color="old('status', $plan->status ?? 'active') === 'active' ? 'green' : 'grey'"
                        size="xs"
                        :dot="true"
                        :label="old('status', $plan->status ?? 'active') === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়'"
                        :label-en="old('status', $plan->status ?? 'active') === 'active' ? 'Active' : 'Inactive'"
                    />
                </div>
            </div>
            <div class="panel-body" style="padding:18px;">
                <div style="display:flex; align-items:baseline; justify-content:space-between; margin-bottom:14px; gap:8px;">
                    <div style="min-width:0; flex:1;">
                        <div id="preview-plan-name" style="font-weight:800; font-size:17px; color:var(--ink-900); word-break:break-word;">
                            {{ old('name', $plan->name) ?: 'প্যাকেজের নাম' }}
                        </div>
                        <div id="preview-plan-slug" style="font-size:11.5px; color:var(--ink-500); font-family:monospace; margin-top:2px; word-break:break-all;">
                            {{ old('slug', $plan->slug) ?: 'package-slug' }}
                        </div>
                    </div>
                    <div style="text-align:right; flex-shrink:0;">
                        <span id="preview-plan-price" style="font-size:20px; font-weight:800; color:var(--teal-800);">
                            ৳{{ number_format((float) (old('price', $plan->price) ?? 0), 0) }}
                        </span>
                        <span id="preview-plan-cycle" style="font-size:11.5px; color:var(--ink-600); font-weight:600;">
                            /{{ old('billing_cycle', $plan->billing_cycle ?? 'monthly') === 'yearly' ? 'বছর' : 'মাস' }}
                        </span>
                    </div>
                </div>

                {{-- Quotas preview strip --}}
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:6px; margin-bottom:14px; background:var(--paper); padding:10px 8px; border-radius:8px; border:1px solid var(--border); text-align:center;">
                    <div>
                        <div style="font-size:10px; color:var(--ink-500); font-weight:600;">ইউজার</div>
                        <div id="preview-users-val" style="font-weight:700; font-size:12px; color:var(--ink-900);">{{ old('max_users', $plan->max_users) ?? '∞' }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px; color:var(--ink-500); font-weight:600;">শাখা</div>
                        <div id="preview-branches-val" style="font-weight:700; font-size:12px; color:var(--ink-900);">{{ old('max_branches', $plan->max_branches) ?? '∞' }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px; color:var(--ink-500); font-weight:600;">গুদাম</div>
                        <div id="preview-warehouses-val" style="font-weight:700; font-size:12px; color:var(--ink-900);">{{ old('max_warehouses', $plan->max_warehouses) ?? '∞' }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px; color:var(--ink-500); font-weight:600;">পণ্য</div>
                        <div id="preview-products-val" style="font-weight:700; font-size:12px; color:var(--ink-900);">{{ old('max_products', $plan->max_products) ? number_format((float) old('max_products', $plan->max_products)) : '∞' }}</div>
                    </div>
                </div>

                {{-- Feature tags preview --}}
                <div style="font-size:11.5px; font-weight:700; color:var(--ink-700); margin-bottom:6px;">
                    <span class="bn">নির্বাচিত ফিচারসমূহ:</span>
                    <span class="en" style="display:none;">Active Features:</span>
                </div>
                <div id="preview-feature-tags" style="display:flex; flex-wrap:wrap; gap:5px; min-height:28px;">
                    <!-- Dynamically populated via jQuery -->
                </div>
            </div>
        </div>

        {{-- Form Actions Card --}}
        <div style="display:flex; align-items:center; gap:10px; padding:14px 18px; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm);">
            <x-core::button
                as="a"
                href="{{ route('plans.index') }}"
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
                style="flex:1.4; justify-content:center;"
            >
                <span class="bn">প্ল্যান সংরক্ষণ করুন</span>
                <span class="en" style="display:none;">Save Plan</span>
            </x-core::button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function initPlanFormInteractions() {
        if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
            setTimeout(initPlanFormInteractions, 30);
            return;
        }

        var $ = window.jQuery;

        // 1. Feature tags live update and counter
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

        // 2. Slug Auto-generation from Plan Name
        var $nameInput = $('input[name="name"]');
        var $slugInput = $('input[name="slug"]');
        var slugManuallyEdited = $slugInput.length && $slugInput.val().trim() !== '';

        $(document).on('input', 'input[name="slug"]', function () {
            slugManuallyEdited = true;
            $('#preview-plan-slug').text($(this).val() || 'package-slug');
        });

        $(document).on('input', 'input[name="name"]', function () {
            var val = $(this).val();
            $('#preview-plan-name').text(val || 'প্যাকেজের নাম');
            if (!slugManuallyEdited && $slugInput.length) {
                var slug = val.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                $slugInput.val(slug);
                $('#preview-plan-slug').text(slug || 'package-slug');
            }
        });

        // 3. Live Price & Billing Cycle Updates
        $(document).on('input', 'input[name="price"]', function () {
            var p = parseFloat($(this).val()) || 0;
            $('#preview-plan-price').text('৳' + p.toLocaleString());
        });

        $(document).on('change', 'select[name="billing_cycle"]', function () {
            var cycle = $(this).val() === 'yearly' ? 'বছর' : 'মাস';
            $('#preview-plan-cycle').text('/' + cycle);
        });

        // 4. Live Status Updates
        $(document).on('change', 'select[name="status"]', function () {
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

        // 5. Live Popular Toggle and Label Updates
        $(document).on('change', '#plan-popular-input', function () {
            var isPopular = $(this).is(':checked');
            if (isPopular) {
                $('#popular-label-container').slideDown(150);
                $('#preview-popular-badge').show();
            } else {
                $('#popular-label-container').slideUp(150);
                $('#preview-popular-badge').hide();
            }
        });

        $(document).on('input', '#plan-popular-label-input', function () {
            var label = $(this).val().trim() || 'সর্বাধিক জনপ্রিয়';
            var $badge = $('#preview-popular-badge');
            if ($badge.find('.bn').length) {
                $badge.find('.bn').text(label);
                $badge.find('.en').text(label);
            } else {
                $badge.text(label);
            }
        });

        // 6. Live Quota Limit Updates
        $(document).on('input', 'input[name="max_users"]', function () {
            $('#preview-users-val').text($(this).val() || '∞');
        });
        $(document).on('input', 'input[name="max_branches"]', function () {
            $('#preview-branches-val').text($(this).val() || '∞');
        });
        $(document).on('input', 'input[name="max_warehouses"]', function () {
            $('#preview-warehouses-val').text($(this).val() || '∞');
        });
        $(document).on('input', 'input[name="max_products"]', function () {
            var val = $(this).val();
            $('#preview-products-val').text(val ? parseInt(val).toLocaleString() : '∞');
        });

        // Run initial live sync on load
        function syncAllInitialValues() {
            var nameVal = $('input[name="name"]').val();
            if (nameVal) $('#preview-plan-name').text(nameVal);

            var slugVal = $('input[name="slug"]').val();
            if (slugVal) $('#preview-plan-slug').text(slugVal);

            var priceVal = parseFloat($('input[name="price"]').val());
            if (!isNaN(priceVal)) $('#preview-plan-price').text('৳' + priceVal.toLocaleString());

            var cycleVal = $('select[name="billing_cycle"]').val();
            if (cycleVal) $('#preview-plan-cycle').text('/' + (cycleVal === 'yearly' ? 'বছর' : 'মাস'));

            var statusVal = $('select[name="status"]').val();
            if (statusVal) {
                var isActive = statusVal === 'active';
                $('#preview-status-badge').text(isActive ? 'সক্রিয়' : 'নিষ্ক্রিয়')
                    .removeClass('b-green b-grey')
                    .addClass(isActive ? 'b-green' : 'b-grey');
            }

            var isPopular = $('#plan-popular-input').is(':checked');
            if (isPopular) {
                var popularText = $('#plan-popular-label-input').val().trim() || 'সর্বাধিক জনপ্রিয়';
                var $popBadge = $('#preview-popular-badge');
                if ($popBadge.find('.bn').length) {
                    $popBadge.find('.bn').text(popularText);
                    $popBadge.find('.en').text(popularText);
                } else {
                    $popBadge.text(popularText);
                }
                $popBadge.show();
            } else {
                $('#preview-popular-badge').hide();
            }

            var usersVal = $('input[name="max_users"]').val();
            $('#preview-users-val').text(usersVal || '∞');

            var branchesVal = $('input[name="max_branches"]').val();
            $('#preview-branches-val').text(branchesVal || '∞');

            var warehousesVal = $('input[name="max_warehouses"]').val();
            $('#preview-warehouses-val').text(warehousesVal || '∞');

            var productsVal = $('input[name="max_products"]').val();
            $('#preview-products-val').text(productsVal ? parseInt(productsVal).toLocaleString() : '∞');

            updateFeaturesPreview();
        }

        syncAllInitialValues();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPlanFormInteractions);
    } else {
        initPlanFormInteractions();
    }
})();
</script>
@endpush
