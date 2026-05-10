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

    // Build sublinks from headings on current page
    const headings = document.querySelectorAll('.wiki-prose h2, .wiki-prose h3');
    if (headings.length > 0 && active) {
        const subList = document.createElement('ul');
        subList.className = 'wiki-sidebar-sublist';

        headings.forEach(h => {
            const anchor = h.querySelector('.wiki-heading-anchor');
            if (!anchor) return;
            const id = anchor.getAttribute('href').slice(1);
            const text = h.childNodes[0].textContent.trim(); // text before the # anchor
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#' + id;
            a.className = 'wiki-sidebar-sublink' + (h.tagName === 'H3' ? ' wiki-sidebar-sublink--h3' : '');
            a.textContent = text;
            li.appendChild(a);
            subList.appendChild(li);
        });

        // Insert after the active link's <li>
        active.parentElement.after(subList);

        // Recalculate max-height for all category lists so sublist is visible
        document.querySelectorAll('.wiki-sidebar-list').forEach(list => {
            list.style.maxHeight = list.scrollHeight + 'px';
        });

        // Highlight active sublink on scroll
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    subList.querySelectorAll('.wiki-sidebar-sublink').forEach(a => {
                        a.classList.toggle('active', a.getAttribute('href') === '#' + id);
                    });
                }
            });
        }, { rootMargin: '-70px 0px -70% 0px', threshold: 0 });

        headings.forEach(h => { if (h.id) observer.observe(h); });
    }

    // Fix anchor-on-load being hidden under fixed nav
    function scrollToHash() {
        if (!window.location.hash) return;
        const target = document.getElementById(decodeURIComponent(window.location.hash.slice(1)));
        if (!target) return;
        const navH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h') || '54');
        const offset = navH + 24;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'instant' });
    }

    // Run after layout is settled
    if (document.readyState === 'complete') {
        scrollToHash();
    } else {
        window.addEventListener('load', scrollToHash);
    }

    // Fix anchor clicks hiding heading under nav
    document.querySelectorAll('a[href^="#"], .wiki-heading-anchor').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (!href || !href.startsWith('#')) return;
            const target = document.getElementById(decodeURIComponent(href.slice(1)));
            if (!target) return;
            e.preventDefault();
            const navH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--nav-h') || '54');
            const offset = navH + 24;
            const top = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
            history.pushState(null, '', href);
        });
    });
})();
</script>

@endsection
