{{--
    Physical cash count sheet (note-by-note).
    @param list<int> $denominations
    @param float|null $expected  Expected cash; when given, the live difference is shown.
--}}
<div class="cash-count-sheet" data-expected="{{ $expected ?? '' }}">
    <div class="table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th><span class="bn">নোট / কয়েন</span><span class="en" style="display:none;">Note / Coin</span></th>
                    <th style="width:150px;"><span class="bn">সংখ্যা</span><span class="en" style="display:none;">Quantity</span></th>
                    <th class="table-cell-right"><span class="bn">মোট</span><span class="en" style="display:none;">Subtotal</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($denominations as $denomination)
                    <tr>
                        <td style="font-weight:700; color:var(--ink-900);">৳{{ number_format($denomination) }}</td>
                        <td>
                            <x-core::input
                                type="number"
                                name="counts[{{ $denomination }}]"
                                min="0"
                                step="1"
                                size="sm"
                                :no-margin="true"
                                :stepper="false"
                                class="cash-count-qty"
                                data-denomination="{{ $denomination }}"
                                :value="old('counts.' . $denomination)"
                                placeholder="0"
                                style="text-align:right;"
                            />
                        </td>
                        <td class="table-cell-right cash-count-subtotal" style="font-family:var(--font-mono, monospace);">৳0.00</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-weight:700; color:var(--ink-900);">
                        <span class="bn">খুচরা / অন্যান্য</span><span class="en" style="display:none;">Loose change / Other</span>
                    </td>
                    <td>
                        <x-core::input
                            type="number"
                            name="other_amount"
                            min="0"
                            step="0.01"
                            size="sm"
                            :no-margin="true"
                            :stepper="false"
                            class="cash-count-other"
                            :value="old('other_amount')"
                            placeholder="0.00"
                            style="text-align:right;"
                        />
                    </td>
                    <td class="table-cell-right cash-count-other-subtotal" style="font-family:var(--font-mono, monospace);">৳0.00</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right; font-weight:700;"><span class="bn">গণনা করা মোট ক্যাশ</span><span class="en" style="display:none;">Counted Cash</span></td>
                    <td class="table-cell-right cash-count-total" style="font-family:var(--font-mono, monospace); font-weight:800; color:var(--ink-900);">৳0.00</td>
                </tr>
                @if ($expected !== null)
                    <tr>
                        <td colspan="2" style="text-align:right; font-weight:700;"><span class="bn">পার্থক্য</span><span class="en" style="display:none;">Difference</span></td>
                        <td class="table-cell-right cash-count-difference" style="font-family:var(--font-mono, monospace); font-weight:800;">—</td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>

@once
    @push('scripts')
        <script>
            $(function () {
                function formatTaka(amount) {
                    const sign = amount < 0 ? '-' : (amount > 0 ? '+' : '');
                    return sign + '৳' + Math.abs(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function recalcSheet($sheet) {
                    let total = 0;

                    $sheet.find('.cash-count-qty').each(function () {
                        const quantity = Math.max(0, parseInt($(this).val(), 10) || 0);
                        const subtotal = quantity * Number($(this).data('denomination'));
                        total += subtotal;
                        $(this).closest('tr').find('.cash-count-subtotal').text('৳' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                    });

                    const other = Math.max(0, parseFloat($sheet.find('.cash-count-other').val()) || 0);
                    total += other;
                    $sheet.find('.cash-count-other-subtotal').text('৳' + other.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $sheet.find('.cash-count-total').text('৳' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                    const expected = $sheet.data('expected');
                    if (expected !== '' && expected !== undefined) {
                        const difference = Math.round((total - Number(expected)) * 100) / 100;
                        $sheet.find('.cash-count-difference')
                            .text(difference === 0 ? '৳0.00' : formatTaka(difference))
                            .css('color', difference === 0 ? 'var(--green-ink)' : 'var(--red-600)');
                    }
                }

                $(document).on('input change', '.cash-count-sheet input', function () {
                    recalcSheet($(this).closest('.cash-count-sheet'));
                });

                $(document).on('wheel', '.cash-count-sheet input[type="number"]', function () {
                    $(this).blur();
                });

                $('.cash-count-sheet').each(function () {
                    recalcSheet($(this));
                });
            });
        </script>
    @endpush
@endonce
