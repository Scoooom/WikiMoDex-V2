@extends('layouts.app')

@section('title', 'New Wiki Article')

@section('content')
<div class="admin-wiki-editor">
    <div class="admin-header">
        <h1>New Article</h1>
        <a href="{{ route('wiki.admin.index') }}" class="btn-sm">← All Articles</a>
    </div>

    @if($errors->any())
        <div class="flash-error">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('wiki.admin.create') }}" class="wiki-editor-form">
        @csrf
        @include('admin.wiki-form', ['article' => null, 'categories' => $categories])
        <div class="editor-actions">
            <button type="submit" class="btn-accent">Create Article</button>
        </div>
    </form>
</div>
@endsection
