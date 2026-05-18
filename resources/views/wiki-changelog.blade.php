@extends('layouts.app')

@section('title', 'Changelog — PokéVoid Wiki')
@section('meta_description', 'WikiMoDex — the PokéVoid fan game wiki. Guides, builds, items, rivals, and more.')

@section('content')

<button class="wiki-sidebar-toggle" id="wikiSidebarToggle" aria-label="Toggle navigation">
    <span class="wiki-sidebar-toggle-icon">☰</span>
    <span class="wiki-sidebar-toggle-label">Contents</span>
</button>
<div class="wiki-sidebar-overlay" id="wikiSidebarOverlay"></div>

<aside class="wiki-sidebar" id="wikiSidebar">
    <div class="wiki-sidebar-header">
        <a href="{{ route('wiki.index') }}" class="wiki-back-link">← Wiki Index</a>
        <button class="wiki-sidebar-close" id="wikiSidebarClose">✕</button>
    </div>
    <div class="wiki-sidebar-inner">
        <div class="wiki-sidebar-section">
            <button class="wiki-sidebar-cat-toggle" aria-expanded="true">
                <span>On this page</span>
                <span class="wiki-sidebar-chevron">▾</span>
            </button>
            <ul class="wiki-sidebar-list">
                @foreach($entries as $entry)
                @if($entry->version)
                <li>
                    <a href="#{{ Str::slug($entry->version . '-' . $entry->committed_at->format('Y-m-d')) }}"
                       class="wiki-sidebar-link">
                        {{ $entry->version }}
                        <span style="color:var(--dim);font-size:0.7em;margin-left:0.3em">
                            {{ $entry->committed_at->format('M j, Y') }}
                        </span>
                    </a>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
    </div>
</aside>

<div class="wiki-layout">
    <main class="wiki-content">
        <div class="wiki-article-meta">
            <span class="wiki-article-category">PokéVoid Source</span>
            <span class="wiki-article-updated">{{ $entries->count() }} releases · auto-generated from git log</span>
        </div>

        <div class="wiki-prose">
            <h1>Changelog</h1>
            <p>All releases of PokéVoid, pulled directly from the game's source repository. Updated automatically on each sync.</p>
        </div>

        <div class="changelog-list">
            @foreach($entries as $entry)
            @php
                $anchorId = Str::slug(($entry->version ?? $entry->hash) . '-' . $entry->committed_at->format('Y-m-d'));
                $isHotfix = str_contains(strtolower($entry->title), 'hotfix');
                $isMajor  = !$isHotfix && $entry->version;
            @endphp
            <div class="changelog-entry {{ $isMajor ? 'changelog-entry--major' : '' }}" id="{{ $anchorId }}">
                <div class="changelog-entry-header">
                    <div class="changelog-entry-left">
                        @if($entry->version)
                        <span class="changelog-version {{ $isHotfix ? 'changelog-version--hotfix' : '' }}">
                            {{ $entry->version }}
                        </span>
                        @endif
                        <span class="changelog-title">{{ $entry->title }}</span>
                    </div>
                    <time class="changelog-date" datetime="{{ $entry->committed_at->toIso8601String() }}">
                        {{ $entry->committed_at->format('M j, Y') }}
                    </time>
                </div>
                @if($entry->body)
                <div class="changelog-body">{{ $entry->body }}</div>
                @endif
            </div>
            @endforeach
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
    toggle.addEventListener('click', () => sidebar.classList.contains('open') ? shut() : open());
    close.addEventListener('click', shut);
    overlay.addEventListener('click', shut);
    if (localStorage.getItem('wikiSidebar') === 'open') open();
    sidebar.addEventListener('transitionend', () => localStorage.setItem('wikiSidebar', sidebar.classList.contains('open') ? 'open' : 'closed'));
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
