<x-core::layout
    title="শাখা সম্পাদনা"
    title-en="Edit Branch"
    subtitle="শাখার তথ্য হালনাগাদ করুন"
    subtitle-en="Update the branch details"
    active="branches"
>
    <x-shop::tabbar active="branches" />

    <div class="panel" style="margin-top:0; max-width:560px;">
        <div class="panel-head">
            <div class="panel-title">
                <span class="bn">শাখার তথ্য</span>
                <span class="en" style="display:none;">Branch Details</span>
            </div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('branches.update', $branch) }}">
                @csrf
                @method('PUT')
                @include('shop::branches._form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button
                        type="submit"
                        variant="solid"
                        color="primary"
                        style="flex:1; justify-content:center;"
                    >
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                    <x-core::button
                        as="a"
                        href="{{ route('branches.index') }}"
                        variant="secondary"
                        style="flex:1; justify-content:center;"
                    >
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
