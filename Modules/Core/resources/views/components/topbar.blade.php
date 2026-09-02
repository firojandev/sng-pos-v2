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
            <div class="shop-switcher-dropdown" style="position: relative;">
                <button type="button" class="btn-shop-switcher" style="display:inline-flex; align-items:center; gap:6px; font-weight:600; font-size:12px; padding:5px 12px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--ink-800); cursor:pointer; height:32px; transition:all 0.15s ease;" title="দোকান পরিবর্তন করুন / Switch Shop">
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
                <div class="shop-switcher-menu" style="display:none; position:absolute; top:calc(100% + 6px); right:0; min-width:240px; background:var(--card); border:1px solid var(--border); border-radius:10px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.18); padding:6px; z-index:100;">
                    <div style="font-size:10.5px; font-weight:700; color:var(--ink-500); padding:4px 8px 6px; text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; justify-content:space-between;">
                        <span>
                            <span class="bn">দোকান পরিবর্তন করুন</span>
                            <span class="en" style="display:none;">Switch Shop</span>
                        </span>
                        <span style="font-size:10px; font-weight:600; color:var(--teal-800); background:var(--teal-50); padding:1px 6px; border-radius:4px;">
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
                                class="shop-item-btn"
                                style="width:100%; text-align:left; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:8px 10px; border-radius:6px; border:none; background:{{ $isActiveShop ? 'var(--teal-50, rgba(13,148,136,0.08))' : 'transparent' }}; color:{{ $isActiveShop ? 'var(--teal-900)' : 'var(--ink-800)' }}; cursor:pointer; font-size:12px; font-weight:{{ $isActiveShop ? '700' : '500' }}; transition:background 0.12s ease;"
                                onmouseover="this.style.background='{{ $isActiveShop ? 'var(--teal-50, rgba(13,148,136,0.12))' : 'var(--paper, rgba(0,0,0,0.04))' }}'"
                                onmouseout="this.style.background='{{ $isActiveShop ? 'var(--teal-50, rgba(13,148,136,0.08))' : 'transparent' }}'"
                            >
                                <span style="display:flex; align-items:center; gap:8px; overflow:hidden; min-width:0;">
                                    <span style="width:7px; height:7px; border-radius:50%; background:{{ $isActiveShop ? 'var(--teal-600)' : 'var(--ink-300)' }}; flex-shrink:0;"></span>
                                    <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $s->name }}</span>
                                </span>
                                <span style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                                    @if ($s->store_code)
                                        <span style="font-size:10px; font-family:monospace; color:var(--ink-500);">#{{ $s->store_code }}</span>
                                    @endif
                                    @if ($isActiveShop)
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--teal-700)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </form>
                    @endforeach
                    <div style="border-top:1px solid var(--border); margin:4px 0; padding-top:4px;">
                        <a href="{{ route('shops.select') }}" style="display:flex; align-items:center; gap:6px; padding:6px 8px; font-size:11.5px; color:var(--teal-800); text-decoration:none; font-weight:600; border-radius:6px;" onmouseover="this.style.background='var(--paper, rgba(0,0,0,0.04))'" onmouseout="this.style.background='transparent'">
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
