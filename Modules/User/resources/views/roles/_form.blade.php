<div class="field" style="margin-top:0;">
    <label class="bn">রোলের নাম</label><label class="en" style="display:none;">Role Name</label>
    <input type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="যেমন ক্যাশিয়ার" required>
    @error('name') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">পারমিশন</label><label class="en" style="display:none;">Permissions</label>
    <div class="helper" style="margin-top:0; margin-bottom:8px;">
        <span class="bn">এই রোলের ইউজাররা শুধু নির্বাচিত অংশগুলো ব্যবহার করতে পারবে।</span>
        <span class="en" style="display:none;">Users with this role will only be able to access the checked sections.</span>
    </div>
    @if (count($features) === 0)
        <div class="helper" style="margin-top:0;">
            <span class="bn">আপনার দোকানের জন্য কোনো ফিচার সক্রিয় নেই।</span>
            <span class="en" style="display:none;">No features are enabled for your shop.</span>
        </div>
    @else
        <div class="mini-grid" style="grid-template-columns:repeat(3,1fr);">
            @foreach ($features as $key => $labels)
                <label style="display:flex; align-items:center; gap:8px; background:var(--card); border:1px solid var(--border); border-radius:10px; padding:10px 12px; cursor:pointer; font-size:12.5px; font-weight:600;">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}" style="width:auto;" {{ in_array($key, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                    <span class="bn">{{ $labels['bn'] }}</span><span class="en">{{ $labels['en'] }}</span>
                </label>
            @endforeach
        </div>
    @endif
    @error('permissions') <div class="field-error">{{ $message }}</div> @enderror
</div>
