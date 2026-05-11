@extends('layouts.app')

@section('title', 'Admin — Wiki Articles')

@section('content')
<div class="admin-shell">

    @include('admin.partials.sidebar', ['active' => 'wiki'])

    <main class="admin-main">
        <div class="admin-page-header">
            <h1 class="admin-page-title">Wiki Articles</h1>
            <a href="{{ route('wiki.admin.new') }}" class="btn-accent">+ New Article</a>
        </div>

        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Order</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($articles as $article)
                <tr>
                    <td>{{ $article->title }}</td>
                    <td><span class="badge">{{ $article->category }}</span></td>
                    <td>{{ $article->order }}</td>
                    <td><code>{{ $article->slug }}</code></td>
                    <td class="admin-actions">
                        <a href="{{ route('wiki.show', $article->slug) }}" class="btn-sm">View</a>
                        <a href="{{ route('wiki.admin.edit', $article->slug) }}" class="btn-sm btn-primary">Edit</a>
                        <form method="POST" action="{{ route('wiki.admin.delete', $article->slug) }}"
                              onsubmit="return confirm('Delete {{ addslashes($article->title) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</div>
@endsection
