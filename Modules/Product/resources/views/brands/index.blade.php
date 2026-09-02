<x-core::layout
    title="ব্র্যান্ড"
    title-en="Brand"
    subtitle="পণ্যের ব্র্যান্ড পরিচালনা করুন"
    subtitle-en="Manage product brands"
    active="products"
>
    <x-product::tabbar active="brands" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-brand-modal">
                    <span class="bn">নতুন ব্র্যান্ড</span><span class="en">New Brand</span>
                </x-core::button>
            </div>

            <div class="mini-grid">
                @forelse ($brands as $brand)
                    <div class="mini-card pm-card">
                        <div class="mini-card-actions">
                            <button
                                type="button"
                                class="act btn-edit-brand"
                                title="Edit"
                                data-id="{{ $brand->id }}"
                                data-name="{{ $brand->name }}"
                                data-description="{{ $brand->description }}"
                                data-action="{{ route('brands.update', $brand) }}"
                            >
                                <x-core::icon name="edit" size="14" />
                            </button>
                            <form method="POST" action="{{ route('brands.destroy', $brand) }}" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act" title="Delete">
                                    <x-core::icon name="trash-2" size="14" class="text-danger" />
                                </button>
                            </form>
                        </div>
                        <div class="row-avatar">
                            <div class="av" style="background:var(--teal-800);">{{ mb_substr($brand->name, 0, 1) }}</div>
                            <div>
                                <div class="nm">{{ $brand->name }}</div>
                                <div class="sub">{{ $brand->models_count }} মডেল &middot; {{ $brand->products_count }} পণ্য</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1;">
                        <x-core::table.empty
                            icon="tag"
                            title="কোনো ব্র্যান্ড নেই"
                            title-en="No brands found"
                        />
                    </div>
                @endforelse
            </div>

            <div style="margin-top:14px;">
                {{ $brands->links() }}
            </div>
        </div>
    </div>

    {{-- Create Brand Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createBrandModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="tag" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন ব্র্যান্ড যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Brand</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('brands.store') }}" id="create_brand_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">ব্র্যান্ডের নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="create_brand_name" value="{{ old('name') }}" placeholder="যেমন: স্যামসাং" required autofocus>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">বিবরণ</label>
                        <label class="en" style="display:none;">Description</label>
                        <textarea name="description" id="create_brand_description" rows="3" placeholder="ঐচ্ছিক বিবরণ">{{ old('description') }}</textarea>
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

    {{-- Edit Brand Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editBrandModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">ব্র্যান্ড সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Brand</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_brand_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">ব্র্যান্ডের নাম <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_brand_name" value="{{ old('name') }}" placeholder="যেমন: স্যামসাং" required>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">বিবরণ</label>
                        <label class="en" style="display:none;">Description</label>
                        <textarea name="description" id="edit_brand_description" rows="3" placeholder="ঐচ্ছিক বিবরণ">{{ old('description') }}</textarea>
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
        $('#btn-open-create-brand-modal').on('click', function () {
            $('#create_brand_form')[0].reset();
            $('#createBrandModal').addClass('open');
            setTimeout(function () {
                $('#create_brand_name').focus();
            }, 100);
        });

        // Open Edit Modal
        $(document).on('click', '.btn-edit-brand', function () {
            var $btn = $(this);
            var action = $btn.data('action');
            var name = $btn.data('name');
            var description = $btn.data('description') || '';

            $('#edit_brand_form').attr('action', action);
            $('#edit_brand_name').val(name);
            $('#edit_brand_description').val(description);

            $('#editBrandModal').addClass('open');
            setTimeout(function () {
                $('#edit_brand_name').focus();
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
