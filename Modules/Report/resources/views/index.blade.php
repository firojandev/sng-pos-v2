<x-core::layout
    title="রিপোর্ট"
    title-en="Reports"
    subtitle="আপনার দোকানের জন্য উপলব্ধ ব্যবসায়িক রিপোর্টসমূহ"
    subtitle-en="Business reports available for your shop"
    active="reports"
>
    @if ($cards->isEmpty())
        <div class="panel" style="margin-top:0;">
            <div class="panel-body">
                <x-core::table.empty
                    icon="file-text"
                    title="কোনো রিপোর্ট সক্রিয় নেই"
                    title-en="No reports enabled"
                    description="আপনার সাবস্ক্রিপশন প্ল্যানে কোনো রিপোর্ট মডিউল অন্তর্ভুক্ত নেই।"
                    description-en="Your subscription plan does not include any report modules."
                />
            </div>
        </div>
    @else
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:14px;">
            @foreach ($cards as $card)
                <a href="{{ route($card['route']) }}" class="panel" style="margin-top:0; padding:18px; display:flex; align-items:center; gap:12px; text-decoration:none;">
                    <div style="width:40px; height:40px; border-radius:10px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="file-text" size="19" />
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px; color:var(--ink-900);">
                            <span class="bn">{{ $card['bn'] }}</span>
                            <span class="en" style="display:none;">{{ $card['en'] }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-core::layout>
