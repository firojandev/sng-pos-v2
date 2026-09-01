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
                if (localStorage.getItem('sidebar-collapsed') === '1' && window.innerWidth > 1024) {
                    document.documentElement.classList.add('sidebar-collapsed-init');
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

@if (session('status'))
    <script>
        $(function () {
            toast(@json(session('status')), @json(session('status')));
        });
    </script>
@endif

@if (session('success'))
    <script>
        $(function () {
            toast(@json(session('success')), @json(session('success')));
        });
    </script>
@endif

@if (session('error'))
    <script>
        $(function () {
            toast(@json(session('error')), @json(session('error')));
        });
    </script>
@endif

</body>
</html>
