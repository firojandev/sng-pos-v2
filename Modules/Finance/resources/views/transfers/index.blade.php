<x-core::layout
    title="ফান্ড ট্রান্সফার"
    title-en="Fund Transfers"
    subtitle="দোকানের এক অ্যাকাউন্ট থেকে অন্য অ্যাকাউন্টে টাকা স্থানান্তরের ইতিহাস"
    subtitle-en="History of fund transfers between accounts"
    active="accounts"
>
    <x-finance::account-tabbar active="account-transfers" />

    {{-- KPI Cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:20px;">
        <div class="panel" style="margin:0; padding:16px; border-left:4px solid #D4AF37;">
            <div style="font-size:12px; color:var(--text-muted); font-weight:600;">
                <span class="bn">মোট স্থানান্তরিত পরিমাণ</span><span class="en" style="display:none;">Total Transferred</span>
            </div>
            <div style="font-size:22px; font-weight:700; color:var(--text-primary); margin-top:4px;">
                ৳ {{ number_format($totalTransferAmount, 2) }}
            </div>
        </div>

        <div class="panel" style="margin:0; padding:16px; border-left:4px solid #ef4444;">
            <div style="font-size:12px; color:var(--text-muted); font-weight:600;">
                <span class="bn">মোট ট্রান্সফার চার্জ / ফি</span><span class="en" style="display:none;">Total Transfer Fee</span>
            </div>
            <div style="font-size:22px; font-weight:700; color:#dc2626; margin-top:4px;">
                ৳ {{ number_format($totalChargeAmount, 2) }}
            </div>
        </div>
    </div>

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row" style="display:flex; flex-wrap:wrap; gap:12px; justify-content:space-between; align-items:center;">
                <form method="GET" action="{{ route('account-transfers.index') }}" class="filters" style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin:0;">
                    <div style="width:200px;">
                        <x-core::input name="q" value="{{ $search }}" placeholder="খুঁজুন..." placeholder-en="Search..." icon="search" size="sm" />
                    </div>

                    <div style="display:flex; align-items:center; gap:6px;">
                        <input type="date" name="from" value="{{ $from }}" style="height:32px; padding:4px 10px; border:1px solid var(--border); border-radius:8px; font-size:12.5px; background:var(--paper);">
                        <span style="color:var(--text-muted); font-size:12px;">হতে</span>
                        <input type="date" name="to" value="{{ $to }}" style="height:32px; padding:4px 10px; border:1px solid var(--border); border-radius:8px; font-size:12.5px; background:var(--paper);">
                    </div>

                    <x-core::button type="submit" color="primary" size="sm">ফিল্টার</x-core::button>
                    @if ($search || $from != now()->startOfMonth()->toDateString() || $to != now()->endOfMonth()->toDateString())
                        <x-core::button variant="secondary" size="sm" href="{{ route('account-transfers.index') }}">রিসেট</x-core::button>
                    @endif
                </form>

                <div style="display:flex; align-items:center; gap:8px;">
                    <x-core::button color="primary" size="sm" id="btnOpenTransferModal" icon="plus">
                        <span class="bn">নতুন ট্রান্সফার</span><span class="en" style="display:none;">New Transfer</span>
                    </x-core::button>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">ট্রান্সফার নং ও তারিখ</th><th class="en" style="display:none;">Transfer No & Date</th>
                            <th class="bn">উৎস অ্যাকাউন্ট</th><th class="en" style="display:none;">From Account</th>
                            <th class="bn">গন্তব্য অ্যাকাউন্ট</th><th class="en" style="display:none;">To Account</th>
                            <th class="bn" style="text-align:right;">পরিমাণ</th><th class="en" style="display:none; text-align:right;">Amount</th>
                            <th class="bn" style="text-align:right;">চার্জ / ফি</th><th class="en" style="display:none; text-align:right;">Fee</th>
                            <th class="bn">মন্তব্য ও তৈরি করেছেন</th><th class="en" style="display:none;">Note & Creator</th>
                            <th style="text-align:right;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transfers as $trf)
                            <tr>
                                <td class="cell-main">
                                    <div style="font-weight:600; font-size:13px;">{{ $trf->transfer_no }}</div>
                                    <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                                        {{ optional($trf->transfer_date)->format('d M, Y') ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight:600; color:#dc2626;">{{ $trf->fromAccount->name ?? '—' }}</span>
                                    <div style="font-size:11px; color:var(--text-muted);">{{ $trf->fromAccount->typeLabel()['bn'] ?? '' }}</div>
                                </td>
                                <td>
                                    <span style="font-weight:600; color:#16a34a;">{{ $trf->toAccount->name ?? '—' }}</span>
                                    <div style="font-size:11px; color:var(--text-muted);">{{ $trf->toAccount->typeLabel()['bn'] ?? '' }}</div>
                                </td>
                                <td style="text-align:right; font-weight:700; font-family:'Manrope',sans-serif; font-size:14px;">
                                    ৳ {{ number_format($trf->amount, 2) }}
                                </td>
                                <td style="text-align:right; font-family:'Manrope',sans-serif; color:{{ $trf->charge > 0 ? '#dc2626' : 'var(--text-muted)' }};">
                                    {{ $trf->charge > 0 ? '৳ '.number_format($trf->charge, 2) : '—' }}
                                </td>
                                <td>
                                    <div style="font-size:13px;">{{ $trf->note ?? '—' }}</div>
                                    <div style="font-size:11px; color:var(--text-muted);">{{ $trf->creator->name ?? 'System' }}</div>
                                </td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content:flex-end;">
                                        <form method="POST" action="{{ route('account-transfers.destroy', $trf) }}" class="delete-form" data-title="ট্রান্সফার বাতিল করবেন?" data-text="এই ট্রান্সফারটি বাতিল করতে চান? সংশ্লিষ্ট অ্যাকাউন্টের ব্যালেন্স আগের অবস্থায় ফিরে যাবে।" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <x-core::button variant="ghost" color="danger" size="xs" icon-only icon="trash-2" type="submit" title="বাতিল করুন" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-core::table.empty
                                        icon="arrow-left-right"
                                        title="কোনো ফান্ড ট্রান্সফার রেকর্ড পাওয়া যায়নি"
                                        title-en="No fund transfers found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>

    {{-- Fund Transfer Modal --}}
    <div class="modal-backdrop @if ($errors->any()) open @endif" id="createTransferModal">
        <div class="modal-box" style="width:640px; max-width:95vw; max-height:90vh; overflow-y:auto;">
            <div class="modal-head">
                <div class="modal-title">
                    <span class="bn">নতুন ফান্ড ট্রান্সফার</span>
                    <span class="en" style="display:none;">New Fund Transfer</span>
                </div>
                <button type="button" class="drawer-x modal-close-btn">&times;</button>
            </div>
            <form method="POST" action="{{ route('account-transfers.store') }}" id="create_transfer_modal_form">
                @csrf
                <input type="hidden" name="redirect_to" value="account-transfers.index">
                @include('finance::transfers._form', ['transfer' => $transfer, 'accounts' => $accounts, 'isModal' => true])
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        function initTransferIndex() {
            if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                setTimeout(initTransferIndex, 20);
                return;
            }

            var $ = window.jQuery;
            $(function () {
                $('#btnOpenTransferModal').on('click', function () {
                    $('#createTransferModal').addClass('open');
                });

                $(document).on('click', '.modal-close-btn', function (e) {
                    e.preventDefault();
                    $(this).closest('.modal-backdrop').removeClass('open');
                });

                $('.modal-backdrop').on('click', function (e) {
                    if ($(e.target).hasClass('modal-backdrop')) {
                        $(this).removeClass('open');
                    }
                });
            });
        }

        initTransferIndex();
    })();
    </script>
    @endpush
</x-core::layout>
