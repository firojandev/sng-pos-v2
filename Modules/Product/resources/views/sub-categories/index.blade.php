<x-core::layout
    title="সাব-ক্যাটাগরি"
    title-en="Sub-category"
    subtitle="পণ্যের সাব-ক্যাটাগরি পরিচালনা করুন"
    subtitle-en="Manage product sub-categories"
    active="products"
>
    <x-product::tabbar active="sub-categories" />

@php
    $parentCategoryFilterOptions = ['' => ['bn' => 'সকল মূল ক্যাটাগরি', 'en' => 'All Categories']];
    foreach ($categories as $cat) {
        $parentCategoryFilterOptions[$cat->id] = $cat->name;
    }
@endphp

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div style="width:200px; flex-shrink:0;">
                <x-core::select
                    name="filter_parent_category"
                    id="filter-parent-category"
                    size="sm"
                    :no-margin="true"
                    :options="$parentCategoryFilterOptions"
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
            <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-subcategory-modal">
                <span class="bn">নতুন সাব-ক্যাটাগরি</span><span class="en" style="display:none;">New Sub-category</span>
            </x-core::button>
        @endcan
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'sub-categories-data-table']) !!}
        </div>
    </div>

    @php
        $categorySelectOptions = ['' => '-- নির্বাচন করুন --'];
        foreach ($categories as $cat) {
            $categorySelectOptions[$cat->id] = $cat->name;
        }
    @endphp

    {{-- Create SubCategory Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createSubCategoryModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="folder-tree" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন সাব-ক্যাটাগরি যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Sub-category</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('sub-categories.store') }}" id="create_subcategory_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::select
                        name="parent_id"
                        id="create_subcat_parent_id"
                        label="মূল ক্যাটাগরি"
                        label-en="Parent Category"
                        :options="$categorySelectOptions"
                        :value="old('parent_id')"
                        size="sm"
                        :required="true"
                    />

                    <x-core::input
                        name="name"
                        id="create_subcat_name"
                        label="সাব-ক্যাটাগরির নাম"
                        label-en="Sub-category Name"
                        placeholder="যেমন: সয়াবিন তেল"
                        placeholder-en="e.g. Soybean oil"
                        :value="old('name')"
                        size="sm"
                        :required="true"
                    />

                    <x-core::textarea
                        name="description"
                        id="create_subcat_description"
                        label="বিবরণ"
                        label-en="Description"
                        placeholder="ঐচ্ছিক বিবরণ"
                        placeholder-en="Optional description"
                        :value="old('description')"
                        rows="3"
                        size="sm"
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

    {{-- Edit SubCategory Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editSubCategoryModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">সাব-ক্যাটাগরি সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Sub-category</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_subcategory_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::select
                        name="parent_id"
                        id="edit_subcat_parent_id"
                        label="মূল ক্যাটাগরি"
                        label-en="Parent Category"
                        :options="$categorySelectOptions"
                        :value="old('parent_id')"
                        size="sm"
                        :required="true"
                    />

                    <x-core::input
                        name="name"
                        id="edit_subcat_name"
                        label="সাব-ক্যাটাগরির নাম"
                        label-en="Sub-category Name"
                        placeholder="যেমন: সয়াবিন তেল"
                        placeholder-en="e.g. Soybean oil"
                        :value="old('name')"
                        size="sm"
                        :required="true"
                    />

                    <x-core::textarea
                        name="description"
                        id="edit_subcat_description"
                        label="বিবরণ"
                        label-en="Description"
                        placeholder="ঐচ্ছিক বিবরণ"
                        placeholder-en="Optional description"
                        :value="old('description')"
                        rows="3"
                        size="sm"
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
            function reloadSubCategoryTable() {
                var tableId = 'sub-categories-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Filter
            $(document).on('change', '#filter-parent-category', function () {
                reloadSubCategoryTable();
            });

            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-parent-category').val('');
                reloadSubCategoryTable();
            });

            // Open Create Modal
            $('#btn-open-create-subcategory-modal').on('click', function () {
                $('#create_subcategory_form')[0].reset();
                $('#createSubCategoryModal').addClass('open');
                setTimeout(function () {
                    $('#create_subcat_parent_id').focus();
                }, 100);
            });

            // Open Edit Modal
            $(document).on('click', '.btn-edit-subcategory', function () {
                var $btn = $(this);
                var action = $btn.data('action');
                var parentId = $btn.data('parent-id');
                var name = $btn.data('name');
                var description = $btn.data('description') || '';

                $('#edit_subcategory_form').attr('action', action);
                $('#edit_subcat_parent_id').val(parentId);
                $('#edit_subcat_name').val(name);
                $('#edit_subcat_description').val(description);

                $('#editSubCategoryModal').addClass('open');
                setTimeout(function () {
                    $('#edit_subcat_name').focus();
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
