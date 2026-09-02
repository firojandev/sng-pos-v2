<x-core::auth-layout
    title="দোকান নির্বাচন"
    title-en="Select Shop"
    card-title="দোকান নির্বাচন করুন"
    card-title-en="Select Shop"
    card-subtitle="যে দোকানে প্রবেশ করতে চান সেটি বেছে নিন"
    card-subtitle-en="Choose which shop you want to access"
    max-width="560px"
>
    <style>
        .shop-select-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 14px;
            margin-bottom: 20px;
        }
        .shop-select-card {
            border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 14px;
            padding: 16px 18px;
            background: var(--card, #ffffff);
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            color: var(--ink-900, #0f172a);
        }
        .shop-select-card:hover {
            border-color: #0d9488;
            box-shadow: 0 8px 20px -6px rgba(13, 148, 136, 0.2);
            transform: translateY(-2px);
        }
        .shop-select-card.active {
            border-color: #0d9488;
            background: rgba(13, 148, 136, 0.05);
        }
        [data-theme="dark"] .shop-select-card,
        :root[data-theme="dark"] .shop-select-card {
            background: var(--card, #111827);
            border-color: var(--border, #1f293d);
            color: #f8fafc;
        }
        [data-theme="dark"] .shop-select-card:hover,
        :root[data-theme="dark"] .shop-select-card:hover {
            border-color: #14b8a6;
            box-shadow: 0 8px 20px -6px rgba(20, 184, 166, 0.25);
        }
        [data-theme="dark"] .shop-select-card.active,
        :root[data-theme="dark"] .shop-select-card.active {
            border-color: #14b8a6 !important;
            background: rgba(20, 184, 166, 0.12) !important;
        }
        .shop-select-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(13, 148, 136, 0.08);
            color: #0f766e;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(13, 148, 136, 0.2);
        }
        [data-theme="dark"] .shop-select-icon,
        :root[data-theme="dark"] .shop-select-icon {
            background: rgba(20, 184, 166, 0.15);
            color: #2dd4bf;
            border-color: rgba(20, 184, 166, 0.3);
        }
        .shop-select-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--ink-900, #0f172a);
            line-height: 1.3;
        }
        [data-theme="dark"] .shop-select-name,
        :root[data-theme="dark"] .shop-select-name,
        [data-theme="dark"] .shop-select-card.active .shop-select-name,
        :root[data-theme="dark"] .shop-select-card.active .shop-select-name {
            color: #f8fafc !important;
        }
        .shop-select-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            color: var(--ink-600, #475569);
            margin-top: 4px;
            flex-wrap: wrap;
        }
        [data-theme="dark"] .shop-select-meta,
        :root[data-theme="dark"] .shop-select-meta,
        [data-theme="dark"] .shop-select-card.active .shop-select-meta,
        :root[data-theme="dark"] .shop-select-card.active .shop-select-meta {
            color: #94a3b8 !important;
        }
        .shop-select-plan-tag {
            color: #0f766e;
            font-weight: 600;
        }
        [data-theme="dark"] .shop-select-plan-tag,
        :root[data-theme="dark"] .shop-select-plan-tag,
        [data-theme="dark"] .shop-select-card.active .shop-select-plan-tag,
        :root[data-theme="dark"] .shop-select-card.active .shop-select-plan-tag {
            color: #2dd4bf !important;
        }
        .shop-select-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 8px;
            background: #0d9488;
            color: #ffffff !important;
            border: none;
            cursor: pointer;
            transition: background 0.15s ease;
            white-space: nowrap;
        }
        .shop-select-btn:hover {
            background: #0f766e;
        }
        [data-theme="dark"] .shop-select-btn,
        :root[data-theme="dark"] .shop-select-btn {
            background: #0d9488;
            color: #ffffff !important;
        }
        [data-theme="dark"] .shop-select-btn:hover,
        :root[data-theme="dark"] .shop-select-btn:hover {
            background: #14b8a6;
        }
        .shop-select-footer {
            border-top: 1px solid var(--border);
            padding-top: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--ink-600);
            flex-wrap: wrap;
            gap: 10px;
        }
        [data-theme="dark"] .shop-select-footer,
        :root[data-theme="dark"] .shop-select-footer {
            color: #cbd5e1;
        }
    </style>

    @if ($shops->isEmpty())
        <div style="text-align:center; padding:30px 10px; color:var(--ink-500);">
            <x-core::icon name="alert-circle" size="36" style="margin:0 auto 10px; color:var(--gold-600);" />
            <div style="font-weight:700; font-size:15px; color:var(--ink-800); margin-bottom:4px;">
                <span class="bn">কোনো সক্রিয় দোকান পাওয়া যায়নি</span>
                <span class="en" style="display:none;">No Active Shops Found</span>
            </div>
            <p style="font-size:12.5px; color:var(--ink-500); margin:0;">
                <span class="bn">অনুগ্রহ করে সিস্টেম এডমিনের সাথে যোগাযোগ করুন।</span>
                <span class="en" style="display:none;">Please contact the system administrator.</span>
            </p>
        </div>
    @else
        <div class="shop-select-list">
            @foreach ($shops as $shop)
                @php
                    $isCurrent = (int) $currentShopId === (int) $shop->id;
                    $subscription = $shop->activeSubscription;
                @endphp
                <form method="POST" action="{{ route('shops.switch', $shop) }}" class="shop-select-form" style="margin:0;">
                    @csrf
                    <div class="shop-select-card {{ $isCurrent ? 'active' : '' }}" data-shop-id="{{ $shop->id }}">
                        <div style="display:flex; align-items:center; gap:12px; min-width:0; flex:1;">
                            <div class="shop-select-icon">
                                <x-core::icon name="shopping-bag" size="20" />
                            </div>

                            <div style="min-width:0; flex:1;">
                                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                    <span class="shop-select-name">{{ $shop->name }}</span>
                                    @if ($shop->store_code)
                                        <x-core::badge color="teal" size="xs" variant="soft">#{{ $shop->store_code }}</x-core::badge>
                                    @endif
                                    @if ($isCurrent)
                                        <x-core::badge color="green" size="xs" variant="soft" :dot="true">
                                            <span class="bn">বর্তমান সক্রিয়</span>
                                            <span class="en" style="display:none;">Current Active</span>
                                        </x-core::badge>
                                    @endif
                                </div>

                                <div class="shop-select-meta">
                                    @if ($shop->phone)
                                        <span style="font-family:monospace;">{{ $shop->phone }}</span>
                                    @endif
                                    @if ($shop->phone && $shop->address)
                                        <span>•</span>
                                    @endif
                                    @if ($shop->address)
                                        <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px;">{{ $shop->address }}</span>
                                    @endif
                                    @if ($subscription && $subscription->plan)
                                        <span>•</span>
                                        <span class="shop-select-plan-tag">{{ $subscription->plan->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="shop-select-btn">
                                <span class="bn">প্রবেশ করুন</span>
                                <span class="en" style="display:none;">Enter</span>
                                <x-core::icon name="arrow-right" size="14" />
                            </button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>
    @endif

    <div class="shop-select-footer">
        <div style="display:flex; align-items:center; gap:8px;">
            <div style="width:26px; height:26px; border-radius:50%; background:var(--ink-100); color:var(--ink-700); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11px;">
                {{ mb_substr($user->name ?? '?', 0, 1) }}
            </div>
            <div>
                <span style="font-weight:600; color:var(--ink-800);">{{ $user->name ?? 'User' }}</span>
                <span style="color:var(--ink-400); font-size:11px;">({{ $user->email ?? '' }})</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" style="background:none; border:none; color:var(--red-600); font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:4px; font-size:12px; padding:4px 8px; border-radius:6px;">
                <x-core::icon name="log-out" size="13" />
                <span class="bn">লগ আউট</span>
                <span class="en" style="display:none;">Logout</span>
            </button>
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        function initShopSelection() {
            if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                setTimeout(initShopSelection, 30);
                return;
            }
            var $ = window.jQuery;
            $(document).on('click', '.shop-select-card', function (e) {
                if ($(e.target).closest('button').length) {
                    return;
                }
                $(this).closest('form.shop-select-form').submit();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initShopSelection);
        } else {
            initShopSelection();
        }
    })();
    </script>
    @endpush
</x-core::auth-layout>
