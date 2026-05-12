@extends('layouts.app')

@section('title', 'Edit Build')

@section('content')
<div class="wiki-page">
    <div class="wiki-page-header">
        <div>
            <a href="/build/{{ $build->slug }}.html" class="build-back-link">← Back to Build</a>
            <h1 class="wiki-page-title">Edit Build</h1>
        </div>
    </div>

    @include('partials.build-form', [
        'action'      => '/build/' . $build->slug . '/edit.html',
        'submitLabel' => 'Save Changes',
        'cancelUrl'   => '/build/' . $build->slug . '.html',
        'build'       => $build,
        'items'       => $items,
    ])
</div>
@endsection
