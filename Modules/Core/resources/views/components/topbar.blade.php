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
        <h1 class="en" style="display:none;">{{ $titleEn }}</h1>
        @if ($subtitle || $subtitleEn)
            <p class="bn">{{ $subtitle }}</p>
            <p class="en" style="display:none;">{{ $subtitleEn }}</p>
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
        <div class="themeswitch">
            <button id="theme-light" onclick="setTheme('light')" title="Light Theme" aria-label="Light theme">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="4.3" stroke="currentColor" stroke-width="1.8"/><path d="M12 2.5v2.4M12 19.1v2.4M4.4 4.4l1.7 1.7M17.9 17.9l1.7 1.7M2.5 12h2.4M19.1 12h2.4M4.4 19.6l1.7-1.7M17.9 6.1l1.7-1.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
            <button id="theme-dark" onclick="setTheme('dark')" title="Dark Theme" aria-label="Dark theme">
                <svg viewBox="0 0 24 24" fill="none"><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
            </button>
        </div>

        <div class="langswitch">
            <button id="btn-bn" class="active" onclick="setLang('bn')" aria-label="বাংলা ভাষা">বাং</button>
            <button id="btn-en" onclick="setLang('en')" aria-label="English language">EN</button>
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
