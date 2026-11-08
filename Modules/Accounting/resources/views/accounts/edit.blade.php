<x-core::layout title="অ্যাকাউন্ট সম্পাদনা" title-en="Edit Account" subtitle="{{ $account->code }} — {{ $account->name }}" subtitle-en="{{ $account->code }} — {{ $account->name }}" active="ledger-accounts">
    <x-accounting::tabbar active="accounts" />

    @php $isStandard = $account->system_key && ! str_starts_with($account->system_key, 'money_account_'); @endphp

    <div class="panel" style="margin-top:16px; max-width:620px;">
        <div class="panel-body">
            @if ($isStandard)
                <p style="font-size:12.5px; color:var(--ink-600); margin:0 0 12px;">
                    <span class="bn">এটি একটি নির্ধারিত অ্যাকাউন্ট; অ্যাপ এতে স্বয়ংক্রিয়ভাবে এন্ট্রি দেয়, তাই কোড ও অবস্থা পরিবর্তন করা যায় না।</span>
                    <span class="en" style="display:none;">This is a standard account the app posts to, so its code and status can't be changed.</span>
                </p>
            @endif
            <form method="POST" action="{{ route('ledger-accounts.update', $account) }}" style="display:flex; flex-direction:column; gap:14px;">
                @csrf
                @method('PUT')
                <div style="display:grid; grid-template-columns:140px 1fr; gap:12px;">
                    <x-core::input size="sm" name="code" label="কোড" label-en="Code" :value="old('code', $account->code)" :required="true" :readonly="$isStandard" />
                    <x-core::input size="sm" name="name" label="নাম" label-en="Name" :value="old('name', $account->name)" :required="true" />
                </div>
                <x-core::input size="sm" name="description" label="বিবরণ" label-en="Description" :value="old('description', $account->description)" />
                @unless ($isStandard)
                    <x-core::checkbox size="sm" name="is_active" value="1" :checked="(bool) old('is_active', $account->is_active)">
                        <span style="font-size:13px;"><span class="bn">সক্রিয়</span><span class="en" style="display:none;">Active</span></span>
                    </x-core::checkbox>
                @endunless
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
