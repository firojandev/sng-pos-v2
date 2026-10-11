@props([
    'value' => null, // pre-formatted HTML/text for the real amount; falls back to the slot
    'mask' => '********.**',
])

<span class="masked-amount" data-masked-amount>
    <span class="masked-amount-real" data-masked-real style="display:none;">@if ($value !== null){!! $value !!}@else{{ $slot }}@endif</span>
    <span class="masked-amount-mask" data-masked-mask>{{ $mask }}</span>
    <button
        type="button"
        class="masked-amount-eye"
        data-masked-eye
        tabindex="-1"
        title="পরিমাণ দেখুন / Reveal amount"
    >
        <x-core::icon name="eye" size="14" data-masked-icon />
    </button>
</span>
