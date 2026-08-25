<x-core::layout
    title="সরবরাহকারী সম্পাদনা"
    title-en="Edit Supplier"
    subtitle="সরবরাহকারীর তথ্য হালনাগাদ করুন"
    subtitle-en="Update supplier details"
    active="suppliers"
>
    <div class="panel" style="margin-top:0; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title bn">সরবরাহকারীর তথ্য</div>
            <div class="panel-title en" style="display:none;">Supplier Details</div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                @csrf
                @method('PUT')
                @include('supplier::_form')

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-gold" style="flex:1; justify-content:center;">
                        <span class="bn">হালনাগাদ করুন</span><span class="en">Update</span>
                    </button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline" style="flex:1; justify-content:center;">
                        <span class="bn">বাতিল</span><span class="en">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
