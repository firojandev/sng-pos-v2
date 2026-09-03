<x-core::layout
    title="{{ $account->name }} - স্টেটমেন্ট"
    title-en="{{ $account->name }} - Ledger"
    subtitle="অ্যাকাউন্টের যাবতীয় জমা, খরচ ও লেনদেন বিবরণী"
    subtitle-en="Account statement and transaction history"
    active="accounts"
>

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

    {{-- Daily Cash In, Out & Net Change Graph --}}
    <div class="panel" style="margin-top:0; margin-bottom:16px;">
        <div class="panel-body" style="padding:18px 20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                <div>
                    <h3 style="font-size:15px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="color:var(--primary);"><path d="M3 3v18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 16l4-4 4 4 5-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="bn">দৈনিক ক্যাশ ইন, আউট ও নেট পরিবর্তন গ্রাফ</span>
                        <span class="en" style="display:none;">Daily Cash In, Out & Net Change Graph</span>
                    </h3>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:3px;">
                        <span class="bn">{{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }} হতে {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }} পর্যন্ত দৈনিক চিত্র</span>
                        <span class="en" style="display:none;">Daily flow from {{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }} to {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}</span>
                    </div>
                </div>

                {{-- Summary Badges --}}
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:6px; font-size:12px; background:rgba(22, 163, 74, 0.08); border:1px solid rgba(22, 163, 74, 0.25); padding:4px 10px; border-radius:6px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:2px; background:#16a34a;"></span>
                        <span class="bn" style="color:#15803d;">মোট জমা: <b>৳ {{ number_format($chartTotalIn, 2) }}</b></span>
                        <span class="en" style="display:none; color:#15803d;">Total In: <b>৳ {{ number_format($chartTotalIn, 2) }}</b></span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px; font-size:12px; background:rgba(220, 38, 38, 0.08); border:1px solid rgba(220, 38, 38, 0.25); padding:4px 10px; border-radius:6px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:2px; background:#dc2626;"></span>
                        <span class="bn" style="color:#b91c1c;">মোট খরচ: <b>৳ {{ number_format($chartTotalOut, 2) }}</b></span>
                        <span class="en" style="display:none; color:#b91c1c;">Total Out: <b>৳ {{ number_format($chartTotalOut, 2) }}</b></span>
                    </div>
                    @php $net = $chartTotalIn - $chartTotalOut; @endphp
                    <div style="display:flex; align-items:center; gap:6px; font-size:12px; background:{{ $net >= 0 ? 'rgba(37, 99, 235, 0.08)' : 'rgba(220, 38, 38, 0.08)' }}; border:1px solid {{ $net >= 0 ? 'rgba(37, 99, 235, 0.25)' : 'rgba(220, 38, 38, 0.25)' }}; padding:4px 10px; border-radius:6px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $net >= 0 ? '#2563eb' : '#dc2626' }};"></span>
                        <span class="bn" style="color:{{ $net >= 0 ? '#1d4ed8' : '#b91c1c' }};">নেট পরিবর্তন: <b>৳ {{ number_format($net, 2) }}</b></span>
                        <span class="en" style="display:none; color:{{ $net >= 0 ? '#1d4ed8' : '#b91c1c' }};">Net Change: <b>৳ {{ number_format($net, 2) }}</b></span>
                    </div>
                </div>
            </div>

            <div style="position:relative; height:280px; width:100%;">
                <canvas id="dailyCashflowChart"></canvas>
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
                        <option value="" data-text-bn="সকল ধরন" data-text-en="All Types">সকল ধরন</option>
                        <option value="in" data-text-bn="জমা" data-text-en="In / Credit" {{ $type === 'in' ? 'selected' : '' }}>জমা</option>
                        <option value="out" data-text-bn="খরচ" data-text-en="Out / Debit" {{ $type === 'out' ? 'selected' : '' }}>খরচ</option>
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        function initLedgerChart() {
            if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined' || typeof window.Chart === 'undefined') {
                setTimeout(initLedgerChart, 30);
                return;
            }

            var $ = window.jQuery;
            $(function () {
                var canvas = document.getElementById('dailyCashflowChart');
                if (!canvas) return;

                var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                var isEn = document.documentElement.classList.contains('lang-en');

                var labels = @json($chartData['labels']);
                var cashIn = @json($chartData['cash_in']);
                var cashOut = @json($chartData['cash_out']);
                var netChange = @json($chartData['net_change']);

                var gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
                var textColor = isDark ? '#94a3b8' : '#64748b';

                var inLabel = isEn ? 'Cash In' : 'জমা (Cash In)';
                var outLabel = isEn ? 'Cash Out' : 'খরচ (Cash Out)';
                var netLabel = isEn ? 'Net Change' : 'নেট পরিবর্তন (Net Change)';

                new window.Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                type: 'line',
                                label: netLabel,
                                data: netChange,
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                                borderWidth: 2.5,
                                tension: 0.35,
                                pointRadius: 3.5,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#2563eb',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5,
                                fill: false,
                                order: 1
                            },
                            {
                                type: 'bar',
                                label: inLabel,
                                data: cashIn,
                                backgroundColor: 'rgba(22, 163, 74, 0.8)',
                                borderColor: '#16a34a',
                                borderWidth: 1,
                                borderRadius: 4,
                                order: 2
                            },
                            {
                                type: 'bar',
                                label: outLabel,
                                data: cashOut,
                                backgroundColor: 'rgba(220, 38, 38, 0.8)',
                                borderColor: '#dc2626',
                                borderWidth: 1,
                                borderRadius: 4,
                                order: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: {
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    usePointStyle: true,
                                    color: isDark ? '#e2e8f0' : '#334155',
                                    font: {
                                        family: "'Noto Sans Bengali', 'Plus Jakarta Sans', sans-serif",
                                        size: 12,
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                titleColor: isDark ? '#f8fafc' : '#0f172a',
                                bodyColor: isDark ? '#cbd5e1' : '#334155',
                                borderColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                                borderWidth: 1,
                                padding: 10,
                                titleFont: {
                                    family: "'Noto Sans Bengali', 'Plus Jakarta Sans', sans-serif",
                                    size: 13,
                                    weight: '700'
                                },
                                bodyFont: {
                                    family: "'Noto Sans Bengali', 'Manrope', sans-serif",
                                    size: 12
                                },
                                callbacks: {
                                    label: function (context) {
                                        var val = context.parsed.y !== null ? context.parsed.y : 0;
                                        return (context.dataset.label || '') + ': ৳ ' + Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: gridColor,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: textColor,
                                    font: {
                                        family: "'Noto Sans Bengali', 'Manrope', sans-serif",
                                        size: 11
                                    },
                                    maxRotation: 45
                                }
                            },
                            y: {
                                grid: {
                                    color: gridColor,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: textColor,
                                    font: {
                                        family: "'Manrope', sans-serif",
                                        size: 11
                                    },
                                    callback: function (val) {
                                        if (Math.abs(val) >= 1000000) {
                                            return '৳ ' + (val / 1000000).toFixed(1) + 'M';
                                        }
                                        if (Math.abs(val) >= 1000) {
                                            return '৳ ' + (val / 1000).toFixed(1) + 'k';
                                        }
                                        return '৳ ' + val;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }

        initLedgerChart();
    })();
    </script>
    @endpush
</x-core::layout>
