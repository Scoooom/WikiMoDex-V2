@php
    $stats = [
        'HP'           => $mon->hp,
        'Attack'       => $mon->atk,
        'Defense'      => $mon->def,
        'Sp. Attack'   => $mon->spatk,
        'Sp. Defense'  => $mon->spdef,
        'Speed'        => $mon->spd,
    ];
@endphp
<div class="stat-block">
    @foreach($stats as $statName => $value)
        @php
            $pct = min(100, floor(($value / 255) * 100));
            $cls = $pct >= 60 ? 'high' : ($pct >= 35 ? 'medium' : 'low');
        @endphp
        <div class="stat-row">
            <div class="stat-meta">
                <span>{{ $statName }}</span>
                <span class="stat-val">{{ $value }}</span>
            </div>
            <div class="stat-track">
                <div class="stat-fill {{ $cls }}" style="width:{{ $pct }}%"></div>
            </div>
        </div>
    @endforeach
</div>
