<x-core::layout
    title="ব্র্যান্ড"
    title-en="Brand"
    subtitle="পণ্যের ব্র্যান্ড পরিচালনা করুন"
    subtitle-en="Manage product brands"
    active="products"
>
    <x-product::tabbar active="brands" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('brands.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন ব্র্যান্ড</span><span class="en">New Brand</span>
                </a>
            </div>

            <div class="mini-grid">
                @forelse ($brands as $brand)
                    <div class="mini-card pm-card">
                        <div class="mini-card-actions">
                            <a class="act" title="Edit" href="{{ route('brands.edit', $brand) }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                            </a>
                            <form method="POST" action="{{ route('brands.destroy', $brand) }}" onsubmit="return confirm('এই ব্র্যান্ড মুছে ফেলতে চান?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act" title="Delete">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#C1443C" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                </button>
                            </form>
                        </div>
                        <div class="row-avatar">
                            <div class="av" style="background:var(--teal-800);">{{ mb_substr($brand->name, 0, 1) }}</div>
                            <div>
                                <div class="nm">{{ $brand->name }}</div>
                                <div class="sub">{{ $brand->models_count }} মডেল &middot; {{ $brand->products_count }} পণ্য</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1;">
                        <x-core::table.empty
                            icon="tag"
                            title="কোনো ব্র্যান্ড নেই"
                            title-en="No brands found"
                        />
                    </div>
                @endforelse
            </div>

            <div style="margin-top:14px;">
                {{ $brands->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
