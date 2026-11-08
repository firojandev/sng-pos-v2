<x-core::layout title="নতুন জার্নাল এন্ট্রি" title-en="New Journal Entry" subtitle="ডেবিট ও ক্রেডিট সমান হতে হবে" subtitle-en="Debits must equal credits" active="journal-entries">
    <x-accounting::tabbar active="journals" />

    @php
        $accountOptions = $accounts->mapWithKeys(fn ($account) => [$account->id => $account->code.' — '.$account->name])->all();
        $oldLines = old('lines', [[], []]);
    @endphp

    <div class="panel" style="margin-top:16px;">
        <div class="panel-body">
            <form method="POST" action="{{ route('journal-entries.store') }}" id="journal-form">
                @csrf
                @error('lines')
                    <div style="color:var(--red-600); font-size:12.5px; margin-bottom:10px;">{{ $message }}</div>
                @enderror
                @error('entry_date')
                    <div style="color:var(--red-600); font-size:12.5px; margin-bottom:10px;">{{ $message }}</div>
                @enderror

                <div style="display:grid; grid-template-columns:170px 1fr 200px; gap:12px; margin-bottom:14px;">
                    <x-core::input size="sm" type="date" name="entry_date" label="তারিখ" label-en="Date" :value="old('entry_date', now()->toDateString())" :required="true" />
                    <x-core::input size="sm" name="narration" label="বিবরণ" label-en="Narration" :value="old('narration')" :required="true" />
                    <x-core::input size="sm" name="reference" label="রেফারেন্স" label-en="Reference" :value="old('reference')" />
                    @if ($shops->isNotEmpty())
                        <x-core::select size="sm" name="shop_id" label="দোকান (ঐচ্ছিক)" label-en="Shop (optional)" :options="$shops->all()" :value="old('shop_id')" placeholder="সমগ্র কোম্পানি" placeholder-en="Whole company" />
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="app-table" id="journal-lines">
                        <thead>
                            <tr>
                                <th><span class="bn">অ্যাকাউন্ট</span><span class="en" style="display:none;">Account</span></th>
                                <th style="width:140px;" class="table-cell-right"><span class="bn">ডেবিট</span><span class="en" style="display:none;">Debit</span></th>
                                <th style="width:140px;" class="table-cell-right"><span class="bn">ক্রেডিট</span><span class="en" style="display:none;">Credit</span></th>
                                <th><span class="bn">নোট</span><span class="en" style="display:none;">Memo</span></th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($oldLines as $index => $line)
                                <tr class="journal-line">
                                    <td><x-core::select size="sm" :no-margin="true" name="lines[{{ $index }}][ledger_account_id]" :options="$accountOptions" :value="$line['ledger_account_id'] ?? null" placeholder="-- অ্যাকাউন্ট --" placeholder-en="-- Account --" /></td>
                                    <td><x-core::input size="sm" :no-margin="true" type="number" step="0.01" min="0" name="lines[{{ $index }}][debit]" class="line-debit" :value="$line['debit'] ?? ''" :stepper="false" /></td>
                                    <td><x-core::input size="sm" :no-margin="true" type="number" step="0.01" min="0" name="lines[{{ $index }}][credit]" class="line-credit" :value="$line['credit'] ?? ''" :stepper="false" /></td>
                                    <td><x-core::input size="sm" :no-margin="true" name="lines[{{ $index }}][memo]" :value="$line['memo'] ?? ''" /></td>
                                    <td><x-core::button type="button" size="sm" variant="soft" color="danger" icon="trash-2" icon-only class="remove-line" title="মুছুন / Remove" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="text-align:right; font-weight:700;"><span class="bn">মোট</span><span class="en" style="display:none;">Total</span></td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;" id="total-debit">0.00</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;" id="total-credit">0.00</td>
                                <td colspan="2" id="balance-status" style="font-size:12.5px;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="display:flex; gap:10px; margin-top:14px;">
                    <x-core::button type="button" size="sm" variant="secondary" icon="plus" id="add-line">
                        <span class="bn">লাইন যোগ করুন</span><span class="en" style="display:none;">Add Line</span>
                    </x-core::button>
                    <x-core::button type="submit" size="sm" variant="solid" color="primary" id="post-entry">
                        <span class="bn">পোস্ট করুন</span><span class="en" style="display:none;">Post Entry</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
        $(function () {
            var $body = $('#journal-lines tbody');

            function renumber() {
                $body.find('tr.journal-line').each(function (index) {
                    $(this).find('[name^="lines["]').each(function () {
                        this.name = this.name.replace(/lines\[\d+\]/, 'lines[' + index + ']');
                    });
                });
            }

            function refreshTotals() {
                var debit = 0, credit = 0;
                $body.find('.line-debit').each(function () { debit += parseFloat(this.value) || 0; });
                $body.find('.line-credit').each(function () { credit += parseFloat(this.value) || 0; });
                $('#total-debit').text(debit.toFixed(2));
                $('#total-credit').text(credit.toFixed(2));
                var balanced = debit > 0 && Math.abs(debit - credit) < 0.005;
                $('#balance-status').html(balanced
                    ? '<span style="color:var(--green-600, #16a34a);">সমান / Balanced</span>'
                    : '<span style="color:var(--red-600);">পার্থক্য / Difference: ' + Math.abs(debit - credit).toFixed(2) + '</span>');
                $('#post-entry').prop('disabled', !balanced);
            }

            $('#add-line').on('click', function () {
                var $row = $body.find('tr.journal-line').first().clone();
                $row.find('input').val('');
                $row.find('select').val('');
                $body.append($row);
                renumber();
                refreshTotals();
            });

            $body.on('click', '.remove-line', function () {
                if ($body.find('tr.journal-line').length > 2) {
                    $(this).closest('tr').remove();
                    renumber();
                    refreshTotals();
                }
            });

            // A line is either a debit or a credit.
            $body.on('input', '.line-debit, .line-credit', function () {
                var $other = $(this).closest('tr').find($(this).hasClass('line-debit') ? '.line-credit' : '.line-debit');
                if (parseFloat(this.value) > 0) {
                    $other.val('');
                }
                refreshTotals();
            });

            refreshTotals();
        });
        </script>
    @endpush
</x-core::layout>
