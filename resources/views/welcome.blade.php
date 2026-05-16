@extends('layouts.app')
@section('title', 'Home')

@section('content')
<div class="container">

    <div class="hero" style="padding:24px 0 20px">
        <div class="hero-eyebrow">PokeVoid Wiki</div>
        <h1 style="font-size:26px;margin-bottom:10px">Harness the power of the void</h1>
        <div class="hero-actions">
            <a href="/wiki.html" class="btn btn-primary">Browse the Wiki</a>
            <a href="/gallery.html" class="btn btn-secondary">Glitch Forms</a>
            <a href="/faq.html" class="btn btn-secondary">FAQ</a>
        </div>
    </div>

    <div class="stats-strip" style="margin-bottom:24px">
        <div class="stat-cell">
            <div class="stat-num">{{ $stats['glitches'] }}</div>
            <div class="stat-label">Glitch forms</div>
        </div>
        <div class="stat-cell">
            <div class="stat-num">{{ $stats['core'] }}</div>
            <div class="stat-label">Core glitches</div>
        </div>
        <div class="stat-cell">
            <div class="stat-num">{{ $stats['smitty'] }}</div>
            <div class="stat-label">SMITTY forms</div>
        </div>
        <div class="stat-cell">
            <div class="stat-num">{{ $stats['users'] }}</div>
            <div class="stat-label">Trainers</div>
        </div>
    </div>

    {{-- Wiki quick links --}}
    <div class="section-header">
        <span class="section-title">Wiki</span>
        <a href="/wiki.html" class="section-link">Browse all →</a>
    </div>
    <div class="home-wiki-grid mb-4">
        <a href="/wiki:chaos-mode.html" class="home-wiki-card">
            <div class="home-wiki-card-icon">🌀</div>
            <div class="home-wiki-card-title">Chaos Mode</div>
            <div class="home-wiki-card-sub">Choose your path through the Void</div>
        </a>
        <a href="/wiki:gauntlet-mode.html" class="home-wiki-card">
            <div class="home-wiki-card-icon">⚔️</div>
            <div class="home-wiki-card-title">Gauntlet Mode</div>
            <div class="home-wiki-card-sub">Wave after wave of intense encounters</div>
        </a>
        <a href="/rivals.html" class="home-wiki-card">
            <div class="home-wiki-card-icon">👤</div>
            <div class="home-wiki-card-title">Rivals</div>
            <div class="home-wiki-card-sub">All 28 rivals and their teams</div>
        </a>
        <a href="/wiki:items.html" class="home-wiki-card">
            <div class="home-wiki-card-icon">🎒</div>
            <div class="home-wiki-card-title">Items & Shop</div>
            <div class="home-wiki-card-sub">Every item in the game</div>
        </a>
        <a href="/wiki:alt-builds.html" class="home-wiki-card">
            <div class="home-wiki-card-icon">⚡</div>
            <div class="home-wiki-card-title">Alt Builds</div>
            <div class="home-wiki-card-sub">Community-curated movesets</div>
        </a>
        <a href="/wiki:changelog.html" class="home-wiki-card">
            <div class="home-wiki-card-icon">📋</div>
            <div class="home-wiki-card-title">Changelog</div>
            <div class="home-wiki-card-sub">Latest updates to the game</div>
        </a>
    </div>

    {{-- Contribute CTA --}}
    <div class="section-header mb-4" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:10px 16px;margin-bottom:32px">
        <span style="font-size:13px;color:var(--muted)">Want to help build the wiki? Join the community and ask for editor access.</span>
        <a href="https://discord.gg/xsQummMK3H" target="_blank" rel="noopener" class="section-link" style="white-space:nowrap">Join Discord →</a>
    </div>

    {{-- Top-rated glitch forms --}}
    @if(count($featured) > 0)
    <div class="section-header">
        <span class="section-title">Top-rated glitch forms</span>
        <a href="/gallery.html" class="section-link">View all →</a>
    </div>
    <div class="mon-card-grid mb-4">
        @foreach($featured as $glitch)
            @php $mon2 = $glitch->getJsonData(); @endphp
            <a href="/g:{{ urlencode(str_replace(' ', '', $glitch->name)) }}:{{ $glitch->id }}.html" class="mon-card">
                <img class="mon-card-sprite"
                    src="/front:{{ $glitch->id }}.png"
                    alt="{{ $glitch->name }}">
                <div class="mon-card-name">{{ $glitch->name }}</div>
                <div class="mon-card-by">by {{ $glitch->creator->username }}</div>
                <div class="type-badges">
                    <span class="type-badge type-{{ $mon2->primaryType }}">
                        {{ \App\Services\PokemonService::getTypeName($mon2->primaryType) }}
                    </span>
                    @if(isset($mon2->secondaryType) && $mon2->secondaryType !== $mon2->primaryType)
                    <span class="type-badge type-{{ $mon2->secondaryType }}">
                        {{ \App\Services\PokemonService::getTypeName($mon2->secondaryType) }}
                    </span>
                    @endif
                </div>
                <div class="mon-card-likes">♥ <span>{{ $glitch->getRating() }}</span> likes</div>
            </a>
        @endforeach
    </div>
    @endif

    <div class="section-header mt-2">
        <span class="section-title">About PokeVoid</span>
    </div>
    <div class="card mb-4">
        <div class="card-body" style="color: var(--muted); line-height: 1.8;">
            <p class="mb-2">
                PokeVoid is built on PokeRogue's foundation, adding two new game modes and a
                deep corruption mechanic. In <strong style="color:var(--text)">Gauntlet mode</strong>
                you chase rivals through the darkness; in
                <strong style="color:var(--text)">Chaos mode</strong> you pick your own path and
                risk everything to uncover the truth.
            </p>
            <p>
                Collect physical shards of the corruption and use them to transform your Pokémon
                into powerful glitch forms — each with new typings, abilities, and stats.
                WikiMoDex is the community wiki tracking every form in the game.
            </p>
        </div>
    </div>

</div>

<style>
.home-wiki-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
}
.home-wiki-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 18px 16px;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    gap: 4px;
    transition: border-color var(--transition), transform var(--transition);
}
.home-wiki-card:hover { border-color: var(--accent); transform: translateY(-2px); color: inherit; }
.home-wiki-card-icon { font-size: 22px; margin-bottom: 4px; }
.home-wiki-card-title { font-size: 14px; font-weight: 700; color: var(--text); }
.home-wiki-card-sub { font-size: 12px; color: var(--muted); line-height: 1.4; }
</style>
@endsection
