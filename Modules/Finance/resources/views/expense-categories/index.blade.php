<x-core::layout
    title="ব্যয় ক্যাটাগরি"
    title-en="Expense Category"
    subtitle="ব্যয়ের ক্যাটাগরি পরিচালনা করুন"
    subtitle-en="Manage expense categories"
    active="expense"
>
    <x-finance::tabbar active="expense-categories" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <x-core::button color="primary" type="button" icon="plus" id="btn-open-create-expense-category-modal">
                    <span class="bn">নতুন ক্যাটাগরি</span><span class="en">New Category</span>
                </x-core::button>
            </div>

            <div class="mini-grid">
                @forelse ($expenseCategories as $expenseCategory)
                    <div class="mini-card pm-card">
                        <div class="mini-card-actions">
                            <button
                                type="button"
                                class="act btn-edit-expense-category"
                                title="Edit"
                                data-id="{{ $expenseCategory->id }}"
                                data-name="{{ $expenseCategory->name }}"
                                data-description="{{ $expenseCategory->description }}"
                                data-action="{{ route('expense-categories.update', $expenseCategory) }}"
                            >
                                <x-core::icon name="edit" size="14" />
                            </button>
                            <form method="POST" action="{{ route('expense-categories.destroy', $expenseCategory) }}" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act" title="Delete">
                                    <x-core::icon name="trash-2" size="14" class="text-danger" />
                                </button>
                            </form>
                        </div>
                        <div class="nm">{{ $expenseCategory->name }}</div>
                        <div class="sub">{{ $expenseCategory->sub_categories_count }} সাব-ক্যাটাগরি &middot; {{ $expenseCategory->expenses_count }} ব্যয়</div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1;">
                        <x-core::table.empty
                            icon="tag"
                            title="কোনো ক্যাটাগরি নেই"
                            title-en="No expense categories found"
                        />
                    </div>
                @endforelse
            </div>

            <div style="margin-top:14px;">
                {{ $expenseCategories->links() }}
            </div>
        </div>
    </div>

    {{-- Create Expense Category Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createExpenseCategoryModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="tag" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন ব্যয় ক্যাটাগরি যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Expense Category</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('expense-categories.store') }}" id="create_expense_category_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="create_exp_cat_name" value="{{ old('name') }}" placeholder="যেমন: দোকান পরিচালনা / অফিস ভাড়া" required autofocus>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">বিবরণ</label>
                        <label class="en" style="display:none;">Description</label>
                        <textarea name="description" id="create_exp_cat_description" rows="3" placeholder="ঐচ্ছিক বিবরণ">{{ old('description') }}</textarea>
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

    {{-- Edit Expense Category Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editExpenseCategoryModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">ব্যয় ক্যাটাগরি সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Expense Category</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_expense_category_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_exp_cat_name" value="{{ old('name') }}" placeholder="যেমন: দোকান পরিচালনা / অফিস ভাড়া" required>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">বিবরণ</label>
                        <label class="en" style="display:none;">Description</label>
                        <textarea name="description" id="edit_exp_cat_description" rows="3" placeholder="ঐচ্ছিক বিবরণ">{{ old('description') }}</textarea>
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
        $('#btn-open-create-expense-category-modal').on('click', function () {
            $('#create_expense_category_form')[0].reset();
            $('#createExpenseCategoryModal').addClass('open');
            setTimeout(function () {
                $('#create_exp_cat_name').focus();
            }, 100);
        });

        // Open Edit Modal
        $(document).on('click', '.btn-edit-expense-category', function () {
            var $btn = $(this);
            var action = $btn.data('action');
            var name = $btn.data('name');
            var description = $btn.data('description') || '';

            $('#edit_expense_category_form').attr('action', action);
            $('#edit_exp_cat_name').val(name);
            $('#edit_exp_cat_description').val(description);

            $('#editExpenseCategoryModal').addClass('open');
            setTimeout(function () {
                $('#edit_exp_cat_name').focus();
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
