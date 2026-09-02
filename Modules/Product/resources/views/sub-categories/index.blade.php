<x-core::layout
    title="সাব-ক্যাটাগরি"
    title-en="Sub-category"
    subtitle="পণ্যের সাব-ক্যাটাগরি পরিচালনা করুন"
    subtitle-en="Manage product sub-categories"
    active="products"
>
    <x-product::tabbar active="sub-categories" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('sub-categories.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন সাব-ক্যাটাগরি</span><span class="en">New Sub-category</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">নাম</th><th class="en" style="display:none;">Name</th>
                            <th class="bn">মূল ক্যাটাগরি</th><th class="en" style="display:none;">Parent Category</th>
                            <th class="bn">পণ্য সংখ্যা</th><th class="en" style="display:none;">Products</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subCategories as $subCategory)
                            <tr>
                                <td class="cell-main">{{ $subCategory->name }}</td>
                                <td>{{ $subCategory->category->name ?? '—' }}</td>
                                <td>{{ $subCategory->products_count }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('sub-categories.edit', $subCategory) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('sub-categories.destroy', $subCategory) }}" onsubmit="return confirm('এই সাব-ক্যাটাগরি মুছে ফেলতে চান?');">
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
                                <td colspan="4">
                                    <x-core::table.empty
                                        icon="folder-tree"
                                        title="কোনো সাব-ক্যাটাগরি নেই"
                                        title-en="No sub-categories found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $subCategories->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
