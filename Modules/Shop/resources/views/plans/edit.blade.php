<x-core::layout
    title="প্ল্যান সম্পাদনা"
    title-en="Edit Plan"
    subtitle="সাবস্ক্রিপশন প্ল্যানের তথ্য ও কোটা হালনাগাদ করুন"
    subtitle-en="Update subscription plan details, resource quotas and feature permissions"
    active="plans"
>
    <div class="panel" style="margin-top:0; width:100%;">
        <div class="panel-head" style="padding:16px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:var(--gold-100); color:var(--gold-ink); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <x-core::icon name="edit" size="18" />
                </div>
                <div>
                    <div class="panel-title bn" style="font-size:16px;">প্ল্যান সম্পাদনা: {{ $plan->name }}</div>
                    <div class="panel-title en" style="display:none; font-size:16px;">Edit Plan: {{ $plan->name }}</div>
                    <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                        <span class="bn">বিদ্যমান প্যাকেজের মূল্য, রিসোর্স কোটা বা মডিউল পারমিশন পরিবর্তন করুন</span>
                        <span class="en" style="display:none;">Update pricing, quotas or feature permissions for this plan</span>
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
            <form method="POST" action="{{ route('plans.update', $plan) }}">
                @csrf
                @method('PUT')
                @include('shop::plans._form')
            </form>
        </div>
    </div>
</x-core::layout>
