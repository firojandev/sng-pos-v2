<x-core::layout
    title="ব্যাচ"
    title-en="Batch"
    subtitle="পণ্যের ব্যাচ পরিচালনা করুন"
    subtitle-en="Manage product batches"
    active="products"
>
    <x-product::tabbar active="batches" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('batches.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন ব্যাচ</span><span class="en">New Batch</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">ব্যাচ নং</th><th class="en" style="display:none;">Batch No</th>
                            <th class="bn">পণ্য</th><th class="en" style="display:none;">Product</th>
                            <th class="bn">উৎপাদন তারিখ</th><th class="en" style="display:none;">Mfg Date</th>
                            <th class="bn">মেয়াদ শেষ</th><th class="en" style="display:none;">Exp Date</th>
                            <th class="bn">পরিমাণ</th><th class="en" style="display:none;">Qty</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($batches as $batch)
                            <tr>
                                <td class="cell-main">{{ $batch->batch_no }}</td>
                                <td>{{ $batch->product->name ?? '—' }}</td>
                                <td>{{ optional($batch->mfg_date)->format('d M, Y') ?? '—' }}</td>
                                <td>{{ optional($batch->expiry_date)->format('d M, Y') ?? '—' }}</td>
                                <td>{{ rtrim(rtrim(number_format($batch->quantity, 2), '0'), '.') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('batches.edit', $batch) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('batches.destroy', $batch) }}" onsubmit="return confirm('এই ব্যাচ মুছে ফেলতে চান?');">
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
                            <tr><td colspan="6"><div class="helper" style="margin-top:0;">কোনো ব্যাচ নেই</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $batches->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
