<x-core::layout
    title="নতুন ধার"
    title-en="New Lend"
    subtitle="একটি নতুন ধার যোগ করুন"
    subtitle-en="Add a new lend"
    active="lend"
>
    <x-financemanagement::tabbar active="lend" />

    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">ধারের তথ্য</div>
            <div class="panel-title en" style="display:none;">Lend Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('lend.store') }}">
                @csrf
                @include('financemanagement::lend._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" size="sm" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                    </x-core::button>
                    <x-core::button variant="secondary" size="sm" :href="route('lend.index')" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
