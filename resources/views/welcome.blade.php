@extends('layouts.app')

@section('content')
<div class="container">

    <div class="hero">
        <div class="hero-eyebrow">PokeVoid Wiki</div>
        <h1>Harness the power<br>of the void</h1>
        <p class="hero-lead">
            PokeVoid is a fork of PokeRogue where you fight to save the world from corruption —
            collecting glitch shards to corrupt your own Pokémon in legendary ways.
        </p>
        <div class="hero-actions">
            <a href="/gallery.html" class="btn btn-primary">Browse glitch forms</a>
            <a href="/faq.html" class="btn btn-secondary">Read the FAQ</a>
        </div>
    </div>

    <div class="stats-strip">
        <div class="stat-cell">
            <div class="stat-num">{{ \App\Models\Glitch::count() }}</div>
            <div class="stat-label">Glitch forms</div>
        </div>
        <div class="stat-cell">
            <div class="stat-num">{{ \App\Models\BuiltinForm::where('form_type', 'core')->count() }}</div>
            <div class="stat-label">Core glitches</div>
        </div>
        <div class="stat-cell">
            <div class="stat-num">{{ \App\Models\BuiltinForm::where('form_type', 'smitty')->count() }}</div>
            <div class="stat-label">SMITTY forms</div>
        </div>
        <div class="stat-cell">
            <div class="stat-num">{{ \App\Models\User::count() }}</div>
            <div class="stat-label">Trainers</div>
        </div>
    </div>

    @php
        $featured = \App\Models\Glitch::withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->take(6)
            ->get();
    @endphp

    @if($featured->count())
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
@endsection
