@extends('layouts.app')

@section('content')
<div class="container" style="text-align:center;padding-top:60px">
    <div style="font-size:80px;font-weight:700;color:var(--border);line-height:1;margin-bottom:16px">404</div>
    <div style="font-size:20px;font-weight:600;color:var(--accent3);margin-bottom:10px">Page not found</div>
    <p style="font-size:14px;color:var(--muted);margin-bottom:28px">
        The page you're looking for doesn't exist or has been moved.
    </p>
    <a href="/" class="btn btn-primary">Go home</a>
</div>
@endsection
