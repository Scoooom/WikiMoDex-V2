@extends('layouts.app')

@section('title', 'Edit: ' . $article->title)

@section('content')
<div class="admin-wiki-editor">
    <div class="admin-header">
        <h1>Edit Article</h1>
        <div class="admin-header-actions">
            <a href="{{ route('wiki.show', $article->slug) }}" class="btn-sm">← View</a>
            <a href="{{ route('wiki.admin.index') }}" class="btn-sm">All Articles</a>
        </div>
    </div>

    @if(session('success'))
        <div class="flash-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="flash-error">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('wiki.admin.save', $article->slug) }}" class="wiki-editor-form">
        @csrf
        @include('admin.wiki-form', ['article' => $article, 'categories' => $categories])
        <div class="editor-actions">
            <button type="submit" class="btn-accent">Save Article</button>
        </div>
    </form>
</div>
@endsection
