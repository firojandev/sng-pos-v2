<x-core::layout
    title="লয়্যালটি পয়েন্ট"
    title-en="Loyalty Points"
    subtitle="কেনাকাটায় গ্রাহকদের পয়েন্ট দিন; সদস্যপদ বিনামূল্যে"
    subtitle-en="Reward customers with points on their purchases; membership is free"
    active="loyalty"
>
    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
        <x-core::stat-card label="সদস্য" label-en="Members" :value="number_format($members->where('status', 'active')->count())" icon="users" />
        <x-core::stat-card label="অব্যবহৃত পয়েন্ট" label-en="Outstanding Points" :value="number_format($outstandingPoints)" icon="sparkles" />
        <x-core::stat-card label="পয়েন্টের মূল্য" label-en="Points Value" :value="'৳'.number_format($outstandingValue, 2)" icon="tag" />
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:16px; align-items:start;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title">
                    <span class="bn">পয়েন্টের নিয়ম</span>
                    <span class="en" style="display:none;">Points Rules</span>
                </div>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('loyalty.program.update') }}">
                    @csrf
                    @method('PUT')
                    <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:14px;">
                        <x-core::checkbox size="sm" name="is_enabled" value="1" :checked="old('is_enabled', $program->is_enabled)">
                            <span style="font-size:13px; font-weight:600;">
                                <span class="bn">কোম্পানিতে লয়্যালটি প্রোগ্রাম চালু</span>
                                <span class="en" style="display:none;">Loyalty programme is on for the company</span>
                            </span>
                        </x-core::checkbox>
                        <x-core::checkbox size="sm" name="shop_participates" value="1" :checked="old('shop_participates', $shopParticipates)">
                            <span style="font-size:13px; font-weight:600;">
                                <span class="bn">এই দোকানে পয়েন্ট দেওয়া হবে</span>
                                <span class="en" style="display:none;">This shop gives points</span>
                            </span>
                        </x-core::checkbox>
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px;">
                        <x-core::input size="sm" type="number" step="0.01" name="spend_amount" label="প্রতি খরচ (৳)" label-en="For Every (৳) Spent" :value="old('spend_amount', $program->spend_amount ?? 100)" :stepper="false" :required="true" />
                        <x-core::input size="sm" type="number" name="points_per_spend" label="পয়েন্ট পাবে" label-en="Points Earned" :value="old('points_per_spend', $program->points_per_spend ?? 1)" :stepper="false" :required="true" />
                        <x-core::input size="sm" type="number" step="0.01" name="point_value" label="১ পয়েন্টের মূল্য (৳)" label-en="1 Point Is Worth (৳)" :value="old('point_value', $program->point_value ?? 1)" :stepper="false" :required="true" />
                        <x-core::input size="sm" type="number" name="min_redeem_points" label="সর্বনিম্ন রিডিম পয়েন্ট" label-en="Minimum Points to Redeem" :value="old('min_redeem_points', $program->min_redeem_points ?? 0)" :stepper="false" :required="true" />
                        <x-core::input size="sm" type="number" name="max_redeem_percent" label="বিলের সর্বোচ্চ % পয়েন্টে" label-en="Max % of Bill in Points" :value="old('max_redeem_percent', $program->max_redeem_percent ?? 100)" :stepper="false" :required="true" />
                        <x-core::input
                            size="sm"
                            type="number"
                            name="points_expire_after_days"
                            label="মেয়াদ (দিন)"
                            label-en="Expires After (Days)"
                            :value="old('points_expire_after_days', $program->points_expire_after_days)"
                            placeholder="খালি = কখনো শেষ হবে না"
                            placeholder-en="Empty = never expires"
                            :stepper="false"
                        />
                    </div>

                    @can('loyalty.edit')
                        <div style="margin-top:16px;">
                            <x-core::button type="submit" size="sm" variant="solid" color="primary">
                                <span class="bn">সংরক্ষণ করুন</span>
                                <span class="en" style="display:none;">Save</span>
                            </x-core::button>
                        </div>
                    @endcan
                </form>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title">
                    <span class="bn">সদস্য</span>
                    <span class="en" style="display:none;">Members</span>
                </div>
            </div>
            <div class="panel-body">
                @can('loyalty.edit')
                    <form method="POST" action="{{ route('loyalty.members.store') }}" style="display:flex; align-items:flex-end; gap:8px; flex-wrap:wrap; margin-bottom:14px;">
                        @csrf
                        <div style="flex:1; min-width:200px;">
                            <x-core::select
                                size="sm"
                                name="customer_id"
                                label="গ্রাহক"
                                label-en="Customer"
                                :options="$enrollableCustomers->mapWithKeys(fn ($customer) => [$customer->id => $customer->name.($customer->phone ? ' ('.$customer->phone.')' : '')])->all()"
                                placeholder="-- গ্রাহক নির্বাচন করুন --"
                                placeholder-en="-- Select a customer --"
                                :required="true"
                            />
                        </div>
                        <div style="width:150px;">
                            <x-core::input size="sm" name="card_no" label="কার্ড নম্বর" label-en="Card No." placeholder="স্বয়ংক্রিয়" placeholder-en="Automatic" />
                        </div>
                        <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus">
                            <span class="bn">সদস্য করুন</span>
                            <span class="en" style="display:none;">Enroll</span>
                        </x-core::button>
                    </form>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">গ্রাহক</span><span class="en" style="display:none;">Customer</span></th>
                            <th><span class="bn">কার্ড</span><span class="en" style="display:none;">Card</span></th>
                            <th class="table-cell-right"><span class="bn">পয়েন্ট</span><span class="en" style="display:none;">Points</span></th>
                            <th class="table-cell-right"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $membership)
                            <tr>
                                <td>
                                    <div style="font-weight:600; color:var(--ink-900);">{{ $membership->customer?->name }}</div>
                                    <div style="font-size:11.5px; color:var(--ink-500);">{{ $membership->customer?->phone }}</div>
                                </td>
                                <td style="font-family:var(--font-mono, monospace);">
                                    {{ $membership->card_no }}
                                    @unless ($membership->isActive())
                                        <x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />
                                    @endunless
                                </td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ number_format((int) ($pointsByCustomer[$membership->customer_id] ?? 0)) }}</td>
                                <td class="table-cell-right">
                                    @can('loyalty.edit')
                                        @if ($membership->isActive())
                                            <form method="POST" action="{{ route('loyalty.members.destroy', $membership) }}" class="delete-form" data-title="সদস্যপদ বাতিল করবেন?" data-text="গ্রাহক আর নতুন পয়েন্ট পাবেন না; আগের পয়েন্টের হিসাব থাকবে।">
                                                @csrf
                                                @method('DELETE')
                                                <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="সদস্যপদ বাতিল / Remove membership" />
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-core::table.empty icon="users" title="এখনো কোনো সদস্য নেই" title-en="No members yet" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-core::layout>
