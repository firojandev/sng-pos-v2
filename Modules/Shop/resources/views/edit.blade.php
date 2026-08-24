<x-core::layout
    title="দোকান সম্পাদনা"
    title-en="Edit Shop"
    subtitle="দোকানের তথ্য, ফিচার ও এডমিন পরিচালনা করুন"
    subtitle-en="Manage shop details, features, and admins"
    active="shops"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">দোকানের তথ্য</div>
            <div class="panel-title en" style="display:none;">Shop Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('shops.update', $shop) }}">
                @csrf
                @method('PUT')

                <div class="field-row">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">দোকানের নাম</label><label class="en" style="display:none;">Shop Name</label>
                        <input type="text" name="name" value="{{ old('name', $shop->name) }}" required>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="field" style="margin-top:0;">
                        <label class="bn">স্লাগ (URL)</label><label class="en" style="display:none;">Slug (URL)</label>
                        <input type="text" name="slug" value="{{ old('slug', $shop->slug) }}" required>
                        @error('slug') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label class="bn">ফোন</label><label class="en" style="display:none;">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $shop->phone) }}">
                    </div>
                    <div class="field">
                        <label class="bn">অবস্থা</label><label class="en" style="display:none;">Status</label>
                        <div class="seg">
                            <button type="button" class="{{ old('status', $shop->status) === 'active' ? 'active' : '' }}" data-target="shop-status-input" data-val="active" onclick="selSegValue(this)">
                                <span class="bn">সক্রিয়</span><span class="en">Active</span>
                            </button>
                            <button type="button" class="{{ old('status', $shop->status) === 'inactive' ? 'active' : '' }}" data-target="shop-status-input" data-val="inactive" onclick="selSegValue(this)">
                                <span class="bn">নিষ্ক্রিয়</span><span class="en">Inactive</span>
                            </button>
                        </div>
                        <input type="hidden" name="status" id="shop-status-input" value="{{ old('status', $shop->status) }}">
                    </div>
                </div>

                <div class="field">
                    <label class="bn">ঠিকানা</label><label class="en" style="display:none;">Address</label>
                    <textarea name="address">{{ old('address', $shop->address) }}</textarea>
                </div>

                <div class="field">
                    <label class="bn">সক্রিয় ফিচার</label><label class="en" style="display:none;">Enabled Features</label>
                    <div class="mini-grid" style="grid-template-columns:repeat(3,1fr);">
                        @foreach ($features as $key => $labels)
                            <label style="display:flex; align-items:center; gap:8px; background:var(--card); border:1px solid var(--border); border-radius:10px; padding:10px 12px; cursor:pointer; font-size:12.5px; font-weight:600;">
                                <input type="checkbox" name="features[]" value="{{ $key }}" style="width:auto;" {{ in_array($key, old('features', $shop->enabled_features ?? [])) ? 'checked' : '' }}>
                                <span class="bn">{{ $labels['bn'] }}</span><span class="en">{{ $labels['en'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('shops.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="panel" style="max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">এডমিনগণ</div>
            <div class="panel-title en" style="display:none;">Admins</div>
        </div>
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">নাম</th><th class="en" style="display:none;">Name</th>
                            <th class="bn">ইমেইল</th><th class="en" style="display:none;">Email</th>
                            <th class="bn">রোল</th><th class="en" style="display:none;">Role</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($admins as $admin)
                            <tr>
                                <td class="cell-main">{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->roles->pluck('name')->implode(', ') ?: '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        <form method="POST" action="{{ route('shops.admins.destroy', [$shop, $admin]) }}" onsubmit="return confirm('এই এডমিন মুছে ফেলতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act" title="Delete">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#C1443C" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="helper" style="margin-top:0;">কোনো এডমিন নেই</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="panel" style="margin-top:16px; box-shadow:none;">
                <div class="panel-head">
                    <div class="panel-title bn">নতুন এডমিন যোগ করুন</div>
                    <div class="panel-title en" style="display:none;">Add New Admin</div>
                </div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('shops.admins.store', $shop) }}">
                        @csrf
                        <div class="field-row">
                            <div class="field" style="margin-top:0;">
                                <label class="bn">নাম</label><label class="en" style="display:none;">Name</label>
                                <input type="text" name="name" placeholder="এডমিনের নাম" required>
                            </div>
                            <div class="field" style="margin-top:0;">
                                <label class="bn">ইমেইল</label><label class="en" style="display:none;">Email</label>
                                <input type="email" name="email" placeholder="admin@example.com" required>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field">
                                <label class="bn">পাসওয়ার্ড</label><label class="en" style="display:none;">Password</label>
                                <input type="password" name="password" required>
                            </div>
                            <div class="field">
                                <label class="bn">পাসওয়ার্ড নিশ্চিত করুন</label><label class="en" style="display:none;">Confirm Password</label>
                                <input type="password" name="password_confirmation" required>
                            </div>
                        </div>
                        <div class="field">
                            <label class="bn">রোল</label><label class="en" style="display:none;">Role</label>
                            <select name="role" required>
                                <option value="">-- নির্বাচন করুন --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-teal" style="margin-top:14px;">
                            <span class="bn">এডমিন যোগ করুন</span><span class="en">Add Admin</span>
                        </button>
                    </form>
                </div>
            </div>
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
