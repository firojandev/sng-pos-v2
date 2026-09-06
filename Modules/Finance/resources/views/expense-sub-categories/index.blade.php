<x-core::layout
    title="ব্যয় সাব-ক্যাটাগরি"
    title-en="Expense Sub-category"
    subtitle="ব্যয়ের সাব-ক্যাটাগরি পরিচালনা করুন"
    subtitle-en="Manage expense sub-categories"
    active="expense"
>
    <x-finance::tabbar active="expense-sub-categories" />

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div style="min-width:220px;">
                <select name="filter_parent_category" id="filter-parent-category" style="height:36px; padding:0 12px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--ink-800); font-size:13px; outline:none;">
                    <option value="" data-text-bn="সকল মূল ক্যাটাগরি" data-text-en="All Categories">সকল মূল ক্যাটাগরি</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
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
        <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-expense-subcategory-modal">
            <span class="bn">নতুন সাব-ক্যাটাগরি</span><span class="en" style="display:none;">New Sub-category</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'expense-sub-categories-data-table']) !!}
        </div>
    </div>

    {{-- Create Expense SubCategory Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createExpenseSubCategoryModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="folder-tree" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন ব্যয় সাব-ক্যাটাগরি যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Expense Sub-category</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('expense-sub-categories.store') }}" id="create_expense_subcategory_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">মূল ক্যাটাগরি <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Parent Category <span class="text-danger">*</span></label>
                        <select name="parent_id" id="create_exp_subcat_parent_id" required>
                            <option value="" data-text-bn="-- নির্বাচন করুন --" data-text-en="-- Select --">-- নির্বাচন করুন --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (int) old('parent_id') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">সাব-ক্যাটাগরির নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Sub-category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="create_exp_subcat_name" value="{{ old('name') }}" placeholder="যেমন: দোকান পরিচালনা / বিদ্যুৎ বিল" required>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">বিবরণ</label>
                        <label class="en" style="display:none;">Description</label>
                        <textarea name="description" id="create_exp_subcat_description" rows="3" placeholder="ঐচ্ছিক বিবরণ">{{ old('description') }}</textarea>
                        @error('description') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-save-create-subcat">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Expense SubCategory Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editExpenseSubCategoryModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">ব্যয় সাব-ক্যাটাগরি সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Expense Sub-category</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_expense_subcategory_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">মূল ক্যাটাগরি <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Parent Category <span class="text-danger">*</span></label>
                        <select name="parent_id" id="edit_exp_subcat_parent_id" required>
                            <option value="" data-text-bn="-- নির্বাচন করুন --" data-text-en="-- Select --">-- নির্বাচন করুন --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">সাব-ক্যাটাগরির নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Sub-category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_exp_subcat_name" value="{{ old('name') }}" placeholder="যেমন: দোকান পরিচালনা / বিদ্যুৎ বিল" required>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">বিবরণ</label>
                        <label class="en" style="display:none;">Description</label>
                        <textarea name="description" id="edit_exp_subcat_description" rows="3" placeholder="ঐচ্ছিক বিবরণ">{{ old('description') }}</textarea>
                        @error('description') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                        <span class="bn">বাতিল</span>
                        <span class="en" style="display:none;">Cancel</span>
                    </x-core::button>
                    <x-core::button type="submit" color="primary" size="sm" icon="check" id="btn-update-subcat">
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
            function showFormErrors($form, errors) {
                clearFormErrors($form);
                $.each(errors, function (field, messages) {
                    var $field = $form.find('[name="' + field + '"]');
                    if ($field.length) {
                        $field.addClass('is-invalid');
                        var msg = messages[0];
                        var $errorEl = $('<div class="field-error dynamic-error" style="color:var(--red-600); font-size:12px; margin-top:4px; font-weight:500;">' + msg + '</div>');
                        var $group = $field.closest('.form-group, .field, div');
                        $group.append($errorEl);
                    }
                });
            }

            function clearFormErrors($form) {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.dynamic-error').remove();
            }

            function reloadExpenseSubCategoryTable() {
                var tableId = 'expense-sub-categories-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Filter
            $(document).on('change', '#filter-parent-category', function () {
                reloadExpenseSubCategoryTable();
            });

            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-parent-category').val('');
                reloadExpenseSubCategoryTable();
            });

            // Open Create Modal
            $('#btn-open-create-expense-subcategory-modal').on('click', function () {
                var $form = $('#create_expense_subcategory_form');
                $form[0].reset();
                clearFormErrors($form);
                $('#createExpenseSubCategoryModal').addClass('open');
                setTimeout(function () {
                    $('#create_exp_subcat_parent_id').focus();
                }, 100);
            });

            // Open Edit Modal
            $(document).on('click', '.btn-edit-expense-subcategory', function () {
                var $btn = $(this);
                var action = $btn.data('action');
                var parentId = $btn.data('parent-id');
                var name = $btn.data('name');
                var description = $btn.data('description') || '';
                var $form = $('#edit_expense_subcategory_form');

                clearFormErrors($form);
                $form.attr('action', action);
                $('#edit_exp_subcat_parent_id').val(parentId);
                $('#edit_exp_subcat_name').val(name);
                $('#edit_exp_subcat_description').val(description);

                $('#editExpenseSubCategoryModal').addClass('open');
                setTimeout(function () {
                    $('#edit_exp_subcat_name').focus();
                }, 100);
            });

            // Submit Create SubCategory via AJAX
            $('#create_expense_subcategory_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-create-subcat');
                var url = $form.attr('action');

                clearFormErrors($form);
                $btn.prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        $btn.prop('disabled', false);
                        $('#createExpenseSubCategoryModal').removeClass('open');
                        $form[0].reset();
                        reloadExpenseSubCategoryTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'ব্যয় সাব-ক্যাটাগরি সফলভাবে যোগ করা হয়েছে', 'Expense sub-category created successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            if (typeof window.toast === 'function') {
                                window.toast('ব্যয় সাব-ক্যাটাগরি যোগ করতে সমস্যা হয়েছে', 'Failed to create expense sub-category');
                            }
                        }
                    }
                });
            });

            // Submit Edit SubCategory via AJAX
            $('#edit_expense_subcategory_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-update-subcat');
                var url = $form.attr('action');

                clearFormErrors($form);
                $btn.prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        $btn.prop('disabled', false);
                        $('#editExpenseSubCategoryModal').removeClass('open');
                        reloadExpenseSubCategoryTable();
                        if (typeof window.toast === 'function') {
                            window.toast(response.message || 'ব্যয় সাব-ক্যাটাগরি হালনাগাদ করা হয়েছে', 'Expense sub-category updated successfully');
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            showFormErrors($form, xhr.responseJSON.errors);
                        } else {
                            if (typeof window.toast === 'function') {
                                window.toast('ব্যয় সাব-ক্যাটাগরি হালনাগাদ করতে সমস্যা হয়েছে', 'Failed to update expense sub-category');
                            }
                        }
                    }
                });
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
