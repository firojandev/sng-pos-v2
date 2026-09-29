<x-core::layout title="কোম্পানি ইউজার" title-en="Company Users" subtitle="কোম্পানি এডমিন ও কর্মচারী — কোম্পানি ওয়ার্কস্পেসে লগইন (POS নয়)" subtitle-en="Company admins and employees — company workspace logins (not the POS)" active="company-users">
    @php
        $roleOptions = ['' => 'কোনো রোল নেই (No role)'] + $roles->all();
        $typeOptions = ['Employee' => 'কর্মচারী (Employee)', 'Admin' => 'কোম্পানি এডমিন (Company Admin)'];
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
</x-core::layout>
