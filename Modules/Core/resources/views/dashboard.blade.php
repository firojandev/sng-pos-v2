<x-core::layout
    title="ড্যাশবোর্ড"
    title-en="Dashboard"
    subtitle="আজ, {{ now()->format('d F Y') }} — আপনার ব্যবসার সারসংক্ষেপ"
    subtitle-en="Today, {{ now()->format('d F Y') }} — your business at a glance"
    active="dashboard"
>
    <div class="stat-grid">
        <div class="stat-card">
            <div class="ic" style="background:var(--teal-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 4h2l2.2 11.5a2 2 0 0 0 2 1.6h6.6a2 2 0 0 0 2-1.6L20 8H7" stroke="var(--teal-800)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="val">৳৪,৮২,৩০০</div>
            <div class="lbl bn">এই মাসের বিক্রয়</div>
            <div class="lbl en" style="display:none;">Sales This Month</div>
            <div class="trend trend-up">+১২%</div>
        </div>
        <div class="stat-card">
            <div class="ic" style="background:var(--gold-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 7h18l-1.5 10.5a2 2 0 0 1-2 1.5H6.5a2 2 0 0 1-2-1.5L3 7Z" stroke="#8A611B" stroke-width="1.7" stroke-linejoin="round"/></svg>
            </div>
            <div class="val">৳৩,১৫,২০০</div>
            <div class="lbl bn">এই মাসের ক্রয়</div>
            <div class="lbl en" style="display:none;">Purchase This Month</div>
            <div class="trend trend-up">+৫%</div>
        </div>
        <div class="stat-card">
            <div class="ic" style="background:var(--red-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 12h16M4 12l5-5M4 12l5 5" stroke="var(--red-600)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div class="val">৳৩৪,২০০</div>
            <div class="lbl bn">গ্রাহকের বাকি (পাওনা)</div>
            <div class="lbl en" style="display:none;">Customer Due (Receivable)</div>
            <div class="trend trend-down">১৮ জন</div>
        </div>
        <div class="stat-card">
            <div class="ic" style="background:var(--green-100);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="7" width="18" height="12" rx="2" stroke="var(--green-600)" stroke-width="1.8"/></svg>
            </div>
            <div class="val">৳৮,৯২,৪০০</div>
            <div class="lbl bn">মজুদ মূল্য</div>
            <div class="lbl en" style="display:none;">Stock Value</div>
            <div class="trend b-grey">১৪২ <span class="bn">পণ্য</span><span class="en" style="display:none;">SKUs</span></div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-title bn">সাম্প্রতিক বিক্রয়</div>
            <div class="panel-title en" style="display:none;">Recent Sales</div>
            <a class="btn btn-outline btn-sm" href="{{ route('sales.index') }}">
                <span class="bn">সব দেখুন</span><span class="en">View all</span>
            </a>
        </div>
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">ইনভয়েস</th><th class="en" style="display:none;">Invoice</th>
                            <th class="bn">গ্রাহক</th><th class="en" style="display:none;">Customer</th>
                            <th class="bn">তারিখ</th><th class="en" style="display:none;">Date</th>
                            <th class="bn">মোট</th><th class="en" style="display:none;">Total</th>
                            <th class="bn">স্ট্যাটাস</th><th class="en" style="display:none;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="cell-main">#SL-1042</td><td>করিম মিয়া</td><td>২৪ আগস্ট, ২০২৬</td><td>৳৮৪৫০</td>
                            <td><span class="badge b-green bn">পরিশোধিত</span><span class="badge b-green en" style="display:none;">Paid</span></td>
                        </tr>
                        <tr>
                            <td class="cell-main">#SL-1041</td><td>সালমা বেগম</td><td>২৪ আগস্ট, ২০২৬</td><td>৳১,২০০</td>
                            <td><span class="badge b-red bn">বাকি</span><span class="badge b-red en" style="display:none;">Due</span></td>
                        </tr>
                        <tr>
                            <td class="cell-main">#SL-1040</td><td>আব্বাস হার্ডওয়্যার</td><td>২৪ আগস্ট, ২০২৬</td><td>৳৪,৫০০</td>
                            <td><span class="badge b-gold bn">আংশিক</span><span class="badge b-gold en" style="display:none;">Partial</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px;" class="dash-2col">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title bn">স্টক কম</div>
                <div class="panel-title en" style="display:none;">Low Stock</div>
            </div>
            <div class="panel-body">
                <div class="mini-card" style="margin-bottom:8px;">
                    <div class="foot">
                        <span class="nm bn">সয়াবিন তেল ৫ লিটার</span><span class="nm en" style="display:none;">Soybean Oil 5L</span>
                        <span class="badge b-red bn">৩ পিস বাকি</span><span class="badge b-red en" style="display:none;">3 left</span>
                    </div>
                </div>
                <div class="mini-card" style="margin-bottom:8px;">
                    <div class="foot">
                        <span class="nm bn">লবণ ১ কেজি</span><span class="nm en" style="display:none;">Salt 1kg</span>
                        <span class="badge b-red bn">৬ পিস বাকি</span><span class="badge b-red en" style="display:none;">6 left</span>
                    </div>
                </div>
                <div class="mini-card">
                    <div class="foot">
                        <span class="nm bn">দেশলাই বক্স</span><span class="nm en" style="display:none;">Matchbox</span>
                        <span class="badge b-red bn">৯ পিস বাকি</span><span class="badge b-red en" style="display:none;">9 left</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title bn">আয় বনাম ব্যয় (এই মাস)</div>
                <div class="panel-title en" style="display:none;">Income vs Expense (This Month)</div>
            </div>
            <div class="panel-body">
                <div class="mini-card" style="margin-bottom:8px;">
                    <div class="foot">
                        <span class="nm bn">মোট আয়</span><span class="nm en" style="display:none;">Total Income</span>
                        <b style="color:var(--green-600); font-family:'Manrope',sans-serif;">৳৫,১০,০০০</b>
                    </div>
                </div>
                <div class="mini-card">
                    <div class="foot">
                        <span class="nm bn">মোট ব্যয়</span><span class="nm en" style="display:none;">Total Expense</span>
                        <b style="color:var(--red-600); font-family:'Manrope',sans-serif;">৳৯৮,৫০০</b>
                    </div>
                </div>
                <div class="helper bn">নিট মুনাফা: <b>৳৪,১১,৫০০</b> — গত মাসের চেয়ে ৯% বেশি।</div>
                <div class="helper en" style="display:none;">Net profit: <b>৳4,11,500</b> — 9% higher than last month.</div>
            </div>
        </div>
    </div>
</x-core::layout>
