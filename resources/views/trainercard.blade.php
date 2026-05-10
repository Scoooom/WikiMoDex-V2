@extends('layouts.app')

@section('content')
@php
    $save = $user->getSave();
    if (is_array($save) && isset($save['er'])) {
        echo '<small>Trainer Card Error [0001]<br>Please DM scooom on Discord if you encountered this page in error.</small>';
        return;
    }
    $defeatedRivals = $save->getDefeatedRivals();
    $glitchUnlocks = $save->getGlitchUnlocks();
    $smittyUnlocks = $save->getSmittyUnlocks();
    $formUnlocks = $save->getFormUnlocks();
@endphp

<style>
img.gray {
    -webkit-filter: grayscale(1);
    filter: grayscale(1);
}
.overlayIMG {
    position: absolute;
    bottom: 0px;
    right: 0px;
    opacity: 0.15;
}
div.rivalImg {
    position: relative;
    overflow: hidden;
    width: 75px;
    height: 75px;
    display: block;
}
p.rivalName {
    left: -20%;
    position: relative;
}
</style>

<section>
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="{{ $user->getAvatarURL() }}" class="rounded-circle img-fluid" style="width: 150px;">
                    <h5 class="my-3">{{ $user->username }}</h5>
                </div>
            </div>
        </div>
        <div class="col-lg">
            <div class="card mb">
                <div class="card-body text-center">
                    <h3>Rivals Defeated</h3>
                    <div class="row">
                        @foreach($defeatedRivals as $i => $rival)
                            @if(!is_string($i))
                                @if($i > 0 && $i % 7 === 0)
                                    </div><div class="row">
                                @endif
                                @php
                                    $gray = $rival['defeated'] === 'true' ? '' : ' gray';
                                    $imgURL = $rival['defeated'] === 'true'
                                        ? '/img/green.png'
                                        : '/img/red.png';
                                    $rivalImg = strtolower(str_replace(' ', '_', $rival['name']));
                                @endphp
                                <div class="col text-center">
                                    <div class="row rivalImg">
                                        <img class="rounded-circle img-fluid{{ $gray }}"
                                            style="height: 75px; width: 75px; background-color: gray"
                                            src="/rivals/{{ $rivalImg }}.png">
                                        <img class="overlayIMG rounded-circle img-fluid"
                                            style="width: 100%; height: 100%;"
                                            src="{{ $imgURL }}">
                                    </div>
                                    <p class="rivalName">{{ $rival['name'] }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Core Glitch Unlocks --}}
    <div>&nbsp;</div>
    <div class="row">
        <div class="col-lg-4"></div>
        <div class="col-lg">
            <div class="card mb">
                <div class="card-body text-center">
                    <h3>Unlocked Core Glitch</h3>
                    <div class="row">
                        @foreach($glitchUnlocks as $i => $un)
                            @if($i > 0 && $i % 4 === 0)
                                </div><div class="row">
                            @endif
                            <div class="col">
                                <img class="rounded-circle img-fluid"
                                    style="max-height: 150px; background-color: gray"
                                    src="/cFront:{{ urlencode($un->name) }}.png">
                                <br>
                                <a href="/core:{{ urlencode($un->name) }}.html">{{ $un->name }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mod Glitch Unlocks --}}
    <div>&nbsp;</div>
    <div class="row">
        <div class="col-lg-4"></div>
        <div class="col-lg">
            <div class="card mb">
                <div class="card-body text-center">
                    <h3>Unlocked ModGlitches</h3>
                    <div class="row">
                        @php $counter = 0; @endphp
                        @foreach($formUnlocks['modFormsUnlocked'] as $unlock)
                            @php
                                $name = preg_replace('/(.*)_(.*)/', '$2', $unlock);
                                $name = str_replace(' ', '', $name);
                                $un = \App\Models\Glitch::where('name', $name)->first();
                            @endphp
                            @if($un)
                                @if($counter > 0 && $counter % 4 === 0)
                                    </div><div class="row">
                                @endif
                                <div class="col">
                                    <img class="rounded-circle img-fluid"
                                        style="max-height: 150px; background-color: gray"
                                        src="/front:{{ $un->id }}.png">
                                    <br>
                                    <a href="/g:{{ urlencode($un->name) }}:{{ $un->id }}.html">{{ $un->name }}</a>
                                </div>
                                @php $counter++; @endphp
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Smitty Form Unlocks --}}
    <div>&nbsp;</div>
    <div class="row">
        <div class="col-lg-4"></div>
        <div class="col-lg">
            <div class="card mb">
                <div class="card-body text-center">
                    <h3>Unlocked Smitty Forms</h3>
                    <div class="row">
                        @foreach($smittyUnlocks as $i => $un)
                            @if($i > 0 && $i % 4 === 0)
                                </div><div class="row">
                            @endif
                            <div class="col">
                                <img class="rounded-circle img-fluid"
                                    style="max-height: 150px; background-color: gray"
                                    src="/cFront:{{ urlencode($un->name) }}.png">
                                <br>
                                <a href="/smittyForm:{{ urlencode($un->name) }}.html">{{ $un->name }}</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- UniSMITTY Unlocks --}}
    <div>&nbsp;</div>
    <div class="row">
        <div class="col-lg-4"></div>
        <div class="col-lg">
            <div class="card mb">
                <div class="card-body text-center">
                    <h3>Unlocked UniSMITTY Forms</h3>
                    <div class="row">
                        @php $counter = 0; @endphp
                        @foreach($formUnlocks['uniSmittyUnlocks'] as $unlock)
                            @if(empty($unlock)) @continue @endif
                            @php
                                $name = preg_replace('/(.*?)_(.*)/', '$2', $unlock);
                                $name = str_replace(' ', '', $name);
                                $un = \App\Services\BuiltInService::loadSmitty($name);
                            @endphp
                            @if($un)
                                @if($counter > 0 && $counter % 4 === 0)
                                    </div><div class="row">
                                @endif
                                <div class="col">
                                    <img class="rounded-circle img-fluid"
                                        style="max-height: 150px; background-color: gray"
                                        src="/cFront:{{ urlencode($un->name) }}.png">
                                    <br>
                                    <a href="/smitty:{{ urlencode($un->name) }}.html">{{ $un->name }}</a>
                                </div>
                                @php $counter++; @endphp
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
