@php
    $stats = [
        'HP'              => $mon->hp,
        'Attack'          => $mon->atk,
        'Defense'         => $mon->def,
        'Special Attack'  => $mon->spatk,
        'Special Defense' => $mon->spdef,
        'Speed'           => $mon->spd,
    ];
@endphp
<details>
    <summary>View Stats</summary>
    @foreach($stats as $statName => $value)
        <p class="mb-1" style="font-size: .77rem;">{{ $statName }} <small>{{ $value }}</small></p>
        <div class="progress rounded" style="height: 5px;">
            <div class="progress-bar" role="progressbar"
                style="width: {{ floor(($value / 255) * 100) }}%"
                aria-valuenow="{{ $value }}"
                aria-valuemin="0" aria-valuemax="255"></div>
        </div>
    @endforeach
</details>
