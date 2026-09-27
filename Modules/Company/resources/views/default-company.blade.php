<x-core::layout title="ডিফল্ট কোম্পানি" title-en="Default Company" subtitle="কোম্পানি ছাড়া নিবন্ধিত সব দোকান" subtitle-en="Every shop registered without a company" active="default-company">
    @if ($errors->any())
        <div style="color:var(--red-600); font-size:12.5px; margin-bottom:12px;">{{ $errors->first() }}</div>
    @endif

    <div class="panel" style="margin-top:0;">
        <div class="panel-body" style="display:flex; flex-wrap:wrap; gap:28px;">
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">কোম্পানি</span><span class="en" style="display:none;">Company</span></div><div style="font-size:18px; font-weight:800;">{{ $company->name }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">মোট দোকান</span><span class="en" style="display:none;">Shops</span></div><div style="font-size:18px; font-weight:700;">{{ $totals['shops'] }}</div></div>
            <div><div style="font-size:12px; color:var(--ink-500);"><span class="bn">সক্রিয়</span><span class="en" style="display:none;">Active</span></div><div style="font-size:18px; font-weight:700;">{{ $totals['active'] }}</div></div>
        </div>
    </div>

    <form method="GET" style="display:flex; gap:8px; align-items:center; flex-wrap:nowrap; margin:16px 0 12px;">
        <div style="flex:1; min-width:200px;"><x-core::input size="sm" :no-margin="true" name="q" :value="request('q')" placeholder="দোকানের নাম, ফোন বা কোড..." placeholder-en="Shop name, phone or code..." /></div>
        <div style="width:160px; flex-shrink:0;"><x-core::select size="sm" :no-margin="true" name="status" :value="request('status')" :options="['' => 'সব (All)', 'active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)']" /></div>
        <x-core::button type="submit" size="sm" variant="secondary" icon="filter"><span class="bn">খুঁজুন</span><span class="en" style="display:none;">Search</span></x-core::button>
    </form>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">দোকান</span><span class="en" style="display:none;">Shop</span></th>
                        <th><span class="bn">মালিক</span><span class="en" style="display:none;">Owner</span></th>
                        <th><span class="bn">প্ল্যান</span><span class="en" style="display:none;">Plan</span></th>
                        <th><span class="bn">মেয়াদ</span><span class="en" style="display:none;">Expires</span></th>
                        <th class="table-cell-center"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                        <th class="table-cell-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shops as $shop)
                        @php
                            $subscription = $shop->company?->activeSubscription;
                            $owner = $shop->users->first();
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $shop->name }}</div>
                                <div style="font-size:11.5px; color:var(--ink-500);">{{ $shop->store_code }}{{ $shop->phone ? ' · '.$shop->phone : '' }} · {{ $shop->created_at?->format('d M, Y') }}</div>
                            </td>
                            <td style="font-size:12.5px;">{{ $owner?->name ?? '—' }}<div style="color:var(--ink-500);">{{ $owner?->phone ?: $owner?->email }}</div></td>
                            <td style="font-size:12.5px;">{{ $subscription?->plan?->name ?? '—' }}</td>
                            <td style="font-size:12.5px; white-space:nowrap;">{{ $subscription?->ends_at?->format('d M, Y') ?? ($subscription ? 'আজীবন / Lifetime' : '—') }}</td>
                            <td class="table-cell-center">
                                @if ($shop->status === 'active')
                                    <x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />
                                @else
                                    <x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />
                                @endif
                            </td>
                            <td class="table-cell-right">
                                @if ($shop->status === 'active')
                                    <form method="POST" action="{{ route('default-company.shops.open', $shop) }}">
                                        @csrf
                                        <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="log-in"><span class="bn">প্রবেশ</span><span class="en" style="display:none;">Open</span></x-core::button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-core::table.empty icon="shopping-bag" title="কোনো দোকান নেই" title-en="No shops" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="margin-top:12px;">{{ $shops->links() }}</div>
</x-core::layout>
