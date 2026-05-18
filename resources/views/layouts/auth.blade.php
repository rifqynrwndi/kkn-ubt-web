<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') &mdash; KKN Universitas Borneo Tarakan</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

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

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">

    <style>
        * { font-family: 'Inter', sans-serif; }

        /* ── AUTH WRAPPER ──────────────────────── */
        .auth-wrapper {
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }
        .auth-banner {
            flex: 0 0 46%;
            background: linear-gradient(135deg, #0f3460 0%, #1a1a2e 40%, #16213e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .auth-banner::before {
            content: '';
            position: absolute;
            top: -30%; right: -20%;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(103,119,239,.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .auth-banner::after {
            content: '';
            position: absolute;
            bottom: -20%; left: 10%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,65,108,.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .auth-banner-content {
            text-align: center;
            color: #fff;
            position: relative;
            z-index: 1;
            padding: 40px;
        }
        .auth-banner-content img {
            height: 80px;
            margin-bottom: 24px;
        }
        .auth-banner-content h2 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -.5px;
        }
        .auth-banner-content p {
            font-size: .9rem;
            opacity: .65;
            max-width: 340px;
            line-height: 1.6;
        }

        /* ── AUTH FORM SIDE ────────────────────── */
        .auth-form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--auth-form-bg, #f8f9fc);
            padding: 40px;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--auth-card-bg, #fff);
            border-radius: 20px;
            box-shadow: 0 4px 32px rgba(0,0,0,.06);
            padding: 40px 36px;
        }
        .auth-card-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .auth-card-header h3 {
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--auth-heading, #1a1a2e);
            margin-bottom: 6px;
        }
        .auth-card-header p {
            font-size: .85rem;
            color: var(--auth-subtext, #6c757d);
            margin: 0;
        }
        .auth-card .form-control {
            border-radius: 10px;
            padding: 11px 16px;
            border: 1.5px solid var(--auth-input-border, #e0e5ef);
            font-size: .9rem;
            transition: .2s;
            background: var(--auth-input-bg, #fff);
            color: var(--auth-text, #1a1a2e);
        }
        .auth-card .form-control:focus {
            border-color: #6777ef;
            box-shadow: 0 0 0 3px rgba(103,119,239,.12);
        }
        .auth-card .form-label {
            font-weight: 600;
            font-size: .82rem;
            color: var(--auth-label, #4a5568);
            margin-bottom: 6px;
        }
        .auth-card .btn-primary {
            border-radius: 10px;
            padding: 12px;
            font-weight: 700;
            font-size: .95rem;
            background: linear-gradient(135deg, #6777ef, #4f5ece);
            border: none;
            transition: .2s;
        }
        .auth-card .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(103,119,239,.3);
        }

        /* ── LIGHT MODE ────────────────────────── */
        :root, [data-bs-theme="light"] {
            --auth-form-bg: #f0f2f8;
            --auth-card-bg: #fff;
            --auth-heading: #1a1a2e;
            --auth-subtext: #6c757d;
            --auth-input-border: #e0e5ef;
            --auth-input-bg: #fff;
            --auth-text: #1a1a2e;
            --auth-label: #4a5568;
        }

        /* ── DARK MODE ─────────────────────────── */
        [data-bs-theme="dark"] {
            --auth-form-bg: #151820;
            --auth-card-bg: #1f2430;
            --auth-heading: #f1f3f8;
            --auth-subtext: #8892a6;
            --auth-input-border: #2d3340;
            --auth-input-bg: #252934;
            --auth-text: #d6d9df;
            --auth-label: #aab1c1;
        }
        [data-bs-theme="dark"] .auth-banner {
            background: linear-gradient(135deg, #0a0e1a 0%, #111827 40%, #0f172a 100%);
        }

        /* ── RESPONSIVE ────────────────────────── */
        @media (max-width: 768px) {
            .auth-banner { display: none; }
            .auth-form-side { padding: 24px; }
            .auth-card { padding: 28px 22px; }
        }
    </style>

    @stack('css')
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-banner">
            <div class="auth-banner-content">
                <img src="{{ asset('images/logo-ubt.png') }}"
                     alt="Universitas Borneo Tarakan"
                     onerror="this.outerHTML='<div style=font-size:60px;margin-bottom:24px;filter:grayscale(1)brightness(10)>🎓</div>'">
                <h2>KKN UBT</h2>
                <p>Sistem Informasi Kuliah Kerja Nyata — Universitas Borneo Tarakan</p>
            </div>
        </div>
        <div class="auth-form-side">
            @yield('content')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
