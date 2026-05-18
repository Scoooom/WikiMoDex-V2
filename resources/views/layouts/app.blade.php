<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WikiMoDex{{ View::hasSection('title') ? ' — ' . View::yieldContent('title') : '' }}</title>

    {{-- SEO: pages can override via @push('meta') --}}
    <meta name="robots" content="index, follow">
    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
        <meta property="og:description" content="@yield('meta_description')">
    @else
        <meta name="description" content="WikiMoDex — the community wiki for PokéVoid, the Pokémon fan game. Mechanics, builds, rivals, items, and more.">
        <meta property="og:description" content="WikiMoDex — the community wiki for PokéVoid, the Pokémon fan game. Mechanics, builds, rivals, items, and more.">
    @endif
    <meta property="og:title" content="WikiMoDex{{ View::hasSection('title') ? ' — ' . View::yieldContent('title') : '' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @stack('meta')

    @stack('head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link href="/css/ScumsCyborg.css?v={{ filemtime(public_path('css/ScumsCyborg.css')) }}" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    @if(app()->environment('production'))
    <!-- Privacy-friendly analytics by Plausible -->
    <script async src="https://stats.scooom.xyz/js/pa-dbuwMAtytRej1UrBohPqo.js" data-outbound-links></script>
    <script>
        window.plausible=window.plausible||function(){(plausible.q=plausible.q||[]).push(arguments)},plausible.init=plausible.init||function(i){plausible.o=i||{}};
        plausible.init()
    </script>
    @endif
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
<script>
async function copyTrainerCard(btn, url) {
    try {
        const fullUrl = window.location.origin + url;
        await navigator.clipboard.writeText(fullUrl);
        const orig = btn.textContent;
        btn.textContent = '✓ Copied!';
        setTimeout(() => btn.textContent = orig, 2000);
    } catch (e) {
        window.open(url, '_blank');
    }
}
</script>
</body>
</html>
