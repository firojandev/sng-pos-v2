<x-core::layout
    title="ধার সম্পাদনা"
    title-en="Edit Lend"
    subtitle="ধারের তথ্য হালনাগাদ করুন"
    subtitle-en="Update lend details"
    active="lend"
>
    <x-financemanagement::tabbar active="lend" />

    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">ধারের তথ্য</div>
            <div class="panel-title en" style="display:none;">Lend Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('lend.update', $lend) }}">
                @csrf
                @method('PUT')
                @include('financemanagement::lend._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" size="sm" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </x-core::button>
                    <x-core::button variant="secondary" size="sm" :href="route('lend.index')" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
