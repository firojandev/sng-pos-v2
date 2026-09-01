<x-core::layout
    title="সাবস্ক্রিপশন"
    title-en="Subscription"
    subtitle="আপনার দোকানের প্ল্যান ও ব্যবহার দেখুন"
    subtitle-en="View your shop's plan and usage"
    active="subscription"
>
    @if ($subscription && ! $subscription->isUsable())
        <div class="panel" style="margin-top:0; max-width:720px; border-color:var(--red-600);">
            <div class="panel-body">
                <div class="badge b-red bn" style="margin-bottom:8px;">সাবস্ক্রিপশন নিষ্ক্রিয়</div>
                <div class="badge b-red en" style="display:none; margin-bottom:8px;">Subscription Inactive</div>
                <p class="bn" style="font-size:13.5px;">আপনার সাবস্ক্রিপশন বর্তমানে সক্রিয় নেই, তাই অ্যাপের অন্যান্য অংশ ব্যবহার করা যাবে না। অনুগ্রহ করে পরিশোধ করুন অথবা প্রশাসকের সাথে যোগাযোগ করুন।</p>
                <p class="en" style="display:none; font-size:13.5px;">Your subscription isn't currently active, so the rest of the app is unavailable. Please renew, or contact your administrator.</p>
            </div>
        </div>
    @endif

    <div class="panel" style="margin-top:0; max-width:720px;">
        <div class="panel-head">
            <div class="panel-title bn">বর্তমান প্ল্যান</div>
            <div class="panel-title en" style="display:none;">Current Plan</div>
        </div>
        <div class="panel-body">
            @if ($subscription && $subscription->plan)
                @php $label = $subscription->statusLabel(); @endphp
                <div class="tx-row strong">
                    <span class="lbl bn">প্ল্যান</span><span class="lbl en" style="display:none;">Plan</span>
                    <span class="val">{{ $subscription->plan->name }} &middot; ৳{{ number_format($subscription->plan->price, 0) }}/{{ $subscription->plan->billing_cycle === 'monthly' ? 'মাস' : 'বছর' }}</span>
                </div>
                <div class="tx-row">
                    <span class="lbl bn">অবস্থা</span><span class="lbl en" style="display:none;">Status</span>
                    <span class="val">
                        @if ($subscription->isUsable())
                            <span class="badge b-green bn">{{ $label['bn'] }}</span><span class="badge b-green en" style="display:none;">{{ $label['en'] }}</span>
                        @else
                            <span class="badge b-red bn">{{ $label['bn'] }}</span><span class="badge b-red en" style="display:none;">{{ $label['en'] }}</span>
                        @endif
                    </span>
                </div>
                @if ($subscription->trial_ends_at)
                    <div class="tx-row">
                        <span class="lbl bn">ট্রায়াল শেষ</span><span class="lbl en" style="display:none;">Trial Ends</span>
                        <span class="val" style="font-weight:400;">{{ $subscription->trial_ends_at->format('d M, Y') }}</span>
                    </div>
                @endif
                @if ($subscription->current_period_end)
                    <div class="tx-row">
                        <span class="lbl bn">বর্তমান মেয়াদ শেষ</span><span class="lbl en" style="display:none;">Current Period Ends</span>
                        <span class="val" style="font-weight:400;">{{ $subscription->current_period_end->format('d M, Y') }}</span>
                    </div>
                @endif

                <div class="drawer-title bn" style="font-size:14px; margin:18px 0 10px;">ব্যবহার</div>
                <div class="drawer-title en" style="display:none; font-size:14px; margin:18px 0 10px;">Usage</div>

                @foreach ([
                    'max_users' => ['bn' => 'ইউজার', 'en' => 'Users'],
                    'max_branches' => ['bn' => 'শাখা', 'en' => 'Branches'],
                    'max_warehouses' => ['bn' => 'গুদাম', 'en' => 'Warehouses'],
                    'max_products' => ['bn' => 'পণ্য', 'en' => 'Products'],
                ] as $key => $label)
                    @php $limit = $subscription->plan->{$key}; @endphp
                    <div class="tx-row">
                        <span class="lbl bn">{{ $label['bn'] }}</span><span class="lbl en" style="display:none;">{{ $label['en'] }}</span>
                        <span class="val" style="font-weight:400;">
                            {{ $usage[$key] }} / {{ $limit ?? '∞' }}
                        </span>
                    </div>
                @endforeach

                @if ($subscription->payments->isNotEmpty())
                    <div class="drawer-title bn" style="font-size:14px; margin:18px 0 10px;">পেমেন্ট ইতিহাস</div>
                    <div class="drawer-title en" style="display:none; font-size:14px; margin:18px 0 10px;">Payment History</div>
                    @foreach ($subscription->payments as $payment)
                        <div class="tx-row">
                            <span class="lbl" style="font-weight:400;">{{ $payment->paid_at->format('d M, Y') }}</span>
                            <span class="val">৳{{ number_format($payment->amount, 2) }}</span>
                        </div>
                    @endforeach
                @endif
            @else
                <div class="helper" style="margin-top:0;">
                    <span class="bn">কোনো সাবস্ক্রিপশন তথ্য নেই।</span>
                    <span class="en" style="display:none;">No subscription information available.</span>
                </div>
            @endif
        </div>
    </div>
</x-core::layout>
