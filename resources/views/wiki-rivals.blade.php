@extends('layouts.app')
@section('title', 'Rivals — PokéVoid Wiki')

@section('content')

<button class="wiki-sidebar-toggle" id="wikiSidebarToggle" aria-label="Toggle navigation">
    <span class="wiki-sidebar-toggle-icon">☰</span>
    <span class="wiki-sidebar-toggle-label">Contents</span>
</button>
<div class="wiki-sidebar-overlay" id="wikiSidebarOverlay"></div>

<aside class="wiki-sidebar" id="wikiSidebar">
    <div class="wiki-sidebar-header">
        <a href="{{ route('wiki.index') }}" class="wiki-back-link">← Wiki Index</a>
        <button class="wiki-sidebar-close" id="wikiSidebarClose" aria-label="Close">✕</button>
    </div>
    <div class="wiki-sidebar-inner">
        @foreach($sidebarGrouped as $category => $articles)
        <div class="wiki-sidebar-section">
            <button class="wiki-sidebar-cat-toggle" aria-expanded="true">
                <span>{{ $category }}</span>
                <span class="wiki-sidebar-chevron">▾</span>
            </button>
            <ul class="wiki-sidebar-list">
                @foreach($articles as $a)
                <li>
                    <a href="{{ route('wiki.show', $a['slug']) }}" class="wiki-sidebar-link">
                        {{ $a['title'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
</aside>

<style>
.rivals-hero { text-align: center; padding: 40px 20px 28px; }
.rivals-hero h1 { font-size: 28px; font-weight: 700; color: var(--accent3); margin-bottom: 6px; }
.rivals-hero p  { color: var(--muted); font-size: 14px; }

.rivals-section-title {
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--muted);
    margin: 32px 0 14px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--border);
}

.rivals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 14px;
    margin-bottom: 8px;
}

.rival-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px 10px 12px;
    text-align: center;
    transition: border-color .15s, transform .15s;
    display: block;
    color: inherit;
}
.rival-card:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
    color: inherit;
}
.rival-card img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border2);
    display: block;
    margin: 0 auto 10px;
    background: var(--surface);
}
.rival-card:hover img { border-color: var(--accent); }
.rival-card-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--accent3);
    display: block;
    margin-bottom: 3px;
}
.rival-card-game {
    font-size: 11px;
    color: var(--muted);
    display: block;
}
.rival-type-badge {
    display: inline-block;
    font-size: 10px;
    padding: 2px 7px;
    border-radius: 99px;
    background: rgba(124,92,191,.2);
    color: var(--accent2);
    margin-top: 5px;
}
</style>

<div class="wiki-layout">
    <main class="wiki-content">
        <div class="rivals-hero">
            <h1>Rivals</h1>
            <p>Every rival you can encounter in PokéVoid, with their possible team members.</p>
        </div>

        @foreach($grouped as $category => $rivals)
        <div class="rivals-section-title">{{ $category }}</div>
        <div class="rivals-grid">
            @foreach($rivals as $rival)
            <a href="/rival:{{ $rival->slug }}.html" class="rival-card">
                <img
                    src="{{ $rival->portraitUrl() }}"
                    alt="{{ $rival->name }}"
                    onerror="this.style.opacity='.3'"
                >
                <span class="rival-card-name">{{ $rival->name }}</span>
                <span class="rival-card-game">{{ $rival->game }}</span>
                <span class="rival-type-badge">{{ $rival->type }}</span>
            </a>
            @endforeach
        </div>
        @endforeach
    </main>
</div>

<script>
(function() {
    const sidebar  = document.getElementById('wikiSidebar');
    const toggle   = document.getElementById('wikiSidebarToggle');
    const close    = document.getElementById('wikiSidebarClose');
    const overlay  = document.getElementById('wikiSidebarOverlay');
    function open()  { sidebar.classList.add('open');  overlay.classList.add('open');  document.body.classList.add('wiki-sidebar-open'); }
    function shut()  { sidebar.classList.remove('open'); overlay.classList.remove('open'); document.body.classList.remove('wiki-sidebar-open'); }
    function isOpen(){ return sidebar.classList.contains('open'); }
    toggle.addEventListener('click', () => isOpen() ? shut() : open());
    close.addEventListener('click', shut);
    overlay.addEventListener('click', shut);
    if (localStorage.getItem('wikiSidebar') === 'open') open();
    sidebar.addEventListener('transitionend', () => {
        localStorage.setItem('wikiSidebar', isOpen() ? 'open' : 'closed');
    });
    document.querySelectorAll('.wiki-sidebar-cat-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const list = btn.nextElementSibling;
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', !expanded);
            list.style.maxHeight = expanded ? '0' : list.scrollHeight + 'px';
            btn.querySelector('.wiki-sidebar-chevron').style.transform = expanded ? 'rotate(-90deg)' : '';
        });
        btn.nextElementSibling.style.maxHeight = btn.nextElementSibling.scrollHeight + 'px';
    });
})();
</script>
@endsection
