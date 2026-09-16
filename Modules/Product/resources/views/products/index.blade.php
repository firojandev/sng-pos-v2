<x-core::layout
    title="পণ্য তালিকা"
    title-en="Product List"
    subtitle="পণ্যের তালিকা পরিচালনা করুন"
    subtitle-en="Manage your product catalogue"
    active="products"
>
    <x-product::tabbar active="products" />

@php
    $categoryFilterOptions = ['' => ['bn' => 'সকল ক্যাটাগরি', 'en' => 'All Categories']];
    foreach ($categories as $cat) {
        $categoryFilterOptions[$cat->id] = $cat->name;
    }

    $brandFilterOptions = ['' => ['bn' => 'সকল ব্র্যান্ড', 'en' => 'All Brands']];
    foreach ($brands as $b) {
        $brandFilterOptions[$b->id] = $b->name;
    }

    $statusFilterOptions = [
        '' => ['bn' => 'সকল অবস্থা', 'en' => 'All Status'],
        'active' => ['bn' => 'সক্রিয়', 'en' => 'Active'],
        'inactive' => ['bn' => 'নিষ্ক্রিয়', 'en' => 'Inactive'],
    ];
@endphp

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div style="width:180px; flex-shrink:0;">
                <x-core::select
                    name="filter_category"
                    id="filter-category"
                    size="sm"
                    :no-margin="true"
                    :options="$categoryFilterOptions"
                />
            </div>
            <div style="width:180px; flex-shrink:0;">
                <x-core::select
                    name="filter_brand"
                    id="filter-brand"
                    size="sm"
                    :no-margin="true"
                    :options="$brandFilterOptions"
                />
            </div>
            <div style="width:150px; flex-shrink:0;">
                <x-core::select
                    name="filter_status"
                    id="filter-status"
                    size="sm"
                    :no-margin="true"
                    :options="$statusFilterOptions"
                />
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
        @can('products.create')
            <x-core::button :href="route('products.create')" size="sm" color="primary" icon="plus">
                <span class="bn">নতুন পণ্য</span><span class="en" style="display:none;">New Product</span>
            </x-core::button>
        @endcan
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'products-data-table']) !!}
        </div>
    </div>

    {{-- Stock History Modal Container --}}
    <div id="stockHistoryModalContainer"></div>

    @push('styles')
        <style>
            #products-data-table {
                width: 100% !important;
            }
            #products-data-table th:first-child,
            #products-data-table td:first-child {
                max-width: 280px;
                width: 260px;
            }
        </style>
    @endpush

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
