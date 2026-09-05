<div style="margin-bottom:16px;">
    <x-core::input
        size="sm"
        name="name"
        label="রোলের নাম"
        label-en="Role Name"
        icon="shield"
        :value="old('name', $role->name)"
        placeholder="যেমন: ক্যাশিয়ার, ম্যানেজার"
        required
        :readonly="$role->name === 'Admin'"
    />
    @if ($role->name === 'Admin')
        <div style="font-size:12px; color:var(--ink-600); margin-top:4px;">
            <span class="bn">ডিফল্ট এডমিন রোলের নাম পরিবর্তন করা যাবে না, তবে পারমিশন কাস্টমাইজ করতে পারেন।</span>
            <span class="en" style="display:none;">Default Admin role name cannot be changed, but its permissions can be customized.</span>
        </div>
    @endif
</div>

<div style="margin-bottom:16px;">
    <label style="display:block; font-size:13px; font-weight:600; color:var(--ink-700); margin-bottom:6px;">
        <span class="bn">পারমিশন নির্ধারণ</span>
        <span class="en" style="display:none;">Assign Permissions</span>
    </label>
    <div class="helper" style="margin-top:0; margin-bottom:10px; color:var(--ink-600); font-size:12px;">
        <span class="bn">এই রোলের ইউজাররা শুধু নির্বাচিত ফিচারগুলোর অনুমোদিত অ্যাকশন সম্পাদন করতে পারবে।</span>
        <span class="en" style="display:none;">Users with this role will only be permitted to perform checked actions.</span>
    </div>

    @if (count($features) === 0)
        <div class="helper" style="margin-top:0; color:var(--ink-600);">
            <span class="bn">আপনার দোকানের জন্য কোনো ফিচার সক্রিয় নেই।</span>
            <span class="en" style="display:none;">No features are enabled for your shop.</span>
        </div>
    @else
        @php
            $currentPermissions = old('permissions', $rolePermissions);
            $actionLabels = [
                'view' => ['bn' => 'দেখা', 'en' => 'View'],
                'write' => ['bn' => 'যোগ / সম্পাদনা', 'en' => 'Write'],
                'delete' => ['bn' => 'মুছে ফেলা', 'en' => 'Delete'],
            ];
        @endphp
        <div class="table-wrap" style="border:1px solid var(--border); border-radius:8px; overflow:hidden; background:var(--card);">
            <table class="data-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--paper); border-bottom:1px solid var(--border);">
                        <th style="padding:10px 14px; text-align:left; font-size:12px; font-weight:600; color:var(--ink-700);">
                            <span class="bn">ফিচার / মডিউল</span>
                            <span class="en" style="display:none;">Feature / Module</span>
                        </th>
                        @foreach ($actionLabels as $action => $labels)
                            <th style="padding:10px 14px; text-align:center; font-size:12px; font-weight:600; color:var(--ink-700);">
                                <span class="bn">{{ $labels['bn'] }}</span>
                                <span class="en" style="display:none;">{{ $labels['en'] }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($features as $key => $labels)
                        <tr style="border-bottom:1px solid var(--border);">
                            <td class="cell-main" style="padding:10px 14px; font-size:13px; font-weight:500; color:var(--ink-900);">
                                <span class="bn">{{ $labels['bn'] }}</span>
                                <span class="en" style="display:none;">{{ $labels['en'] }}</span>
                            </td>
                            @foreach (array_keys($actionLabels) as $action)
                                <td style="padding:10px 14px; text-align:center;">
                                    <div style="display:inline-flex; justify-content:center;">
                                        <x-core::checkbox
                                            size="sm"
                                            color="primary"
                                            name="permissions[]"
                                            value="{{ $key }}.{{ $action }}"
                                            :checked="in_array(\"{$key}.{$action}\", $currentPermissions)"
                                        />
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @error('permissions') <div class="field-error" style="color:var(--red-600); font-size:12px; margin-top:4px;">{{ $message }}</div> @enderror
</div>
