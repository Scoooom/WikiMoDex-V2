@extends('layouts.app')

@section('title', 'Items Reference — PokéVoid Wiki')

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
                       class="wiki-sidebar-link">
                        {{ $a['title'] }}
                    </a>
                </li>
                @endforeach
                @if($category === 'Items & Shop')
                <li>
                    <a href="{{ route('wiki.items') }}" class="wiki-sidebar-link active">
                        Items Reference
                    </a>
                </li>
                @endif
            </ul>
        </div>
        @endforeach
    </div>
</aside>

<div class="wiki-layout">
    <main class="wiki-content">
        <div class="wiki-article-meta">
            <span class="wiki-article-category">Items & Shop</span>
            @auth
                @if(auth()->user()->user_id === '356260100064673814')
                <span class="wiki-edit-btn">Auto-generated · run <code>php artisan items:parse</code> to update</span>
                @endif
            @endauth
        </div>

        <div class="wiki-prose">
            <h1>Items Reference</h1>
            <p>All items available in PokéVoid, organised by tier. Items marked <strong>★</strong> only appear conditionally based on your party or game state.</p>
        </div>

        {{-- Tier tabs --}}
        <div class="items-tabs" id="itemsTabs">
            @foreach(\App\Models\GameItem::TIER_ORDER as $tier)
                @if(isset($byTier[$tier]) && count($byTier[$tier]) > 0)
                <button class="items-tab {{ $loop->first ? 'active' : '' }}"
                        data-tier="{{ $tier }}"
                        style="--tier-color: {{ \App\Models\GameItem::TIER_COLORS[$tier] }}">
                    {{ \App\Models\GameItem::TIER_LABELS[$tier] }}
                    <span class="items-tab-count">{{ count($byTier[$tier]) }}</span>
                </button>
                @endif
            @endforeach
        </div>

        {{-- Search --}}
        <div class="items-search-wrap">
            <input type="text" id="itemSearch" class="items-search" placeholder="Search items…" autocomplete="off">
        </div>

        {{-- Tables per tier --}}
        @foreach(\App\Models\GameItem::TIER_ORDER as $tier)
            @if(isset($byTier[$tier]) && count($byTier[$tier]) > 0)
            <div class="items-panel {{ $loop->first ? 'active' : '' }}" data-tier="{{ $tier }}">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Description</th>
                            <th>Spawn Condition</th>
                            @if($tier !== 'OMEGA')
                            <th>Pool</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byTier[$tier] as $item)
                        <tr class="items-row" data-search="{{ strtolower($item->name . ' ' . $item->description . ' ' . $item->spawn_condition) }}">
                            <td class="items-name">{{ $item->name }}</td>
                            <td class="items-desc">{{ $item->description ?: '—' }}</td>
                            <td class="items-condition">
                                @if($item->spawn_condition)
                                    <span class="items-condition-badge">★ {{ $item->spawn_condition }}</span>
                                @else
                                    <span class="items-condition-always">Always</span>
                                @endif
                            </td>
                            @if($tier !== 'OMEGA')
                            <td class="items-pool">{{ ucfirst($item->pool) }}</td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @endforeach

    </main>
</div>

<script>
(function() {
    // Sidebar
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

    // Tabs
    const tabs   = document.querySelectorAll('.items-tab');
    const panels = document.querySelectorAll('.items-panel');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            document.querySelector(`.items-panel[data-tier="${tab.dataset.tier}"]`).classList.add('active');
            document.getElementById('itemSearch').value = '';
            document.querySelectorAll('.items-row').forEach(r => r.style.display = '');
        });
    });

    // Search
    document.getElementById('itemSearch').addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        const activePanel = document.querySelector('.items-panel.active');
        if (!activePanel) return;
        activePanel.querySelectorAll('.items-row').forEach(row => {
            row.style.display = (!q || row.dataset.search.includes(q)) ? '' : 'none';
        });
    });
})();
</script>
@endsection
