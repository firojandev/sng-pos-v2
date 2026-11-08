<x-core::layout title="নতুন কোম্পানি" title-en="New Company" subtitle="কোম্পানি ও তার প্ল্যান — কোম্পানির সব দোকান এই প্ল্যান ব্যবহার করবে" subtitle-en="A company and its plan — every shop of the company uses this plan" active="companies">
    <form method="POST" action="{{ route('companies.store') }}">
        @csrf
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; align-items:start;">
            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">কোম্পানির তথ্য</span><span class="en" style="display:none;">Company Details</span></div></div>
                <div class="panel-body">@include('company::_form')</div>
            </div>
            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">প্ল্যান</span><span class="en" style="display:none;">Plan</span></div></div>
                <div class="panel-body" style="display:flex; flex-direction:column; gap:12px;">
                    <x-core::select size="sm" name="plan_id" label="প্ল্যান" label-en="Plan" :value="old('plan_id')" placeholder="-- পরে দেওয়া হবে --" placeholder-en="-- Assign later --"
                        :options="$plans->mapWithKeys(fn ($plan) => [$plan->id => $plan->name.' — ৳'.number_format((float) $plan->price, 0)])->all()" />
                    <x-core::select size="sm" name="subscription_status" label="অবস্থা" label-en="Status" :value="old('subscription_status', 'active')" :options="['active' => 'সক্রিয় (Active)', 'trialing' => 'ট্রায়াল (Trial)']" />
                    <x-core::input size="sm" type="date" name="current_period_end" label="মেয়াদ শেষ (ঐচ্ছিক)" label-en="Period Ends (optional)" :value="old('current_period_end')" />
                    <p style="font-size:12px; color:var(--ink-500); margin:0;">
                        <span class="bn">তারিখ না দিলে প্ল্যানের মেয়াদ (মাসিক ৩০ / বার্ষিক ৩৬৫ দিন) অনুযায়ী হবে। কোম্পানি তৈরির পর "দোকান তৈরি" থেকে এই কোম্পানির দোকান তৈরি করুন — প্রথম দোকানের এডমিন কোম্পানির মালিক হবেন।</span>
                        <span class="en" style="display:none;">Without a date the plan's cycle is used (30 / 365 days). Then create the company's shops from "Create Shop" — the first shop's admin becomes the company owner.</span>
                    </p>
                    <div style="display:flex; gap:8px;">
                        <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="check"><span class="bn">তৈরি করুন</span><span class="en" style="display:none;">Create</span></x-core::button>
                        <x-core::button as="a" href="{{ route('companies.index') }}" size="sm" variant="secondary"><span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span></x-core::button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-core::layout>
