<x-core::layout title="হিসাব সেটআপ" title-en="Accounting Setup" subtitle="অর্থবছর ও প্রারম্ভিক ব্যালেন্স" subtitle-en="Fiscal years and the opening balance" active="accounting-setup">
    <x-accounting::tabbar active="setup" />

    <div class="panel" style="margin-top:16px;">
        <div class="panel-head"><div class="panel-title"><span class="bn">ক্যাশ ও ব্যাংকের প্রারম্ভিক ব্যালেন্স</span><span class="en" style="display:none;">Opening Cash & Bank Balances</span></div></div>
        <div class="panel-body" style="font-size:12.5px; color:var(--ink-600); padding-bottom:0;">
            <span class="bn">প্রতিটি অ্যাকাউন্টে আসলে কত টাকা আছে (গুনে/ব্যাংক স্টেটমেন্ট দেখে) লিখুন। ব্যালেন্স সরাসরি বদলানো হয় না — পার্থক্যটি "প্রারম্ভিক ব্যালেন্স" লেনদেন হিসেবে যোগ হয় এবং লেজারে যায়: ডেবিট ক্যাশ/ব্যাংক, ক্রেডিট প্রারম্ভিক ব্যালেন্স ইকুইটি। পরে মালিকের নতুন টাকা ক্যাশবক্সের "ক্যাশ ইন" দিয়ে দিন।</span>
            <span class="en" style="display:none;">Enter what each account really holds (counted, or per the bank statement). The balance isn't overwritten — the difference is added as an "opening balance" transaction and posted: Dr Cash/Bank, Cr Opening Balance Equity. Later owner money goes in through the cashbox "Cash In".</span>
        </div>
        <form method="POST" action="{{ route('accounting-setup.money-opening') }}">
            @csrf
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">অ্যাকাউন্ট</span><span class="en" style="display:none;">Account</span></th>
                            <th><span class="bn">দোকান</span><span class="en" style="display:none;">Shop</span></th>
                            <th class="table-cell-right"><span class="bn">সিস্টেমের ব্যালেন্স</span><span class="en" style="display:none;">System Balance</span></th>
                            <th style="width:180px;"><span class="bn">প্রকৃত ব্যালেন্স</span><span class="en" style="display:none;">Actual Balance</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($moneyAccounts as $moneyAccount)
                            <tr>
                                <td style="font-weight:600;">{{ $moneyAccount->name }} <span style="font-size:11.5px; color:var(--ink-500); font-weight:400;">{{ strtoupper($moneyAccount->type) }}</span></td>
                                <td>{{ $moneyAccount->shop?->name }}</td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); color:{{ (float) $moneyAccount->current_balance < 0 ? 'var(--red-600)' : 'inherit' }};">{{ number_format((float) $moneyAccount->current_balance, 2) }}</td>
                                <td><x-core::input size="sm" :no-margin="true" type="number" step="0.01" :name="'counted['.$moneyAccount->id.']'" placeholder="অপরিবর্তিত" placeholder-en="Unchanged" :stepper="false" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-core::table.empty icon="wallet" title="কোনো অ্যাকাউন্ট নেই" title-en="No accounts" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($moneyAccounts->isNotEmpty())
                <div class="panel-body" style="display:flex; gap:8px; align-items:flex-end;">
                    <div style="width:180px;"><x-core::input size="sm" type="date" name="as_of" label="তারিখ" label-en="As of" :value="now()->toDateString()" :required="true" /></div>
                    <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="check"><span class="bn">সংরক্ষণ</span><span class="en" style="display:none;">Save</span></x-core::button>
                </div>
            @endif
        </form>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title"><span class="bn">প্রারম্ভিক ব্যালেন্স</span><span class="en" style="display:none;">Opening Balance</span></div>
            </div>
            <div class="panel-body">
                @if ($openingPosted)
                    <p style="font-size:13px; color:var(--ink-700); margin:0;">
                        <span class="bn">প্রারম্ভিক ব্যালেন্স পোস্ট করা হয়েছে; লেজার চালু আছে।</span>
                        <span class="en" style="display:none;">The opening balance is posted; the ledger is running.</span>
                    </p>
                    <p style="font-size:12.5px; color:var(--ink-600); margin:12px 0 8px;">
                        <span class="bn">লেজার বনাম অ্যাপের হিসাব (পার্থক্য থাকলে জার্নাল এন্ট্রি দিয়ে সংশোধন করুন):</span>
                        <span class="en" style="display:none;">Ledger vs the app's figures (correct any difference with a journal entry):</span>
                    </p>
                    <div class="table-responsive">
                        <table class="app-table">
                            <thead>
                                <tr>
                                    <th><span class="bn">হিসাব</span><span class="en" style="display:none;">Item</span></th>
                                    <th class="table-cell-right"><span class="bn">অ্যাপ</span><span class="en" style="display:none;">App</span></th>
                                    <th class="table-cell-right"><span class="bn">লেজার</span><span class="en" style="display:none;">Ledger</span></th>
                                    <th class="table-cell-right"><span class="bn">পার্থক্য</span><span class="en" style="display:none;">Difference</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reconciliation as $row)
                                    <tr>
                                        <td style="font-size:12.5px;">{{ $row['label'] }}</td>
                                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ number_format($row['app'], 2) }}</td>
                                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ number_format($row['ledger'], 2) }}</td>
                                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700; color:{{ abs($row['difference']) < 0.01 ? 'var(--green-600, #16a34a)' : 'var(--red-600)' }};">{{ number_format($row['difference'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="font-size:12.5px; color:var(--ink-600); margin:0 0 12px;">
                        <span class="bn">বর্তমান ক্যাশ, ব্যাংক, পাওনা, মজুদ, ঋণ ও দেনা থেকে লেজার শুরু হবে। নিচের লাইনগুলো দেখে পোস্ট করুন; এটি একবারই করা যায়।</span>
                        <span class="en" style="display:none;">The ledger starts from current cash, bank, receivables, stock, loans and payables. Review the lines below and post; this is done once.</span>
                    </p>
                    @error('opening')
                        <div style="color:var(--red-600); font-size:12.5px; margin-bottom:10px;">{{ $message }}</div>
                    @enderror
                    <div class="table-responsive" style="max-height:360px; overflow-y:auto; margin-bottom:12px;">
                        <table class="app-table">
                            <thead>
                                <tr>
                                    <th><span class="bn">অ্যাকাউন্ট</span><span class="en" style="display:none;">Account</span></th>
                                    <th><span class="bn">দোকান / পক্ষ</span><span class="en" style="display:none;">Shop / Party</span></th>
                                    <th class="table-cell-right"><span class="bn">ডেবিট</span><span class="en" style="display:none;">Debit</span></th>
                                    <th class="table-cell-right"><span class="bn">ক্রেডিট</span><span class="en" style="display:none;">Credit</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($openingLines as $line)
                                    <tr>
                                        <td style="font-size:12.5px;">{{ $line['account']->name }}</td>
                                        <td style="font-size:12px; color:var(--ink-600);">{{ \Modules\Shop\Models\Shop::find($line['shop_id'])?->name }} @if ($line['party']) · {{ $line['party']->name }} @endif</td>
                                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $line['debit'] ? number_format($line['debit'], 2) : '' }}</td>
                                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $line['credit'] ? number_format($line['credit'], 2) : '' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4"><x-core::table.empty icon="file-text" title="পোস্ট করার মতো কোনো ব্যালেন্স নেই" title-en="No balances to post" /></td></tr>
                                @endforelse
                            </tbody>
                            @if ($openingLines->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="text-align:right; font-weight:700;"><span class="bn">মোট</span><span class="en" style="display:none;">Total</span></td>
                                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ number_format($openingLines->sum('debit'), 2) }}</td>
                                        <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ number_format($openingLines->sum('credit'), 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                    @if ($openingLines->isNotEmpty())
                        <form method="POST" action="{{ route('accounting-setup.opening-balance') }}" class="delete-form" data-title="প্রারম্ভিক ব্যালেন্স পোস্ট করবেন?" data-text="এটি একবারই পোস্ট করা যায়।"
                              style="display:flex; align-items:flex-end; gap:10px;">
                            @csrf
                            <div style="width:170px;">
                                <x-core::input size="sm" type="date" name="opening_date" label="চালুর তারিখ" label-en="Go-live Date" :value="now()->toDateString()" :required="true" />
                            </div>
                            <x-core::button type="submit" size="sm" variant="solid" color="primary">
                                <span class="bn">পোস্ট করুন</span><span class="en" style="display:none;">Post Opening Balance</span>
                            </x-core::button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="panel-title"><span class="bn">অর্থবছর</span><span class="en" style="display:none;">Fiscal Years</span></div>
                <form method="POST" action="{{ route('accounting-setup.fiscal-years.store') }}">
                    @csrf
                    <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="plus">
                        <span class="bn">পরবর্তী বছর</span><span class="en" style="display:none;">Add Next Year</span>
                    </x-core::button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">বছর</span><span class="en" style="display:none;">Year</span></th>
                            <th><span class="bn">সময়কাল</span><span class="en" style="display:none;">Period</span></th>
                            <th class="table-cell-center"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                            <th class="table-cell-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($fiscalYears as $year)
                            <tr>
                                <td style="font-weight:600;">{{ $year->name }}</td>
                                <td style="font-size:12.5px;">{{ $year->starts_on->format('d M, Y') }} – {{ $year->ends_on->format('d M, Y') }}</td>
                                <td class="table-cell-center">
                                    @if ($year->is_closed)
                                        <x-core::badge color="grey" size="xs" label="বন্ধ" label-en="Closed" />
                                    @else
                                        <x-core::badge color="green" size="xs" :dot="true" label="খোলা" label-en="Open" />
                                    @endif
                                </td>
                                <td class="table-cell-right">
                                    <form method="POST" action="{{ route('accounting-setup.fiscal-years.toggle', $year) }}">
                                        @csrf
                                        <x-core::button type="submit" size="sm" variant="soft" :color="$year->is_closed ? 'primary' : 'danger'" :icon="$year->is_closed ? 'unlock' : 'lock'">
                                            @if ($year->is_closed)
                                                <span class="bn">খুলুন</span><span class="en" style="display:none;">Reopen</span>
                                            @else
                                                <span class="bn">বন্ধ করুন</span><span class="en" style="display:none;">Close</span>
                                            @endif
                                        </x-core::button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-core::table.empty icon="calendar" title="কোনো অর্থবছর নেই" title-en="No fiscal years" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-core::layout>
