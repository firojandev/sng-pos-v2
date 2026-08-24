@php
    $initialUnits = old('units') ?? ($product->exists
        ? $product->units->map(fn ($u) => [
            'unit_id' => $u->id,
            'is_base' => (bool) $u->pivot->is_base,
            'conversion_factor' => (float) $u->pivot->conversion_factor,
            'purchase_price' => (float) $u->pivot->purchase_price,
            'sale_price' => (float) $u->pivot->sale_price,
        ])->values()->all()
        : [['unit_id' => '', 'is_base' => true, 'conversion_factor' => 1, 'purchase_price' => 0, 'sale_price' => 0]]);

    $subCategoriesByCategory = $categories->mapWithKeys(
        fn ($category) => [$category->id => $category->subCategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()]
    );
@endphp

<div class="field-row">
    <div class="field" style="margin-top:0;">
        <label class="bn">পণ্যের নাম</label><label class="en" style="display:none;">Product Name</label>
        <input type="text" name="name" value="{{ old('name', $product->name) }}" placeholder="যেমন স্যামসাং গ্যালাক্সি এস২৪" required>
        @error('name') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field" style="margin-top:0;">
        <label class="bn">SKU</label><label class="en" style="display:none;">SKU</label>
        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" placeholder="যেমন SKU-1001" required>
        @error('sku') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field">
    <label class="bn">পণ্যের ছবি</label><label class="en" style="display:none;">Product Image</label>
    @if ($product->image_url)
        <div style="margin-bottom:8px;">
            <img src="{{ $product->image_url }}" alt="" style="width:64px; height:64px; border-radius:10px; object-fit:cover; border:1px solid var(--border);">
        </div>
    @endif
    <input type="file" name="image" accept="image/*">
    @error('image') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">ক্যাটাগরি</label><label class="en" style="display:none;">Category</label>
        <select name="category_id" id="f-category" required onchange="filterSubCategories()">
            <option value="">-- নির্বাচন করুন --</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ (int) old('category_id', $product->category_id) === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label class="bn">সাব-ক্যাটাগরি</label><label class="en" style="display:none;">Sub-category</label>
        <select name="sub_category_id" id="f-subcategory"></select>
        @error('sub_category_id') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="field">
    <label class="bn">ব্র্যান্ড</label><label class="en" style="display:none;">Brand</label>
    <select name="brand_id">
        <option value="">-- নির্বাচন করুন --</option>
        @foreach ($brands as $brand)
            <option value="{{ $brand->id }}" {{ (int) old('brand_id', $product->brand_id) === $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
        @endforeach
    </select>
    @error('brand_id') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">সংক্ষিপ্ত বিবরণ</label><label class="en" style="display:none;">Short Description</label>
    <textarea name="short_description" placeholder="ঐচ্ছিক বিবরণ">{{ old('short_description', $product->short_description) }}</textarea>
</div>

<div class="field-row">
    <div class="field">
        <label class="bn">এলার্ট পরিমাণ</label><label class="en" style="display:none;">Alert Quantity</label>
        <input type="number" min="0" name="alert_qty" value="{{ old('alert_qty', $product->alert_qty ?? 0) }}" required>
        @error('alert_qty') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label class="bn">অবস্থা</label><label class="en" style="display:none;">Status</label>
        <div class="seg">
            <button type="button" class="seg-btn {{ old('status', $product->status ?? 'active') === 'active' ? 'active' : '' }}" data-target="status-input" data-val="active" onclick="selSegValue(this)">
                <span class="bn">সক্রিয়</span><span class="en">Active</span>
            </button>
            <button type="button" class="seg-btn {{ old('status', $product->status ?? 'active') === 'inactive' ? 'active' : '' }}" data-target="status-input" data-val="inactive" onclick="selSegValue(this)">
                <span class="bn">নিষ্ক্রিয়</span><span class="en">Inactive</span>
            </button>
        </div>
        <input type="hidden" name="status" id="status-input" value="{{ old('status', $product->status ?? 'active') }}">
    </div>
</div>

@php $isVat = (bool) old('is_vat', $product->is_vat ?? false); @endphp
<div class="field">
    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
        <input type="checkbox" name="is_vat" value="1" id="is-vat-check" style="width:auto;" {{ $isVat ? 'checked' : '' }} onchange="toggleField('is-vat-check','vat-percentage-field')">
        <span class="bn">এই পণ্যে ভ্যাট প্রযোজ্য</span><span class="en">VAT applicable on this product</span>
    </label>
</div>
<div class="field" id="vat-percentage-field" style="{{ $isVat ? '' : 'display:none;' }}">
    <label class="bn">ভ্যাটের হার (%)</label><label class="en" style="display:none;">VAT Percentage (%)</label>
    <input type="number" step="0.01" min="0" max="100" name="vat_percentage" value="{{ old('vat_percentage', $product->vat_percentage) }}">
    @error('vat_percentage') <div class="field-error">{{ $message }}</div> @enderror
</div>

@php $hasWarranty = (bool) old('has_warranty', $product->has_warranty ?? false); @endphp
<div class="field">
    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
        <input type="checkbox" name="has_warranty" value="1" id="has-warranty-check" style="width:auto;" {{ $hasWarranty ? 'checked' : '' }} onchange="toggleField('has-warranty-check','warranty-fields')">
        <span class="bn">এই পণ্যে ওয়ারেন্টি আছে</span><span class="en">This product has warranty</span>
    </label>
</div>
<div class="field-row" id="warranty-fields" style="{{ $hasWarranty ? '' : 'display:none;' }}">
    <div class="field">
        <label class="bn">ওয়ারেন্টির মেয়াদ</label><label class="en" style="display:none;">Warranty Duration</label>
        <input type="number" min="1" name="warranty_duration" value="{{ old('warranty_duration', $product->warranty_duration) }}">
        @error('warranty_duration') <div class="field-error">{{ $message }}</div> @enderror
    </div>
    <div class="field">
        <label class="bn">ওয়ারেন্টির ধরন</label><label class="en" style="display:none;">Warranty Type</label>
        <select name="warranty_type">
            <option value="day" {{ old('warranty_type', $product->warranty_type) === 'day' ? 'selected' : '' }}>দিন / Day</option>
            <option value="month" {{ old('warranty_type', $product->warranty_type) === 'month' ? 'selected' : '' }}>মাস / Month</option>
            <option value="year" {{ old('warranty_type', $product->warranty_type) === 'year' ? 'selected' : '' }}>বছর / Year</option>
        </select>
        @error('warranty_type') <div class="field-error">{{ $message }}</div> @enderror
    </div>
</div>

@php $hasExpiry = (bool) old('has_expiry', $product->has_expiry ?? false); @endphp
<div class="field">
    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
        <input type="checkbox" name="has_expiry" value="1" id="has-expiry-check" style="width:auto;" {{ $hasExpiry ? 'checked' : '' }} onchange="toggleField('has-expiry-check','expiry-field')">
        <span class="bn">এই পণ্যের মেয়াদ শেষ হয়</span><span class="en">This product has an expiry date</span>
    </label>
</div>
<div class="field" id="expiry-field" style="{{ $hasExpiry ? '' : 'display:none;' }}">
    <label class="bn">মেয়াদ শেষের তারিখ</label><label class="en" style="display:none;">Expiry Date</label>
    <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($product->expiry_date)->format('Y-m-d')) }}">
    @error('expiry_date') <div class="field-error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label class="bn">ইউনিট</label><label class="en" style="display:none;">Units</label>
    <div class="helper" style="margin-top:0; margin-bottom:8px;">
        <span class="bn">একটি পণ্যের একাধিক ইউনিট থাকতে পারে (যেমন পিস, কার্টুন)। ঠিক একটি ইউনিটকে বেস ইউনিট নির্বাচন করুন।</span>
        <span class="en" style="display:none;">A product can have multiple units (e.g. Piece, Carton). Select exactly one as the base unit.</span>
    </div>
    @error('units') <div class="field-error">{{ $message }}</div> @enderror

    <div class="table-wrap">
        <table id="units-table">
            <thead>
                <tr>
                    <th style="width:60px;"><span class="bn">বেস</span><span class="en">Base</span></th>
                    <th><span class="bn">ইউনিট</span><span class="en">Unit</span></th>
                    <th><span class="bn">রূপান্তর হার</span><span class="en">Conversion Factor</span></th>
                    <th><span class="bn">ক্রয় মূল্য</span><span class="en">Purchase Price</span></th>
                    <th><span class="bn">বিক্রয় মূল্য</span><span class="en">Sale Price</span></th>
                    <th style="width:30px;"></th>
                </tr>
            </thead>
            <tbody id="units-tbody"></tbody>
        </table>
    </div>
    <button type="button" class="pos-addrow" onclick="addUnitRow()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"/></svg>
        <span class="bn">ইউনিট যোগ করুন</span><span class="en">Add Unit</span>
    </button>
</div>

<script>
    function toggleField(checkboxId, fieldId) {
        var checkbox = document.getElementById(checkboxId);
        var field = document.getElementById(fieldId);
        field.style.display = checkbox.checked ? '' : 'none';
    }

    function selSegValue(btn) {
        btn.parentElement.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.getAttribute('data-target')).value = btn.getAttribute('data-val');
    }

    var ALL_UNITS = @json($units->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'code' => $u->short_code]));
    var SUBCATS_BY_CATEGORY = @json($subCategoriesByCategory);
    var SELECTED_SUBCATEGORY = @json(old('sub_category_id', $product->sub_category_id));
    var unitRowIndex = 0;

    function filterSubCategories() {
        var categoryId = document.getElementById('f-category').value;
        var select = document.getElementById('f-subcategory');
        var options = (SUBCATS_BY_CATEGORY[categoryId] || []);
        var html = '<option value="">-- নির্বাচন করুন --</option>';
        options.forEach(function (sub) {
            var selected = String(sub.id) === String(SELECTED_SUBCATEGORY) ? ' selected' : '';
            html += '<option value="' + sub.id + '"' + selected + '>' + sub.name + '</option>';
        });
        select.innerHTML = html;
    }

    function unitOptionsHtml(selectedUnitId) {
        return ALL_UNITS.map(function (u) {
            var selected = String(u.id) === String(selectedUnitId) ? ' selected' : '';
            return '<option value="' + u.id + '"' + selected + '>' + u.name + ' (' + u.code + ')</option>';
        }).join('');
    }

    function addUnitRow(row) {
        row = row || { unit_id: '', is_base: false, conversion_factor: 1, purchase_price: 0, sale_price: 0 };
        var idx = unitRowIndex++;
        var tbody = document.getElementById('units-tbody');
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td style="text-align:center;"><input type="radio" name="units_base_selector" style="width:auto;" ' + (row.is_base ? 'checked' : '') + ' onchange="setBaseRow(' + idx + ')"><input type="hidden" name="units[' + idx + '][is_base]" id="is-base-' + idx + '" value="' + (row.is_base ? 1 : 0) + '"></td>' +
            '<td><select name="units[' + idx + '][unit_id]" required>' + unitOptionsHtml(row.unit_id) + '</select></td>' +
            '<td><input type="number" step="0.0001" min="0.0001" name="units[' + idx + '][conversion_factor]" value="' + row.conversion_factor + '" required></td>' +
            '<td><input type="number" step="0.01" min="0" name="units[' + idx + '][purchase_price]" value="' + row.purchase_price + '" required></td>' +
            '<td><input type="number" step="0.01" min="0" name="units[' + idx + '][sale_price]" value="' + row.sale_price + '" required></td>' +
            '<td><button type="button" class="pos-rm" onclick="removeUnitRow(this)">&times;</button></td>';
        tbody.appendChild(tr);
    }

    function removeUnitRow(btn) {
        var tbody = document.getElementById('units-tbody');
        if (tbody.rows.length <= 1) {
            alert('অন্তত একটি ইউনিট থাকতে হবে');
            return;
        }
        btn.closest('tr').remove();
    }

    function setBaseRow(activeIdx) {
        document.querySelectorAll('#units-tbody input[type="hidden"][id^="is-base-"]').forEach(function (input) {
            input.value = 0;
        });
        document.getElementById('is-base-' + activeIdx).value = 1;
    }

    (@json($initialUnits)).forEach(function (row) {
        addUnitRow(row);
    });

    filterSubCategories();
</script>
