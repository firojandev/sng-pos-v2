<x-core::layout
    title="ক্যাটালগ রিভিউ"
    title-en="Catalogue Review"
    subtitle="কোম্পানিগুলোর প্রস্তাবিত পণ্য শেয়ার্ড ক্যাটালগে অনুমোদন করুন"
    subtitle-en="Approve products that companies suggested for the shared catalogue"
    active="catalogue-review"
>
    <div class="section-row" style="margin-bottom:16px;">
        <div class="filters"></div>
        <x-core::button as="a" href="{{ route('catalogue-merge.index') }}" size="sm" variant="secondary" icon="layers">
            <span class="bn">ডুপ্লিকেট মার্জ</span>
            <span class="en" style="display:none;">Merge Duplicates</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">পণ্য</span><span class="en" style="display:none;">Product</span></th>
                        <th><span class="bn">ক্যাটাগরি / ব্র্যান্ড</span><span class="en" style="display:none;">Category / Brand</span></th>
                        <th><span class="bn">বারকোড</span><span class="en" style="display:none;">Barcode</span></th>
                        <th><span class="bn">প্রস্তাবের সময়</span><span class="en" style="display:none;">Suggested</span></th>
                        <th class="table-cell-right"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suggestions as $product)
                        <tr>
                            <td style="font-weight:600; color:var(--ink-900);">{{ $product->name }}</td>
                            <td>{{ $product->category?->name ?? '—' }} / {{ $product->brand?->name ?? '—' }}</td>
                            <td style="font-family:var(--font-mono, monospace);">{{ $product->barcode ?: '—' }}</td>
                            <td>{{ $product->suggested_at?->format('d M, Y') }}</td>
                            <td class="table-cell-right">
                                <x-core::button-group size="xs">
                                    <form method="POST" action="{{ route('catalogue-review.approve', $product) }}">
                                        @csrf
                                        <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="check" title="অনুমোদন / Approve">
                                            <span class="bn">অনুমোদন</span>
                                            <span class="en" style="display:none;">Approve</span>
                                        </x-core::button>
                                    </form>
                                    <form method="POST" action="{{ route('catalogue-review.reject', $product) }}">
                                        @csrf
                                        <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="x" title="বাতিল / Reject">
                                            <span class="bn">বাতিল</span>
                                            <span class="en" style="display:none;">Reject</span>
                                        </x-core::button>
                                    </form>
                                </x-core::button-group>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-core::table.empty icon="check" title="রিভিউয়ের জন্য কোনো প্রস্তাব নেই" title-en="No suggestions to review" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
