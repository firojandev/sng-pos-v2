<x-core::layout
    title="নতুন দোকান"
    title-en="New Shop"
    subtitle="নতুন দোকান ও এর প্রথম এডমিন তৈরি করুন"
    subtitle-en="Create a new shop and its first admin"
    active="shops"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">দোকানের তথ্য</div>
            <div class="panel-title en" style="display:none;">Shop Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('shops.store') }}">
                @csrf

                <div class="field-row">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">দোকানের নাম</label><label class="en" style="display:none;">Shop Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="যেমন রহিম জেনারেল স্টোর" required>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="field" style="margin-top:0;">
                        <label class="bn">স্লাগ (URL)</label><label class="en" style="display:none;">Slug (URL)</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" placeholder="rahim-general-store" required>
                        @error('slug') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label class="bn">ফোন</label><label class="en" style="display:none;">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01xxxxxxxxx">
                    </div>
                    <div class="field">
                        <label class="bn">অবস্থা</label><label class="en" style="display:none;">Status</label>
                        <div class="seg">
                            <button type="button" class="{{ old('status', 'active') === 'active' ? 'active' : '' }}" data-target="shop-status-input" data-val="active" onclick="selSegValue(this)">
                                <span class="bn">সক্রিয়</span><span class="en">Active</span>
                            </button>
                            <button type="button" class="{{ old('status') === 'inactive' ? 'active' : '' }}" data-target="shop-status-input" data-val="inactive" onclick="selSegValue(this)">
                                <span class="bn">নিষ্ক্রিয়</span><span class="en">Inactive</span>
                            </button>
                        </div>
                        <input type="hidden" name="status" id="shop-status-input" value="{{ old('status', 'active') }}">
                    </div>
                </div>

                <div class="field">
                    <label class="bn">ঠিকানা</label><label class="en" style="display:none;">Address</label>
                    <textarea name="address" placeholder="দোকানের ঠিকানা">{{ old('address') }}</textarea>
                </div>

                <div class="helper" style="background:var(--gold-100); margin-top:20px;">
                    <span class="bn">প্রথম এডমিনের তথ্য দিন — এই ইউজার এই দোকান পরিচালনা করবে।</span>
                    <span class="en" style="display:none;">Enter the first admin's details — this user will manage this shop.</span>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label class="bn">এডমিনের নাম</label><label class="en" style="display:none;">Admin Name</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" placeholder="এডমিনের পূর্ণ নাম" required>
                        @error('admin_name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="field">
                        <label class="bn">এডমিনের ইমেইল</label><label class="en" style="display:none;">Admin Email</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="admin@example.com" required>
                        @error('admin_email') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label class="bn">পাসওয়ার্ড</label><label class="en" style="display:none;">Password</label>
                        <input type="password" name="admin_password" required>
                        @error('admin_password') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="field">
                        <label class="bn">পাসওয়ার্ড নিশ্চিত করুন</label><label class="en" style="display:none;">Confirm Password</label>
                        <input type="password" name="admin_password_confirmation" required>
                    </div>
                </div>

                <div class="field">
                    <label class="bn">রোল নির্বাচন করুন</label><label class="en" style="display:none;">Assign Role</label>
                    <select name="admin_role" required>
                        <option value="">-- নির্বাচন করুন --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" {{ old('admin_role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('admin_role') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="bn">এই দোকানের জন্য সক্রিয় ফিচার</label><label class="en" style="display:none;">Enabled Features for this Shop</label>
                    <div class="helper" style="margin-top:0; margin-bottom:8px;">
                        <span class="bn">শুধু নির্বাচিত মডিউলগুলো এই দোকানের এডমিন সাইডবারে দেখতে ও ব্যবহার করতে পারবে।</span>
                        <span class="en" style="display:none;">Only checked modules will be visible/usable by this shop's admins.</span>
                    </div>
                    <div class="mini-grid" style="grid-template-columns:repeat(3,1fr);">
                        @foreach ($features as $key => $labels)
                            <label style="display:flex; align-items:center; gap:8px; background:var(--card); border:1px solid var(--border); border-radius:10px; padding:10px 12px; cursor:pointer; font-size:12.5px; font-weight:600;">
                                <input type="checkbox" name="features[]" value="{{ $key }}" style="width:auto;" {{ in_array($key, old('features', array_keys($features))) ? 'checked' : '' }}>
                                <span class="bn">{{ $labels['bn'] }}</span><span class="en">{{ $labels['en'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">দোকান তৈরি করুন</span><span class="en">Create Shop</span>
                    </button>
                    <a href="{{ route('shops.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selSegValue(btn) {
            btn.parentElement.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.getAttribute('data-target')).value = btn.getAttribute('data-val');
        }
    </script>
</x-core::layout>
