<x-core::layout
    title="ক্যাশ সেশন"
    title-en="Cash Session"
    :subtitle="($session->account?->name ?? '') . ' · ' . $session->opened_at->format('d M Y, h:i A')"
    :subtitle-en="($session->account?->name ?? '') . ' · ' . $session->opened_at->format('d M Y, h:i A')"
    active="cash-reconciliation"
>
    @php
        $formatTaka = fn ($amount) => ((float) $amount < 0 ? '-৳' : '৳') . number_format(abs((float) $amount), 2);
        $signedTaka = fn ($amount) => ((float) $amount > 0 ? '+' : '') . $formatTaka($amount);
        $differenceStatus = \Modules\Finance\Models\CashSession::differenceStatus($session->closing_difference);
        $differenceColor = ['over' => 'gold', 'short' => 'red', 'balanced' => 'green'][$differenceStatus['key']];
    @endphp

    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:14px;">
        <x-core::button size="sm" variant="secondary" icon="chevron-left" :href="route('cash-reconciliation.index')">
            <span class="bn">ফিরে যান</span><span class="en" style="display:none;">Back</span>
        </x-core::button>
        @if ($session->isOpen())
            <x-core::badge color="green" :dot="true"><span class="bn">সেশন চালু আছে</span><span class="en" style="display:none;">Session open</span></x-core::badge>
        @else
            <x-core::badge :color="$differenceColor"><span class="bn">{{ $differenceStatus['bn'] }}</span><span class="en" style="display:none;">{{ $differenceStatus['en'] }}</span></x-core::badge>
        @endif
    </div>

    {{-- Reconciliation summary --}}
    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); margin-bottom:16px;">
        <x-core::stat-card
            icon="log-in"
            color="blue"
            :value="$formatTaka($session->opening_balance)"
            label="শুরুর ক্যাশ"
            label-en="Opening Cash"
            :subtext="$session->counted_opening_balance !== null ? 'গণনা করা হয়েছে' : 'আগের সেশন থেকে আনা'"
            :subtext-en="$session->counted_opening_balance !== null ? 'Counted' : 'Carried forward'"
        />
        <x-core::stat-card
            icon="trending-up"
            color="green"
            value-color="green"
            :value="'+' . $formatTaka($summary['cash_in'])"
            label="ক্যাশ জমা"
            label-en="Cash In"
            subtext="এই সেশনে নগদ আগমন"
            subtext-en="Cash received this session"
        />
        <x-core::stat-card
            icon="trending-down"
            color="red"
            value-color="red"
            :value="'-' . $formatTaka($summary['cash_out'])"
            label="ক্যাশ খরচ"
            label-en="Cash Out"
            subtext="এই সেশনে নগদ নির্গমন"
            subtext-en="Cash paid out this session"
        />
        <x-core::stat-card
            icon="wallet"
            color="teal"
            :value="$formatTaka($expectedClosing)"
            label="প্রত্যাশিত ক্যাশ"
            label-en="Expected Cash"
            :subtext="$session->isOpen() ? 'এখন ড্রয়ারে থাকার কথা' : 'বন্ধের সময় হিসাব'"
            :subtext-en="$session->isOpen() ? 'Should be in the drawer now' : 'Calculated at closing'"
        />
        @unless ($session->isOpen())
            <x-core::stat-card
                icon="check-circle"
                :color="$differenceColor"
                :value-color="$differenceColor"
                :value="$formatTaka($session->counted_closing_balance)"
                label="গণনা করা ক্যাশ"
                label-en="Counted Cash"
                :subtext="'পার্থক্য: ' . $signedTaka($session->closing_difference)"
                :subtext-en="'Difference: ' . $signedTaka($session->closing_difference)"
            />
        @endunless
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; align-items:start;">
        {{-- Movement breakdown --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title"><span class="bn">ক্যাশ লেনদেনের হিসাব</span><span class="en" style="display:none;">Cash Movements</span></div>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <tbody>
                        <tr>
                            <td style="font-weight:700;"><span class="bn">শুরুর ক্যাশ</span><span class="en" style="display:none;">Opening cash</span></td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ $formatTaka($session->opening_balance) }}</td>
                        </tr>
                        @foreach ($summary['rows'] as $row)
                            <tr>
                                <td>
                                    <span class="bn">{{ $row['bn'] }}</span><span class="en" style="display:none;">{{ $row['en'] }}</span>
                                    <span style="font-size:11.5px; color:var(--ink-400);">({{ $row['count'] }})</span>
                                </td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); color:{{ $row['type'] === 'in' ? 'var(--green-ink)' : 'var(--red-600)' }};">
                                    {{ $row['type'] === 'in' ? '+' : '-' }}{{ $formatTaka($row['amount']) }}
                                </td>
                            </tr>
                        @endforeach
                        @if (round((float) $summary['other_changes'], 2) != 0)
                            <tr>
                                <td>
                                    <span class="bn">আগের লেনদেন সম্পাদনা / মুছে ফেলা</span><span class="en" style="display:none;">Edits / deletions of earlier entries</span>
                                </td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $signedTaka($summary['other_changes']) }}</td>
                            </tr>
                        @endif
                        @if (count($summary['rows']) === 0 && round((float) $summary['other_changes'], 2) == 0)
                            <tr>
                                <td colspan="2" style="color:var(--ink-400); font-size:12.5px;">
                                    <span class="bn">এই সেশনে এখনো কোনো ক্যাশ লেনদেন হয়নি।</span><span class="en" style="display:none;">No cash movements in this session yet.</span>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="font-weight:800;"><span class="bn">প্রত্যাশিত ক্যাশ</span><span class="en" style="display:none;">Expected cash</span></td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:800;">{{ $formatTaka($expectedClosing) }}</td>
                        </tr>
                        @unless ($session->isOpen())
                            <tr>
                                <td style="font-weight:700;"><span class="bn">গণনা করা ক্যাশ</span><span class="en" style="display:none;">Counted cash</span></td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700;">{{ $formatTaka($session->counted_closing_balance) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight:700;"><span class="bn">পার্থক্য</span><span class="en" style="display:none;">Difference</span></td>
                                <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:800; color:{{ $differenceStatus['key'] === 'balanced' ? 'var(--green-ink)' : 'var(--red-600)' }};">{{ $signedTaka($session->closing_difference) }}</td>
                            </tr>
                        @endunless
                    </tfoot>
                </table>
            </div>
            <div style="padding:12px 16px; font-size:12.5px; color:var(--ink-600); border-top:1px solid var(--border);">
                <div>
                    <span class="bn">চালু করেছেন</span><span class="en" style="display:none;">Opened by</span>:
                    {{ $session->opener?->name ?? '—' }} · {{ $session->opened_at->format('d M Y, h:i A') }}
                    @if ($session->counted_opening_balance !== null && round((float) $session->opening_difference, 2) != 0)
                        · <span class="bn">শুরুর গণনায় পার্থক্য</span><span class="en" style="display:none;">Opening count difference</span>: {{ $signedTaka($session->opening_difference) }}
                    @endif
                </div>
                @if ($session->opening_note)
                    <div style="margin-top:4px;"><span class="bn">শুরুর মন্তব্য</span><span class="en" style="display:none;">Opening note</span>: {{ $session->opening_note }}</div>
                @endif
                @unless ($session->isOpen())
                    <div style="margin-top:4px;">
                        <span class="bn">বন্ধ করেছেন</span><span class="en" style="display:none;">Closed by</span>:
                        {{ $session->closer?->name ?? '—' }} · {{ $session->closed_at?->format('d M Y, h:i A') }}
                    </div>
                    @if ($session->closing_note)
                        <div style="margin-top:4px;"><span class="bn">কারণ / মন্তব্য</span><span class="en" style="display:none;">Reason / note</span>: {{ $session->closing_note }}</div>
                    @endif
                @endunless
            </div>
        </div>

        {{-- Close form or recorded counts --}}
        @if ($session->isOpen())
            @can('cash-reconciliation.close')
                <form method="POST" action="{{ route('cash-reconciliation.close', $session) }}" class="panel" style="margin-top:0;">
                    @csrf
                    <div class="panel-head">
                        <div class="panel-title"><span class="bn">ক্যাশ গুনে সেশন বন্ধ করুন</span><span class="en" style="display:none;">Count Cash & Close Session</span></div>
                    </div>

                    @if ($errors->has('session'))
                        <div style="margin:12px 16px 0; padding:10px 12px; border-radius:8px; background:var(--red-100); color:var(--red-600); font-weight:600;">
                            {{ $errors->first('session') }}
                        </div>
                    @endif

                    @include('finance::cash-reconciliation._count_sheet', ['denominations' => $denominations, 'expected' => $expectedClosing])

                    <div style="padding:12px 16px 16px;">
                        <x-core::textarea
                            name="note"
                            size="sm"
                            :rows="2"
                            label="কারণ / মন্তব্য"
                            label-en="Reason / Note"
                            placeholder="ক্যাশ কম বা বেশি হলে কারণ লেখা বাধ্যতামূলক"
                            placeholder-en="Required when the counted cash is over or short"
                            :value="old('note')"
                        />
                        <div style="display:flex; justify-content:flex-end; margin-top:12px;">
                            <x-core::button type="submit" size="sm" icon="lock">
                                <span class="bn">সেশন বন্ধ করুন</span><span class="en" style="display:none;">Close Session</span>
                            </x-core::button>
                        </div>
                    </div>
                </form>
            @endcan
        @else
            <div class="panel" style="margin-top:0;">
                <div class="panel-head">
                    <div class="panel-title"><span class="bn">ক্যাশ গণনা</span><span class="en" style="display:none;">Cash Counts</span></div>
                </div>
                <div class="table-responsive">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th><span class="bn">নোট / কয়েন</span><span class="en" style="display:none;">Note / Coin</span></th>
                                <th class="table-cell-right"><span class="bn">শুরু</span><span class="en" style="display:none;">Opening</span></th>
                                <th class="table-cell-right"><span class="bn">বন্ধ</span><span class="en" style="display:none;">Closing</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $countRows = collect($denominations)->push(0)->filter(
                                    fn ($denomination) => $openingCounts->contains(fn ($count) => (float) $count->denomination == $denomination)
                                        || $closingCounts->contains(fn ($count) => (float) $count->denomination == $denomination)
                                );
                                $lineFor = fn ($counts, $denomination) => $counts->first(fn ($count) => (float) $count->denomination == $denomination);
                            @endphp
                            @forelse ($countRows as $denomination)
                                @php
                                    $openingLine = $lineFor($openingCounts, $denomination);
                                    $closingLine = $lineFor($closingCounts, $denomination);
                                @endphp
                                <tr>
                                    <td style="font-weight:700;">
                                        @if ($denomination == 0)
                                            <span class="bn">খুচরা / অন্যান্য</span><span class="en" style="display:none;">Loose change / Other</span>
                                        @else
                                            ৳{{ number_format($denomination) }}
                                        @endif
                                    </td>
                                    <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">
                                        @if ($openingLine)
                                            {{ $denomination == 0 ? '' : $openingLine->quantity . ' × ' }}{{ $formatTaka($openingLine->subtotal) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">
                                        @if ($closingLine)
                                            {{ $denomination == 0 ? '' : $closingLine->quantity . ' × ' }}{{ $formatTaka($closingLine->subtotal) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <x-core::table.empty icon="wallet" title="কোনো নোট গণনা নেই" title-en="No note counts recorded" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-core::layout>
