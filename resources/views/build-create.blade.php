@extends('layouts.app')

@section('title', 'Submit a Build')

@section('content')
<div class="wiki-page">
    <div class="wiki-page-header">
        <div>
            <a href="/builds.html" class="build-back-link">← All Builds</a>
            <h1 class="wiki-page-title">Submit a Build</h1>
        </div>
    </div>

    @include('partials.build-form', [
        'action'      => '/builds',
        'submitLabel' => 'Submit Build',
        'cancelUrl'   => '/builds.html',
        'build'       => null,
        'items'       => $items,
    ])
</div>
@endsection
