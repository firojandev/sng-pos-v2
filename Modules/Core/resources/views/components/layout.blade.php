@props([
    'title' => '',
    'titleEn' => '',
    'subtitle' => '',
    'subtitleEn' => '',
    'active' => null,
])

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F172A">
    <title>{{ $title ? $title . ' · ' : '' }}মাস্টারপস</title>

    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t === 'light' || t === 'dark') {
                    document.documentElement.setAttribute('data-theme', t);
                }
                var l = localStorage.getItem('lang');
                if (l === 'en') {
                    document.documentElement.classList.add('lang-en');
                }
                if (localStorage.getItem('sidebar-collapsed') === '1' && window.innerWidth > 1024) {
                    document.documentElement.classList.add('sidebar-collapsed-init');
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>

<div class="app">
    <div class="sidebar-overlay" id="overlay" onclick="toggleSidebar(false)"></div>

    <x-core::sidebar :active="$active" />

    <div class="main">
        <x-core::topbar :title="$title" :title-en="$titleEn" :subtitle="$subtitle" :subtitle-en="$subtitleEn" />

        <main class="content">
            {{ $slot }}
        </main>

        <x-core::footer />
    </div>
</div>

<div class="toast" id="toast"></div>

@if (session('status') || session('success') || session('error'))
    <script>
        (function () {
            function showToasts() {
                if (typeof window.toast !== 'function') {
                    setTimeout(showToasts, 30);
                    return;
                }
                @if (session('status'))
                    toast(@json(session('status')), @json(session('status')));
                @endif
                @if (session('success'))
                    toast(@json(session('success')), @json(session('success')));
                @endif
                @if (session('error'))
                    toast(@json(session('error')), @json(session('error')));
                @endif
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showToasts);
            } else {
                showToasts();
            }
        })();
    </script>
@endif

@stack('scripts')
</body>
</html>
