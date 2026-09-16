<x-core::layout
    title="মডেল"
    title-en="Model"
    subtitle="পণ্যের মডেল পরিচালনা করুন"
    subtitle-en="Manage product models"
    active="products"
>
    <x-product::tabbar active="models" />

@php
    $brandSelectOptions = ['' => ['bn' => '-- নির্বাচন করুন --', 'en' => '-- Select --']];
    foreach ($brands as $brand) {
        $brandSelectOptions[$brand->id] = $brand->name;
    }
@endphp

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div style="width:200px; flex-shrink:0;">
                <x-core::select
                    name="filter_brand"
                    id="filter-brand"
                    size="sm"
                    :no-margin="true"
                    :options="['' => ['bn' => 'সকল ব্র্যান্ড', 'en' => 'All Brands']] + $brands->pluck('name', 'id')->toArray()"
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
            <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-model-modal">
                <span class="bn">নতুন মডেল</span><span class="en" style="display:none;">New Model</span>
            </x-core::button>
        @endcan
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'models-data-table']) !!}
        </div>
    </div>

    {{-- Create Model Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createModelModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="layers" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন মডেল যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Model</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('models.store') }}" id="create_model_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::select
                        name="brand_id"
                        id="create_model_brand_id"
                        label="ব্র্যান্ড"
                        label-en="Brand"
                        :options="$brandSelectOptions"
                        :value="old('brand_id')"
                        size="sm"
                        :required="true"
                    />

                    <x-core::input
                        name="name"
                        id="create_model_name"
                        label="মডেলের নাম"
                        label-en="Model Name"
                        placeholder="যেমন: গ্যালাক্সি এস২৪"
                        placeholder-en="e.g. Galaxy S24"
                        :value="old('name')"
                        size="sm"
                        :required="true"
                    />
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Model Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editModelModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">মডেল সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Model</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_model_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::select
                        name="brand_id"
                        id="edit_model_brand_id"
                        label="ব্র্যান্ড"
                        label-en="Brand"
                        :options="$brandSelectOptions"
                        size="sm"
                        :required="true"
                    />

                    <x-core::input
                        name="name"
                        id="edit_model_name"
                        label="মডেলের নাম"
                        label-en="Model Name"
                        placeholder="যেমন: গ্যালাক্সি এস২৪"
                        placeholder-en="e.g. Galaxy S24"
                        :value="old('name')"
                        size="sm"
                        :required="true"
                    />
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check">
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            function reloadModelTable() {
                var tableId = 'models-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Filter
            $(document).on('change', '#filter-brand', function () {
                reloadModelTable();
            });

            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-brand').val('');
                reloadModelTable();
            });

            // Open Create Modal
            $('#btn-open-create-model-modal').on('click', function () {
                $('#create_model_form')[0].reset();
                $('#createModelModal').addClass('open');
                setTimeout(function () {
                    $('#create_model_brand_id').focus();
                }, 100);
            });

            // Open Edit Modal
            $(document).on('click', '.btn-edit-model', function () {
                var $btn = $(this);
                var action = $btn.data('action');
                var brandId = $btn.data('brand-id');
                var name = $btn.data('name');

                $('#edit_model_form').attr('action', action);
                $('#edit_model_brand_id').val(brandId);
                $('#edit_model_name').val(name);

                $('#editModelModal').addClass('open');
                setTimeout(function () {
                    $('#edit_model_name').focus();
                }, 100);
            });

            // Close Modals
            $(document).on('click', '.modal-close-btn', function () {
                $(this).closest('.modal-backdrop').removeClass('open');
            });

            $('.modal-backdrop').on('click', function (e) {
                if ($(e.target).hasClass('modal-backdrop')) {
                    $(this).removeClass('open');
                }
            });
        });
        </script>
    @endpush
</x-core::layout>
