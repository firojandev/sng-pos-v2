@props([
    'title' => '',
    'titleEn' => '',
    'subtitle' => '',
    'subtitleEn' => '',
    'active' => null,
])

@php
    $cookieTheme = request()->cookie('theme');
    $cookieLang = request()->cookie('lang');
    $isDark = $cookieTheme === 'dark';
    $isEn = $cookieLang === 'en';
@endphp

<!DOCTYPE html>
<html lang="{{ $isEn ? 'en' : 'bn' }}" @if($cookieTheme) data-theme="{{ $cookieTheme }}" @endif class="{{ $isEn ? 'lang-en' : '' }}">
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
                    if (!document.cookie.includes('theme=' + t)) {
                        document.cookie = "theme=" + t + ";path=/;max-age=31536000;SameSite=Lax";
                    }
                }
                var l = localStorage.getItem('lang');
                if (l === 'en' || l === 'bn') {
                    if (l === 'en') {
                        document.documentElement.classList.add('lang-en');
                    } else {
                        document.documentElement.classList.remove('lang-en');
                    }
                    if (!document.cookie.includes('lang=' + l)) {
                        document.cookie = "lang=" + l + ";path=/;max-age=31536000;SameSite=Lax";
                    }
                }
                if (localStorage.getItem('sidebar-collapsed') === '1' && window.innerWidth > 1024) {
                    document.documentElement.classList.add('sidebar-collapsed-init');
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&family=Baloo+Da+2:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.maateen.me/solaiman-lipi/font.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        (function () {
            if (window.$) {
                if (!window.$.trim) {
                    window.$.trim = function (str) {
                        return str == null ? '' : (str + '').trim();
                    };
                }
                if (window.$.ajaxPrefilter) {
                    window.$.ajaxPrefilter(function (options, originalOptions, xhr) {
                        var token = $('meta[name="csrf-token"]').attr('content');
                        if (token) {
                            xhr.setRequestHeader('X-CSRF-TOKEN', token);
                        }
                    });
                }
            }
        })();
    </script>
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

@include('sales::quick-sale._modal')

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
