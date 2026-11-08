<x-core::layout :title="$role->exists ? 'রোল সম্পাদনা' : 'নতুন রোল'" :title-en="$role->exists ? 'Edit Role' : 'New Role'" subtitle="কোম্পানি কর্মচারীর রোল ও তার অনুমতি" subtitle-en="A company employee role and its permissions" active="company-roles">
    @php
        $granted = old('permissions', $role->permissions ?? []);
        $actionLabels = \Modules\Core\Support\Permissions::actionLabels();
    @endphp

    <form method="POST" action="{{ $role->exists ? route('company.roles.update', $role) : route('company.roles.store') }}">
        @csrf
        @if ($role->exists)
            @method('PUT')
        @endif

        <div class="panel" style="margin-top:0;">
            <div class="panel-body" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
                <div style="width:320px;"><x-core::input size="sm" name="name" label="রোলের নাম" label-en="Role Name" :value="old('name', $role->name)" placeholder="যেমন: এইচআর অফিসার" placeholder-en="e.g. HR Officer" :required="true" /></div>
                <x-core::button type="button" size="sm" variant="secondary" icon="check" id="company-role-all"><span class="bn">সব</span><span class="en" style="display:none;">All</span></x-core::button>
                <x-core::button type="button" size="sm" variant="secondary" icon="x" id="company-role-none"><span class="bn">কোনোটি না</span><span class="en" style="display:none;">None</span></x-core::button>
            </div>
            @if ($errors->any())
                <div class="panel-body" style="color:var(--red-600); font-size:12.5px; padding-top:0;">{{ $errors->first() }}</div>
            @endif
        </div>

        <div class="table-container table-teal" style="margin-top:16px;">
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">মডিউল</span><span class="en" style="display:none;">Module</span></th>
                            <th><span class="bn">অনুমতি</span><span class="en" style="display:none;">Permissions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grantable as $feature => $group)
                            <tr>
                                <td style="font-weight:600; white-space:nowrap;"><span class="bn">{{ $group['label']['bn'] }}</span><span class="en" style="display:none;">{{ $group['label']['en'] }}</span></td>
                                <td>
                                    <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                        @foreach ($group['actions'] as $action)
                                            @php $permission = $feature.'.'.$action; @endphp
                                            <x-core::checkbox size="sm" name="permissions[]" :value="$permission" :checked="in_array($permission, $granted, true)" class="company-role-permission">
                                                <span style="font-size:12.5px;"><span class="bn">{{ $actionLabels[$action]['bn'] ?? $action }}</span><span class="en" style="display:none;">{{ $actionLabels[$action]['en'] ?? $action }}</span></span>
                                            </x-core::checkbox>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display:flex; gap:8px; margin-top:16px;">
            <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="check"><span class="bn">সংরক্ষণ</span><span class="en" style="display:none;">Save</span></x-core::button>
            <x-core::button as="a" href="{{ route('company.roles.index') }}" size="sm" variant="secondary"><span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span></x-core::button>
        </div>
    </form>

    @push('scripts')
        <script>
            $(function () {
                $('#company-role-all').on('click', function () { $('input[name="permissions[]"]').prop('checked', true); });
                $('#company-role-none').on('click', function () { $('input[name="permissions[]"]').prop('checked', false); });
            });
        </script>
    @endpush
</x-core::layout>
