<x-core::layout
    title="দ্রুত বেচা"
    title-en="Quick Sale"
    subtitle="এক ক্লিকে দ্রুত নগদ বা ব্যাংক বিক্রয় যোগ করুন"
    subtitle-en="Add a quick counter sale via cash, bank, or both"
    active="quick-sale"
>
    <div style="padding:40px 24px; text-align:center; max-width:540px; margin:40px auto; background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow-card);">
        <div style="width:56px; height:56px; border-radius:14px; background:var(--teal-100); color:var(--teal-800); display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px;">
            <x-core::icon name="sparkles" size="28" />
        </div>
        <h2 style="font-size:18px; font-weight:700; color:var(--ink-900); margin:0 0 8px;">
            <span class="bn">দ্রুত বেচা মোডাল</span>
            <span class="en" style="display:none;">Quick Sale Modal</span>
        </h2>
        <p style="font-size:13px; color:var(--ink-600); margin:0 0 24px; line-height:1.6;">
            <span class="bn">এখন থেকে যেকোনো পেজ (যেমন পণ্য তৈরি, ক্রয়, গ্রাহক ইত্যাদি) থেকে কোনো তথ্য না হারিয়ে <strong>Alt+Q</strong> বা হেডার/সাইডবারের <strong>দ্রুত বেচা</strong> বাটনে ক্লিক করে সাথে সাথে বিক্রি সম্পন্ন করতে পারবেন।</span>
            <span class="en" style="display:none;">You can now record quick sales from any page (e.g. product creation) without losing your work by pressing <strong>Alt+Q</strong> or clicking the <strong>Quick Sale</strong> button.</span>
        </p>
        <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
            <x-core::button
                type="button"
                color="primary"
                size="sm"
                icon="sparkles"
                onclick="openQuickSaleModal()"
            >
                <span class="bn">দ্রুত বেচা মোডাল খুলুন</span>
                <span class="en" style="display:none;">Open Quick Sale Modal</span>
            </x-core::button>
            <x-core::button
                href="{{ route('sales.index') }}"
                variant="secondary"
                size="sm"
                icon="arrow-left"
            >
                <span class="bn">বিক্রয় তালিকা</span>
                <span class="en" style="display:none;">Sales List</span>
            </x-core::button>
        </div>
    </div>

    @push('scripts')
    <script>
        $(function () {
            if (typeof openQuickSaleModal === 'function') {
                openQuickSaleModal();
            }
        });
    </script>
    @endpush
</x-core::layout>
