<x-core::layout
    title="ডুপ্লিকেট পণ্য মার্জ"
    title-en="Merge Duplicate Products"
    subtitle="একই পণ্যের একাধিক এন্ট্রি একটিতে মার্জ করুন; বিক্রয়, ক্রয় ও স্টক সব রাখা পণ্যে চলে যাবে"
    subtitle-en="Fold duplicate entries into one product; its sales, purchases and stock move to the product you keep"
    active="catalogue-review"
>
    <div class="section-row" style="margin-bottom:16px;">
        <div class="filters"></div>
        <x-core::button as="a" href="{{ route('catalogue-review.index') }}" size="sm" variant="secondary" icon="arrow-left">
            <span class="bn">ক্যাটালগ রিভিউ</span>
            <span class="en" style="display:none;">Catalogue Review</span>
        </x-core::button>
    </div>

    <div class="panel" style="margin-top:0; margin-bottom:16px;">
        <div class="panel-head">
            <div class="panel-title">
                <span class="bn">আইডি দিয়ে মার্জ করুন</span>
                <span class="en" style="display:none;">Merge by Product ID</span>
            </div>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('catalogue-merge.merge') }}" class="delete-form" data-title="পণ্য মার্জ করবেন?" data-text="ডুপ্লিকেট পণ্যটি মুছে যাবে এবং এর সব রেকর্ড রাখা পণ্যে চলে যাবে।"
                style="display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap;">
                @csrf
                <div style="width:200px;">
                    <x-core::input size="sm" type="number" name="duplicate_id" label="ডুপ্লিকেট পণ্যের আইডি" label-en="Duplicate Product ID" :stepper="false" :required="true" />
                </div>
                <div style="width:200px;">
                    <x-core::input size="sm" type="number" name="keep_id" label="রাখা পণ্যের আইডি" label-en="Product ID to Keep" :stepper="false" :required="true" />
                </div>
                <x-core::button type="submit" size="sm" variant="solid" color="danger" icon="arrow-right">
                    <span class="bn">মার্জ করুন</span>
                    <span class="en" style="display:none;">Merge</span>
                </x-core::button>
            </form>
        </div>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><span class="bn">পণ্য</span><span class="en" style="display:none;">Product</span></th>
                        <th><span class="bn">মালিক</span><span class="en" style="display:none;">Owner</span></th>
                        <th class="table-cell-right"><span class="bn">স্টক</span><span class="en" style="display:none;">Stock</span></th>
                        <th class="table-cell-right"><span class="bn">মার্জ করুন</span><span class="en" style="display:none;">Merge Into</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        @foreach ($group as $product)
                            <tr @if ($loop->first) style="border-top:2px solid var(--border);" @endif>
                                <td style="font-family:var(--font-mono, monospace);">{{ $product->id }}</td>
                                <td>
                                    <div style="font-weight:600; color:var(--ink-900);">{{ $product->name }}</div>
                                    <div style="font-size:11.5px; color:var(--ink-500);">{{ $product->category?->name }} @if ($product->barcode) · {{ $product->barcode }} @endif</div>
                                </td>
                                <td>
                                    @if ($product->isShared())
                                        <x-core::badge color="blue" size="xs" variant="soft" label="শেয়ার্ড" label-en="Shared" />
                                    @else
                                        {{ $product->company?->name }}
                                    @endif
                                </td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ rtrim(rtrim(number_format((float) $product->stock_quantity, 2), '0'), '.') }}</td>
                                <td class="table-cell-right">
                                    <form method="POST" action="{{ route('catalogue-merge.merge') }}" class="delete-form" data-title="পণ্য মার্জ করবেন?" data-text="এই পণ্যটি মুছে যাবে এবং এর সব রেকর্ড নির্বাচিত পণ্যে চলে যাবে।"
                                        style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">
                                        @csrf
                                        <input type="hidden" name="duplicate_id" value="{{ $product->id }}">
                                        <div style="width:190px;">
                                            <x-core::select
                                                size="sm"
                                                name="keep_id"
                                                :no-margin="true"
                                                :options="$group->where('id', '!=', $product->id)->mapWithKeys(fn ($other) => [$other->id => '#'.$other->id.' '.($other->isShared() ? '(Shared)' : '('.($other->company?->name ?? '—').')')])->all()"
                                            />
                                        </div>
                                        <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="arrow-right" icon-only title="মার্জ করুন / Merge" />
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-core::table.empty icon="check" title="একই নামের কোনো ডুপ্লিকেট পণ্য পাওয়া যায়নি" title-en="No products share a name" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
