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
        @php
            $currentPermissions = old('permissions', $rolePermissions);
            $actionLabels = [
                'view' => ['bn' => 'দেখা', 'en' => 'View'],
                'write' => ['bn' => 'যোগ/সম্পাদনা', 'en' => 'Write'],
                'delete' => ['bn' => 'মুছে ফেলা', 'en' => 'Delete'],
            ];
        @endphp
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="bn">ফিচার</th><th class="en" style="display:none;">Feature</th>
                        @foreach ($actionLabels as $action => $labels)
                            <th class="bn" style="text-align:center;">{{ $labels['bn'] }}</th>
                            <th class="en" style="display:none; text-align:center;">{{ $labels['en'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($features as $key => $labels)
                        <tr>
                            <td class="cell-main">
                                <span class="bn">{{ $labels['bn'] }}</span><span class="en">{{ $labels['en'] }}</span>
                            </td>
                            @foreach (array_keys($actionLabels) as $action)
                                <td style="text-align:center;">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}.{{ $action }}" style="width:auto;" {{ in_array("{$key}.{$action}", $currentPermissions) ? 'checked' : '' }}>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @error('permissions') <div class="field-error">{{ $message }}</div> @enderror
</div>
