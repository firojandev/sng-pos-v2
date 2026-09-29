<x-core::layout
    title="ক্যাটালগ"
    title-en="Catalogue"
    subtitle="দোকানে কোন ক্যাটাগরির পণ্য বিক্রি হয় তা নির্বাচন করুন এবং ক্যাটালগ থেকে পণ্য যোগ করুন"
    subtitle-en="Choose the categories this shop sells and add products from the catalogue"
    active="products"
>
    <x-product::tabbar active="catalogue" />

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title">
                    <span class="bn">এই দোকানের ক্যাটাগরি</span>
                    <span class="en" style="display:none;">Categories This Shop Sells</span>
                </div>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('catalogue.categories.update') }}">
                    @csrf
                    @method('PUT')
                    <div style="display:flex; flex-direction:column; gap:8px; max-height:420px; overflow-y:auto;">
                        @forelse ($categories as $category)
                            <x-core::checkbox
                                size="sm"
                                name="category_ids[]"
                                :value="$category->id"
                                :checked="in_array($category->id, $selectedCategoryIds)"
                            >
                                <span style="font-size:13px; color:var(--ink-800);">{{ $category->name }}</span>
                                @if ($category->company_id === null)
                                    <x-core::badge color="blue" size="xs" variant="soft" label="শেয়ার্ড" label-en="Shared" />
                                @endif
                            </x-core::checkbox>
                        @empty
                            <x-core::table.empty icon="tag" title="কোনো ক্যাটাগরি নেই" title-en="No categories yet" />
                        @endforelse
                    </div>
                    @can('products.edit')
                        <div style="margin-top:16px;">
                            <x-core::button type="submit" size="sm" variant="solid" color="primary">
                                <span class="bn">সংরক্ষণ করুন</span>
                                <span class="en" style="display:none;">Save</span>
                            </x-core::button>
                        </div>
                    @endcan
                </form>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title">
                    <span class="bn">যোগ করার মতো পণ্য</span>
                    <span class="en" style="display:none;">Products You Can Add</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">পণ্য</span><span class="en" style="display:none;">Product</span></th>
                            <th class="table-cell-right"><span class="bn">মূল্য</span><span class="en" style="display:none;">Price</span></th>
                            <th class="table-cell-right"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($addableProducts as $categoryProducts)
                            @php $category = $categoryProducts->first()->category; @endphp
                            <tr>
                                <td colspan="2" style="background:var(--paper); font-weight:700; color:var(--ink-800);">
                                    {{ $category?->name }} ({{ $categoryProducts->count() }})
                                </td>
                                <td class="table-cell-right" style="background:var(--paper);">
                                    @can('products.create')
                                        <form method="POST" action="{{ route('catalogue.categories.list', $category) }}" style="display:inline;">
                                            @csrf
                                            <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="plus">
                                                <span class="bn">সব যোগ করুন</span>
                                                <span class="en" style="display:none;">Add All</span>
                                            </x-core::button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                            @foreach ($categoryProducts as $product)
                                <tr>
                                    <td>
                                        <span style="font-weight:600; color:var(--ink-900);">{{ $product->name }}</span>
                                    </td>
                                    <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">৳{{ number_format((float) $product->sale_price, 2) }}</td>
                                    <td class="table-cell-right">
                                        @can('products.create')
                                            <form method="POST" action="{{ route('catalogue.products.list', $product) }}" style="display:inline;">
                                                @csrf
                                                <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="plus" icon-only title="দোকানে যোগ করুন / Add to shop" />
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="3">
                                    <x-core::table.empty
                                        icon="package"
                                        title="যোগ করার মতো কোনো পণ্য নেই"
                                        title-en="No products to add"
                                        description="বাম পাশে ক্যাটাগরি নির্বাচন করুন; সেই ক্যাটাগরির যে পণ্য এই দোকানে নেই সেগুলো এখানে দেখা যাবে।"
                                        description-en="Select categories on the left; their products that this shop doesn't sell yet appear here."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-core::layout>
