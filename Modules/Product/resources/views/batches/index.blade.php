<x-core::layout
    title="ব্যাচ"
    title-en="Batch"
    subtitle="পণ্যের ব্যাচ পরিচালনা করুন"
    subtitle-en="Manage product batches"
    active="products"
>
    <x-product::tabbar active="batches" />

    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <div style="min-width:240px;">
                <select name="filter_product" id="filter-product" style="height:36px; padding:0 12px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--ink-800); font-size:13px; outline:none;">
                    <option value="">সকল পণ্য (All Products)</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
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
        <x-core::button color="primary" size="sm" type="button" icon="plus" id="btn-open-create-batch-modal">
            <span class="bn">নতুন ব্যাচ</span><span class="en">New Batch</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'batches-data-table']) !!}
        </div>
    </div>

    {{-- Create Batch Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') !== 'PUT') open @endif" id="createBatchModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="boxes" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">নতুন ব্যাচ যোগ করুন</span>
                        <span class="en" style="display:none;">Add New Batch</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="{{ route('batches.store') }}" id="create_batch_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">পণ্য <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="create_batch_product_id" required>
                            <option value="">-- নির্বাচন করুন --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ (int) old('product_id') === $product->id ? 'selected' : '' }}>{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                        @error('product_id') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">ব্যাচ নং <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Batch No <span class="text-danger">*</span></label>
                        <input type="text" name="batch_no" id="create_batch_no" value="{{ old('batch_no') }}" placeholder="যেমন: BT-2026-001" required>
                        @error('batch_no') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field-row">
                        <div class="field" style="margin-top:0;">
                            <label class="bn">উৎপাদন তারিখ</label>
                            <label class="en" style="display:none;">Mfg Date</label>
                            <input type="date" name="mfg_date" id="create_batch_mfg_date" value="{{ old('mfg_date') }}">
                            @error('mfg_date') <div class="field-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="field" style="margin-top:0;">
                            <label class="bn">মেয়াদ শেষের তারিখ</label>
                            <label class="en" style="display:none;">Expiry Date</label>
                            <input type="date" name="expiry_date" id="create_batch_expiry_date" value="{{ old('expiry_date') }}">
                            @error('expiry_date') <div class="field-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">পরিমাণ <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="quantity" id="create_batch_quantity" value="{{ old('quantity') }}" placeholder="0" required>
                        @error('quantity') <div class="field-error">{{ $message }}</div> @enderror
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

    {{-- Edit Batch Modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('_method') === 'PUT') open @endif" id="editBatchModal" style="z-index:999;">
        <div class="modal-box" style="width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                        <span class="bn">ব্যাচ সম্পাদনা</span>
                        <span class="en" style="display:none;">Edit Batch</span>
                    </div>
                </div>
                <button type="button" class="drawer-x modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>
            <form method="POST" action="" id="edit_batch_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div class="field" style="margin-top:0;">
                        <label class="bn">পণ্য <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="edit_batch_product_id" required>
                            <option value="">-- নির্বাচন করুন --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                        @error('product_id') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">ব্যাচ নং <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Batch No <span class="text-danger">*</span></label>
                        <input type="text" name="batch_no" id="edit_batch_no" value="{{ old('batch_no') }}" placeholder="যেমন: BT-2026-001" required>
                        @error('batch_no') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field-row">
                        <div class="field" style="margin-top:0;">
                            <label class="bn">উৎপাদন তারিখ</label>
                            <label class="en" style="display:none;">Mfg Date</label>
                            <input type="date" name="mfg_date" id="edit_batch_mfg_date" value="{{ old('mfg_date') }}">
                            @error('mfg_date') <div class="field-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="field" style="margin-top:0;">
                            <label class="bn">মেয়াদ শেষের তারিখ</label>
                            <label class="en" style="display:none;">Expiry Date</label>
                            <input type="date" name="expiry_date" id="edit_batch_expiry_date" value="{{ old('expiry_date') }}">
                            @error('expiry_date') <div class="field-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="field" style="margin-top:0;">
                        <label class="bn">পরিমাণ <span class="text-danger">*</span></label>
                        <label class="en" style="display:none;">Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="quantity" id="edit_batch_quantity" value="{{ old('quantity') }}" placeholder="0" required>
                        @error('quantity') <div class="field-error">{{ $message }}</div> @enderror
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
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            function reloadBatchTable() {
                var tableId = 'batches-data-table';
                if (window.LaravelDataTables && window.LaravelDataTables[tableId]) {
                    window.LaravelDataTables[tableId].ajax.reload(null, false);
                } else if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
                    $('#' + tableId).DataTable().ajax.reload(null, false);
                }
            }

            // Filter
            $(document).on('change', '#filter-product', function () {
                reloadBatchTable();
            });

            $(document).on('click', '#btn-reset-filters', function (e) {
                e.preventDefault();
                $('#filter-product').val('');
                reloadBatchTable();
            });

            // Open Create Modal
            $('#btn-open-create-batch-modal').on('click', function () {
                $('#create_batch_form')[0].reset();
                $('#createBatchModal').addClass('open');
                setTimeout(function () {
                    $('#create_batch_product_id').focus();
                }, 100);
            });

            // Open Edit Modal
            $(document).on('click', '.btn-edit-batch', function () {
                var $btn = $(this);
                var action = $btn.data('action');
                var productId = $btn.data('product-id');
                var batchNo = $btn.data('batch-no');
                var mfgDate = $btn.data('mfg-date') || '';
                var expiryDate = $btn.data('expiry-date') || '';
                var quantity = $btn.data('quantity');

                $('#edit_batch_form').attr('action', action);
                $('#edit_batch_product_id').val(productId);
                $('#edit_batch_no').val(batchNo);
                $('#edit_batch_mfg_date').val(mfgDate);
                $('#edit_batch_expiry_date').val(expiryDate);
                $('#edit_batch_quantity').val(quantity);

                $('#editBatchModal').addClass('open');
                setTimeout(function () {
                    $('#edit_batch_no').focus();
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
