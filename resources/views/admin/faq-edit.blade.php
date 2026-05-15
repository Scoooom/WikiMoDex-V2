@extends('layouts.app')
@section('title', 'Edit FAQ: ' . $entry->question)

@section('content')
<div class="admin-wiki-editor">
    <div class="admin-header">
        <h1>Edit FAQ Entry</h1>
        <div class="admin-header-actions">
            <a href="{{ route('faq.admin.index') }}" class="btn-sm">← All FAQs</a>
            <a href="{{ url('/faq.html#' . $entry->slug) }}" class="btn-sm" target="_blank">View ↗</a>
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

    <form method="POST" action="{{ route('faq.admin.save', $entry->slug) }}" class="wiki-editor-form">
        @csrf
        @include('admin.faq-form', ['entry' => $entry, 'groups' => $groups])
        <div class="editor-actions">
            <button type="submit" class="btn-accent">Save Entry</button>
        </div>
    </form>
</div>
@endsection
