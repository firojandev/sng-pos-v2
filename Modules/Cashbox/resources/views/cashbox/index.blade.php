<x-core::layout
    title="ক্যাশবক্স"
    title-en="Cashbox"
    subtitle="দোকানের নগদ লেনদেন পরিচালনা করুন"
    subtitle-en="Manage your shop's cash transactions"
    active="cashbox"
>
    <div class="cash-page-head">
        <a href="{{ route('dashboard') }}" class="back" title="Back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M11 18l-6-6 6-6" stroke="#1C2B27" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <div class="ttl bn">ক্যাশবক্স</div>
        <div class="ttl en" style="display:none;">Cashbox</div>

        <div class="actions">
            <button type="button" class="btn btn-green" onclick="openModal('cashInModal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9.2" stroke="#fff" stroke-width="1.7"/><path d="M12 8v8M8 12h8" stroke="#fff" stroke-width="1.9" stroke-linecap="round"/></svg>
                <span class="bn">ক্যাশ ইন</span><span class="en">Cash In</span>
            </button>
            <button type="button" class="btn btn-red" onclick="openModal('cashOutModal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9.2" stroke="#fff" stroke-width="1.7"/><path d="M8 12h8" stroke="#fff" stroke-width="1.9" stroke-linecap="round"/></svg>
                <span class="bn">ক্যাশ আউট</span><span class="en">Cash Out</span>
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('cashbox.index') }}" class="section-row" style="margin-bottom:16px;">
        <div class="filters">
            <select name="type" onchange="this.form.submit()">
                <option value="all" @selected($type === 'all')>সব লেনদেন</option>
                <option value="cash_in" @selected($type === 'cash_in')>ক্যাশ ইন</option>
                <option value="cash_out" @selected($type === 'cash_out')>ক্যাশ আউট</option>
                <option value="sale" @selected($type === 'sale')>বেচা</option>
                <option value="purchase" @selected($type === 'purchase')>কেনা</option>
                <option value="income" @selected($type === 'income')>আয়</option>
                <option value="expense" @selected($type === 'expense')>ব্যয়</option>
            </select>

            <input type="date" name="from" value="{{ $from }}">
            <input type="date" name="to" value="{{ $to }}">

            @if ($creators->count())
                <select name="creator" onchange="this.form.submit()">
                    <option value="">সব ({{ $creators->count() }})</option>
                    @foreach ($creators as $user)
                        <option value="{{ $user->id }}" @selected((string) $creator === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <button type="submit" class="btn btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M4 12a8 8 0 0 1 13.7-5.7L20 8.5M20 4v4.5h-4.5M20 12a8 8 0 0 1-13.7 5.7L4 15.5M4 20v-4.5h4.5" stroke="#1C2B27" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="bn">রিফ্রেশ</span><span class="en">Refresh</span>
        </button>
    </form>

    <div class="cash-summary">
        <div class="cash-card blue">
            <div>
                <div class="lbl bn">ব্যালেন্স</div><div class="lbl en" style="display:none;">Balance</div>
                <div class="val">৳{{ number_format($balance, 2) }}</div>
            </div>
            <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="2.5" y="6" width="19" height="13" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12.5" r="3" stroke="currentColor" stroke-width="1.6"/></svg></div>
        </div>
        <div class="cash-card green">
            <div>
                <div class="lbl bn">ক্যাশ ইন</div><div class="lbl en" style="display:none;">Cash In</div>
                <div class="val">৳{{ number_format($summary->cash_in ?? 0, 2) }}</div>
            </div>
            <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 19V5M6 11l6-6 6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        </div>
        <div class="cash-card red">
            <div>
                <div class="lbl bn">ক্যাশ আউট</div><div class="lbl en" style="display:none;">Cash Out</div>
                <div class="val">৳{{ number_format($summary->cash_out ?? 0, 2) }}</div>
            </div>
            <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M6 13l6 6 6-6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        </div>
    </div>

    <div class="panel" style="margin-top:0;">
        <div class="panel-head">
            <div>
                <span class="panel-title bn">মোট লেনদেন: {{ $summary->total_count ?? 0 }}</span>
                <span class="panel-title en" style="display:none;">Total Transactions: {{ $summary->total_count ?? 0 }}</span>
            </div>
            <div>
                <span class="bn">পরিমান: </span><span class="en" style="display:none;">Amount: </span>
                <b>৳{{ number_format(($summary->cash_in ?? 0) + ($summary->cash_out ?? 0), 2) }}</b>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">রিসোর্স</th><th class="en" style="display:none;">Resource</th>
                            <th class="bn">তারিখ ও সময়</th><th class="en" style="display:none;">Date &amp; Time</th>
                            <th class="bn">ধরন</th><th class="en" style="display:none;">Type</th>
                            <th class="bn">নোট</th><th class="en" style="display:none;">Note</th>
                            <th class="bn" style="text-align:right;">পরিমান</th><th class="en" style="display:none; text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                            @php $label = $tx->sourceLabel(); @endphp
                            <tr>
                                <td class="cell-main">
                                    <span class="bn">{{ $label['bn'] }}</span><span class="en" style="display:none;">{{ $label['en'] }}</span>
                                </td>
                                <td>{{ $tx->occurred_at->format('d M, Y, h:i A') }}</td>
                                <td>
                                    @if ($tx->type === 'in')
                                        <span class="badge b-green bn">ক্যাশ ইন</span><span class="badge b-green en" style="display:none;">Cash In</span>
                                    @else
                                        <span class="badge b-red bn">ক্যাশ আউট</span><span class="badge b-red en" style="display:none;">Cash Out</span>
                                    @endif
                                </td>
                                <td>{{ $tx->note ?: '—' }}</td>
                                <td style="text-align:right; font-weight:700; color:{{ $tx->type === 'in' ? 'var(--green-600)' : 'var(--red-600)' }};">
                                    {{ $tx->type === 'in' ? '+' : '-' }}৳{{ number_format($tx->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-core::table.empty
                                        icon="coins"
                                        title="কোনো লেনদেন নেই"
                                        title-en="No cash transactions found"
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

    {{-- Cash In modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('cash_form') === 'in') open @endif" id="cashInModal">
        <div class="modal-box">
            <div class="modal-head">
                <div class="modal-title bn">ক্যাশ ইন</div>
                <div class="modal-title en" style="display:none;">Cash In</div>
                <button type="button" class="drawer-x" onclick="closeModal('cashInModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('cashbox.cash-in') }}">
                @csrf
                <input type="hidden" name="cash_form" value="in">
                <div class="field">
                    <label class="bn">পরিমাণ</label><label class="en" style="display:none;">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('cash_form') === 'in' ? old('amount') : '' }}" required>
                </div>
                <div class="field">
                    <label class="bn">তারিখ ও সময়</label><label class="en" style="display:none;">Date &amp; Time</label>
                    <input type="datetime-local" name="occurred_at" value="{{ old('cash_form') === 'in' ? old('occurred_at') : now()->format('Y-m-d\TH:i') }}">
                </div>
                <div class="field">
                    <label class="bn">নোট</label><label class="en" style="display:none;">Note</label>
                    <textarea name="note">{{ old('cash_form') === 'in' ? old('note') : '' }}</textarea>
                </div>
                @if ($errors->any() && old('cash_form') === 'in')
                    <div class="field-error">{{ $errors->first() }}</div>
                @endif
                <button type="submit" class="btn btn-green" style="width:100%; justify-content:center; margin-top:16px;">
                    <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Cash Out modal --}}
    <div class="modal-backdrop @if ($errors->any() && old('cash_form') === 'out') open @endif" id="cashOutModal">
        <div class="modal-box">
            <div class="modal-head">
                <div class="modal-title bn">ক্যাশ আউট</div>
                <div class="modal-title en" style="display:none;">Cash Out</div>
                <button type="button" class="drawer-x" onclick="closeModal('cashOutModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('cashbox.cash-out') }}">
                @csrf
                <input type="hidden" name="cash_form" value="out">
                <div class="field">
                    <label class="bn">পরিমাণ</label><label class="en" style="display:none;">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('cash_form') === 'out' ? old('amount') : '' }}" required>
                </div>
                <div class="field">
                    <label class="bn">তারিখ ও সময়</label><label class="en" style="display:none;">Date &amp; Time</label>
                    <input type="datetime-local" name="occurred_at" value="{{ old('cash_form') === 'out' ? old('occurred_at') : now()->format('Y-m-d\TH:i') }}">
                </div>
                <div class="field">
                    <label class="bn">নোট</label><label class="en" style="display:none;">Note</label>
                    <textarea name="note">{{ old('cash_form') === 'out' ? old('note') : '' }}</textarea>
                </div>
                @if ($errors->any() && old('cash_form') === 'out')
                    <div class="field-error">{{ $errors->first() }}</div>
                @endif
                <button type="submit" class="btn btn-red" style="width:100%; justify-content:center; margin-top:16px;">
                    <span class="bn">সংরক্ষণ করুন</span><span class="en">Save</span>
                </button>
            </form>
        </div>
    </div>
</x-core::layout>
