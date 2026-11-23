<x-core::layout
    title="ক্যাশ মিলানো"
    title-en="Cash Reconciliation"
    subtitle="প্রতিদিন ক্যাশ সেশন চালু ও বন্ধ করুন, প্রত্যাশিত ও গণনা করা ক্যাশ মিলিয়ে দেখুন"
    subtitle-en="Open and close daily cash sessions and match expected cash with the physical count"
    active="cash-reconciliation"
>
    @php
        $formatTaka = fn ($amount) => ((float) $amount < 0 ? '-৳' : '৳') . number_format(abs((float) $amount), 2);
    @endphp

    {{-- Cash drawers --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:16px; margin-bottom:20px;">
        @forelse ($drawers as $drawer)
            @php
                $account = $drawer['account'];
                $openSession = $drawer['openSession'];
            @endphp
            <div class="panel" style="margin-top:0;">
                <div class="panel-head" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                    <div class="panel-title">{{ $account->name }}</div>
                    @if ($openSession)
                        <x-core::badge color="green" :dot="true"><span class="bn">সেশন চালু</span><span class="en" style="display:none;">Session open</span></x-core::badge>
                    @else
                        <x-core::badge color="grey"><span class="bn">সেশন বন্ধ</span><span class="en" style="display:none;">No open session</span></x-core::badge>
                    @endif
                </div>
                <div class="panel-body" style="padding:16px 20px;">
                    <div style="font-size:12px; color:var(--ink-400);">
                        @if ($openSession)
                            <span class="bn">এখন ড্রয়ারে থাকার কথা</span><span class="en" style="display:none;">Expected in drawer now</span>
                        @else
                            <span class="bn">পরবর্তী সেশনের প্রত্যাশিত শুরুর ক্যাশ</span><span class="en" style="display:none;">Expected opening cash for next session</span>
                        @endif
                    </div>
                    <div style="font-size:24px; font-weight:800; color:var(--ink-900); font-family:var(--font-mono, monospace); margin:4px 0 10px;">{{ $formatTaka($drawer['expected']) }}</div>

                    @if ($openSession)
                        <div style="font-size:12.5px; color:var(--ink-600); margin-bottom:12px;">
                            <span class="bn">চালু করেছেন</span><span class="en" style="display:none;">Opened by</span>
                            {{ $openSession->opener?->name ?? '—' }} · {{ $openSession->opened_at->format('d M Y, h:i A') }}
                        </div>
                        <x-core::button size="sm" icon="check-circle" :href="route('cash-reconciliation.show', $openSession)">
                            <span class="bn">ক্যাশ গুনে সেশন বন্ধ করুন</span><span class="en" style="display:none;">Count & Close Session</span>
                        </x-core::button>
                    @else
                        <div style="font-size:12.5px; color:var(--ink-600); margin-bottom:12px;">
                            <span class="bn">সিস্টেম ব্যালেন্স</span><span class="en" style="display:none;">System balance</span>: {{ $formatTaka($account->current_balance) }}
                        </div>
                        @can('cash-reconciliation.open')
                            <x-core::button size="sm" icon="plus" :href="route('cash-reconciliation.create', $account)">
                                <span class="bn">সেশন চালু করুন</span><span class="en" style="display:none;">Open Session</span>
                            </x-core::button>
                        @endcan
                    @endif
                </div>
            </div>
        @empty
            <div class="panel" style="margin-top:0;">
                <x-core::table.empty
                    icon="wallet"
                    title="কোনো সক্রিয় ক্যাশ অ্যাকাউন্ট নেই"
                    title-en="No active cash account"
                    description="প্রথম নগদ বিক্রি বা লেনদেনের সময় ক্যাশ অ্যাকাউন্ট স্বয়ংক্রিয়ভাবে তৈরি হয়।"
                    description-en="A cash account is created automatically with the first cash sale or transaction."
                />
            </div>
        @endforelse
    </div>

    {{-- Session history --}}
    <div class="panel" style="margin-top:0;">
        <div class="panel-head" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div class="panel-title"><span class="bn">সেশনের ইতিহাস</span><span class="en" style="display:none;">Session History</span></div>
            @if ($drawers->count() > 1)
                <form method="GET" action="{{ route('cash-reconciliation.index') }}" style="width:220px;">
                    <x-core::select
                        name="account"
                        size="sm"
                        :no-margin="true"
                        :value="$accountFilter"
                        :options="['' => 'সব ক্যাশ অ্যাকাউন্ট (All cash accounts)'] + $drawers->mapWithKeys(fn ($drawer) => [$drawer['account']->id => $drawer['account']->name])->all()"
                        onchange="this.form.submit()"
                    />
                </form>
            @endif
        </div>
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">চালু</span><span class="en" style="display:none;">Opened</span></th>
                        <th><span class="bn">বন্ধ</span><span class="en" style="display:none;">Closed</span></th>
                        <th><span class="bn">অ্যাকাউন্ট</span><span class="en" style="display:none;">Account</span></th>
                        <th class="table-cell-right"><span class="bn">শুরুর ক্যাশ</span><span class="en" style="display:none;">Opening</span></th>
                        <th class="table-cell-right"><span class="bn">প্রত্যাশিত</span><span class="en" style="display:none;">Expected</span></th>
                        <th class="table-cell-right"><span class="bn">গণনা</span><span class="en" style="display:none;">Counted</span></th>
                        <th class="table-cell-right"><span class="bn">পার্থক্য</span><span class="en" style="display:none;">Difference</span></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        @php $status = \Modules\Finance\Models\CashSession::differenceStatus($session->closing_difference); @endphp
                        <tr>
                            <td style="white-space:nowrap;">
                                {{ $session->opened_at->format('d M Y, h:i A') }}
                                <div style="font-size:11.5px; color:var(--ink-400);">{{ $session->opener?->name }}</div>
                            </td>
                            <td style="white-space:nowrap;">
                                @if ($session->isOpen())
                                    <x-core::badge color="green" :dot="true"><span class="bn">চালু আছে</span><span class="en" style="display:none;">Open</span></x-core::badge>
                                @else
                                    {{ $session->closed_at?->format('d M Y, h:i A') }}
                                    <div style="font-size:11.5px; color:var(--ink-400);">{{ $session->closer?->name }}</div>
                                @endif
                            </td>
                            <td>{{ $session->account?->name }}</td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $formatTaka($session->opening_balance) }}</td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $session->isOpen() ? '—' : $formatTaka($session->expected_closing_balance) }}</td>
                            <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ $session->isOpen() ? '—' : $formatTaka($session->counted_closing_balance) }}</td>
                            <td class="table-cell-right" style="white-space:nowrap;">
                                @if ($session->isOpen())
                                    —
                                @else
                                    <x-core::badge :color="['over' => 'gold', 'short' => 'red', 'balanced' => 'green'][$status['key']]">
                                        {{ (float) $session->closing_difference > 0 ? '+' : '' }}{{ $formatTaka($session->closing_difference) }}
                                    </x-core::badge>
                                @endif
                            </td>
                            <td class="table-cell-right">
                                <x-core::button size="sm" variant="secondary" icon="eye" :href="route('cash-reconciliation.show', $session)">
                                    <span class="bn">দেখুন</span><span class="en" style="display:none;">View</span>
                                </x-core::button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-core::table.empty icon="clock" title="এখনো কোনো ক্যাশ সেশন নেই" title-en="No cash sessions yet" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sessions->hasPages())
            <div style="padding:12px 16px;">{{ $sessions->links() }}</div>
        @endif
    </div>
</x-core::layout>
