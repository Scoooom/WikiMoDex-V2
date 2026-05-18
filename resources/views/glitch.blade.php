@extends('layouts.app')
@section('title', $glitch->name . ' by ' . $creator->username)

@push('head')
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $glitch->name }} | {{ $creator->username }}">
<meta property="og:description" content="{{ $glitch->name }}; {{ $abilityOne['name'] }} / {{ $abilityTwo['name'] }} / {{ $abilityHA['name'] }}">
<meta property="og:image" content="https://pokevoid.wiki/front:{{ $glitch->id }}.png">
@endpush

@section('content')
<div class="container">
    <div class="mon-detail-grid mt-2">

        {{-- Sidebar --}}
        <div class="mon-sidebar">
            <div class="card">
                <div class="card-body">
                    <div class="sprite-duo">
                        <img src="/front:{{ $glitch->id }}.png" class="sprite-lg" alt="{{ $glitch->name }} front">
                        <img src="/back:{{ $glitch->id }}.png"  class="sprite-lg back" alt="{{ $glitch->name }} back">
                    </div>
                    <div class="mon-name">{{ $glitch->name }}</div>
                    <div class="mon-creator">
                        by <a href="/u:{{ $creator->username }}.html">{{ $creator->username }}</a>
                    </div>
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
                    <div class="mon-actions">
                        <a href="/d:{{ $glitch->id }}.html" class="btn btn-primary btn-sm">↓ Download</a>
                        @auth
                            @if($userLikesGlitch)
                            <form action="/rLike:{{ $glitch->id }}.html" method="post">
                                @csrf
                                <input type="hidden" name="returnURL" value="{{ url()->current() }}">
                                <button type="submit" class="btn btn-success btn-sm">♥ Unlike ({{ $rating }})</button>
                            </form>
                            @else
                            <form action="/like:{{ $glitch->id }}.html" method="post">
                                @csrf
                                <input type="hidden" name="returnURL" value="{{ url()->current() }}">
                                <button type="submit" class="btn btn-secondary btn-sm">♡ Like ({{ $rating }})</button>
                            </form>
                            @endif
                        @else
                        <span class="btn btn-secondary btn-sm" style="cursor:default">♥ {{ $rating }}</span>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div>
            <div class="card mb-3">
                <div class="tab-strip">
                    <button class="tab-btn active" onclick="switchTab('stats', this)">Stats</button>
                    <button class="tab-btn" onclick="switchTab('abilities', this)">Abilities</button>
                    <button class="tab-btn" onclick="switchTab('details', this)">Details</button>
                </div>

                <div id="tab-stats" class="tab-panel active">
                    <p style="font-size:12px;color:var(--muted);margin-bottom:14px">
                        Boosted stats · BST <strong style="color:var(--text)">{{ $boostedBST }}</strong>
                    </p>
                    @php
                        $bStats = [];
                        foreach (['HP','Attack','Defense','Special Attack','Special Defense','Speed'] as $i => $n) {
                            $bStats[] = ['name' => $n, 'value' => $boostedStats[$i]['value'], 'percent' => $boostedStats[$i]['percent']];
                        }
                    @endphp
                    <div class="stat-block">
                        @foreach($bStats as $s)
                        @php $cls = $s['percent'] >= 60 ? 'high' : ($s['percent'] >= 35 ? 'medium' : 'low'); @endphp
                        <div class="stat-row">
                            <div class="stat-meta">
                                <span>{{ $s['name'] }}</span>
                                <span class="stat-val">{{ $s['value'] }}</span>
                            </div>
                            <div class="stat-track">
                                <div class="stat-fill {{ $cls }}" style="width:{{ $s['percent'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <details style="margin-top:16px">
                        <summary style="font-size:12px;color:var(--muted);cursor:pointer">Show original stats (BST {{ $ogBST }})</summary>
                        <div style="margin-top:12px" class="stat-block">
                            @foreach(['HP','Attack','Defense','Special Attack','Special Defense','Speed'] as $i => $statName)
                            @php $cls = $ogStats[$i]['percent'] >= 60 ? 'high' : ($ogStats[$i]['percent'] >= 35 ? 'medium' : 'low'); @endphp
                            <div class="stat-row">
                                <div class="stat-meta">
                                    <span>{{ $statName }}</span>
                                    <span class="stat-val">{{ $ogStats[$i]['value'] }}</span>
                                </div>
                                <div class="stat-track">
                                    <div class="stat-fill {{ $cls }}" style="width:{{ $ogStats[$i]['percent'] }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </details>
                </div>

                <div id="tab-abilities" class="tab-panel">
                    <table class="info-table">
                        <tr>
                            <td>Ability 1</td>
                            <td>
                                <strong>{{ $abilityOne['name'] }}</strong><br>
                                <span style="font-size:12px;color:var(--muted)">{{ $abilityOne['desc'] }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Ability 2</td>
                            <td>
                                <strong>{{ $abilityTwo['name'] }}</strong><br>
                                <span style="font-size:12px;color:var(--muted)">{{ $abilityTwo['desc'] }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Hidden ability</td>
                            <td>
                                <strong>{{ $abilityHA['name'] }}</strong><br>
                                <span style="font-size:12px;color:var(--muted)">{{ $abilityHA['desc'] }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="tab-details" class="tab-panel">
                    <table class="info-table">
                        <tr>
                            <td>Glitch of</td>
                            <td>{{ $ogMon->name }}</td>
                        </tr>
                        <tr>
                            <td>Stat spread</td>
                            <td>{{ $statBalance }}</td>
                        </tr>
                        <tr>
                            <td>Rivals</td>
                            <td>{{ $rivals ?: 'None' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function switchTab(id, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
}
</script>
@endsection
