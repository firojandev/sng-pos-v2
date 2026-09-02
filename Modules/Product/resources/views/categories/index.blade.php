<x-core::layout
    title="ক্যাটাগরি"
    title-en="Category"
    subtitle="পণ্যের ক্যাটাগরি পরিচালনা করুন"
    subtitle-en="Manage product categories"
    active="products"
>
    <x-product::tabbar active="categories" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-category-modal">
                    <span class="bn">নতুন ক্যাটাগরি</span><span class="en">New Category</span>
                </x-core::button>
            </div>

            <div class="mini-grid">
                @forelse ($categories as $category)
                    <div class="mini-card pm-card">
                        <div class="mini-card-actions">
                            <button
                                type="button"
                                class="act btn-edit-category"
                                title="Edit"
                                data-id="{{ $category->id }}"
                                data-name="{{ $category->name }}"
                                data-description="{{ $category->description }}"
                                data-action="{{ route('categories.update', $category) }}"
                            >
                                <x-core::icon name="edit" size="14" />
                            </button>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act" title="Delete">
                                    <x-core::icon name="trash-2" size="14" class="text-danger" />
                                </button>
                            </form>
                        </div>
                        <div class="nm">{{ $category->name }}</div>
                        <div class="sub">{{ $category->sub_categories_count }} সাব-ক্যাটাগরি &middot; {{ $category->products_count }} পণ্য</div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1;">
                        <x-core::table.empty
                            icon="folder"
                            title="কোনো ক্যাটাগরি নেই"
                            title-en="No categories found"
                        />
                    </div>
                @endforelse
            </div>

            <div style="margin-top:14px;">
                {{ $categories->links() }}
            </div>
        </div>
    </div>

    {{-- Create Category Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createCategoryModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="folder-plus" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন ক্যাটাগরি যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Category</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('categories.store') }}" id="create_category_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="create_cat_name" value="{{ old('name') }}" placeholder="যেমন: ইলেকট্রনিক্স" required autofocus>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">বিবরণ</label>
                        <label class="en" style="display:none;">Description</label>
                        <textarea name="description" id="create_cat_description" rows="3" placeholder="ঐচ্ছিক বিবরণ">{{ old('description') }}</textarea>
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

    {{-- Edit Category Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editCategoryModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">ক্যাটাগরি সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Category</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_category_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_cat_name" value="{{ old('name') }}" placeholder="যেমন: ইলেকট্রনিক্স" required>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">বিবরণ</label>
                        <label class="en" style="display:none;">Description</label>
                        <textarea name="description" id="edit_cat_description" rows="3" placeholder="ঐচ্ছিক বিবরণ">{{ old('description') }}</textarea>
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
        $('#btn-open-create-category-modal').on('click', function () {
            $('#create_category_form')[0].reset();
            $('#createCategoryModal').addClass('open');
            setTimeout(function () {
                $('#create_cat_name').focus();
            }, 100);
        });

        // Open Edit Modal
        $(document).on('click', '.btn-edit-category', function () {
            var $btn = $(this);
            var action = $btn.data('action');
            var name = $btn.data('name');
            var description = $btn.data('description') || '';

            $('#edit_category_form').attr('action', action);
            $('#edit_cat_name').val(name);
            $('#edit_cat_description').val(description);

            $('#editCategoryModal').addClass('open');
            setTimeout(function () {
                $('#edit_cat_name').focus();
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
