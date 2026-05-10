<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WikiMoDex{{ isset($title) ? ' — ' . $title : '' }}</title>

    @stack('head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link href="/css/ScumsCyborg.css" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
</head>
<body>
    @include('partials.nav')

    <main class="site-main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <span class="footer-brand">WikiMoDex</span>
            <span class="footer-sep">·</span>
            <span class="footer-text">Questions? Find <code>scooom</code> on Discord.</span>
        </div>
    </footer>
</body>
</html>
