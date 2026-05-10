@extends('layouts.app')

@section('title', 'Wiki — PokéVoid')

@section('content')
<div class="wiki-index-page">
    <div class="wiki-hero">
        <h1 class="wiki-hero-title">PokéVoid Wiki</h1>
        <p class="wiki-hero-sub">Game mechanics, systems, and secrets — straight from the source.</p>
    </div>

    <div class="wiki-grid">
        @foreach($grouped as $category => $articles)
        <div class="wiki-category-card">
            <h2 class="wiki-cat-title">{{ $category }}</h2>
            <ul class="wiki-cat-list">
                @foreach($articles as $article)
                <li>
                    <a href="{{ route('wiki.show', $article['slug']) }}" class="wiki-cat-link">
                        {{ $article['title'] }}
                    </a>
                </li>
                @endforeach
                @if($category === 'Items & Shop')
                <li><a href="{{ route('wiki.items') }}" class="wiki-cat-link">Items Reference</a></li>
                @endif
            </ul>
        </div>
        @endforeach

        {{-- Special pages --}}
        <div class="wiki-category-card">
            <h2 class="wiki-cat-title">Meta</h2>
            <ul class="wiki-cat-list">
                <li><a href="{{ route('wiki.altbuilds') }}" class="wiki-cat-link">Alt Builds</a></li>
                <li><a href="{{ route('wiki.changelog') }}" class="wiki-cat-link">Changelog</a></li>
            </ul>
        </div>
    </div>
</div>
@endsection
