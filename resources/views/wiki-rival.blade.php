@extends('layouts.app')
@section('title', $rival->name . ' — PokéVoid Rivals')
@section('meta_description', $rival->name . ' is a ' . $rival->role . ' rival in PokéVoid. View their full team, encounter conditions, and battle details on WikiMoDex.')

@section('content')

<button class="wiki-sidebar-toggle" id="wikiSidebarToggle" aria-label="Toggle navigation">
    <span class="wiki-sidebar-toggle-icon">☰</span>
    <span class="wiki-sidebar-toggle-label">Contents</span>
</button>
<div class="wiki-sidebar-overlay" id="wikiSidebarOverlay"></div>

<aside class="wiki-sidebar" id="wikiSidebar">
    <div class="wiki-sidebar-header">
        <a href="/rivals.html" class="wiki-back-link">← All Rivals</a>
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
.rival-page-layout {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 28px;
    align-items: start;
}
@media (max-width: 700px) {
    .rival-page-layout { grid-template-columns: 1fr; }
}

/* ── Sidebar card ── */
.rival-sidebar-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 24px 16px 20px;
    text-align: center;
    position: sticky;
    top: 72px;
}
.rival-portrait {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--accent);
    display: block;
    margin: 0 auto 14px;
    background: var(--surface);
}
.rival-page-name {
    font-size: 20px;
    font-weight: 700;
    color: var(--accent3);
    margin-bottom: 4px;
}
.rival-page-role {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 10px;
}
.rival-meta-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    padding: 6px 0;
    border-top: 1px solid var(--border);
}
.rival-meta-row .label { color: var(--muted); }
.rival-meta-row .value { color: var(--accent2); font-weight: 600; }

/* ── Main content ── */
.rival-main-content { min-width: 0; }

.rival-section-heading {
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--muted);
    margin: 0 0 14px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--border);
}

.rival-section { margin-bottom: 32px; }

/* ── Pokémon pill grid ── */
.rival-pokemon-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.rival-pokemon-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 99px;
    padding: 5px 14px 5px 8px;
    font-size: 13px;
    color: var(--accent3);
    transition: border-color .12s;
}
.rival-pokemon-pill:hover { border-color: var(--accent); }
.rival-pokemon-pill img {
    width: 32px;
    height: 32px;
    object-fit: contain;
    image-rendering: pixelated;
}
.rival-pokemon-pill .pill-name { font-weight: 500; }

/* ── Notice for no rematch ── */
.rival-note {
    font-size: 13px;
    color: var(--muted);
    font-style: italic;
}

/* ── Nav between rivals ── */
.rival-nav {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
    font-size: 13px;
}
.rival-nav a { color: var(--accent2); }
.rival-nav a:hover { color: var(--accent3); }
</style>

<div class="wiki-layout">
    <main class="wiki-content">
        <div class="rival-page-layout">

            {{-- Sidebar --}}
            <div>
                <div class="rival-sidebar-card">
                    <img
                        src="{{ $rival->portraitUrl() }}"
                        alt="{{ $rival->name }}"
                        class="rival-portrait"
                        onerror="this.style.opacity='.3'"
                    >
                    <div class="rival-page-name">{{ $rival->name }}</div>
                    <div class="rival-page-role">{{ $rival->role }}</div>
                    <div class="rival-meta-row">
                        <span class="label">Game</span>
                        <span class="value">{{ $rival->game }}</span>
                    </div>
                    <div class="rival-meta-row">
                        <span class="label">Type</span>
                        <span class="value">{{ $rival->type }}</span>
                    </div>
                    <div class="rival-meta-row">
                        <span class="label">Pokémon pool</span>
                        <span class="value">{{ count($rival->encounter_pokemon) + count($rival->rematch_pokemon) }}</span>
                    </div>
                </div>
            </div>

            {{-- Main --}}
            <div class="rival-main-content">

                {{-- Encounter team --}}
                <div class="rival-section">
                    <div class="rival-section-heading">Possible encounter team</div>
                    @if(count($rival->encounter_pokemon))
                    <div class="rival-pokemon-grid" id="encounter-grid">
                        @foreach($rival->encounter_pokemon as $mon)
                        @php
                            $dexSlug = strtolower(preg_replace(['/[^a-zA-Z0-9\s]/', '/\s+/'], ['', '-'], $mon));
                            $spriteUrl = '/cFront:' . $dexSlug . '.png';
                        @endphp
                        <div class="rival-pokemon-pill">
                            <img src="{{ $spriteUrl }}" alt="{{ $mon }}" onerror="this.style.display='none'">
                            <span class="pill-name">{{ $mon }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="rival-note">Team is dynamically generated from a pool — no fixed list available.</p>
                    @endif
                </div>

                {{-- Rematch team --}}
                <div class="rival-section">
                    <div class="rival-section-heading">Possible rematch team</div>
                    @if(count($rival->rematch_pokemon))
                    <div class="rival-pokemon-grid" id="rematch-grid">
                        @foreach($rival->rematch_pokemon as $mon)
                        @php
                            $dexSlug = strtolower(preg_replace(['/[^a-zA-Z0-9\s]/', '/\s+/'], ['', '-'], $mon));
                            $spriteUrl = '/cFront:' . $dexSlug . '.png';
                        @endphp
                        <div class="rival-pokemon-pill">
                            <img src="{{ $spriteUrl }}" alt="{{ $mon }}" onerror="this.style.display='none'">
                            <span class="pill-name">{{ $mon }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="rival-note">No separate rematch pool — same team as first encounter.</p>
                    @endif
                </div>

                {{-- Back link --}}
                <div class="rival-nav">
                    <a href="/rivals.html">← All Rivals</a>
                </div>

            </div>{{-- /main --}}
        </div>{{-- /layout --}}
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
