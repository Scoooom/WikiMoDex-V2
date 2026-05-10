@extends('layouts.app')

@section('title', $article->title . ' — PokéVoid Wiki')

@section('content')

{{-- Floating sidebar toggle button (mobile + collapsed state) --}}
<button class="wiki-sidebar-toggle" id="wikiSidebarToggle" aria-label="Toggle navigation">
    <span class="wiki-sidebar-toggle-icon">☰</span>
    <span class="wiki-sidebar-toggle-label">Contents</span>
</button>

{{-- Sidebar overlay (mobile) --}}
<div class="wiki-sidebar-overlay" id="wikiSidebarOverlay"></div>

{{-- Floating sidebar --}}
<aside class="wiki-sidebar" id="wikiSidebar">
    <div class="wiki-sidebar-header">
        <a href="{{ route('wiki.index') }}" class="wiki-back-link">← Wiki Index</a>
        <button class="wiki-sidebar-close" id="wikiSidebarClose" aria-label="Close">✕</button>
    </div>
    <div class="wiki-sidebar-inner">
        @foreach($grouped as $category => $articles)
        <div class="wiki-sidebar-section">
            <button class="wiki-sidebar-cat-toggle" aria-expanded="true">
                <span>{{ $category }}</span>
                <span class="wiki-sidebar-chevron">▾</span>
            </button>
            <ul class="wiki-sidebar-list">
                @foreach($articles as $a)
                <li>
                    <a href="{{ route('wiki.show', $a['slug']) }}"
                       class="wiki-sidebar-link {{ $a['slug'] === $article->slug ? 'active' : '' }}">
                        {{ $a['title'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
</aside>

{{-- Main content --}}
<div class="wiki-layout">
    <main class="wiki-content">
        <div class="wiki-article-meta">
            <span class="wiki-article-category">{{ $article->category }}</span>
            @auth
                @if(auth()->user()->discord_id === '356260100064673814')
                <a href="{{ route('wiki.admin.edit', $article->slug) }}" class="wiki-edit-btn">Edit Article</a>
                @endif
            @endauth
        </div>

        <div class="wiki-prose">
            {!! $html !!}
        </div>
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

    // Restore state from localStorage
    if (localStorage.getItem('wikiSidebar') === 'open') open();

    // Persist state
    sidebar.addEventListener('transitionend', () => {
        localStorage.setItem('wikiSidebar', isOpen() ? 'open' : 'closed');
    });

    // Category collapse toggles
    document.querySelectorAll('.wiki-sidebar-cat-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const list = btn.nextElementSibling;
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', !expanded);
            list.style.maxHeight = expanded ? '0' : list.scrollHeight + 'px';
            btn.querySelector('.wiki-sidebar-chevron').style.transform = expanded ? 'rotate(-90deg)' : '';
        });
        // Init heights
        btn.nextElementSibling.style.maxHeight = btn.nextElementSibling.scrollHeight + 'px';
    });

    // Auto-scroll active link into view
    const active = sidebar.querySelector('.wiki-sidebar-link.active');
    if (active) active.scrollIntoView({ block: 'nearest' });
})();
</script>

@endsection
