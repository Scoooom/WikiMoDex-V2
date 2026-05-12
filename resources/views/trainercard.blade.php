@extends('layouts.app')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
@endpush

@section('content')
@php
    $save = $user->getSave();
    if (is_array($save) && isset($save['er'])) {
        echo '<div class="alert alert-danger">Trainer Card Error [0001] — Please DM scooom on Discord if you encountered this page in error.</div>';
        return;
    }
    if ($save->getSystemData() === null) {
        echo '<div class="alert alert-danger">Trainer Card Error [0002] — Save file appears invalid or corrupt. Please re-upload your system save file.</div>';
        return;
    }

    $defeatedRivals  = $save->getDefeatedRivals();
    $glitchUnlocks   = $save->getGlitchUnlocks();
    $smittyUnlocks   = $save->getSmittyUnlocks();
    $formUnlocks     = $save->getFormUnlocks();

    $totalRivals  = count(array_filter($defeatedRivals, fn($r) => !is_string(array_search($r, $defeatedRivals))));
    $beatenRivals = count(array_filter($defeatedRivals, fn($r) => !is_string(array_search($r, $defeatedRivals)) && $r['defeated'] === 'true'));
    $glitchCount  = count($glitchUnlocks);
    $smittyCount  = count($smittyUnlocks);
    $submittedCount = $user->glitches()->count();

    $modForms = collect($formUnlocks['modFormsUnlocked'])->map(function($unlock) {
        $name = preg_replace('/(.*)_(.*)/', '$2', $unlock);
        $name = str_replace(' ', '', $name);
        return \App\Models\Glitch::where('name', $name)->first();
    })->filter();
    $modCount = $modForms->count();

    $uniSmitty = collect($formUnlocks['uniSmittyUnlocks'])->filter()->map(function($unlock) {
        $name = preg_replace('/(.*?)_(.*)/', '$2', $unlock);
        $name = str_replace(' ', '', $name);
        return \App\Services\BuiltInService::loadSmitty($name);
    })->filter();
    $uniSmittyCount = $uniSmitty->count();

    $color = $user->tc_color ?? 'blue';

    $schemes = [
        'blue'   => ['bg1'=>'#4a90c8','bg2'=>'#2a5a8a','header'=>'#1a3a5a','field'=>'#8ec8f0','text'=>'#ffffff','dark'=>'#0a1a2a','bar'=>'#4caf7d','barBg'=>'#1a3a5a'],
        'red'    => ['bg1'=>'#c84a4a','bg2'=>'#8a2a2a','header'=>'#5a1a1a','field'=>'#f08e8e','text'=>'#ffffff','dark'=>'#2a0a0a','bar'=>'#f0c040','barBg'=>'#5a1a1a'],
        'green'  => ['bg1'=>'#4a9a5a','bg2'=>'#2a6a3a','header'=>'#1a4a2a','field'=>'#8ef0a0','text'=>'#ffffff','dark'=>'#0a2a10','bar'=>'#f0e040','barBg'=>'#1a4a2a'],
        'gold'   => ['bg1'=>'#c8a030','bg2'=>'#8a6a10','header'=>'#5a4a08','field'=>'#f0d880','text'=>'#ffffff','dark'=>'#2a1a00','bar'=>'#4caf7d','barBg'=>'#5a4a08'],
        'purple' => ['bg1'=>'#7a4ac8','bg2'=>'#4a1a8a','header'=>'#2a0a5a','field'=>'#c08ef0','text'=>'#ffffff','dark'=>'#100a2a','bar'=>'#f0a0e0','barBg'=>'#2a0a5a'],
        'black'  => ['bg1'=>'#444444','bg2'=>'#222222','header'=>'#111111','field'=>'#888888','text'=>'#ffffff','dark'=>'#000000','bar'=>'#4caf7d','barBg'=>'#111111'],
    ];
    $s = $schemes[$color] ?? $schemes['blue'];

    $isOwner = Auth::check() && Auth::user()->username === $user->username;
    $rivalPct = $totalRivals > 0 ? round(($beatenRivals / $totalRivals) * 100) : 0;
@endphp

<style>
.tc-wrap { display:flex; flex-direction:column; align-items:center; gap:24px; padding:24px 0; }

.tc-card {
    width:480px; max-width:calc(100vw - 32px);
    border-radius:12px; overflow:hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.2);
    font-family:'Press Start 2P',monospace;
    border:3px solid {{ $s['dark'] }};
}

.tc-header {
    background:{{ $s['header'] }};
    padding:6px 12px;
    display:flex; justify-content:space-between; align-items:center;
    border-bottom:2px solid {{ $s['dark'] }};
}
.tc-header-title { font-size:9px; color:{{ $s['field'] }}; letter-spacing:1px; }
.tc-header-id    { font-size:8px; color:{{ $s['field'] }}; }

.tc-body {
    background:linear-gradient(160deg, {{ $s['bg1'] }} 0%, {{ $s['bg2'] }} 100%);
    padding:14px;
    display:grid; grid-template-columns:1fr 96px; gap:12px;
}

.tc-fields { display:flex; flex-direction:column; gap:7px; }

.tc-field {
    display:flex; align-items:center; gap:8px;
    background:rgba(0,0,0,.18); border-radius:3px; padding:5px 8px;
}
.tc-field-label { font-size:6px; color:{{ $s['field'] }}; min-width:60px; letter-spacing:.5px; }
.tc-field-value { font-size:8px; color:{{ $s['text'] }}; text-shadow:1px 1px 0 {{ $s['dark'] }}; }

.tc-avatar-col { display:flex; align-items:flex-start; justify-content:center; }
.tc-avatar {
    width:80px; height:80px; border-radius:4px;
    border:3px solid {{ $s['dark'] }}; box-shadow:3px 3px 0 {{ $s['dark'] }};
    object-fit:cover;
}

.tc-rivals-section {
    background:{{ $s['header'] }};
    padding:10px 14px;
    border-top:2px solid {{ $s['dark'] }};
}
.tc-rivals-label { font-size:6px; color:{{ $s['field'] }}; letter-spacing:1px; margin-bottom:7px; }

.tc-bar-track {
    background:{{ $s['barBg'] }}; border-radius:2px; height:10px;
    border:1px solid {{ $s['dark'] }}; overflow:hidden;
}
.tc-bar-fill {
    background:{{ $s['bar'] }}; height:100%; width:{{ $rivalPct }}%;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.3);
}
.tc-bar-count { font-size:7px; color:{{ $s['field'] }}; margin-top:5px; text-align:right; }

.tc-rival-sprites { display:flex; flex-wrap:wrap; gap:4px; margin-top:8px; }
.tc-rival-pip {
    width:24px; height:24px; border-radius:50%; overflow:hidden;
    border:1px solid {{ $s['dark'] }}; opacity:.3; filter:grayscale(1);
}
.tc-rival-pip.beaten { opacity:1; filter:none; box-shadow:0 0 5px {{ $s['bar'] }}; }
.tc-rival-pip img { width:100%; height:100%; object-fit:cover; }

.tc-color-btn {
    width:28px; height:28px; border-radius:50%; border:3px solid transparent;
    cursor:pointer; transition:transform .15s, border-color .15s;
}
.tc-color-btn:hover { transform:scale(1.15); }
.tc-color-btn.active { border-color:var(--accent); }
.tc-color-btn[data-color="blue"]   { background:#4a90c8; }
.tc-color-btn[data-color="red"]    { background:#c84a4a; }
.tc-color-btn[data-color="green"]  { background:#4a9a5a; }
.tc-color-btn[data-color="gold"]   { background:#c8a030; }
.tc-color-btn[data-color="purple"] { background:#7a4ac8; }
.tc-color-btn[data-color="black"]  { background:#444444; }
</style>

<div class="tc-wrap">

    <div class="tc-card">
        <div class="tc-header">
            <span class="tc-header-title">TRAINER CARD</span>
            <span class="tc-header-id">ID No.{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="tc-body">
            <div class="tc-fields">
                <div class="tc-field">
                    <span class="tc-field-label">■ NAME</span>
                    <span class="tc-field-value">{{ strtoupper($user->username) }}</span>
                </div>
                <div class="tc-field">
                    <span class="tc-field-label">■ GLITCHES</span>
                    <span class="tc-field-value">{{ $glitchCount + $modCount }}</span>
                </div>
                <div class="tc-field">
                    <span class="tc-field-label">■ SMITTY</span>
                    <span class="tc-field-value">{{ $smittyCount + $uniSmittyCount }}</span>
                </div>
                <div class="tc-field">
                    <span class="tc-field-label">■ SUBMITTED</span>
                    <span class="tc-field-value">{{ $submittedCount }}</span>
                </div>
            </div>
            <div class="tc-avatar-col">
                <img src="{{ $user->getAvatarURL() }}" class="tc-avatar" alt="{{ $user->username }}">
            </div>
        </div>

        <div class="tc-rivals-section">
            <div class="tc-rivals-label">■ RIVALS DEFEATED</div>
            <div class="tc-bar-track"><div class="tc-bar-fill"></div></div>
            <div class="tc-bar-count">{{ $beatenRivals }} / {{ $totalRivals }}</div>
            <div class="tc-rival-sprites">
                @foreach($defeatedRivals as $i => $rival)
                    @if(!is_string($i))
                    @php
                        $beaten   = $rival['defeated'] === 'true';
                        $rivalImg = strtolower(str_replace(' ', '_', $rival['name']));
                    @endphp
                    <div class="tc-rival-pip {{ $beaten ? 'beaten' : '' }}" title="{{ $rival['name'] }}">
                        <img src="/rivals/{{ $rivalImg }}.png" alt="{{ $rival['name'] }}" onerror="this.parentElement.style.display='none'">
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    @if($isOwner)
    <div>
        <div style="font-size:12px;color:var(--muted);text-align:center;margin-bottom:10px">Card colour</div>
        <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap">
            @foreach(['blue','red','green','gold','purple','black'] as $c)
            <button class="tc-color-btn {{ $color === $c ? 'active' : '' }}"
                    data-color="{{ $c }}"
                    title="{{ ucfirst($c) }}"
                    onclick="setColor('{{ $c }}')"></button>
            @endforeach
        </div>
    </div>

    <form id="tc-color-form" method="POST" action="/u:{{ $user->username }}.html" style="display:none">
        @csrf
        <input type="hidden" name="action" value="setTcColor">
        <input type="hidden" name="tc_color" id="tc-color-input" value="{{ $color }}">
    </form>
    <script>
    function setColor(c) {
        document.getElementById('tc-color-input').value = c;
        document.getElementById('tc-color-form').submit();
    }
    </script>
    @endif

</div>
@endsection
