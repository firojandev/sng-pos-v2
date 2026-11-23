<x-core::layout
    title="ক্যাশ সেশন চালু"
    title-en="Open Cash Session"
    :subtitle="$account->name"
    :subtitle-en="$account->name"
    active="cash-reconciliation"
>
    @php
        $formatTaka = fn ($amount) => ((float) $amount < 0 ? '-৳' : '৳') . number_format(abs((float) $amount), 2);
        $isCounting = (bool) old('count_opening');
    @endphp

    <div style="margin-bottom:14px;">
        <x-core::button size="sm" variant="secondary" icon="chevron-left" :href="route('cash-reconciliation.index')">
            <span class="bn">ফিরে যান</span><span class="en" style="display:none;">Back</span>
        </x-core::button>
    </div>

    @if ($errors->has('session'))
        <div class="panel" style="margin-top:0; margin-bottom:16px; padding:12px 16px; background:var(--red-100); color:var(--red-600); font-weight:600;">
            {{ $errors->first('session') }}
        </div>
    @endif

    <form method="POST" action="{{ route('cash-reconciliation.store', $account) }}" style="max-width:720px;">
        @csrf

        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title"><span class="bn">শুরুর ক্যাশ</span><span class="en" style="display:none;">Opening Cash</span></div>
            </div>
            <div class="panel-body" style="padding:16px 20px;">
                <div style="font-size:12px; color:var(--ink-400);">
                    <span class="bn">আগের সেশন থেকে আনা প্রত্যাশিত ক্যাশ</span><span class="en" style="display:none;">Expected cash carried forward</span>
                </div>
                <div style="font-size:26px; font-weight:800; color:var(--ink-900); font-family:var(--font-mono, monospace); margin:4px 0 6px;">{{ $formatTaka($expectedOpening) }}</div>
                <div style="font-size:12.5px; color:var(--ink-600);">
                    @if ($lastClosed)
                        <span class="bn">শেষ সেশনের গণনা করা ক্যাশ</span><span class="en" style="display:none;">Last session's counted cash</span>
                        {{ $formatTaka($lastClosed->counted_closing_balance) }} ({{ $lastClosed->closed_at->format('d M Y, h:i A') }})
                        @if (round($expectedOpening - (float) $lastClosed->counted_closing_balance, 2) != 0)
                            · <span class="bn">মাঝের লেনদেন</span><span class="en" style="display:none;">Movements in between</span>
                            {{ $formatTaka($expectedOpening - (float) $lastClosed->counted_closing_balance) }}
                        @endif
                    @else
                        <span class="bn">এটি প্রথম সেশন — ক্যাশ অ্যাকাউন্টের সিস্টেম ব্যালেন্স থেকে শুরু।</span>
                        <span class="en" style="display:none;">This is the first session — starting from the cash account's system balance.</span>
                    @endif
                </div>

                <div style="margin-top:16px;">
                    <x-core::checkbox
                        name="count_opening"
                        id="count-opening-toggle"
                        value="1"
                        :checked="$isCounting"
                        label="এখন ড্রয়ারের ক্যাশ গুনব (ঐচ্ছিক)"
                        label-en="Count the drawer cash now (optional)"
                    />
                </div>
            </div>
        </div>

        <div class="panel" id="opening-count-panel" style="margin-top:16px; {{ $isCounting ? '' : 'display:none;' }}">
            <div class="panel-head">
                <div class="panel-title"><span class="bn">ক্যাশ গণনা</span><span class="en" style="display:none;">Cash Count</span></div>
            </div>
            @include('finance::cash-reconciliation._count_sheet', ['denominations' => $denominations, 'expected' => $expectedOpening])
        </div>

        <div class="panel" style="margin-top:16px;">
            <div class="panel-body" style="padding:16px 20px;">
                <x-core::textarea
                    name="note"
                    size="sm"
                    :rows="2"
                    label="মন্তব্য"
                    label-en="Note"
                    placeholder="ঐচ্ছিক — যেমন গণনায় পার্থক্যের কারণ"
                    placeholder-en="Optional — e.g. reason for a count difference"
                    :value="old('note')"
                />
                <div style="display:flex; justify-content:flex-end; margin-top:12px;">
                    <x-core::button type="submit" size="sm" icon="check-circle">
                        <span class="bn">সেশন চালু করুন</span><span class="en" style="display:none;">Open Session</span>
                    </x-core::button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            $(function () {
                $(document).on('change', '#count-opening-toggle', function () {
                    $('#opening-count-panel').toggle(this.checked);
                });
            });
        </script>
    @endpush
</x-core::layout>
