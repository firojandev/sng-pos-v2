@props([
    'title' => '',
    'titleEn' => '',
    'subtitle' => '',
    'subtitleEn' => '',
])

<header class="topbar">
    <button class="hamburger" onclick="toggleSidebar(true)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M3 6h18M3 12h18M3 18h18" stroke="var(--ink-900)" stroke-width="1.9" stroke-linecap="round"/>
        </svg>
    </button>

    <div class="titles">
        <h1 class="bn">{{ $title }}</h1>
        <h1 class="en" style="display:none;">{{ $titleEn }}</h1>
        <p class="bn">{{ $subtitle }}</p>
        <p class="en" style="display:none;">{{ $subtitleEn }}</p>
    </div>

    <div class="top-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="7" stroke="#8B978F" stroke-width="2"/>
            <path d="M21 21l-4.3-4.3" stroke="#8B978F" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <input placeholder="খুঁজুন..." class="bn-ph">
    </div>

    <div class="top-actions">
        <div class="langswitch">
            <button id="btn-bn" class="active" onclick="setLang('bn')">বাং</button>
            <button id="btn-en" onclick="setLang('en')">EN</button>
        </div>

        <div class="icbtn" onclick="toast('৩টি নতুন নোটিফিকেশন','3 new notifications')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M12 3C9 3 7 5.3 7 8.2V11c0 1-.4 2-1.1 2.7L5 14.6V16h14v-1.4l-.9-.9C17.4 13 17 12 17 11V8.2C17 5.3 15 3 12 3Z" stroke="var(--ink-900)" stroke-width="1.4" stroke-linejoin="round"/>
            </svg>
            <div class="dot"></div>
        </div>

        <div class="top-avatar">{{ mb_substr(auth()->user()->name ?? '?', 0, 1) }}</div>
    </div>
</header>
