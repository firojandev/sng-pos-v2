@props([
    'title' => '',
    'titleEn' => '',
    'subtitle' => '',
    'subtitleEn' => '',
])

<header class="topbar">
    <button class="sidebar-toggle-btn" onclick="toggleSidebar()" aria-label="সাইডবার টগল / Toggle Sidebar" title="সাইডবার টগল / Toggle Sidebar">
        <svg class="toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="9" y1="3" x2="9" y2="21"></line>
            <path class="arrow-indicator" d="m14 9-3 3 3 3"></path>
        </svg>
    </button>

    <div class="titles">
        <h1 class="bn">{{ $title }}</h1>
        <h1 class="en">{{ $titleEn }}</h1>
        @if ($subtitle || $subtitleEn)
            <p class="bn">{{ $subtitle }}</p>
            <p class="en">{{ $subtitleEn }}</p>
        @endif
    </div>

    <div class="top-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
            <path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <input placeholder="খুঁজুন..." class="bn-ph" aria-label="Search">
        <span class="search-kbd">⌘K</span>
    </div>

    <div class="top-actions">
        @php
            $authUser = auth()->user();
            $accessibleShops = $authUser && ! $authUser->isSuperAdmin()
                ? $authUser->activeShops()->get()
                : collect();
            $currentShop = $authUser?->shop;
        @endphp

        @if ($accessibleShops->count() > 1)
            <style>
                .shop-switcher-dropdown { position: relative; }
                .btn-shop-switcher {
                    display: inline-flex; align-items: center; gap: 6px;
                    font-weight: 600; font-size: 12px; padding: 5px 12px;
                    border-radius: 8px; border: 1px solid var(--border);
                    background: var(--card); color: var(--ink-800);
                    cursor: pointer; height: 32px; transition: all 0.15s ease;
                }
                .btn-shop-switcher:hover { border-color: #0d9488; }
                .shop-switcher-menu {
                    display: none; position: absolute; top: calc(100% + 6px);
                    right: 0; min-width: 240px; background: var(--card);
                    border: 1px solid var(--border); border-radius: 10px;
                    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.25);
                    padding: 6px; z-index: 100;
                }
                .shop-switcher-header {
                    font-size: 10.5px; font-weight: 700; color: var(--ink-500);
                    padding: 4px 8px 6px; text-transform: uppercase;
                    letter-spacing: 0.5px; display: flex; align-items: center;
                    justify-content: space-between;
                }
                .shop-count-badge {
                    font-size: 10px; font-weight: 600; color: #0f766e;
                    background: rgba(13, 148, 136, 0.1); padding: 1px 6px;
                    border-radius: 4px;
                }
                [data-theme="dark"] .shop-count-badge,
                :root[data-theme="dark"] .shop-count-badge {
                    color: #2dd4bf; background: rgba(20, 184, 166, 0.18);
                }
                .shop-item-btn {
                    width: 100%; text-align: left; display: flex;
                    align-items: center; justify-content: space-between;
                    gap: 8px; padding: 8px 10px; border-radius: 6px;
                    border: none; background: transparent; color: var(--ink-800);
                    cursor: pointer; font-size: 12px; font-weight: 500;
                    transition: all 0.12s ease;
                }
                .shop-item-btn:hover {
                    background: var(--paper, rgba(0, 0, 0, 0.04));
                    color: var(--ink-900);
                }
                .shop-item-btn.is-active {
                    background: rgba(13, 148, 136, 0.08);
                    color: #0f766e;
                    font-weight: 700;
                }
                .shop-item-btn.is-active:hover {
                    background: rgba(13, 148, 136, 0.14);
                }
                [data-theme="dark"] .shop-item-btn,
                :root[data-theme="dark"] .shop-item-btn {
                    color: #cbd5e1;
                }
                [data-theme="dark"] .shop-item-btn:hover,
                :root[data-theme="dark"] .shop-item-btn:hover {
                    background: rgba(255, 255, 255, 0.06);
                    color: #ffffff;
                }
                [data-theme="dark"] .shop-item-btn.is-active,
                :root[data-theme="dark"] .shop-item-btn.is-active {
                    background: rgba(20, 184, 166, 0.18) !important;
                    color: #2dd4bf !important;
                }
                [data-theme="dark"] .shop-item-btn.is-active:hover,
                :root[data-theme="dark"] .shop-item-btn.is-active:hover {
                    background: rgba(20, 184, 166, 0.25) !important;
                    color: #5eead4 !important;
                }
                .shop-code-tag {
                    font-size: 10px; font-family: monospace; color: var(--ink-500);
                }
                .shop-item-btn.is-active .shop-code-tag {
                    color: #0f766e;
                }
                [data-theme="dark"] .shop-item-btn.is-active .shop-code-tag,
                :root[data-theme="dark"] .shop-item-btn.is-active .shop-code-tag {
                    color: #2dd4bf !important; opacity: 0.9;
                }
                .shop-switcher-all-link {
                    display: flex; align-items: center; gap: 6px;
                    padding: 6px 8px; font-size: 11.5px; color: #0f766e;
                    text-decoration: none; font-weight: 600; border-radius: 6px;
                    transition: background 0.12s ease;
                }
                .shop-switcher-all-link:hover {
                    background: var(--paper, rgba(0, 0, 0, 0.04));
                }
                [data-theme="dark"] .shop-switcher-all-link,
                :root[data-theme="dark"] .shop-switcher-all-link {
                    color: #2dd4bf;
                }
                [data-theme="dark"] .shop-switcher-all-link:hover,
                :root[data-theme="dark"] .shop-switcher-all-link:hover {
                    background: rgba(255, 255, 255, 0.06);
                }
            </style>
            <div class="shop-switcher-dropdown">
                <button type="button" class="btn-shop-switcher" title="দোকান পরিবর্তন করুন / Switch Shop">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--teal-800); flex-shrink:0;">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $currentShop?->name ?? 'দোকান পরিবর্তন' }}
                    </span>
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; color:var(--ink-500);">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div class="shop-switcher-menu">
                    <div class="shop-switcher-header">
                        <span>
                            <span class="bn">দোকান পরিবর্তন করুন</span>
                            <span class="en" style="display:none;">Switch Shop</span>
                        </span>
                        <span class="shop-count-badge">
                            {{ $accessibleShops->count() }} টি দোকান
                        </span>
                    </div>
                    @foreach ($accessibleShops as $s)
                        @php
                            $isActiveShop = $currentShop && $currentShop->id === $s->id;
                        @endphp
                        <form method="POST" action="{{ route('shops.switch', $s) }}" style="margin:0;">
                            @csrf
                            <button
                                type="submit"
                                class="shop-item-btn {{ $isActiveShop ? 'is-active' : '' }}"
                            >
                                <span style="display:flex; align-items:center; gap:8px; overflow:hidden; min-width:0;">
                                    <span style="width:7px; height:7px; border-radius:50%; background:{{ $isActiveShop ? '#14b8a6' : 'var(--ink-300)' }}; flex-shrink:0;"></span>
                                    <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $s->name }}</span>
                                </span>
                                <span style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                                    @if ($s->store_code)
                                        <span class="shop-code-tag">#{{ $s->store_code }}</span>
                                    @endif
                                    @if ($isActiveShop)
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </form>
                    @endforeach
                    <div style="border-top:1px solid var(--border); margin:4px 0; padding-top:4px;">
                        <a href="{{ route('shops.select') }}" class="shop-switcher-all-link">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                            <span class="bn">সকল দোকান নির্বাচন পেজ</span>
                            <span class="en" style="display:none;">All Shops Selection</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="topbar-switchers">
            <x-core::theme-switcher />

            <div class="topbar-switcher-divider"></div>

            <x-core::lang-switcher />
        </div>

        <div class="icbtn" onclick="toast('৩টি নতুন নোটিফিকেশন','3 new notifications')" title="নোটিফিকেশন / Notifications" aria-label="Notifications">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M12 3C9 3 7 5.3 7 8.2V11c0 1-.4 2-1.1 2.7L5 14.6V16h14v-1.4l-.9-.9C17.4 13 17 12 17 11V8.2C17 5.3 15 3 12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            </svg>
            <div class="dot"></div>
        </div>

        <div class="top-avatar" title="{{ auth()->user()->name ?? 'User' }}">
            {{ mb_substr(auth()->user()->name ?? '?', 0, 1) }}
        </div>
    </div>
</header>
