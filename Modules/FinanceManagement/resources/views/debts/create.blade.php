<x-core::layout
    title="নতুন দেনা"
    title-en="New Debt"
    subtitle="একটি নতুন দেনা যোগ করুন"
    subtitle-en="Add a new debt"
    active="debts"
>
    <x-financemanagement::tabbar active="debts" />

    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">দেনার তথ্য</div>
            <div class="panel-title en" style="display:none;">Debt Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('debts.store') }}">
                @csrf
                @include('financemanagement::debts._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" size="sm" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </x-core::button>
                    <x-core::button variant="secondary" size="sm" :href="route('debts.index')" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
