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
            </ul>
        </div>
        @endforeach
    </div>
</div>
@endsection
