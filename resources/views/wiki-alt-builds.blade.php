@extends('layouts.app')

@section('title', 'Alt Builds — PokéVoid Wiki')

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
        {{-- Jump to champion section --}}
        <div class="wiki-sidebar-section">
            <button class="wiki-sidebar-cat-toggle" aria-expanded="true">
                <span>By Champion</span>
                <span class="wiki-sidebar-chevron">▾</span>
            </button>
            <ul class="wiki-sidebar-list">
                @foreach(\App\Models\AltBuild::championLabel() as $key => $label)
                    @if(isset($grouped[$key]))
                    <li>
                        <a href="#champion-{{ $key }}" class="wiki-sidebar-link">
                            {{ $label }}
                            <span style="color:var(--dim);font-size:0.7em;margin-left:0.3em">{{ $grouped[$key]->count() }}</span>
                        </a>
                    </li>
                    @endif
                @endforeach
                @foreach($grouped->keys()->diff(array_keys(\App\Models\AltBuild::championLabel())) as $key)
                <li>
                    <a href="#champion-{{ $key }}" class="wiki-sidebar-link">{{ ucfirst($key) }}</a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Wiki articles sidebar --}}
        @foreach($sidebarGrouped as $category => $articles)
        <div class="wiki-sidebar-section">
            <button class="wiki-sidebar-cat-toggle" aria-expanded="false">
                <span>{{ $category }}</span>
                <span class="wiki-sidebar-chevron">▾</span>
            </button>
            <ul class="wiki-sidebar-list" style="max-height:0;overflow:hidden">
                @foreach($articles as $a)
                <li><a href="{{ route('wiki.show', $a['slug']) }}" class="wiki-sidebar-link">{{ $a['title'] }}</a></li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>
</aside>

<div class="wiki-layout">
    <main class="wiki-content">
        <div class="wiki-article-meta">
            <span class="wiki-article-category">Champions</span>
            <span class="wiki-article-updated">{{ $builds->count() }} builds · auto-generated from source</span>
        </div>

        <div class="wiki-prose">
            <h1>Alt Builds</h1>
            <p>Alt Builds are alternate forms of a Champion's Signature Pokémon — distinct identities with changed types, abilities, stat focuses, and movesets. Each is unlocked progressively through a Champion's Skill Tree.</p>
            <p>Every Alt Build has a <strong>Rank</strong> (its tier of power), a <strong>Stat Focus</strong> (the stats it prioritises), and optional type changes. They require first unlocking the base Signature version of that Pokémon.</p>
        </div>

        @php
            $championLabels = \App\Models\AltBuild::championLabel();
            $championLinks  = [
                'apollo_diana' => route('wiki.show', 'apollo'),
                'brock'        => route('wiki.show', 'brock'),
                'misty'        => route('wiki.show', 'misty'),
            ];
        @endphp

        @foreach($grouped as $champion => $championBuilds)
        <div class="altbuild-champion-section" id="champion-{{ $champion }}">
            <div class="altbuild-champion-header">
                <h2 class="wiki-prose" style="border-bottom:none;margin:0">
                    @if(isset($championLinks[$champion]))
                        <a href="{{ $championLinks[$champion] }}" class="altbuild-champion-link">
                            {{ $championLabels[$champion] ?? ucfirst(str_replace('_', ' ', $champion)) }}
                        </a>
                    @else
                        {{ $championLabels[$champion] ?? ucfirst(str_replace('_', ' ', $champion)) }}
                    @endif
                </h2>
                <span class="altbuild-count">{{ $championBuilds->count() }} builds</span>
            </div>

            <div class="altbuild-grid">
                @foreach($championBuilds as $build)
                <div class="altbuild-card">
                    <div class="altbuild-card-header">
                        <div class="altbuild-card-title-wrap">
                            <span class="altbuild-species">{{ $build->species }}</span>
                            <span class="altbuild-name">{{ $build->name }}</span>
                        </div>
                        <div class="altbuild-badges">
                            @if($build->type1)
                                <span class="altbuild-type type-{{ strtolower($build->type1) }}">{{ $build->type1 }}</span>
                            @endif
                            @if($build->type2)
                                <span class="altbuild-type type-{{ strtolower($build->type2) }}">{{ $build->type2 }}</span>
                            @endif
                            @if(!$build->type1)
                                <span class="altbuild-type-unchanged">Type unchanged</span>
                            @endif
                        </div>
                    </div>

                    <div class="altbuild-card-body">
                        <div class="altbuild-row">
                            <span class="altbuild-label">Stat Focus</span>
                            <span class="altbuild-val">{{ $build->stat_focus }}</span>
                        </div>

                        @if($build->ability1 || $build->ability2 || $build->ability3)
                        <div class="altbuild-row">
                            <span class="altbuild-label">Abilities</span>
                            <span class="altbuild-val">
                                {{ collect([$build->ability1, $build->ability2, $build->ability3])->filter()->join(' / ') }}
                            </span>
                        </div>
                        @endif

                        @if($build->passive_ability)
                        <div class="altbuild-row">
                            <span class="altbuild-label">Passive</span>
                            <span class="altbuild-val">{{ $build->passive_ability }}</span>
                        </div>
                        @endif

                        @if($build->key_moves && count($build->key_moves))
                        <div class="altbuild-row altbuild-row--moves">
                            <span class="altbuild-label">Key Moves</span>
                            <span class="altbuild-val altbuild-moves">
                                @foreach($build->key_moves as $move)
                                <span class="altbuild-move">{{ $move }}</span>
                                @endforeach
                            </span>
                        </div>
                        @endif

                        @if($build->prevents_evolution)
                        <div class="altbuild-row">
                            <span class="altbuild-label">Evolution</span>
                            <span class="altbuild-val altbuild-noevo">⚠ Prevents evolution</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
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
        const list = btn.nextElementSibling;
        if (btn.getAttribute('aria-expanded') === 'true') {
            list.style.maxHeight = list.scrollHeight + 'px';
        }
    });

    // Anchor scroll fix
    if (window.location.hash) {
        setTimeout(() => {
            const el = document.querySelector(window.location.hash);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
})();
</script>
@endsection
