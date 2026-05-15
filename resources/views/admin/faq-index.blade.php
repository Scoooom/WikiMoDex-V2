@extends('layouts.app')
@section('title', 'Admin — FAQ')

@section('content')
<div class="admin-shell">

    @include('admin.partials.sidebar', ['active' => 'faq'])

    <main class="admin-main">
        <div class="admin-page-header">
            <h1 class="admin-page-title">FAQ Entries</h1>
            <a href="{{ route('faq.admin.new') }}" class="btn-accent">+ New Entry</a>
        </div>

        @if(session('success'))
            <div class="flash-success">{{ session('success') }}</div>
        @endif

        @foreach($grouped as $groupName => $entries)
        <div style="margin-bottom: 28px;">
            <div class="admin-table-group-label">{{ $groupName }}</div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Question</th>
                        <th style="width:80px">Default</th>
                        <th style="width:140px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                    <tr>
                        <td class="muted">{{ $entry['order'] }}</td>
                        <td>{{ $entry['question'] }}</td>
                        <td>{{ $entry['open_by_default'] ? '✓' : '' }}</td>
                        <td class="admin-actions">
                            <a href="{{ route('faq.admin.edit', $entry['slug']) }}" class="btn-sm btn-primary">Edit</a>
                            <form method="POST" action="{{ route('faq.admin.delete', $entry['slug']) }}"
                                  onsubmit="return confirm('Delete this FAQ entry?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </main>
</div>
@endsection
