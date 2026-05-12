@extends('layouts.app')
@section('title', 'Mod Glitch Gallery')

@section('content')
<div class="container">
    <div class="section-header mt-2 mb-3">
        <span class="section-title">Mod Glitch Forms</span>
        <span id="gallery-upload-slot"></span>
    </div>
    <script>
    fetch('/me.json',{credentials:'same-origin'}).then(r=>r.json()).then(me=>{
        if(!me.authed) return;
        const s=document.getElementById('gallery-upload-slot');
        if(s) s.innerHTML='<a href="/create.html" class="btn btn-primary btn-sm">+ Upload glitch</a>';
    }).catch(()=>{});
    </script>

    <div class="gallery-wrap">
        <div class="gallery-toolbar">
            <input type="text" id="gallery-search" class="gallery-search"
                placeholder="Search by name, creator, or base Pokémon…">
        </div>

        <div style="overflow-x:auto">
        <table class="gallery-table">
            <thead>
                <tr>
                    <th style="width:72px">Sprite</th>
                    <th data-col="1">Name</th>
                    <th data-col="2">Creator</th>
                    <th data-col="3">Glitch of</th>
                    <th>Types</th>
                    <th data-col="5">Rating</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($glitches as $glitch)
                    @php
                        $mon2 = $glitch->getJsonData();
                        $ogMon = $glitch->getOGMon();
                        $ogName = ucwords(str_replace('-', ' ', $ogMon->name));
                    @endphp
                    <tr data-search="{{ strtolower($glitch->name . ' ' . $glitch->creator->username . ' ' . $ogName) }}">
                        <td>
                            <img src="/front:{{ $glitch->id }}.png"
                                class="sprite-sm"
                                alt="{{ $glitch->name }}">
                        </td>
                        <td data-sort="{{ $glitch->name }}">
                            <strong>{{ $glitch->name }}</strong>
                        </td>
                        <td data-sort="{{ $glitch->creator->username }}">
                            <a href="/u:{{ $glitch->creator->username }}.html">{{ $glitch->creator->username }}</a>
                        </td>
                        <td data-sort="{{ $ogName }}">{{ $ogName }}</td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap">
                                <span class="type-badge type-{{ $mon2->primaryType }}">
                                    {{ \App\Services\PokemonService::getTypeName($mon2->primaryType) }}
                                </span>
                                @if(isset($mon2->secondaryType) && $mon2->secondaryType !== $mon2->primaryType)
                                <span class="type-badge type-{{ $mon2->secondaryType }}">
                                    {{ \App\Services\PokemonService::getTypeName($mon2->secondaryType) }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td data-sort="{{ $glitch->getRating() }}">
                            <span style="color:var(--accent2);font-weight:600">♥ {{ $glitch->getRating() }}</span>
                        </td>
                        <td>
                            <a href="/g:{{ urlencode(str_replace(' ', '', $glitch->name)) }}:{{ $glitch->id }}.html"
                                class="btn btn-secondary btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        <div class="pagination">
            <span class="pagination-info" id="pag-info"></span>
            <div class="pagination-btns" id="pag-btns"></div>
        </div>
    </div>
</div>

@include('partials.gallery-js')
@endsection
