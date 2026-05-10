@extends('layouts.app')

@push('head')
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $glitch->name }} | {{ $creator->username }}">
<meta property="og:description" content="{{ $glitch->name }}; Primary {{ $abilityOne['name'] }}; Ability 2: {{ $abilityTwo['name'] }}; HA: {{ $abilityHA['name'] }}; Rivals: {{ $rivals }}">
<meta property="og:image" content="https://void.scooom.com/front:{{ $glitch->id }}.png">
@endpush

@section('content')
<section>
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="/front:{{ $glitch->id }}.png" class="rounded-circle img-fluid" style="width: 150px;">
                    <img src="/back:{{ $glitch->id }}.png" class="rounded-circle img-fluid" style="width: 150px;">
                    <h5 class="my-3">{{ $glitch->name }}</h5>
                    <p class="text-muted mb-1">Created By: <a href="/u:{{ $creator->username }}.html">{{ $creator->username }}</a></p>
                    <div class="d-flex justify-content-center mb-2">
                        <img src="/img/types/{{ $mon2->primaryType }}.png">
                        <img src="/img/types/{{ $mon2->secondaryType }}.png">
                    </div>
                    <div class="d-flex justify-content-center mb-2">
                        <a href="/d:{{ $glitch->id }}.html" class="btn btn-primary">Download</a>
                        &nbsp;
                        <button type="button" class="btn btn-secondary">Rating: {{ $rating }}</button>
                    </div>

                    @auth
                    <div class="d-flex justify-content-center mb-2">
                        @if($userLikesGlitch)
                            <form action="/rLike:{{ $glitch->id }}.html" method="post">
                                @csrf
                                <input type="hidden" name="returnURL" value="{{ url()->current() }}">
                                <button type="submit" class="btn btn-success">Remove Like</button>
                            </form>
                        @else
                            <form action="/like:{{ $glitch->id }}.html" method="post">
                                @csrf
                                <input type="hidden" name="returnURL" value="{{ url()->current() }}">
                                <button type="submit" class="btn btn-success">Like</button>
                            </form>
                        @endif
                    </div>
                    @endauth
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Glitched Pokemon</p></div>
                        <div class="col-sm-9"><p class="text-muted mb-0">{{ $ogMon->name }}</p></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Stat Spread Type</p></div>
                        <div class="col-sm-9"><p class="text-muted mb-0">{{ $statBalance }}</p></div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Ability One</p></div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">
                                <strong>{{ $abilityOne['name'] }}</strong><br>
                                <small>{{ $abilityOne['desc'] }}</small>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Ability Two</p></div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">
                                <strong>{{ $abilityTwo['name'] }}</strong><br>
                                <small>{{ $abilityTwo['desc'] }}</small>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Hidden Ability</p></div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">
                                <strong>{{ $abilityHA['name'] }}</strong><br>
                                <small>{{ $abilityHA['desc'] }}</small>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Rivals</p></div>
                        <div class="col-sm-9"><p class="text-muted mb-0">{{ $rivals }}</p></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md">
                    <div class="card mb-4">
                        <div class="card-body">
                            <p class="mb-4"><span class="text-primary font-italic">boosted</span> Stats <small>BST {{ $boostedBST }}</small></p>
                            <details>
                                <summary>See Boosted Stats</summary>
                                @foreach(['HP', 'Attack', 'Defense', 'Special Attack', 'Special Defense', 'Speed'] as $i => $statName)
                                <p class="mb-1" style="font-size: .77rem;">{{ $statName }} <small>{{ $boostedStats[$i]['value'] }}</small></p>
                                <div class="progress rounded" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $boostedStats[$i]['percent'] }}%"
                                        aria-valuenow="{{ $boostedStats[$i]['value'] }}"
                                        aria-valuemin="0" aria-valuemax="255"></div>
                                </div>
                                @endforeach
                            </details>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="card mb-4">
                        <div class="card-body">
                            <p class="mb-4"><span class="text-primary font-italic">original</span> Stats <small>BST {{ $ogBST }}</small></p>
                            <details>
                                <summary>See Original Stats</summary>
                                @foreach(['HP', 'Attack', 'Defense', 'Special Attack', 'Special Defense', 'Speed'] as $i => $statName)
                                <p class="mb-1" style="font-size: .77rem;">{{ $statName }} <small>{{ $ogStats[$i]['value'] }}</small></p>
                                <div class="progress rounded" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ $ogStats[$i]['percent'] }}%"
                                        aria-valuenow="{{ $ogStats[$i]['value'] }}"
                                        aria-valuemin="0" aria-valuemax="255"></div>
                                </div>
                                @endforeach
                            </details>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
