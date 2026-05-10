@extends('layouts.app')

@section('title', $article->title . ' — PokéVoid Wiki')

@section('content')
<div class="wiki-layout">

    {{-- Sidebar --}}
    <aside class="wiki-sidebar">
        <div class="wiki-sidebar-inner">
            <a href="{{ route('wiki.index') }}" class="wiki-back-link">← Wiki Index</a>
            @foreach($grouped as $category => $articles)
            <div class="wiki-sidebar-section">
                <h3 class="wiki-sidebar-cat">{{ $category }}</h3>
                <ul class="wiki-sidebar-list">
                    @foreach($articles as $a)
                    <li>
                        <a href="{{ route('wiki.show', $a['slug']) }}"
                           class="wiki-sidebar-link {{ $a['slug'] === $article->slug ? 'active' : '' }}">
                            {{ $a['title'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </aside>

    {{-- Main content --}}
    <main class="wiki-content">
        <div class="wiki-article-meta">
            <span class="wiki-article-category">{{ $article->category }}</span>
            @auth
                @if(auth()->user()->discord_id === '170629697212243968')
                <a href="{{ route('wiki.admin.edit', $article->slug) }}" class="wiki-edit-btn">Edit Article</a>
                @endif
            @endauth
        </div>

        <div class="wiki-prose">
            {!! $html !!}
        </div>
    </main>

</div>
@endsection
