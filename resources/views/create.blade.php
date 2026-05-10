@extends('layouts.app')

@section('content')
<div class="mt-4">
    <h1>Upload a glitch to share!</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="/upload.html" method="post" enctype="multipart/form-data">
        @csrf
        <div class="custom-file">
            <input type="file" class="custom-file-input" name="pokeData" id="customFile">
            <label class="custom-file-label" for="customFile">Choose file</label>
            <button type="submit" class="btn btn-primary mt-2">Submit</button>
        </div>
    </form>
</div>
@endsection
