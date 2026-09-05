<x-core::layout
    title="পণ্য তালিকা"
    title-en="Product List"
    subtitle="পণ্যের তালিকা পরিচালনা করুন"
    subtitle-en="Manage your product catalogue"
    active="products"
>
    <x-product::tabbar active="products" />

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div>
                <select name="filter_category" id="filter-category" style="height:36px; padding:0 12px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--ink-800); font-size:13px; outline:none;">
                    <option value="" data-text-bn="সকল ক্যাটাগরি" data-text-en="All Categories">সকল ক্যাটাগরি</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="filter_brand" id="filter-brand" style="height:36px; padding:0 12px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--ink-800); font-size:13px; outline:none;">
                    <option value="" data-text-bn="সকল ব্র্যান্ড" data-text-en="All Brands">সকল ব্র্যান্ড</option>
                    @foreach ($brands as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="filter_status" id="filter-status" style="height:36px; padding:0 12px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--ink-800); font-size:13px; outline:none;">
                    <option value="" data-text-bn="সকল অবস্থা" data-text-en="All Status">সকল অবস্থা</option>
                    <option value="active" data-text-bn="সক্রিয়" data-text-en="Active">সক্রিয়</option>
                    <option value="inactive" data-text-bn="নিষ্ক্রিয়" data-text-en="Inactive">নিষ্ক্রিয়</option>
                </select>
            </div>
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="rotate-ccw"
                id="btn-reset-filters"
                title="রিসেট / Reset"
            >
                <span class="bn">রিসেট</span>
                <span class="en" style="display:none;">Reset</span>
            </x-core::button>
        </div>
        <x-core::button :href="route('products.create')" size="sm" color="primary" icon="plus">
            <span class="bn">নতুন পণ্য</span><span class="en">New Product</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'products-data-table']) !!}
        </div>
    </div>

    {{-- Stock History Modal Container --}}
    <div id="stockHistoryModalContainer"></div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            function reloadProductTable() {
                var tableId = 'products-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Filters
            $(document).on('change', '#filter-category, #filter-brand, #filter-status', function () {
                reloadProductTable();
            });

            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-category').val('');
                $('#filter-brand').val('');
                $('#filter-status').val('');
                reloadProductTable();
            });

            // Stock History Modal
            $(document).on('click', '.btn-stock-history', function (e) {
                e.preventDefault();
                var url = $(this).data('url') || $(this).attr('href');
                if (!url) return;

                $.get(url, function (html) {
                    $('#stockHistoryModalContainer').html(html);
                    openModal('stockHistoryModal');
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                }).fail(function () {
                    window.location.href = url;
                });
            });
        });
        </script>
    @endpush
</x-core::layout>
