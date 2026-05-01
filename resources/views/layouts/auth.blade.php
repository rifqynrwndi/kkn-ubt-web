<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') &mdash; KKN Universitas Borneo Tarakan</title>

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme ?? (prefersDark ? 'dark' : 'light');

            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @livewireStyles

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/skins/reverse.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">

    @stack('style')
    @stack('css')
</head>
<body>
    <div id="app" class="min-vh-100 d-flex flex-column">

        <main class="flex-grow-1 d-flex align-items-center justify-content-center">
            @yield('content')
        </main>

        <footer class="text-center py-3 text-muted small">
            &copy; 2026 KKN Universitas Borneo Tarakan — LPPM Universitas Borneo Tarakan
        </footer>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="{{ asset('library/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('library/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('js/stisla.js') }}"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
