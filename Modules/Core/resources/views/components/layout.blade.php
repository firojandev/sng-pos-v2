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
    <title>{{ $title }} &middot; মাস্টারপস</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<div class="app">
    <div class="sidebar-overlay" id="overlay" onclick="toggleSidebar(false)"></div>

    <x-core::sidebar :active="$active" />

    <div class="main">
        <x-core::topbar :title="$title" :title-en="$titleEn" :subtitle="$subtitle" :subtitle-en="$subtitleEn" />

        <div class="content">
            {{ $slot }}
        </div>

        <x-core::footer />
    </div>
</div>

<div class="toast" id="toast"></div>

@if (session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            toast(@json(session('status')), @json(session('status')));
        });
    </script>
@endif

</body>
</html>
