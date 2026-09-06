{{-- Quick Add Customer Modal --}}
<div class="modal-backdrop" id="quickCustomerModal" style="z-index:999;">
    <div class="modal-box" style="width:500px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px; background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card);">
        <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                    <x-core::icon name="user-plus" size="18" />
                </div>
                <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                    <span class="bn">নতুন গ্রাহক যোগ করুন</span>
                    <span class="en" style="display:none;">Add New Customer</span>
                </div>
            </div>
            <x-core::button type="button" variant="ghost" size="sm" icon="x" class="modal-close-btn" style="width:28px; height:28px; padding:0; display:flex; align-items:center; justify-content:center;" />
        </div>

        <form method="POST" action="{{ route('customers.store') }}" id="quick_customer_form" onsubmit="return false;">
            @csrf
            <input type="hidden" name="status" value="active">

            <div style="display:flex; flex-direction:column; gap:12px;">
                <x-core::input
                    name="name"
                    id="quick_customer_name"
                    label="গ্রাহকের নাম"
                    label-en="Customer Name"
                    placeholder="যেমন: মোঃ রহিম হোসেন"
                    placeholder-en="e.g. Md. Rahim Hossain"
                    size="sm"
                    :required="true"
                    :no-margin="true"
                />

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <x-core::input
                        name="phone"
                        id="quick_customer_phone"
                        label="মোবাইল / ফোন নম্বর"
                        label-en="Phone Number"
                        placeholder="01XXXXXXXXX"
                        placeholder-en="01XXXXXXXXX"
                        size="sm"
                        :no-margin="true"
                    />
                    <x-core::input
                        name="email"
                        id="quick_customer_email"
                        type="email"
                        label="ইমেইল"
                        label-en="Email"
                        placeholder="customer@example.com"
                        placeholder-en="customer@example.com"
                        size="sm"
                        :no-margin="true"
                    />
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <x-core::input
                        name="opening_due"
                        id="quick_customer_opening_due"
                        type="number"
                        step="0.01"
                        min="0"
                        value="0"
                        label="প্রারম্ভিক বাকি (৳)"
                        label-en="Opening Due (৳)"
                        placeholder="0.00"
                        prefix="৳"
                        size="sm"
                        :no-margin="true"
                    />
                    <x-core::input
                        name="address"
                        id="quick_customer_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="ঠিকানা"
                        placeholder-en="Address"
                        size="sm"
                        :no-margin="true"
                    />
                </div>
            </div>

            <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    class="modal-close-btn"
                >
                    <span class="bn">বাতিল</span>
                    <span class="en" style="display:none;">Cancel</span>
                </x-core::button>
                <x-core::button
                    type="button"
                    variant="solid"
                    color="primary"
                    size="sm"
                    id="btn-save-quick-customer"
                >
                    <span class="bn">সংরক্ষণ করুন</span>
                    <span class="en" style="display:none;">Save</span>
                </x-core::button>
            </div>
        </form>
    </div>
</div>
