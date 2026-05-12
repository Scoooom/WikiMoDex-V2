@extends('layouts.app')

@section('title', 'Lost in the Void')

@section('content')
<style>
.void-404 {
    min-height: 70vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 40px 20px;
    position: relative;
}

.void-splash {
    position: fixed;
    inset: 0;
    z-index: 0;
    background-size: cover;
    background-position: center;
    opacity: 0.07;
    pointer-events: none;
}

.void-content {
    position: relative;
    z-index: 1;
}

.void-spinda {
    width: 180px;
    height: 180px;
    image-rendering: pixelated;
    margin-bottom: 24px;
    animation: void-spin 8s linear infinite;
    opacity: 0.9;
}

@keyframes void-spin {
    0%   { transform: rotate(0deg) scale(1); }
    25%  { transform: rotate(5deg) scale(1.03); }
    50%  { transform: rotate(0deg) scale(1); }
    75%  { transform: rotate(-5deg) scale(1.03); }
    100% { transform: rotate(0deg) scale(1); }
}

.void-404-num {
    font-size: 96px;
    font-weight: 800;
    color: var(--border);
    line-height: 1;
    letter-spacing: -4px;
    margin-bottom: 8px;
}

.void-404-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--accent3);
    margin-bottom: 12px;
}

.void-404-messages {
    font-size: 14px;
    color: var(--muted);
    margin-bottom: 32px;
    max-width: 400px;
    line-height: 1.6;
}

.void-404-messages em {
    color: var(--accent2);
    font-style: normal;
}
</style>

@php
    $messages = [
        "You've wandered too deep into the Void. Even Spinda doesn't know where you are.",
        "404: Glitch not found. Have you tried turning the void off and on again?",
        "This page got corrupted. Spinda has been spinning in confusion ever since.",
        "The rival you're looking for has already fled. This page fled too.",
        "You've unlocked a secret form: <em>404-type</em>. It only knows the move 'Go Home'.",
        "Spinda used Teeter Dance. It's confused! The page fainted.",
        "This URL rolled a nat 1 on the existence check.",
    ];
    $msg = $messages[array_rand($messages)];
    $bg = rand(0, 1);
@endphp

<div class="void-splash" id="void-splash"></div>

<div class="void-404">
    <div class="void-content">
        {{-- Spinda placeholder — drop spinda.png into public/images/ to activate --}}
        @if(file_exists(public_path('images/spinda-404.png')))
        <img src="/images/spinda-404.png" class="void-spinda" alt="Spinda">
        @endif

        <div class="void-404-num">404</div>
        <div class="void-404-title">Lost in the Void</div>
        <p class="void-404-messages">{!! $msg !!}</p>
        <a href="/" class="btn btn-primary">Escape the Void</a>
    </div>
</div>

<script>
const bg = {{ $bg }};
document.getElementById('void-splash').style.backgroundImage = `url('/pokevoid-title-bg/${bg}.png')`;
</script>
@endsection
