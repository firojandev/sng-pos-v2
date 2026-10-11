<x-core::layout title="কোম্পানি ইউজার" title-en="Company Users" subtitle="কোম্পানি এডমিন ও কর্মচারী — কোম্পানি ওয়ার্কস্পেসে লগইন (POS নয়)" subtitle-en="Company admins and employees — company workspace logins (not the POS)" active="company-users">
    @php
        $roleOptions = ['' => 'কোনো রোল নেই (No role)'] + $roles->all();
        $typeOptions = ['Employee' => 'কর্মচারী (Employee)', 'Admin' => 'কোম্পানি এডমিন (Company Admin)'];
        $selectedEmployee = $employees->firstWhere('id', (int) old('employee_id'));
        $employeeLabel = fn ($employee) => trim(($employee->employee_code ? $employee->employee_code.' — ' : '').$employee->name);
    @endphp

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-bottom:12px;">{{ $errors->first() }}</div>
    @endif

    <div style="display:grid; grid-template-columns:minmax(300px, 360px) 1fr; gap:16px; align-items:start;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">নতুন ইউজার</span><span class="en" style="display:none;">New User</span></div></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('company.users.store') }}" style="display:flex; flex-direction:column; gap:10px;">
                    @csrf
                    <div class="employee-picker" id="company-user-employee-picker">
                        <input type="hidden" name="employee_id" value="{{ $selectedEmployee?->id }}">
                        <x-core::input size="sm" id="company-user-employee-search" icon="search" :clearable="true" autocomplete="off"
                            label="কর্মচারী (ঐচ্ছিক)" label-en="Employee (optional)"
                            placeholder="নাম, কোড বা মোবাইল দিয়ে খুঁজুন" placeholder-en="Search by name, code or phone"
                            :value="$selectedEmployee ? $employeeLabel($selectedEmployee) : ''" />
                        <div class="employee-picker-menu" hidden>
                            @foreach ($employees as $employee)
                                <div class="employee-picker-item" role="option" tabindex="-1"
                                    data-id="{{ $employee->id }}" data-label="{{ $employeeLabel($employee) }}"
                                    data-name="{{ $employee->name }}" data-phone="{{ $employee->phone }}" data-email="{{ $employee->email }}"
                                    data-search="{{ mb_strtolower($employee->employee_code.' '.$employee->name.' '.$employee->phone.' '.$employee->designation) }}">
                                    <div class="employee-picker-name">{{ $employeeLabel($employee) }}</div>
                                    <div class="employee-picker-meta">{{ $employee->phone }}{{ $employee->designation ? ' · '.$employee->designation : '' }}</div>
                                </div>
                            @endforeach
                            <div class="employee-picker-empty" hidden><span class="bn">কোনো কর্মচারী পাওয়া যায়নি</span><span class="en" style="display:none;">No employee found</span></div>
                        </div>
                    </div>
                    <p style="font-size:12px; color:var(--ink-400); margin:0;">
                        <span class="bn">কর্মচারী বাছলে নাম, মোবাইল ও ইমেইল নিজে থেকে বসবে এবং ইউজারটি তার সাথে যুক্ত হবে।</span>
                        <span class="en" style="display:none;">Choosing an employee fills in the name, phone and email, and links the user to them.</span>
                    </p>
                    <x-core::input size="sm" name="name" label="নাম" label-en="Name" :value="old('name')" :required="true" />
                    <x-core::input size="sm" name="phone" label="মোবাইল" label-en="Phone" :value="old('phone')" :required="true" />
                    <x-core::input size="sm" type="email" name="email" label="ইমেইল (ঐচ্ছিক)" label-en="Email (optional)" :value="old('email')" />
                    <x-core::input size="sm" name="username" label="ইউজারনেম (ঐচ্ছিক)" label-en="Username (optional)" :value="old('username')" />
                    <x-core::select size="sm" name="role" label="ধরন" label-en="Type" :value="old('role', 'Employee')" :options="$typeOptions" />
                    <x-core::select size="sm" name="company_role_id" label="রোল (কর্মচারীর জন্য)" label-en="Role (for employees)" :value="old('company_role_id')" :options="$roleOptions" />
                    <x-core::input size="sm" type="password" name="password" label="পাসওয়ার্ড" label-en="Password" :required="true" />
                    <x-core::input size="sm" type="password" name="password_confirmation" label="পাসওয়ার্ড নিশ্চিত" label-en="Confirm Password" :required="true" />
                    <p style="font-size:12px; color:var(--ink-500); margin:0;">
                        <span class="bn">কোম্পানি এডমিন সব কোম্পানি মডিউল পান; কর্মচারী তার রোলের অনুমতি অনুযায়ী পান। POS এর জন্য "আমার কোম্পানি" থেকে দোকান এডমিন দিন।</span>
                        <span class="en" style="display:none;">A company admin gets every company module; an employee gets what their role allows. For the POS, add shop admins under "My Company".</span>
                    </p>
                    <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="user-plus"><span class="bn">তৈরি করুন</span><span class="en" style="display:none;">Create</span></x-core::button></div>
                </form>
            </div>
        </div>

        <div class="table-container table-teal">
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">ইউজার</span><span class="en" style="display:none;">User</span></th>
                            <th style="width:170px;"><span class="bn">ধরন</span><span class="en" style="display:none;">Type</span></th>
                            <th style="width:190px;"><span class="bn">রোল</span><span class="en" style="display:none;">Role</span></th>
                            <th class="table-cell-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $companyUser)
                            @php
                                $isOwner = (bool) $companyUser->pivot->is_owner;
                                $formId = 'company-user-'.$companyUser->id;
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $companyUser->name }}
                                        @if ($isOwner)
                                            <x-core::badge color="gold" size="xs" label="মালিক" label-en="Owner" />
                                        @endif
                                    </div>
                                    <div style="font-size:11.5px; color:var(--ink-500);">{{ $companyUser->phone }}{{ $companyUser->email ? ' · '.$companyUser->email : '' }}{{ $companyUser->username ? ' · @'.$companyUser->username : '' }}</div>
                                </td>
                                @if ($isOwner)
                                    <td colspan="3" style="font-size:12.5px; color:var(--ink-500);"><span class="bn">সব কোম্পানি মডিউল</span><span class="en" style="display:none;">Every company module</span></td>
                                @else
                                    <td><x-core::select size="sm" :no-margin="true" name="role" :form="$formId" :value="$companyUser->pivot->role === 'Admin' ? 'Admin' : 'Employee'" :options="$typeOptions" /></td>
                                    <td><x-core::select size="sm" :no-margin="true" name="company_role_id" :form="$formId" :value="$companyUser->pivot->company_role_id" :options="$roleOptions" /></td>
                                    <td class="table-cell-right">
                                        <div style="display:flex; gap:4px; justify-content:flex-end;">
                                            <form method="POST" action="{{ route('company.users.update', $companyUser) }}" id="{{ $formId }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="name" value="{{ $companyUser->name }}">
                                                <input type="hidden" name="phone" value="{{ $companyUser->phone }}">
                                                <input type="hidden" name="email" value="{{ $companyUser->email }}">
                                                <input type="hidden" name="username" value="{{ $companyUser->username }}">
                                                <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="check" icon-only title="সংরক্ষণ / Save" />
                                            </form>
                                            @if ($companyUser->id !== auth()->id())
                                                <form method="POST" action="{{ route('company.users.destroy', $companyUser) }}" class="delete-form" data-title="{{ $companyUser->name }} কে সরাবেন?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="সরান / Remove" />
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .employee-picker { position: relative; }
            .employee-picker-menu { position: absolute; left: 0; right: 0; top: 100%; z-index: 30; max-height: 240px; overflow-y: auto; margin-top: 4px; background: var(--card); border: 1px solid var(--border); border-radius: 8px; color: var(--ink-900); box-shadow: var(--shadow-card); }
            .employee-picker-item { padding: 7px 10px; cursor: pointer; }
            .employee-picker-item:hover, .employee-picker-item.is-active { background: var(--paper); }
            .employee-picker-name { font-size: 13px; font-weight: 600; }
            .employee-picker-meta, .employee-picker-empty { font-size: 11.5px; color: var(--ink-400); }
            .employee-picker-empty { padding: 10px; }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(function () {
                var $picker = $('#company-user-employee-picker');
                var $form = $picker.closest('form');
                var $search = $('#company-user-employee-search');
                var $hidden = $picker.find('input[name="employee_id"]');
                var $menu = $picker.find('.employee-picker-menu');
                var $items = $menu.find('.employee-picker-item');
                var $empty = $menu.find('.employee-picker-empty');

                function filter() {
                    var term = $.trim($search.val()).toLowerCase();
                    var shown = 0;
                    $items.each(function () {
                        var match = term === '' || String($(this).data('search')).indexOf(term) !== -1;
                        $(this).prop('hidden', !match).removeClass('is-active');
                        shown += match ? 1 : 0;
                    });
                    $empty.prop('hidden', shown > 0);
                    $menu.prop('hidden', false);
                }

                function choose($item) {
                    $hidden.val($item.data('id'));
                    $search.val($item.data('label'));
                    $form.find('[name="name"]').val($item.data('name'));
                    $form.find('[name="phone"]').val($item.data('phone'));
                    if ($item.data('email')) {
                        $form.find('[name="email"]').val($item.data('email'));
                    }
                    $menu.prop('hidden', true);
                }

                $search.on('focus', filter).on('input', function () {
                    // Typing again (or clearing) drops the earlier choice.
                    $hidden.val('');
                    filter();
                }).on('keydown', function (event) {
                    var $visible = $items.filter(':not([hidden])');
                    var index = $visible.index($visible.filter('.is-active'));

                    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        if ($menu.prop('hidden')) { filter(); $visible = $items.filter(':not([hidden])'); index = -1; }
                        if (!$visible.length) { return; }
                        index = event.key === 'ArrowDown' ? (index + 1) % $visible.length : (index <= 0 ? $visible.length - 1 : index - 1);
                        $items.removeClass('is-active');
                        $visible.eq(index).addClass('is-active')[0].scrollIntoView({ block: 'nearest' });
                    } else if (event.key === 'Enter' && !$menu.prop('hidden')) {
                        event.preventDefault();
                        if (index >= 0) { choose($visible.eq(index)); }
                    } else if (event.key === 'Escape') {
                        $menu.prop('hidden', true);
                    }
                });

                $items.on('mousedown', function (event) {
                    event.preventDefault();
                    choose($(this));
                });

                $(document).on('mousedown', function (event) {
                    if (!$(event.target).closest($picker).length) {
                        $menu.prop('hidden', true);
                        // Text that matches no chosen employee is not a selection.
                        if (!$hidden.val()) { $search.val(''); }
                    }
                });
            });
        </script>
    @endpush
</x-core::layout>
