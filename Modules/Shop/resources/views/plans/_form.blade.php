@php
    $selectedFeatures = old('features', $plan->features ?? []);
@endphp

<div class="field-row">
    <div class="field" style="margin-top:0;">
        <label class="bn">প্ল্যানের নাম</label><label class="en" style="display:none;">Plan Name</label>
        <input type="text" name="name" value="{{ old('name', $plan->name) }}" placeholder="যেমন Professional" required>
        @error('name') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field" style="margin-top:0;">
        <label class="bn">স্লাগ</label><label class="en" style="display:none;">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" placeholder="professional" required>
        @error('slug') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">মূল্য (৳)</label><label class="en" style="display:none;">Price (৳)</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan->price ?? 0) }}" required>
    </div>
    <div class="field">
        <label class="bn">বিলিং সাইকেল</label><label class="en" style="display:none;">Billing Cycle</label>
        <select name="billing_cycle" required>
            <option value="monthly" {{ old('billing_cycle', $plan->billing_cycle ?? 'monthly') === 'monthly' ? 'selected' : '' }}>মাসিক</option>
            <option value="yearly" {{ old('billing_cycle', $plan->billing_cycle ?? 'monthly') === 'yearly' ? 'selected' : '' }}>বাৎসরিক</option>
        </select>
    </div>
</div>

<div class="helper" style="margin-top:14px;">
    <span class="bn">সীমা খালি রাখলে তা সীমাহীন ধরা হবে।</span>
    <span class="en" style="display:none;">Leave a limit blank for unlimited.</span>
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">সর্বোচ্চ ইউজার</label><label class="en" style="display:none;">Max Users</label>
        <input type="number" min="1" name="max_users" value="{{ old('max_users', $plan->max_users) }}" placeholder="সীমাহীন">
    </div>
    <div class="field">
        <label class="bn">সর্বোচ্চ শাখা</label><label class="en" style="display:none;">Max Branches</label>
        <input type="number" min="1" name="max_branches" value="{{ old('max_branches', $plan->max_branches) }}" placeholder="সীমাহীন">
    </div>
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">সর্বোচ্চ গুদাম</label><label class="en" style="display:none;">Max Warehouses</label>
        <input type="number" min="1" name="max_warehouses" value="{{ old('max_warehouses', $plan->max_warehouses) }}" placeholder="সীমাহীন">
    </div>
    <div class="field">
        <label class="bn">সর্বোচ্চ পণ্য</label><label class="en" style="display:none;">Max Products</label>
        <input type="number" min="1" name="max_products" value="{{ old('max_products', $plan->max_products) }}" placeholder="সীমাহীন">
    </div>
</div>

<div class="field">
    <label class="bn">অন্তর্ভুক্ত ফিচার</label><label class="en" style="display:none;">Included Features</label>
    <div class="mini-grid" style="grid-template-columns:repeat(3,1fr);">
        @foreach ($features as $key => $labels)
            <label style="display:flex; align-items:center; gap:8px; background:var(--card); border:1px solid var(--border); border-radius:10px; padding:10px 12px; cursor:pointer; font-size:12.5px; font-weight:600;">
                <input type="checkbox" name="features[]" value="{{ $key }}" style="width:auto;" {{ in_array($key, $selectedFeatures) ? 'checked' : '' }}>
                <span class="bn">{{ $labels['bn'] }}</span><span class="en">{{ $labels['en'] }}</span>
            </label>
        @endforeach
    </div>
</div>

<div class="field">
    <label class="bn">অবস্থা</label><label class="en" style="display:none;">Status</label>
    <div class="seg">
        <button type="button" class="{{ old('status', $plan->status ?? 'active') === 'active' ? 'active' : '' }}" onclick="setSegValue(this, 'plan-status', 'active')">
            <span class="bn">সক্রিয়</span><span class="en">Active</span>
        </button>
        <button type="button" class="{{ old('status', $plan->status ?? 'active') === 'inactive' ? 'active' : '' }}" onclick="setSegValue(this, 'plan-status', 'inactive')">
            <span class="bn">নিষ্ক্রিয়</span><span class="en">Inactive</span>
        </button>
    </div>
    <input type="hidden" name="status" id="plan-status" value="{{ old('status', $plan->status ?? 'active') }}">
</div>

<script>
function setSegValue(btn, inputId, value) {
    document.getElementById(inputId).value = value;
    btn.parentElement.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
}
</script>
