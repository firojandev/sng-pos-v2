<x-core::layout
    title="আমার কোম্পানি"
    title-en="My Company"
    subtitle="{{ $company->name }} — কোম্পানির তথ্য, দোকান ও দোকানের এডমিন"
    subtitle-en="{{ $company->name }} — company details, shops and shop admins"
    active="company-settings"
>
    @php
        $shopOptions = $company->shops->pluck('name', 'id')->all();
        $adminRoles = ['Admin', 'Shop Admin', 'Owner', 'Shop Owner'];
    @endphp

    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-bottom:12px;">{{ $errors->first() }}</div>
    @endif

    <div class="panel" style="margin-top:0;">
        <div class="panel-body" style="display:flex; flex-wrap:wrap; gap:28px;">
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">কোম্পানি</span><span class="en" style="display:none;">Company</span></div><div style="font-size:18px; font-weight:800;">{{ $company->name }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">মালিক</span><span class="en" style="display:none;">Owner</span></div><div style="font-size:18px; font-weight:700;">{{ $owner?->name ?? '—' }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">দোকান</span><span class="en" style="display:none;">Shops</span></div><div style="font-size:18px; font-weight:700;">{{ $company->shops->count() }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">ইউজার</span><span class="en" style="display:none;">Users</span></div><div style="font-size:18px; font-weight:700;">{{ $members->count() }}</div></div>
        </div>
    </div>

    <div class="panel" style="margin-top:16px;">
        <div class="panel-head"><div class="panel-title"><span class="bn">দোকানসমূহ ও এডমিন</span><span class="en" style="display:none;">Shops & Their Admins</span></div></div>
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">দোকান</span><span class="en" style="display:none;">Shop</span></th>
                        <th><span class="bn">ইউজার ও ভূমিকা</span><span class="en" style="display:none;">Users & Roles</span></th>
                        <th class="table-cell-center"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($company->shops as $shop)
                        <tr>
                            <td style="vertical-align:top;">
                                <div style="font-weight:600; color:var(--ink-900);">{{ $shop->name }}</div>
                                <div style="font-size:11.5px; color:var(--ink-500);">{{ $shop->store_code }}{{ $shop->phone ? ' · '.$shop->phone : '' }}</div>
                                @if ((int) $shop->id === (int) auth()->user()->shop_id)
                                    <x-core::badge color="green" size="xs" variant="soft" :dot="true" label="বর্তমান" label-en="Current" />
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                    @forelse ($shop->users as $member)
                                        @php $isAdmin = in_array($member->pivot->role, $adminRoles, true) || $member->pivot->is_owner; @endphp
                                        <span style="display:inline-flex; align-items:center; gap:6px; border:1px solid var(--border); border-radius:999px; padding:3px 4px 3px 10px; font-size:12px; background:var(--paper);">
                                            {{ $member->name }}
                                            <span style="color:var(--ink-500);">· {{ $member->pivot->role ?: 'Staff' }}</span>
                                            @if ($isAdmin && (int) $member->id !== (int) $owner?->id)
                                                <form method="POST" action="{{ route('company.shop-admins.destroy', [$shop, $member]) }}" class="delete-form" data-title="{{ $member->name }} কে এই দোকান থেকে সরাবেন?" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-core::button type="submit" size="xs" variant="soft" color="danger" icon="x" icon-only title="সরান / Remove" />
                                                </form>
                                            @elseif ((int) $member->id === (int) $owner?->id)
                                                <x-core::badge color="gold" size="xs" label="মালিক" label-en="Owner" />
                                            @endif
                                        </span>
                                    @empty
                                        <span style="font-size:12px; color:var(--ink-500);">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="table-cell-center" style="vertical-align:top;">
                                @if ($shop->status === 'active')
                                    <x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />
                                @else
                                    <x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-core::table.empty icon="shopping-bag" title="কোনো দোকান নেই" title-en="No shops" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">নতুন দোকান খুলুন</span><span class="en" style="display:none;">Open a New Shop</span></div></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('company.shops.store') }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    @csrf
                    <div style="grid-column:1 / -1;"><x-core::input size="sm" name="name" label="দোকানের নাম" label-en="Shop Name" :value="old('name')" :required="true" /></div>
                    <x-core::input size="sm" name="slug" label="স্লাগ (ঐচ্ছিক)" label-en="Slug (optional)" :value="old('slug')" placeholder="auto" />
                    <x-core::input size="sm" name="phone" label="ফোন" label-en="Phone" :value="old('phone')" />
                    <div style="grid-column:1 / -1;"><x-core::input size="sm" name="address" label="ঠিকানা" label-en="Address" :value="old('address')" /></div>
                    <x-core::input size="sm" type="number" step="0.01" min="0" name="opening_cash" label="শুরুর নগদ" label-en="Opening Cash" :value="old('opening_cash')" :stepper="false" />
                    <div></div>
                    @if ($categories->isNotEmpty())
                        <div style="grid-column:1 / -1;">
                            <div style="font-size:12px; font-weight:600; color:var(--ink-700); margin-bottom:6px;"><span class="bn">কী ধরনের পণ্য বিক্রি হবে</span><span class="en" style="display:none;">Product Categories Sold</span></div>
                            <div style="display:flex; flex-wrap:wrap; gap:8px; max-height:140px; overflow-y:auto;">
                                @foreach ($categories as $category)
                                    <x-core::checkbox size="sm" name="category_ids[]" :value="$category->id" :checked="in_array($category->id, old('category_ids', []))"><span style="font-size:12px;">{{ $category->name }}</span></x-core::checkbox>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <p style="grid-column:1 / -1; font-size:12px; color:var(--ink-500); margin:0;">
                        <span class="bn">নতুন দোকান কোম্পানির সাবস্ক্রিপশন, গ্রাহক, পণ্য তালিকা ও হিসাব একসাথে ব্যবহার করবে। প্রধান শাখা, গুদাম ও ক্যাশ অ্যাকাউন্ট স্বয়ংক্রিয়ভাবে তৈরি হবে।</span>
                        <span class="en" style="display:none;">The new shop shares the company's subscription, customers, catalogue and accounts. A main branch, warehouse and cash account are created for it.</span>
                    </p>
                    <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">দোকান খুলুন</span><span class="en" style="display:none;">Open Shop</span></x-core::button></div>
                </form>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">দোকানের এডমিন দিন</span><span class="en" style="display:none;">Add a Shop Admin</span></div></div>
            <div class="panel-body">
                @php $adminType = old('admin_type', 'new'); @endphp
                <form method="POST" action="{{ route('company.shop-admins.store') }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    @csrf
                    <div style="grid-column:1 / -1;">
                        <x-core::select size="sm" name="admin_type" id="company-admin-type" label="এডমিন" label-en="Admin" :value="$adminType"
                            :options="['new' => 'নতুন ইউজার তৈরি (Create a new user)', 'existing' => 'বিদ্যমান দোকান ইউজার (Existing shop user)']" />
                    </div>
                    <div style="grid-column:1 / -1;" class="company-admin-existing" @if ($adminType !== 'existing') hidden @endif>
                        <x-core::select size="sm" name="user_id" label="ইউজার" label-en="User" :value="old('user_id')" placeholder="--" placeholder-en="--"
                            :options="$members->mapWithKeys(fn ($member) => [$member->id => $member->name.($member->phone ? ' ('.$member->phone.')' : '')])->all()" />
                    </div>
                    <div class="company-admin-new" @if ($adminType === 'existing') hidden @endif><x-core::input size="sm" name="name" label="নাম" label-en="Name" :value="old('name')" /></div>
                    <div class="company-admin-new" @if ($adminType === 'existing') hidden @endif><x-core::input size="sm" name="phone" label="মোবাইল" label-en="Phone" :value="old('phone')" /></div>
                    <div class="company-admin-new" @if ($adminType === 'existing') hidden @endif><x-core::input size="sm" type="email" name="email" label="ইমেইল (ঐচ্ছিক)" label-en="Email (optional)" :value="old('email')" /></div>
                    <div class="company-admin-new" @if ($adminType === 'existing') hidden @endif><x-core::input size="sm" name="username" label="ইউজারনেম (ঐচ্ছিক)" label-en="Username (optional)" :value="old('username')" /></div>
                    <div class="company-admin-new" @if ($adminType === 'existing') hidden @endif><x-core::input size="sm" type="password" name="password" label="পাসওয়ার্ড" label-en="Password" /></div>
                    <div class="company-admin-new" @if ($adminType === 'existing') hidden @endif><x-core::input size="sm" type="password" name="password_confirmation" label="পাসওয়ার্ড নিশ্চিত" label-en="Confirm Password" /></div>
                    <div style="grid-column:1 / -1;">
                        <div style="font-size:12px; font-weight:600; color:var(--ink-700); margin-bottom:6px;"><span class="bn">কোন দোকানের এডমিন</span><span class="en" style="display:none;">Admin of Which Shops</span></div>
                        <div style="display:flex; flex-wrap:wrap; gap:8px;">
                            @foreach ($shopOptions as $shopId => $shopName)
                                <x-core::checkbox size="sm" name="shop_ids[]" :value="$shopId" :checked="in_array($shopId, old('shop_ids', []))"><span style="font-size:12px;">{{ $shopName }}</span></x-core::checkbox>
                            @endforeach
                        </div>
                    </div>
                    <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="user-plus"><span class="bn">এডমিন করুন</span><span class="en" style="display:none;">Make Admin</span></x-core::button></div>
                </form>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">কোম্পানি ইউজার ও রোল</span><span class="en" style="display:none;">Company Users & Roles</span></div></div>
            <div class="panel-body" style="font-size:12.5px; color:var(--ink-600); display:flex; flex-direction:column; gap:10px;">
                <span><span class="bn">কোম্পানি এডমিন ও কর্মচারী (কোম্পানি ওয়ার্কস্পেস, POS নয়): {{ $companyUsers->count() }} জন।</span><span class="en" style="display:none;">Company admins and employees (company workspace, not the POS): {{ $companyUsers->count() }}.</span></span>
                <div style="display:flex; gap:8px;">
                    <x-core::button as="a" href="{{ route('company.users.index') }}" size="sm" variant="secondary" icon="users"><span class="bn">ইউজার</span><span class="en" style="display:none;">Users</span></x-core::button>
                    <x-core::button as="a" href="{{ route('company.roles.index') }}" size="sm" variant="secondary" icon="shield"><span class="bn">রোল ও পারমিশন</span><span class="en" style="display:none;">Roles & Permissions</span></x-core::button>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">কোম্পানির তথ্য</span><span class="en" style="display:none;">Company Details</span></div></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('company-settings.update') }}">
                    @csrf
                    @method('PUT')
                    @include('company::_form')

                    <div style="display:flex; gap:10px; margin-top:20px;">
                        <x-core::button type="submit" size="sm" variant="solid" color="primary">
                            <span class="bn">সংরক্ষণ করুন</span>
                            <span class="en" style="display:none;">Save</span>
                        </x-core::button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                $('#company-admin-type').on('change', function () {
                    var existing = $(this).val() === 'existing';
                    $('.company-admin-existing').prop('hidden', !existing);
                    $('.company-admin-new').prop('hidden', existing);
                });
            });
        </script>
    @endpush
</x-core::layout>
