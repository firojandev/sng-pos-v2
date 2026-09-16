<x-core::layout
    title="ইউনিট"
    title-en="Unit"
    subtitle="পরিমাপের ইউনিট পরিচালনা করুন"
    subtitle-en="Manage measurement units"
    active="products"
>
    <x-product::tabbar active="units" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                @can('products.create')
                    <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-unit-modal">
                        <span class="bn">নতুন ইউনিট</span><span class="en">New Unit</span>
                    </x-core::button>
                @endcan
            </div>

            <div class="mini-grid">
                @forelse ($units as $unit)
                    <div class="mini-card pm-card">
                        <div class="mini-card-actions">
                            @can('products.edit')
                                <x-core::button
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    color="secondary"
                                    icon="edit"
                                    class="btn-edit-unit"
                                    title="Edit"
                                    data-id="{{ $unit->id }}"
                                    data-name="{{ $unit->name }}"
                                    data-short-code="{{ $unit->short_code }}"
                                    data-action="{{ route('units.update', $unit) }}"
                                />
                            @endcan
                            @can('products.delete')
                                <form method="POST" action="{{ route('units.destroy', $unit) }}" class="delete-form" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <x-core::button
                                        type="submit"
                                        size="sm"
                                        variant="ghost"
                                        color="danger"
                                        icon="trash-2"
                                        title="Delete"
                                    />
                                </form>
                            @endcan
                        </div>
                        <div class="nm">{{ $unit->name }}</div>
                        <div class="sub">{{ $unit->short_code }} &middot; {{ $unit->products_count }} পণ্য</div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1;">
                        <x-core::table.empty
                            icon="scale"
                            title="কোনো ইউনিট নেই"
                            title-en="No units found"
                        />
                    </div>
                @endforelse
            </div>

            <div style="margin-top:14px;">
                {{ $units->links() }}
            </div>
        </div>
    </div>

    {{-- Create Unit Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createUnitModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="scale" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন ইউনিট যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Unit</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('units.store') }}" id="create_unit_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="create_unit_name"
                        label="ইউনিটের নাম"
                        label-en="Unit Name"
                        placeholder="যেমন: কিলোগ্রাম / পিস"
                        placeholder-en="e.g. Kilogram / Pcs"
                        :value="old('name')"
                        size="sm"
                        :required="true"
                        autofocus
                    />

                    <x-core::input
                        name="short_code"
                        id="create_unit_short_code"
                        label="সংক্ষিপ্ত কোড"
                        label-en="Short Code"
                        placeholder="যেমন: Kg / Pcs"
                        placeholder-en="e.g. Kg / Pcs"
                        :value="old('short_code')"
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

    {{-- Edit Unit Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editUnitModal" style="z-index:999;">
        <div class="modal-box" style="width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">ইউনিট সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Unit</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_unit_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="edit_unit_name"
                        label="ইউনিটের নাম"
                        label-en="Unit Name"
                        placeholder="যেমন: কিলোগ্রাম / পিস"
                        placeholder-en="e.g. Kilogram / Pcs"
                        :value="old('name')"
                        size="sm"
                        :required="true"
                    />

                    <x-core::input
                        name="short_code"
                        id="edit_unit_short_code"
                        label="সংক্ষিপ্ত কোড"
                        label-en="Short Code"
                        placeholder="যেমন: Kg / Pcs"
                        placeholder-en="e.g. Kg / Pcs"
                        :value="old('short_code')"
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
    <script>
    $(function () {
        // Open Create Modal
        $('#btn-open-create-unit-modal').on('click', function () {
            $('#create_unit_form')[0].reset();
            $('#createUnitModal').addClass('open');
            setTimeout(function () {
                $('#create_unit_name').focus();
            }, 100);
        });

        // Open Edit Modal
        $(document).on('click', '.btn-edit-unit', function () {
            var $btn = $(this);
            var action = $btn.data('action');
            var name = $btn.data('name');
            var shortCode = $btn.data('short-code');

            $('#edit_unit_form').attr('action', action);
            $('#edit_unit_name').val(name);
            $('#edit_unit_short_code').val(shortCode);

            $('#editUnitModal').addClass('open');
            setTimeout(function () {
                $('#edit_unit_name').focus();
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
