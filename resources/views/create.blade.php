@extends('layouts.app')
@section('title', 'Upload Glitch')

@section('content')
<div class="container">
    <div class="section-header mt-2 mb-3">
        <span class="section-title">Upload a glitch form</span>
    </div>

    @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    <div class="card" style="max-width:480px">
        <div class="card-body">
            <p style="font-size:13.5px;color:var(--muted);margin-bottom:18px;line-height:1.7">
                Upload your <code>.json</code> glitch file and we'll extract your glitch form.
                Your form will appear in the gallery once submitted.
            </p>
            <form action="/upload.html" method="post" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:14px">
                    <label style="font-size:12px;font-weight:600;color:var(--muted);display:block;margin-bottom:6px">
                        Glitch file (.json)
                    </label>
                    <input type="file" name="pokeData" class="form-input" accept=".json">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Upload glitch</button>
            </form>
        </div>
    </div>
</div>
@endsection
