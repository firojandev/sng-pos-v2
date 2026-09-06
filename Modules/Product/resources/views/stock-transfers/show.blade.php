<x-core::layout
    title="ট্রান্সফার বিবরণ"
    title-en="Transfer Details"
    subtitle="স্টক ট্রান্সফারের সম্পূর্ণ বিবরণ"
    subtitle-en="View transfer movement details"
    active="stock-transfers"
>
    <div style="max-width:800px; margin:0 auto;">
        <div style="margin-bottom:16px;">
            <x-core::button :href="route('stock-transfers.index')" size="sm" variant="secondary" icon="arrow-left">
                <span class="bn">সকল ট্রান্সফার</span><span class="en" style="display:none;">All Transfers</span>
            </x-core::button>
        </div>

        <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow-card);">
            @include('product::stock-transfers._detail_drawer', ['transfer' => $transfer])
        </div>
    </div>
</x-core::layout>
