<x-core::layout title="নতুন অ্যাকাউন্ট" title-en="New Account" subtitle="হিসাবের তালিকায় অ্যাকাউন্ট যোগ করুন" subtitle-en="Add an account to the chart" active="ledger-accounts">
    <x-accounting::tabbar active="accounts" />

    <div class="panel" style="margin-top:16px; max-width:620px;">
        <div class="panel-body">
            <form method="POST" action="{{ route('ledger-accounts.store') }}" style="display:flex; flex-direction:column; gap:14px;">
                @csrf
                <x-core::select
                    size="sm"
                    name="parent_id"
                    label="যে গ্রুপের অধীনে"
                    label-en="Under Group"
                    :options="$groups->mapWithKeys(fn ($group) => [$group->id => $group->code.' — '.$group->name])->all()"
                    :value="old('parent_id')"
                    :required="true"
                />
                <div style="display:grid; grid-template-columns:140px 1fr; gap:12px;">
                    <x-core::input size="sm" name="code" label="কোড" label-en="Code" :value="old('code')" :required="true" />
                    <x-core::input size="sm" name="name" label="নাম" label-en="Name" :value="old('name')" :required="true" />
                </div>
                <x-core::checkbox size="sm" name="is_group" value="1" :checked="(bool) old('is_group')">
                    <span style="font-size:13px;">
                        <span class="bn">এটি একটি গ্রুপ (এতে সরাসরি এন্ট্রি হবে না)</span>
                        <span class="en" style="display:none;">This is a group (no entries are posted to it)</span>
                    </span>
                </x-core::checkbox>
                <x-core::input size="sm" name="description" label="বিবরণ" label-en="Description" :value="old('description')" />
                <div style="display:flex; gap:10px;">
                    <x-core::button type="submit" size="sm" variant="solid" color="primary">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en" style="display:none;">Save</span>
                    </x-core::button>
                    <x-core::button as="a" href="{{ route('ledger-accounts.index') }}" size="sm" variant="secondary">
                        <span class="bn">বাতিল</span><span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
