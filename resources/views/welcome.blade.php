@extends('layouts.app')

@section('content')
<div class="mt-4">
    {!! \Illuminate\Support\Str::markdown("
# Welcome to PokeVoid!
PokeVoid is a fork of PokeRogue by a brand new developer. In PokeVoid you are attempting to save the world from corruption and darkness, and only you can harness the power of the void to protect everyone.

Collect physical shards of the corruption and use them to corrupt your own Pokemon in ___legendary___ ways!

Play in Gauntlet mode for an adventure chasing your rivals away into the darkness, or play in Chaos Mode, and pick your path and risk everything to find the truth!
    ") !!}
</div>
@endsection
