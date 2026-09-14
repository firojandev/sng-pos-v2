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

    @push('scripts')
        <script>
        $(function () {
            function updateNetPreview() {
                var amount = parseFloat($('input[name="amount"]').val()) || 0;
                var type = $('select[name="depreciation_type"]').val() || 'flat';
                var depreciation = parseFloat($('input[name="depreciation"]').val()) || 0;
                var $depInput = $('input[name="depreciation"]');
                var $preview = $('.asset-net-preview');

                var depAmount = 0;
                if (type === 'percentage') {
                    depAmount = (amount * depreciation) / 100;
                    $depInput.attr('max', '100');
                } else {
                    depAmount = depreciation;
                    $depInput.removeAttr('max');
                }

                if (amount > 0 || depreciation > 0) {
                    var net = Math.max(0, amount - depAmount);
                    $preview.css('display', 'flex');
                    var subtext = type === 'percentage'
                        ? '৳' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (অবচয়: ৳' + depAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' / ' + depreciation + '%)'
                        : '৳' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (অবচয়: ৳' + depAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
                    $preview.find('.asset-net-val').text(subtext);
                } else {
                    $preview.hide();
                }
            }

            $(document).on('input', 'input[name="amount"], input[name="depreciation"]', updateNetPreview);
            $(document).on('change', 'select[name="depreciation_type"]', updateNetPreview);
            updateNetPreview();
        });
        </script>
    @endpush
</x-core::layout>
