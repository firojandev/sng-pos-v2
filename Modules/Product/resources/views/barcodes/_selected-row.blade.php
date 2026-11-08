<tr class="bc-product-row" data-id="{{ $product->id }}" data-price="{{ number_format((float) $product->sale_price, 2) }}">
    <td class="bc-check-cell"><x-core::checkbox size="sm" name="product_ids[]" :value="$product->id" checked form="barcode-form"><span class="bc-sr-only">Remove {{ $product->name }}</span></x-core::checkbox></td>
    <td><div class="bc-product-name">{{ $product->name }}</div><div class="bc-product-sku">{{ $product->sku ? 'SKU: '.$product->sku : '—' }}</div></td>
    <td>
        @if ($product->barcode)
            <span class="bc-barcode-value"><x-core::icon name="barcode" />{{ $product->barcode }}</span>
        @else
            <span class="bc-muted"><span class="bn">এখনও তৈরি হয়নি</span><span class="en">Not assigned yet</span></span>
        @endif
    </td>
    <td><x-core::badge :color="$product->barcode ? 'success' : 'secondary'" size="sm" dot><span class="bn">{{ $product->barcode ? 'প্রস্তুত' : 'নতুন' }}</span><span class="en">{{ $product->barcode ? 'Ready' : 'New' }}</span></x-core::badge></td>
    <td class="bc-quantity-cell">
        <x-core::input size="sm" :no-margin="true" type="number" :name="'quantities['.$product->id.']'" value="1" min="1" max="100" step="1" required form="barcode-form" class="bc-product-quantity" :aria-label="'Label quantity for '.$product->name" />
    </td>
</tr>
