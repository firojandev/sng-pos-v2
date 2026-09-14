<x-core::layout
    title="জামানত সম্পাদনা"
    title-en="Edit Security Money"
    subtitle="জামানতের তথ্য হালনাগাদ করুন"
    subtitle-en="Update security money details"
    active="security-money"
>
    <x-financemanagement::tabbar active="security-money" />

    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">জামানতের তথ্য</div>
            <div class="panel-title en" style="display:none;">Security Money Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('security-money.update', $securityMoney) }}">
                @csrf
                @method('PUT')
                @include('financemanagement::security-money._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" size="sm" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </x-core::button>
                    <x-core::button variant="secondary" size="sm" :href="route('security-money.index')" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
