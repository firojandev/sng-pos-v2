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
        <div class="topbar-switchers">
            <x-core::toggle
                id="theme-toggle"
                color="primary"
                size="sm"
                icon-on="moon"
                icon-off="sun"
                label-off="লাইট"
                label-off-en="Light"
                label-on="ডার্ক"
                label-on-en="Dark"
            />

            <div class="topbar-switcher-divider"></div>

            <x-core::toggle
                id="lang-toggle"
                color="primary"
                size="sm"
                label-off="বাংলা"
                label-off-en="Bangla"
                label-on="English"
                label-on-en="English"
            />
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
