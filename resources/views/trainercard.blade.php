@extends('layouts.app')

@section('content')
@php
    $save = $user->getSave();
    if (is_array($save) && isset($save['er'])) {
        echo '<div class="alert alert-danger">Trainer Card Error [0001] — Please DM scooom on Discord if you encountered this page in error.</div>';
        return;
    }
    if ($save->getSystemData() === null) {
        echo '<div class="alert alert-danger">Trainer Card Error [0002] — Save file appears invalid or corrupt. Please re-upload your system save file, or DM scooom on Discord.</div>';
        return;
    }

    $defeatedRivals = $save->getDefeatedRivals();
    $glitchUnlocks  = $save->getGlitchUnlocks();
    $smittyUnlocks  = $save->getSmittyUnlocks();
    $formUnlocks    = $save->getFormUnlocks();

    $modForms = collect($formUnlocks['modFormsUnlocked'])->map(function($unlock) {
        $name = preg_replace('/(.*)_(.*)/', '$2', $unlock);
        $name = str_replace(' ', '', $name);
        return \App\Models\Glitch::where('name', $name)->first();
    })->filter();

    $uniSmitty = collect($formUnlocks['uniSmittyUnlocks'])->filter()->map(function($unlock) {
        $name = preg_replace('/(.*?)_(.*)/', '$2', $unlock);
        $name = str_replace(' ', '', $name);
        return \App\Services\BuiltInService::loadSmitty($name);
    })->filter();

    // Owner sees all sections; public respects user's toggle settings
    $s = $isOwner
        ? array_fill_keys(['rivals','core','mod','smitty','unismitty','submitted'], true)
        : $user->getTcSections();
@endphp

<style>
.tc-grid { display: grid; grid-template-columns: 260px 1fr; gap: 20px; align-items: start; }
.tc-section { margin-bottom: 20px; }
.tc-mon-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; }
.tc-mon-item { background: var(--card); border: 1px solid var(--border); border-radius: 9px; padding: 10px 8px; text-align: center; transition: border-color .15s; }
.tc-mon-item:hover { border-color: var(--accent); }
.tc-mon-item a { display: block; }
.tc-mon-item a img { width: 80px; height: 80px; object-fit: contain; image-rendering: pixelated; display: block; margin: 0 auto; background: var(--surface); border-radius: 50%; padding: 4px; border: 1px solid var(--border); }
.tc-mon-item span { font-size: 11px; color: var(--accent2); display: block; margin-top: 5px; }
.rival-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; }
.rival-item { text-align: center; }
.rival-wrap { position: relative; width: 72px; height: 72px; margin: 0 auto 5px; }
.rival-wrap img.rival-sprite { width: 72px; height: 72px; border-radius: 50%; background: var(--surface); object-fit: cover; }
.rival-wrap img.rival-sprite.gray { filter: grayscale(1); opacity: .5; }
.rival-status { position: absolute; bottom: 2px; right: 2px; width: 16px; height: 16px; border-radius: 50%; border: 2px solid var(--card); }
.rival-status.defeated { background: #4caf7d; }
.rival-status.not-defeated { background: var(--dim); }
.rival-name { font-size: 10px; color: var(--muted); }
.tc-owner-notice { font-size: 12px; color: var(--muted); margin-bottom: 16px; display:flex; align-items:center; gap:8px; }
</style>

<div class="container mt-2">

    @if($isOwner)
    <div class="tc-owner-notice">
        👁 You're viewing your full trainer card.
        <a href="/trainercard-public:{{ $user->username }}.html" style="color:var(--accent)">Preview public view</a> ·
        <a href="/settings.html" style="color:var(--accent)">Edit settings</a> ·
        <a href="/trainercard-img:{{ $user->username }}.png" target="_blank" style="color:var(--accent)">🖼 Share image</a>
    </div>
    @endif

    <div class="tc-grid">

        {{-- Sidebar --}}
        <div>
            <div class="card">
                <div class="card-body" style="text-align:center">
                    <img src="{{ $user->getAvatarURL() }}" class="profile-avatar mb-3" alt="{{ $user->username }}">
                    <div class="mon-name" style="font-size:18px">{{ $user->getDisplayName() }}</div>
                    @if($user->pronouns)
                    <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $user->pronouns }}</div>
                    @endif
                    <div style="font-size:12px;color:var(--muted);margin-top:4px">Trainer Card</div>
                </div>
            </div>
        </div>

        {{-- Main --}}
        <div>

            {{-- Rivals --}}
            @if($s['rivals'])
            <div class="card tc-section">
                <div class="card-header">Rivals defeated</div>
                <div class="card-body">
                    <div class="rival-grid">
                        @foreach($defeatedRivals as $i => $rival)
                            @if(!is_string($i))
                            @php
                                $defeated = $rival['defeated'] === 'true';
                                $rivalImg = strtolower(str_replace(' ', '_', $rival['name']));
                            @endphp
                            <div class="rival-item">
                                <div class="rival-wrap">
                                    <img class="rival-sprite{{ $defeated ? '' : ' gray' }}" src="/rivals/{{ $rivalImg }}.png" alt="{{ $rival['name'] }}">
                                    <span class="rival-status {{ $defeated ? 'defeated' : 'not-defeated' }}"></span>
                                </div>
                                <div class="rival-name">{{ $rival['name'] }}</div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Core Glitch Unlocks --}}
            @if($s['core'] && count($glitchUnlocks) > 0)
            <div class="card tc-section">
                <div class="card-header">Unlocked core glitches</div>
                <div class="card-body">
                    <div class="tc-mon-grid">
                        @foreach($glitchUnlocks as $un)
                        <div class="tc-mon-item">
                            <a href="/core:{{ urlencode($un->name) }}.html">
                                <img src="/cFront:{{ urlencode($un->name) }}.png" alt="{{ $un->name }}">
                                <span>{{ ucwords($un->name) }}</span>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Mod Glitch Unlocks --}}
            @if($s['mod'] && $modForms->count() > 0)
            <div class="card tc-section">
                <div class="card-header">Unlocked mod glitches</div>
                <div class="card-body">
                    <div class="tc-mon-grid">
                        @foreach($modForms as $un)
                        <div class="tc-mon-item">
                            <a href="/g:{{ urlencode($un->name) }}:{{ $un->id }}.html">
                                <img src="/front:{{ $un->id }}.png" alt="{{ $un->name }}">
                                <span>{{ $un->name }}</span>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Smitty Form Unlocks --}}
            @if($s['smitty'] && count($smittyUnlocks) > 0)
            <div class="card tc-section">
                <div class="card-header">Unlocked SMITTY forms</div>
                <div class="card-body">
                    <div class="tc-mon-grid">
                        @foreach($smittyUnlocks as $un)
                        @php $smittyItems = \App\Services\BuiltInService::getSmittyItemsWithIcons($un->name); @endphp
                        <div class="tc-mon-item">
                            <a href="/smittyForm:{{ urlencode($un->name) }}.html">
                                <img src="/cFront:{{ urlencode($un->name) }}.png" alt="{{ $un->name }}">
                                <span>{{ ucwords($un->name) }}</span>
                            </a>
                            @if($smittyItems)
                            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:3px;margin-top:5px">
                                @foreach($smittyItems as $item)
                                <img src="/item-icon/{{ $item['icon'] }}.png"
                                     alt="{{ $item['name'] }}" title="{{ $item['name'] }}"
                                     style="width:20px;height:20px;image-rendering:pixelated"
                                     onerror="this.style.display='none'">
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- UniSMITTY Unlocks --}}
            @if($s['unismitty'] && $uniSmitty->count() > 0)
            <div class="card tc-section">
                <div class="card-header">Unlocked UniSMITTY forms</div>
                <div class="card-body">
                    <div class="tc-mon-grid">
                        @foreach($uniSmitty as $un)
                        <div class="tc-mon-item">
                            <a href="/smitty:{{ urlencode($un->name) }}.html">
                                <img src="/cFront:{{ urlencode($un->name) }}.png" alt="{{ $un->name }}">
                                <span>{{ ucwords($un->name) }}</span>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
