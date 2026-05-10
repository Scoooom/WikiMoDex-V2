@extends('layouts.app')

@push('head')
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $mon->name }}">
<meta property="og:description" content="{{ $mon->name }}; Ability 1: {{ $mon->ab1->name }}; Ability 2: {{ $mon->ab2->name }}; HA: {{ $mon->ha->name }}; Requires: {{ $items }}">
<meta property="og:image" content="https://void.scooom.xyz/cFront:{{ $mon->name }}.png">
@endpush

@section('content')
<section>
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="/cFront:{{ $mon->name }}.png" class="rounded-circle img-fluid" style="width: 150px;">
                    <img src="/cBack:{{ $mon->name }}.png" class="rounded-circle img-fluid" style="width: 150px;">
                    <h5 class="my-3">{{ ucwords($mon->name) }}{{ $code ? ' (' . $code . ')' : '' }}</h5>
                    <div class="d-flex justify-content-center mb-2">
                        <img src="/img/types/{{ $mon->type1 }}.png">
                        @if(!empty($mon->type2))
                            <img src="/img/types/{{ $mon->type2 }}.png">
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Ability One</p></div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">
                                <strong>{{ $mon->ab1->name }}</strong><br>
                                <small>{{ $mon->ab1->description }}</small>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Ability Two</p></div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">
                                <strong>{{ $mon->ab2->name }}</strong><br>
                                <small>{{ $mon->ab2->description }}</small>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">Hidden Ability</p></div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0">
                                <strong>{{ $mon->ha->name }}</strong><br>
                                <small>{{ $mon->ha->description }}</small>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3"><p class="mb-0">SMITTY Items</p></div>
                        <div class="col-sm-9"><p class="text-muted mb-0">{{ $items }}</p></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md">
                    <div class="card mb-4">
                        <div class="card-body">
                            <p class="mb-4">
                                <span class="text-primary font-italic">glitched</span>
                                Stats <small>BST {{ $mon->bst }}</small>
                            </p>
                            @include('partials.statblock')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
