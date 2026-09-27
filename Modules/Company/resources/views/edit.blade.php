<x-core::layout
    title="কোম্পানি সম্পাদনা"
    title-en="Edit Company"
    subtitle="কোম্পানির তথ্য, দোকান ও সাবস্ক্রিপশন"
    subtitle-en="Company details, shops and subscription"
    active="companies"
>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:16px; align-items:start;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title">
                    <span class="bn">কোম্পানির তথ্য</span>
                    <span class="en" style="display:none;">Company Details</span>
                </div>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('companies.update', $company) }}">
                    @csrf
                    @method('PUT')
                    @include('company::_form', ['showStatus' => true])

                    <div style="display:flex; gap:10px; margin-top:20px;">
                        <x-core::button type="submit" size="sm" variant="solid" color="primary">
                            <span class="bn">সংরক্ষণ করুন</span>
                            <span class="en" style="display:none;">Save</span>
                        </x-core::button>
                        <x-core::button as="a" href="{{ route('companies.index') }}" size="sm" variant="secondary">
                            <span class="bn">বাতিল</span>
                            <span class="en" style="display:none;">Cancel</span>
                        </x-core::button>
                    </div>
                </form>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:16px;">
            <div class="panel" style="margin-top:0;">
                <div class="panel-head">
                    <div class="panel-title">
                        <span class="bn">সাবস্ক্রিপশন</span>
                        <span class="en" style="display:none;">Subscription</span>
                    </div>
                </div>
                <div class="panel-body">
                    @if ($subscription && $subscription->plan)
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                            <span style="font-weight:700; font-size:14px; color:var(--ink-900);">{{ $subscription->plan->name }}</span>
                            <x-core::badge :color="$subscription->isUsable() ? 'teal' : 'red'" size="xs" variant="soft">
                                {{ $subscription->statusLabel()['bn'] }}
                            </x-core::badge>
                        </div>
                        <div style="font-size:12px; color:var(--ink-600); margin-top:6px;">
                            @if ($company->isDefault())
                                <span class="bn">এই প্ল্যান শুধু ডিফল্ট কোম্পানির নিজের ফিচার (যেমন টাস্ক) নির্ধারণ করে; একক দোকানগুলো তাদের নিজস্ব প্ল্যান ব্যবহার করে।</span>
                                <span class="en" style="display:none;">This plan only sets the Default Company's own features (e.g. Tasks); standalone shops keep their own plans.</span>
                            @else
                                <span class="bn">কোম্পানির সকল দোকান এই সাবস্ক্রিপশন ব্যবহার করে।</span>
                                <span class="en" style="display:none;">All shops of the company share this subscription.</span>
                            @endif
                        </div>
                    @else
                        <span style="font-size:12.5px; color:var(--ink-500);">
                            <span class="bn">কোনো সাবস্ক্রিপশন নেই</span>
                            <span class="en" style="display:none;">No subscription</span>
                        </span>
                    @endif

                    <form method="POST" action="{{ route('companies.plan.update', $company) }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:14px; padding-top:12px; border-top:1px solid var(--border);">
                        @csrf
                        @method('PUT')
                        <div style="grid-column:1 / -1;">
                            <x-core::select size="sm" name="plan_id" label="প্ল্যান দিন / পরিবর্তন" label-en="Assign / Change Plan" :value="$subscription?->plan_id" :required="true" placeholder="--" placeholder-en="--"
                                :options="$plans->mapWithKeys(fn ($plan) => [$plan->id => $plan->name.' — ৳'.number_format((float) $plan->price, 0)])->all()" />
                        </div>
                        <x-core::select size="sm" name="subscription_status" label="অবস্থা" label-en="Status" value="active" :options="['active' => 'সক্রিয় (Active)', 'trialing' => 'ট্রায়াল (Trial)']" />
                        <x-core::input size="sm" type="date" name="current_period_end" label="মেয়াদ শেষ (ঐচ্ছিক)" label-en="Ends (optional)" />
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="check"><span class="bn">সংরক্ষণ</span><span class="en" style="display:none;">Save Plan</span></x-core::button></div>
                    </form>
                </div>
            </div>

            <div class="panel" style="margin-top:0;">
                <div class="panel-head">
                    <div class="panel-title">
                        <span class="bn">{{ $company->isDefault() ? 'ডিফল্ট কোম্পানির ইউজার' : 'কোম্পানির ইউজার (মালিক, এডমিন, কর্মচারী)' }}</span>
                        <span class="en" style="display:none;">{{ $company->isDefault() ? 'Default Company Users' : 'Company Users (Owner, Admins, Employees)' }}</span>
                    </div>
                </div>
                @if ($company->isDefault())
                    <div class="panel-body" style="font-size:12.5px; color:var(--ink-600); padding-bottom:0;">
                        <span class="bn">এই এডমিনরা লগইন করলে সব একক দোকানের তালিকা দেখবেন এবং যেকোনো দোকানে প্রবেশ করতে পারবেন।</span>
                        <span class="en" style="display:none;">These admins log in to the list of all standalone shops and can open any of them.</span>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="app-table">
                        <tbody>
                            @forelse ($admins as $admin)
                                <tr>
                                    <td>{{ $admin->name }} <div style="font-size:11.5px; color:var(--ink-500);">{{ $admin->phone }}{{ $admin->email ? ' · '.$admin->email : '' }}</div></td>
                                    @php $memberRole = $admin->pivot->is_owner ? 'Owner' : (in_array($admin->pivot->role, ['Owner', 'Admin'], true) ? 'Admin' : 'Employee'); @endphp
                                    <td><x-core::badge :color="['Owner' => 'gold', 'Admin' => 'blue', 'Employee' => 'grey'][$memberRole]" size="xs">{{ $memberRole }}</x-core::badge></td>
                                    <td class="table-cell-right">
                                        <form method="POST" action="{{ route('companies.admins.destroy', [$company, $admin]) }}" class="delete-form" data-title="{{ $admin->name }} কে সরাবেন?">
                                            @csrf
                                            @method('DELETE')
                                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="x" icon-only title="সরান / Remove" />
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><x-core::table.empty icon="users" title="কোনো এডমিন নেই" title-en="No admins" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="panel-body">
                    @if ($errors->any())
                        <div style="color:var(--red-600); font-size:12.5px; margin-bottom:10px;">{{ $errors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('companies.admins.store', $company) }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        @csrf
                        <x-core::input size="sm" name="phone" label="মোবাইল" label-en="Phone" :required="true" />
                        <x-core::select size="sm" name="role" label="ভূমিকা" label-en="Role" value="Admin" :options="['Admin' => 'এডমিন (Admin)', 'Employee' => 'কর্মচারী (Employee)', 'Owner' => 'মালিক (Owner)']" />
                        <x-core::input size="sm" name="name" label="নাম (নতুন ইউজার)" label-en="Name (new user)" />
                        <x-core::input size="sm" type="email" name="email" label="ইমেইল" label-en="Email" />
                        <x-core::input size="sm" type="password" name="password" label="পাসওয়ার্ড (নতুন ইউজার)" label-en="Password (new user)" />
                        <x-core::input size="sm" type="password" name="password_confirmation" label="পাসওয়ার্ড নিশ্চিত" label-en="Confirm Password" />
                        <p style="grid-column:1 / -1; font-size:12px; color:var(--ink-500); margin:0;">
                            <span class="bn">এই মোবাইলের ইউজার থাকলে তাকেই এডমিন করা হবে; না থাকলে নতুন ইউজার তৈরি হবে।</span>
                            <span class="en" style="display:none;">If a user has this phone they're made admin; otherwise a new user is created.</span>
                        </p>
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="user-plus"><span class="bn">এডমিন করুন</span><span class="en" style="display:none;">Add Admin</span></x-core::button></div>
                    </form>
                </div>
            </div>

            @if ($company->isDefault())
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center;">
                        <div class="panel-title"><span class="bn">একক দোকান ({{ $standaloneCount }})</span><span class="en" style="display:none;">Standalone Shops ({{ $standaloneCount }})</span></div>
                        <x-core::button as="a" href="{{ route('default-company.index') }}" size="sm" variant="secondary" icon="list"><span class="bn">সব দেখুন</span><span class="en" style="display:none;">View All</span></x-core::button>
                    </div>
                    <div class="table-responsive">
                        <table class="app-table">
                            <tbody>
                                @forelse ($standaloneShops as $shop)
                                    <tr>
                                        <td style="font-weight:600;">{{ $shop->name }} <div style="font-size:11.5px; color:var(--ink-500); font-weight:400;">#{{ $shop->store_code }}</div></td>
                                        <td class="table-cell-right"><x-core::button :href="route('shops.edit', $shop)" size="sm" variant="soft" color="primary" icon="edit" icon-only title="সম্পাদনা / Edit" /></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2"><x-core::table.empty icon="shopping-bag" title="কোনো একক দোকান নেই" title-en="No standalone shops" /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
            <div class="panel" style="margin-top:0;">
                <div class="panel-head">
                    <div class="panel-title">
                        <span class="bn">দোকানসমূহ ({{ $company->shops->count() }})</span>
                        <span class="en" style="display:none;">Shops ({{ $company->shops->count() }})</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th><span class="bn">দোকান</span><span class="en" style="display:none;">Shop</span></th>
                                <th class="table-cell-center"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                                <th class="table-cell-right"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($company->shops as $shop)
                                <tr>
                                    <td>
                                        <div style="font-weight:600; color:var(--ink-900);">{{ $shop->name }}</div>
                                        @if ($shop->store_code)
                                            <x-core::badge color="teal" size="xs" variant="soft">#{{ $shop->store_code }}</x-core::badge>
                                        @endif
                                    </td>
                                    <td class="table-cell-center">
                                        @if ($shop->status === 'active')
                                            <x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />
                                        @else
                                            <x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />
                                        @endif
                                    </td>
                                    <td class="table-cell-right">
                                        <x-core::button :href="route('shops.edit', $shop)" size="sm" variant="soft" color="primary" icon="edit" icon-only title="সম্পাদনা / Edit" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <x-core::table.empty icon="shopping-bag" title="এই কোম্পানিতে কোনো দোকান নেই" title-en="This company has no shops" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-core::layout>
