<x-core::layout
    title="{{ $account->name }} - স্টেটমেন্ট"
    title-en="{{ $account->name }} - Ledger"
    subtitle="অ্যাকাউন্টের যাবতীয় জমা, খরচ ও লেনদেন বিবরণী"
    subtitle-en="Account statement and transaction history"
    active="accounts"
>
    <x-finance::account-tabbar active="accounts" />

    <div class="panel" style="margin-top:0; margin-bottom:16px;">
        <div class="panel-body" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="{{ route('accounts.index') }}" class="btn btn-secondary" style="padding:4px 8px; font-size:12px;">← ফিরে যান</a>
                    <h2 style="font-size:18px; font-weight:700; margin:0;">{{ $account->name }}</h2>
                    @if ($account->is_default)
                        <span style="font-size:11px; font-weight:700; background:#fef08a; color:#854d0e; padding:2px 6px; border-radius:4px;">ডিফল্ট</span>
                    @endif
                </div>
                <div style="font-size:13px; color:var(--text-muted); margin-top:4px;">
                    {{ $account->typeLabel()['bn'] }}
                    @if ($account->account_number) • নম্বর: <b>{{ $account->account_number }}</b> @endif
                    @if ($account->bank_name) • {{ $account->bank_name }} @endif
                </div>
            </div>

            <div style="text-align:right;">
                <div style="font-size:12px; color:var(--text-muted);">বর্তমান ব্যালেন্স</div>
                <div style="font-size:24px; font-weight:700; color:#16a34a; font-family:'Manrope',sans-serif;">
                    ৳ {{ number_format($account->current_balance, 2) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <form method="GET" action="{{ route('accounts.ledger', $account) }}" class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; margin-bottom:16px;">
                <div>
                    <label style="font-size:11px; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px;">শুরুর তারিখ</label>
                    <input type="date" name="from" value="{{ $from }}" style="padding:6px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px;">
                </div>

                <div>
                    <label style="font-size:11px; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px;">শেষ তারিখ</label>
                    <input type="date" name="to" value="{{ $to }}" style="padding:6px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px;">
                </div>

                <div>
                    <label style="font-size:11px; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px;">লেনদেনের ধরন</label>
                    <select name="type" style="padding:6px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px; background:var(--bg-card);">
                        <option value="">সকল ধরন</option>
                        <option value="in" {{ $type === 'in' ? 'selected' : '' }}>জমা (In / Credit)</option>
                        <option value="out" {{ $type === 'out' ? 'selected' : '' }}>খরচ (Out / Debit)</option>
                    </select>
                </div>

                <div>
                    <label style="font-size:11px; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px;">উৎস</label>
                    <select name="source" style="padding:6px 10px; border:1px solid var(--border); border-radius:6px; font-size:13px; background:var(--bg-card);">
                        <option value="">সকল উৎস</option>
                        @foreach ($sourceLabels as $sKey => $sLabel)
                            <option value="{{ $sKey }}" {{ $source === $sKey ? 'selected' : '' }}>
                                {{ $sLabel['bn'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <x-core::button type="submit" color="primary" size="sm">ফিল্টার করুন</x-core::button>
                <x-core::button variant="secondary" size="sm" href="{{ route('accounts.ledger', $account) }}">রিসেট</x-core::button>
            </form>

            {{-- Summary Chips --}}
            <div style="display:flex; gap:16px; margin-bottom:16px; padding:12px; background:var(--paper); border-radius:8px; border:1px solid var(--border); flex-wrap:wrap;">
                <div>
                    <span style="font-size:12px; color:var(--text-muted);">মোট জমা (Total In):</span>
                    <b style="color:#16a34a; margin-left:4px;">৳ {{ number_format($totalIn, 2) }}</b>
                </div>
                <div style="border-left:1px solid var(--border); padding-left:16px;">
                    <span style="font-size:12px; color:var(--text-muted);">মোট খরচ (Total Out):</span>
                    <b style="color:#dc2626; margin-left:4px;">৳ {{ number_format($totalOut, 2) }}</b>
                </div>
                <div style="border-left:1px solid var(--border); padding-left:16px;">
                    <span style="font-size:12px; color:var(--text-muted);">নেট পরিবর্তন:</span>
                    <b style="color:{{ ($totalIn - $totalOut) < 0 ? '#dc2626' : '#16a34a' }}; margin-left:4px;">
                        ৳ {{ number_format($totalIn - $totalOut, 2) }}
                    </b>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">তারিখ ও সময়</th><th class="en" style="display:none;">Date & Time</th>
                            <th class="bn">উৎস ও বিবরণ</th><th class="en" style="display:none;">Source / Details</th>
                            <th class="bn">ধরন</th><th class="en" style="display:none;">Type</th>
                            <th class="bn" style="text-align:right;">পরিমাণ</th><th class="en" style="display:none; text-align:right;">Amount</th>
                            <th class="bn" style="text-align:right;">ব্যালেন্স (পরে)</th><th class="en" style="display:none; text-align:right;">Balance After</th>
                            <th class="bn">ব্যবহারকারী</th><th class="en" style="display:none;">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                            <tr>
                                <td style="font-size:12px; white-space:nowrap;">
                                    {{ optional($tx->occurred_at)->format('d M, Y h:i A') ?? '—' }}
                                </td>
                                <td>
                                    <div style="font-weight:600; font-size:13px;">
                                        {{ $tx->sourceLabel()['bn'] }}
                                    </div>
                                    @if ($tx->note)
                                        <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                                            {{ $tx->note }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($tx->type === 'in')
                                        <span class="badge" style="background:#dcfce7; color:#15803d; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:600;">+ জমা (In)</span>
                                    @else
                                        <span class="badge" style="background:#fee2e2; color:#b91c1c; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:600;">- খরচ (Out)</span>
                                    @endif
                                </td>
                                <td style="text-align:right; font-weight:700; font-family:'Manrope',sans-serif; color:{{ $tx->type === 'in' ? '#16a34a' : '#dc2626' }};">
                                    {{ $tx->type === 'in' ? '+' : '-' }} ৳ {{ number_format($tx->amount, 2) }}
                                </td>
                                <td style="text-align:right; font-weight:600; font-family:'Manrope',sans-serif;">
                                    ৳ {{ number_format($tx->balance_after, 2) }}
                                </td>
                                <td style="font-size:12px; color:var(--text-muted);">
                                    {{ $tx->creator->name ?? 'System' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-core::table.empty
                                        icon="receipt"
                                        title="এই সময়ের মধ্যে কোনো লেনদেন পাওয়া যায়নি"
                                        title-en="No transactions found in this period"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
