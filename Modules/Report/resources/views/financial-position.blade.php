<x-core::layout
    title="আর্থিক অবস্থান"
    title-en="Financial Position"
    subtitle="বর্তমান সম্পদ, পাওনা ও দেনার সারসংক্ষেপ"
    subtitle-en="A snapshot of what the business currently owns and is owed"
    active="report-financial-position"
>
    <x-report::tabbar active="financial-position" />

    <div class="report-printable-area">
        @include('report::partials._snapshot-print-header', [
            'reportTitle' => 'আর্থিক অবস্থান রিপোর্ট',
            'reportTitleEn' => 'Financial Position Report',
            'printPermission' => 'report-financial-position.print',
        ])

        @unless ($asOf->isToday())
            <div style="margin-bottom:16px; padding:10px 14px; border-radius:8px; background:var(--gold-100); color:var(--gold-ink); font-size:12px;">
                <span class="bn">দ্রষ্টব্য: ধার, দেনা, জামানত, গ্রাহকের পাওনা ও সরবরাহকারীর দেনা তাদের বর্তমান অবস্থা অনুযায়ী দেখানো হয়েছে, নির্বাচিত তারিখের প্রকৃত অবস্থা নয় — এই আইটেমগুলোর ইতিহাস রক্ষণাবেক্ষণ করা হয় না।</span>
                <span class="en" style="display:none;">Note: Lend, Debts, Security Money, Customer Receivable, and Supplier Due reflect their current recorded status, not necessarily their exact status on the selected date — status history isn't tracked for these items.</span>
            </div>
        @endunless

        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="shield"
                color="gold"
                value-color="gold"
                :value="'৳' . number_format($totalAssetsWithSecurity, 2)"
                label="মোট সম্পদ (জামানতসহ)"
                label-en="Total Assets (with Security)"
            />
            <x-core::stat-card
                icon="layers"
                color="blue"
                value-color="blue"
                :value="'৳' . number_format($stockValue, 2)"
                label="মজুদ পণ্যের মূল্য"
                label-en="Inventory / Stock Value"
            />
            <x-core::stat-card
                icon="trending-up"
                color="green"
                value-color="green"
                :value="'৳' . number_format($receivable, 2)"
                label="গ্রাহকের কাছে পাওনা"
                label-en="Receivable from Customers"
            />
            <x-core::stat-card
                icon="trending-down"
                color="red"
                value-color="red"
                :value="'৳' . number_format($payable, 2)"
                label="সরবরাহকারীকে দেনা"
                label-en="Supplier Due"
            />
            <x-core::stat-card
                icon="arrow-left-right"
                color="teal"
                value-color="teal"
                :value="'৳' . number_format($lendOutstanding, 2)"
                label="ধার (বকেয়া)"
                label-en="Lend (Outstanding)"
            />
            <x-core::stat-card
                icon="wallet"
                color="primary"
                value-color="primary"
                :value="'৳' . number_format($cashAndBank, 2)"
                label="ক্যাশ ও ব্যাংক ব্যালেন্স"
                label-en="Cash & Bank Balance"
            />
        </div>

        <div class="table-container table-teal">
            <div class="panel-head" style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                    <span class="bn">নিট আর্থিক অবস্থান</span>
                    <span class="en" style="display:none;">Net Financial Position</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <tbody>
                        <tr>
                            <td class="cell-main">
                                <span class="bn">মোট সম্পদ, পাওনা ও ব্যালেন্স বিয়োগ সরবরাহকারীর দেনা</span>
                                <span class="en" style="display:none;">Total assets, receivables & balances minus supplier due</span>
                            </td>
                            <td style="text-align:right; font-weight:800; font-size:15px; color:{{ $netPosition >= 0 ? 'var(--green-ink, #059669)' : 'var(--red-ink, #dc2626)' }};">
                                ৳{{ number_format($netPosition, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @can('report-financial-position.print')
        <div class="report-print-footer" style="display:none;">
            <div>
                <span class="bn">এটি একটি কম্পিউটার প্রস্তুতকৃত রিপোর্ট &middot; {{ auth()->user()?->shop?->name ?? 'POS' }}</span>
                <span class="en" style="display:none;">Computer generated report &middot; {{ auth()->user()?->shop?->name ?? 'POS' }}</span>
            </div>
            <div>
                <span class="bn">মুদ্রণ সময়: {{ now()->format('d M Y, h:i A') }}</span>
                <span class="en" style="display:none;">Printed: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        @endcan
    </div>
</x-core::layout>
