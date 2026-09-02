<x-core::layout
    title="ব্যয় সাব-ক্যাটাগরি"
    title-en="Expense Sub-category"
    subtitle="ব্যয়ের সাব-ক্যাটাগরি পরিচালনা করুন"
    subtitle-en="Manage expense sub-categories"
    active="expense"
>
    <x-finance::tabbar active="expense-sub-categories" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <x-core::button color="primary" type="button" icon="plus" id="btn-open-create-expense-subcategory-modal">
                    <span class="bn">নতুন সাব-ক্যাটাগরি</span><span class="en">New Sub-category</span>
                </x-core::button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">নাম</th><th class="en" style="display:none;">Name</th>
                            <th class="bn">মূল ক্যাটাগরি</th><th class="en" style="display:none;">Parent Category</th>
                            <th class="bn">ব্যয় সংখ্যা</th><th class="en" style="display:none;">Expenses</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subCategories as $subCategory)
                            <tr>
                                <td class="cell-main">{{ $subCategory->name }}</td>
                                <td>{{ $subCategory->category->name ?? '—' }}</td>
                                <td>{{ $subCategory->expenses_count }}</td>
                                <td>
                                    <div class="row-actions">
                                        <button
                                            type="button"
                                            class="act btn-edit-expense-subcategory"
                                            title="Edit"
                                            data-id="{{ $subCategory->id }}"
                                            data-parent-id="{{ $subCategory->parent_id }}"
                                            data-name="{{ $subCategory->name }}"
                                            data-description="{{ $subCategory->description }}"
                                            data-action="{{ route('expense-sub-categories.update', $subCategory) }}"
                                        >
                                            <x-core::icon name="edit" size="14" />
                                        </button>
                                        <form method="POST" action="{{ route('expense-sub-categories.destroy', $subCategory) }}" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act" title="Delete">
                                                <x-core::icon name="trash-2" size="14" class="text-danger" />
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
                            <option value="">-- নির্বাচন করুন --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (int) old('parent_id') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">সাব-ক্যাটাগরির নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Sub-category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="create_exp_subcat_name" value="{{ old('name') }}" placeholder="যেমন: দোকান ভাড়া / বিদ্যুৎ বিল" required>
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
                    <x-core::button type="submit" color="primary" size="sm" icon="check">
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
                            <option value="">-- নির্বাচন করুন --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">সাব-ক্যাটাগরির নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Sub-category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_exp_subcat_name" value="{{ old('name') }}" placeholder="যেমন: দোকান ভাড়া / বিদ্যুৎ বিল" required>
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
                    <x-core::button type="submit" color="primary" size="sm" icon="check">
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    $(function () {
        // Open Create Modal
        $('#btn-open-create-expense-subcategory-modal').on('click', function () {
            $('#create_expense_subcategory_form')[0].reset();
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

            $('#edit_expense_subcategory_form').attr('action', action);
            $('#edit_exp_subcat_parent_id').val(parentId);
            $('#edit_exp_subcat_name').val(name);
            $('#edit_exp_subcat_description').val(description);

            $('#editExpenseSubCategoryModal').addClass('open');
            setTimeout(function () {
                $('#edit_exp_subcat_name').focus();
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
