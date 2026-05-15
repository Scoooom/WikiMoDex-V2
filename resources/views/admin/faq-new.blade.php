@extends('layouts.app')
@section('title', 'New FAQ Entry')

@section('content')
<div class="admin-wiki-editor">
    <div class="admin-header">
        <h1>New FAQ Entry</h1>
        <div class="admin-header-actions">
            <a href="{{ route('faq.admin.index') }}" class="btn-sm">← All FAQs</a>
        </div>
    </div>

    @if($errors->any())
        <div class="flash-error">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('faq.admin.create') }}" class="wiki-editor-form">
        @csrf
        @include('admin.faq-form', ['entry' => null, 'groups' => $groups])
        <div class="editor-actions">
            <button type="submit" class="btn-accent">Create Entry</button>
        </div>
    </form>
</div>
@endsection
