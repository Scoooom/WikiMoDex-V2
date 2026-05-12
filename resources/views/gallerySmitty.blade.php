@extends('layouts.app')
@section('title', 'SMITTY Pokémon')

@section('content')
<div class="container">
    <div class="section-header mt-2 mb-3">
        <span class="section-title">SMITTY Pokémon</span>
    </div>

    <div class="gallery-wrap">
        <div class="gallery-toolbar">
            <input type="text" id="gallery-search" class="gallery-search"
                placeholder="Search by name or type…">
        </div>

        <div style="overflow-x:auto">
        <table class="gallery-table">
            <thead>
                <tr>
                    <th style="width:72px">Sprite</th>
                    <th data-col="1">Name</th>
                    <th>Types</th>
                    <th data-col="3">BST</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($glitches as $glitch)
                    <tr data-search="{{ strtolower(ucwords($glitch->name)) }}">
                        <td>
                            <img src="/cFront:{{ $glitch->name }}.png"
                                class="sprite-sm"
                                alt="{{ ucwords($glitch->name) }}">
                        </td>
                        <td data-sort="{{ $glitch->name }}">
                            <strong>{{ ucwords($glitch->name) }}</strong>
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap">
                                <span class="type-badge type-{{ $glitch->type1 }}">
                                    {{ \App\Services\PokemonService::getTypeName($glitch->type1) }}
                                </span>
                                @if(!empty($glitch->type2))
                                <span class="type-badge type-{{ $glitch->type2 }}">
                                    {{ \App\Services\PokemonService::getTypeName($glitch->type2) }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td data-sort="{{ $glitch->bst }}">
                            <span style="font-family:var(--font-mono);font-size:13px">{{ $glitch->bst }}</span>
                        </td>
                        <td>
                            <a href="/smitty:{{ $glitch->name }}.html"
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
