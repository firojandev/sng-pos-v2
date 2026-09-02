<x-core::layout
    title="নতুন প্ল্যান তৈরি"
    title-en="Create Plan"
    subtitle="নতুন সাবস্ক্রিপশন প্যাকেজ, রিসোর্স কোটা ও ফিচার কনফিগার করুন"
    subtitle-en="Configure new subscription plan, quotas, pricing and feature permissions"
    active="plans"
>
    <div class="panel" style="margin-top:0; width:100%;">
        <div class="panel-head" style="padding:16px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <x-core::icon name="plus" size="18" />
                </div>
                <div>
                    <div class="panel-title bn" style="font-size:16px;">নতুন সাবস্ক্রিপশন প্ল্যান তৈরি</div>
                    <div class="panel-title en" style="display:none; font-size:16px;">Create New Subscription Plan</div>
                    <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                        <span class="bn">দোকানদারদের সাবস্ক্রিপশন প্যাকেজের মূল্য, রিসোর্স কোটা ও পারমিশন নির্ধারণ করুন</span>
                        <span class="en" style="display:none;">Define pricing, quotas, and feature permissions for merchant subscriptions</span>
                    </div>
                </div>
            </div>

            <x-core::button
                as="a"
                href="{{ route('plans.index') }}"
                variant="soft"
                color="secondary"
                size="sm"
                icon="arrow-left"
            >
                <span class="bn">প্ল্যান তালিকায় ফিরে যান</span>
                <span class="en" style="display:none;">Back to Plan List</span>
            </x-core::button>
        </div>

        <div class="panel-body" style="padding:22px;">
            <form method="POST" action="{{ route('plans.store') }}">
                @csrf
                @include('shop::plans._form')
            </form>
        </div>
    </div>
</x-core::layout>
