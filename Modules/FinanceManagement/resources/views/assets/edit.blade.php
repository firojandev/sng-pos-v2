<x-core::layout
    title="সম্পদ সম্পাদনা"
    title-en="Edit Asset"
    subtitle="সম্পদের তথ্য হালনাগাদ করুন"
    subtitle-en="Update asset details"
    active="assets"
>
    <x-financemanagement::tabbar active="assets" />

    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">সম্পদের তথ্য</div>
            <div class="panel-title en" style="display:none;">Asset Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('assets.update', $asset) }}">
                @csrf
                @method('PUT')
                @include('financemanagement::assets._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" color="primary" size="sm" icon="check" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </x-core::button>
                    <x-core::button variant="secondary" size="sm" :href="route('assets.index')" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
