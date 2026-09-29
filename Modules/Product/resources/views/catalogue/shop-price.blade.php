<x-core::layout
    title="দোকানের নিজস্ব মূল্য"
    title-en="Shop Price"
    subtitle="শুধুমাত্র এই দোকানের জন্য মূল্য নির্ধারণ করুন"
    subtitle-en="Set prices for this shop only"
    active="products"
>
    <x-product::tabbar active="products" />

    <div class="panel" style="margin-top:16px; max-width:640px;">
        <div class="panel-head">
            <div class="panel-title">{{ $product->name }}</div>
        </div>
        <div class="panel-body">
            <p style="font-size:12.5px; color:var(--ink-600); margin:0 0 14px;">
                <span class="bn">খালি রাখলে কোম্পানির মূল্য প্রযোজ্য হবে (বন্ধনীতে দেখানো)।</span>
                <span class="en" style="display:none;">Leave a field empty to use the company value (shown as the placeholder).</span>
            </p>
            @php
                $fields = [
                    'sale_price' => ['বিক্রয় মূল্য', 'Sale Price'],
                    'purchase_price' => ['ক্রয় মূল্য', 'Purchase Price'],
                    'wholesale_price' => ['পাইকারি মূল্য', 'Wholesale Price'],
                    'alert_qty' => ['সতর্কতা পরিমাণ', 'Alert Quantity'],
                    'vat_percentage' => ['ভ্যাট (%)', 'VAT (%)'],
                ];
            @endphp
            <form method="POST" action="{{ route('products.shop-price.update', $product) }}">
                @csrf
                @method('PUT')
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px;">
                    @foreach ($fields as $field => [$labelBn, $labelEn])
                        <x-core::input
                            size="sm"
                            type="number"
                            step="0.01"
                            min="0"
                            :name="$field"
                            :label="$labelBn"
                            :label-en="$labelEn"
                            :value="old($field, $listing->getRawOriginal($field))"
                            :placeholder="(string) ($product->companyValue($field) ?? '')"
                            :placeholder-en="(string) ($product->companyValue($field) ?? '')"
                            :stepper="false"
                        />
                    @endforeach
                </div>

                <div style="display:flex; gap:10px; margin-top:20px;">
                    <x-core::button type="submit" size="sm" variant="solid" color="primary">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                    <x-core::button as="a" href="{{ route('products.index') }}" size="sm" variant="secondary">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
