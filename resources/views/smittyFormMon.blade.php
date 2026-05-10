@extends('layouts.app')

@push('head')
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ ucwords($mon->name) }}">
<meta property="og:description" content="{{ ucwords($mon->name) }}; {{ $mon->ab1->name }} / {{ $mon->ab2->name }} / {{ $mon->ha->name }}; SMITTY of {{ $mons }}; Requires: {{ $items }}">
<meta property="og:image" content="https://void.scooom.xyz/cFront:{{ $mon->name }}.png">
@endpush

@section('content')
<div class="container">
    <div class="mon-detail-grid mt-2">

        <div class="mon-sidebar">
            <div class="card">
                <div class="card-body">
                    <div class="sprite-duo">
                        <img src="/cFront:{{ $mon->name }}.png" class="sprite-lg" alt="{{ ucwords($mon->name) }} front">
                        <img src="/cBack:{{ $mon->name }}.png"  class="sprite-lg back" alt="{{ ucwords($mon->name) }} back">
                    </div>
                    <div class="mon-name">{{ ucwords($mon->name) }}{{ $code ? ' (' . $code . ')' : '' }}</div>
                    <div class="type-badges">
                        <span class="type-badge type-{{ $mon->type1 }}">
                            {{ \App\Services\PokemonService::getTypeName($mon->type1) }}
                        </span>
                        @if(!empty($mon->type2))
                        <span class="type-badge type-{{ $mon->type2 }}">
                            {{ \App\Services\PokemonService::getTypeName($mon->type2) }}
                        </span>
                        @endif
                    </div>
                    @if($items)
                    <div style="margin-top:8px;font-size:12px;color:var(--muted)">
                        <div style="margin-bottom:4px;font-weight:600">Required items</div>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:center">
                            @foreach(explode(',', $items) as $item)
                            <span class="smitty-item">{{ trim($item) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="card mb-3">
                <div class="tab-strip">
                    <button class="tab-btn active" onclick="switchTab('stats', this)">Stats</button>
                    <button class="tab-btn" onclick="switchTab('abilities', this)">Abilities</button>
                    <button class="tab-btn" onclick="switchTab('details', this)">Details</button>
                </div>

                <div id="tab-stats" class="tab-panel active">
                    <p style="font-size:12px;color:var(--muted);margin-bottom:14px">
                        SMITTY stats · BST <strong style="color:var(--text)">{{ $mon->bst }}</strong>
                    </p>
                    @include('partials.statblock')
                </div>

                <div id="tab-abilities" class="tab-panel">
                    <table class="info-table">
                        <tr>
                            <td>Ability 1</td>
                            <td>
                                <strong>{{ $mon->ab1->name }}</strong><br>
                                <span style="font-size:12px;color:var(--muted)">{{ $mon->ab1->description }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Ability 2</td>
                            <td>
                                <strong>{{ $mon->ab2->name }}</strong><br>
                                <span style="font-size:12px;color:var(--muted)">{{ $mon->ab2->description }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Hidden ability</td>
                            <td>
                                <strong>{{ $mon->ha->name }}</strong><br>
                                <span style="font-size:12px;color:var(--muted)">{{ $mon->ha->description }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="tab-details" class="tab-panel">
                    <table class="info-table">
                        <tr>
                            <td>SMITTY Pokémon</td>
                            <td>{{ $mons }}</td>
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
