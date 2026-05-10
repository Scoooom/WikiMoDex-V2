@extends('layouts.app')

@push('head')
<script>
    var floor  = Math.floor
    var ceil   = Math.ceil
    var round  = Math.round
    var abs    = Math.abs
    var min    = Math.min
    var max    = Math.max
    var log    = Math.log
    var sqrt   = Math.sqrt
    var pow    = Math.pow
    function map(v, a, b, c, d) { return c + (d - c) * ((v - a) / (b - a)) }
    function random(a, b) {
        if (a === undefined) return Math.random()
        if (b === undefined) return Math.random() * a
        return a + Math.random() * (b - a)
    }
</script>
<script src="https://unpkg.com/pokeapi-js-wrapper@1.2.8/dist/index.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js" crossorigin="anonymous"></script>
<script src="/gacha/pokerogue_data/pokemonData.js"></script>
<script src="/gacha/phaser-rand.js"></script>
<link rel="stylesheet" href="/gacha/gacha.css">
@endpush

@section('content')
<div class="container">
    <div id="gacha-header">
        <button class="nav-btn" id="btn-prev-year">«</button>
        <button class="nav-btn" id="btn-prev-month">‹</button>
        <span id="month-label"></span>
        <button class="nav-btn" id="btn-today">Today</button>
        <button class="nav-btn" id="btn-next-month">›</button>
        <button class="nav-btn" id="btn-next-year">»</button>
    </div>

    <div id="gacha-body">
        <div id="cal-grid-wrap">
            <div id="weekdays">
                <div>Sunday</div><div>Monday</div><div>Tuesday</div>
                <div>Wednesday</div><div>Thursday</div><div>Friday</div><div>Saturday</div>
            </div>
            <div id="cal-grid"></div>
        </div>
        <div id="sidebar" class="hidden">
            <div id="sidebar-date"></div>
            <div id="sidebar-sub"></div>
            <div id="sidebar-sprite-wrap">
                <img id="sidebar-sprite" src="" alt="">
            </div>
            <div id="sidebar-legend-name"></div>
            <div id="sidebar-divider"></div>
            <div id="sidebar-rus-label">Pokérus</div>
            <div id="sidebar-rus-grid"></div>
        </div>
    </div>
</div>

<script src="/gacha/gacha.js"></script>
@endsection
