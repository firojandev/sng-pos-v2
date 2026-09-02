<x-core::layout
    title="পণ্য তালিকা"
    title-en="Product List"
    subtitle="পণ্যের তালিকা পরিচালনা করুন"
    subtitle-en="Manage your product catalogue"
    active="products"
>
    <x-product::tabbar active="products" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('products.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন পণ্য</span><span class="en">New Product</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                            <th class="bn">SKU</th><th class="en" style="display:none;">SKU</th>
                            <th class="bn">ক্যাটাগরি</th><th class="en" style="display:none;">Category</th>
                            <th class="bn">ব্র্যান্ড</th><th class="en" style="display:none;">Brand</th>
                            <th class="bn">ইউনিট</th><th class="en" style="display:none;">Units</th>
                            <th class="bn">ভ্যাট</th><th class="en" style="display:none;">VAT</th>
                            <th class="bn">অবস্থা</th><th class="en" style="display:none;">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <div class="row-avatar">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="" style="width:30px; height:30px; border-radius:8px; object-fit:cover; flex:0 0 auto;">
                                        @else
                                            <div class="av" style="background:var(--teal-800);">{{ mb_substr($product->name, 0, 1) }}</div>
                                        @endif
                                        <div class="cell-main">{{ $product->name }}{{ $product->size ? ' ('.$product->size.')' : '' }}</div>
                                    </div>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->category->name ?? '—' }}</td>
                                <td>{{ $product->brand->name ?? '—' }}</td>
                                <td>{{ $product->units->pluck('short_code')->implode(', ') }}</td>
                                <td>
                                    @if ($product->is_vat)
                                        <span class="badge b-teal">{{ rtrim(rtrim(number_format($product->vat_percentage, 2), '0'), '.') }}%</span>
                                    @else
                                        <span class="badge b-grey bn">নেই</span><span class="badge b-grey en" style="display:none;">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($product->status === 'active')
                                        <span class="badge b-green bn">সক্রিয়</span><span class="badge b-green en" style="display:none;">Active</span>
                                    @else
                                        <span class="badge b-grey bn">নিষ্ক্রিয়</span><span class="badge b-grey en" style="display:none;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('products.edit', $product) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('এই পণ্য মুছে ফেলতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act" title="Delete">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#C1443C" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-core::table.empty
                                        icon="package"
                                        title="কোনো পণ্য নেই"
                                        title-en="No products found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
